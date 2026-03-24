<?php

require_once __DIR__ . '/SessionManager.php';

function ensureSessionStarted(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function clearLegacyAuthSession(): void
{
    unset(
        $_SESSION['auth_user_id'],
        $_SESSION['auth_role'],
        $_SESSION['user_id'],
        $_SESSION['user_name'],
        $_SESSION['user_type'],
        $_SESSION['user_cliente']
    );
}

function normalizeRole(string $role): string
{
    $role = strtolower(trim($role));
    if ($role === 'cliente') {
        return 'customer';
    }
    if ($role === 'usuario') {
        return 'customer';
    }
    return $role;
}

function authenticateFromAuthUsers(string $usuario, string $password, PDO $con, array $allowRoles, array $redirectMap): ?array
{
    try {
        $sql = $con->prepare("SELECT id, username, password_hash, role, is_active, legacy_admin_id, legacy_usuario_id, legacy_cliente_id FROM auth_users WHERE username = ? LIMIT 1");
        $sql->execute([$usuario]);
        $row = $sql->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [
            'ok' => false,
            'error' => 'No fue posible validar el acceso. Contacte al soporte tecnico.'
        ];
    }

    if (!$row) {
        return null;
    }

    $role = normalizeRole((string) ($row['role'] ?? ''));
    $isActive = isset($row['is_active']) ? (int) $row['is_active'] : 0;
    $hash = (string) ($row['password_hash'] ?? '');

    if ($isActive !== 1) {
        return [
            'ok' => false,
            'error' => 'La cuenta no esta activa.'
        ];
    }

    if (!password_verify($password, $hash)) {
        return [
            'ok' => false,
            'error' => 'El usuario y/o contrasena son incorrectos.'
        ];
    }

    if (!in_array($role, $allowRoles, true)) {
        return [
            'ok' => false,
            'error' => 'No tiene permisos para ingresar desde esta pantalla.'
        ];
    }

    session_regenerate_id(true);
    clearLegacyAuthSession();

    $_SESSION['auth_user_id'] = (int) $row['id'];
    $_SESSION['auth_role'] = $role;

    // Compatibilidad con el codigo existente mientras se completa la migracion.
    if ($role === 'admin') {
        $_SESSION['user_id'] = (int) ($row['legacy_admin_id'] ?: $row['id']);
        $_SESSION['user_name'] = (string) $row['username'];
        $_SESSION['user_type'] = 'admin';
    } else {
        $_SESSION['user_id'] = (int) ($row['legacy_usuario_id'] ?: $row['id']);
        $_SESSION['user_name'] = (string) $row['username'];
        $_SESSION['user_cliente'] = (int) ($row['legacy_cliente_id'] ?: 0);
    }

    return [
        'ok' => true,
        'role' => $role,
        'redirect' => $redirectMap[$role] ?? $redirectMap['customer']
    ];
}

function authenticateUnified(string $usuario, string $password, PDO $con, array $options = []): array
{
    ensureSessionStarted();

    $allowRoles = $options['allow_roles'] ?? ['admin', 'customer'];
    $redirects = $options['redirects'] ?? [];
    $defaultRedirects = [
        'admin' => 'inicio.php',
        'customer' => 'index.php'
    ];
    $redirectMap = array_merge($defaultRedirects, $redirects);
    $usuario = trim($usuario);
    $password = trim($password);

    if ($usuario === '' || $password === '') {
        return [
            'ok' => false,
            'error' => 'Debe llenar todos los campos.'
        ];
    }

    $authFromUnified = authenticateFromAuthUsers($usuario, $password, $con, $allowRoles, $redirectMap);
    if ($authFromUnified !== null) {
        return $authFromUnified;
    }

    return [
        'ok' => false,
        'error' => 'El usuario y/o contrasena son incorrectos.'
    ];
}

/**
 * Registra una nueva sesión en el gestor de sesiones múltiples.
 * DEBE llamarse inmediatamente después de una autenticación exitosa.
 *
 * @param string $authUserId ID del usuario (desde $_SESSION['auth_user_id'])
 * @param PDO $con Conexión a la BD
 * @param bool $enforceLimit Si true, cierra sesiones antiguas si se excede el límite
 * @return array ['ok' => bool, 'session_id' => string, ...]
 */
function registerUserSession(string $authUserId, PDO $con, bool $enforceLimit = true): array
{
    ensureSessionStarted();

    try {
        $sessionManager = new SessionManager($con);
        $sessionId = session_id();
        $ipAddress = getClientIpAddress();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if ($enforceLimit) {
            $result = $sessionManager->createSessionWithLimit($authUserId, $sessionId, $ipAddress, $userAgent);
        } else {
            $result = [
                'ok' => $sessionManager->createSession($authUserId, $sessionId, $ipAddress, $userAgent),
                'session_id' => $sessionId,
                'closed_sessions' => 0
            ];
        }

        return $result;
    } catch (Throwable $e) {
        error_log("registerUserSession - Error: " . $e->getMessage());
        return [
            'ok' => false,
            'error' => 'No se pudo registrar la sesión: ' . $e->getMessage()
        ];
    }
}

/**
 * Obtiene la dirección IP del cliente
 *
 * @return string IP address
 */
function getClientIpAddress(): string
{
    // Verificar si usa proxy
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP']; // Cloudflare
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED'])) {
        return $_SERVER['HTTP_X_FORWARDED'];
    }
    if (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_FORWARDED_FOR'];
    }
    if (!empty($_SERVER['HTTP_FORWARDED'])) {
        return $_SERVER['HTTP_FORWARDED'];
    }

    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Obtiene el SessionManager (útil en endpoints).
 *
 * @param PDO $con Conexión a la BD
 * @return SessionManager
 */
function getSessionManager(PDO $con): SessionManager
{
    return new SessionManager($con);
}

/**
 * Cierra la sesión actual y registra en el gestor.
 *
 * @param PDO $con Conexión a la BD
 * @param string|null $reason Motivo del logout
 * @return bool
 */
function logoutCurrentSession(PDO $con, ?string $reason = null): bool
{
    ensureSessionStarted();

    try {
        $sessionId = session_id();
        $sessionManager = new SessionManager($con);

        $result = $sessionManager->closeSession($sessionId, $reason ?? 'logout');

        // Destruir sesión PHP
        $_SESSION = [];
        session_destroy();

        return $result;
    } catch (Throwable $e) {
        error_log("logoutCurrentSession - Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Actualiza la última actividad de la sesión actual.
 * DEBE llamarse en cada request autenticado para evitar timeout.
 *
 * @param PDO $con Conexión a la BD
 * @return bool
 */
function updateSessionActivity(PDO $con): bool
{
    ensureSessionStarted();

    try {
        $sessionId = session_id();
        $sessionManager = new SessionManager($con);
        return $sessionManager->updateLastActivity($sessionId);
    } catch (Throwable $e) {
        error_log("updateSessionActivity - Error: " . $e->getMessage());
        return false;
    }
}
