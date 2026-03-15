<?php

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
