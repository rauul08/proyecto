<?php

require_once __DIR__ . '/AuthService.php';

function currentAuthRole(): ?string
{
    ensureSessionStarted();

    if (!empty($_SESSION['auth_role'])) {
        return normalizeRole((string) $_SESSION['auth_role']);
    }

    if (!empty($_SESSION['user_type']) && strtolower((string) $_SESSION['user_type']) === 'admin') {
        return 'admin';
    }

    if (!empty($_SESSION['user_cliente'])) {
        return 'customer';
    }

    return null;
}

function isAdminAuthenticated(): bool
{
    return currentAuthRole() === 'admin';
}

function isCustomerAuthenticated(): bool
{
    return currentAuthRole() === 'customer';
}

function denyUnauthorized(string $responseMode, string $redirectUrl, string $message = 'No autenticado'): void
{
    if ($responseMode === 'json') {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(401);
        }
        echo json_encode([
            'ok' => false,
            'error' => $message
        ]);
        exit;
    }

    header('Location: ' . $redirectUrl);
    exit;
}

function requireAdminAuth(array $options = []): void
{
    $responseMode = $options['response_mode'] ?? 'redirect';
    $redirectUrl = $options['redirect'] ?? '../phpAdmin/loginAdmin.php';

    if (isAdminAuthenticated()) {
        return;
    }

    denyUnauthorized($responseMode, $redirectUrl, 'Acceso de administrador requerido');
}

function requireCustomerAuth(array $options = []): void
{
    $responseMode = $options['response_mode'] ?? 'redirect';
    $redirectUrl = $options['redirect'] ?? 'login.php';

    if (isCustomerAuthenticated()) {
        return;
    }

    denyUnauthorized($responseMode, $redirectUrl, 'Debe iniciar sesion para continuar');
}

function requireAuth(array $options = []): void
{
    $responseMode = $options['response_mode'] ?? 'redirect';
    $redirectUrl = $options['redirect'] ?? 'login.php';
    $requiredRoles = $options['roles'] ?? ['admin', 'customer'];

    $currentRole = currentAuthRole();

    if ($currentRole !== null && in_array($currentRole, $requiredRoles, true)) {
        return;
    }

    $message = isset($options['message']) ? $options['message'] : 'Acceso denegado';
    denyUnauthorized($responseMode, $redirectUrl, $message);
}

function requireRole(string $role, array $options = []): void
{
    $options['roles'] = [$role];
    $options['message'] = $options['message'] ?? "Acceso de $role requerido";
    requireAuth($options);
}

/**
 * Matriz de permisos de rutas del sitio.
 *
 * @return array<string, string[]>
 */
function getRoutePermissionMatrix(): array
{
    return [
        // Rutas públicas
        'clientes/phpClientes/index.php' => ['guest', 'customer', 'admin'],
        'clientes/phpClientes/details.php' => ['guest', 'customer', 'admin'],

        // Rutas cliente
        'clientes/phpClientes/perfil.php' => ['customer'],
        'clientes/phpClientes/checkout.php' => ['customer'],
        'clientes/phpClientes/checkout_paquetes.php' => ['customer'],
        'clientes/phpClientes/proceso_pago.php' => ['customer'],

        // Rutas admin
        'admin/phpAdmin/inicio.php' => ['admin'],
        'admin/phpAdmin/pedidos.php' => ['admin'],
        'admin/phpAdmin/usuarios.php' => ['admin'],
        'admin/phpAdmin/productos.php' => ['admin'],
        'admin/phpAdmin/buzon.php' => ['admin'],
        'admin/charts/graficoPedidos.php' => ['admin'],
        'admin/charts/graficoCompras.php' => ['admin'],

        // Endpoints AJAX
        'clientes/clases/clienteAjax.php' => ['guest', 'customer', 'admin'],
        'clientes/clases/perfilCliente.php' => ['customer'],
        'admin/clases/actualizarClientes.php' => ['admin'],
        'admin/clases/actualizarProductos.php' => ['admin'],
        'admin/clases/actualizarPaquetes.php' => ['admin'],
        'admin/clases/crearUsuarioAdmin.php' => ['admin'],
        'admin/clases/procesarPedidos.php' => ['admin'],
    ];
}

/**
 * Verifica el acceso de la ruta actual contra la matriz de permisos.
 *
 * @param string $route Ruta relativa (path) para verificar
 * @param array $options Opciones para requireAuth (response_mode, redirect, message)
 * @return void
 */
function requireRoutePermission(string $route, array $options = []): void
{
    $matrix = getRoutePermissionMatrix();
    $normalizedRoute = str_replace('\\', '/', ltrim($route, '/'));

    $allowedRoles = $matrix[$normalizedRoute] ?? [];

    if (empty($allowedRoles)) {
        // Ruta desconocida -> restringir
        requireAuth(array_merge($options, ['message' => 'Acceso restringido', 'roles' => []]));
        return;
    }

    // Si la ruta permite 'guest', no es necesaria autenticación adicional
    if (in_array('guest', $allowedRoles, true)) {
        return;
    }

    $currentRole = currentAuthRole();
    if ($currentRole === null || !in_array($currentRole, $allowedRoles, true)) {
        $options['message'] = $options['message'] ?? 'No tienes permisos para acceder a esta ruta';
        requireAuth(array_merge($options, ['roles' => $allowedRoles]));
    }
}

/**
 * Valida que la solicitud AJAX venga de un cliente autenticado.
 * Envía respuesta JSON con código 401 si no está autenticado.
 *
 * @param string $customMessage Mensaje de error personalizado
 * @return void
 */
function requireCustomerAjax(string $customMessage = 'Cliente no autenticado'): void
{
    ensureSessionStarted();

    if (isCustomerAuthenticated()) {
        return;
    }

    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(401);
    }

    echo json_encode([
        'ok' => false,
        'error' => $customMessage,
        'code' => 'UNAUTHORIZED'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Valida que la solicitud AJAX venga de un administrador autenticado.
 * Envía respuesta JSON con código 401 si no está autenticado.
 *
 * @param string $customMessage Mensaje de error personalizado
 * @return void
 */
function requireAdminAjax(string $customMessage = 'Acceso de administrador requerido'): void
{
    ensureSessionStarted();

    if (isAdminAuthenticated()) {
        return;
    }

    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(401);
    }

    echo json_encode([
        'ok' => false,
        'error' => $customMessage,
        'code' => 'ADMIN_REQUIRED'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Valida que la solicitud sea AJAX (XMLHttpRequest).
 * Devuelve false si no es una solicitud AJAX válida.
 *
 * @return bool
 */
function isAjaxRequest(): bool
{
    return (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    );
}

/**
 * Valida que sea una solicitud AJAX válida, sino envía error JSON.
 *
 * @return void
 */
function requireAjaxRequest(): void
{
    if (isAjaxRequest()) {
        return;
    }

    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(400);
    }

    echo json_encode([
        'ok' => false,
        'error' => 'Solicitud AJAX inválida',
        'code' => 'INVALID_REQUEST'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function requireAjaxAuth(array $options = []): void
{
    requireAjaxRequest();

    $requiredRoles = $options['roles'] ?? ['admin', 'customer'];
    $currentRole = currentAuthRole();

    if ($currentRole !== null && in_array($currentRole, $requiredRoles, true)) {
        return;
    }

    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(401);
    }

    echo json_encode([
        'ok' => false,
        'error' => $options['message'] ?? 'Acceso no autorizado',
        'code' => 'UNAUTHORIZED'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Valida autenticación de cliente para endpoints AJAX.
 * Valida que sea una solicitud AJAX válida.
 *
 * @param string $customMessage Mensaje de error personalizado
 * @return void
 */
function requireCustomerAjaxAuth(string $customMessage = 'Cliente no autenticado'): void
{
    requireAjaxAuth([
        'roles' => ['customer'],
        'message' => $customMessage
    ]);
}

/**
 * Valida autenticación de admin para endpoints AJAX.
 * Valida que sea una solicitud AJAX válida.
 *
 * @param string $customMessage Mensaje de error personalizado
 * @return void
 */
function requireAdminAjaxAuth(string $customMessage = 'Acceso de administrador requerido'): void
{
    requireAjaxAuth([
        'roles' => ['admin'],
        'message' => $customMessage
    ]);
}

/**
 * Devuelve el ID del usuario autenticado actualmente.
 *
 * @return int|null ID del usuario o null si no está autenticado
 */
function getCurrentAuthUserId(): ?int
{
    ensureSessionStarted();
    return !empty($_SESSION['auth_user_id']) ? (int) $_SESSION['auth_user_id'] : null;
}

/**
 * Devuelve el nombre del usuario autenticado actualmente.
 *
 * @return string|null Nombre del usuario o null si no está autenticado
 */
function getCurrentAuthUserName(): ?string
{
    ensureSessionStarted();
    return !empty($_SESSION['user_name']) ? (string) $_SESSION['user_name'] : null;
}

/**
 * Envía una respuesta JSON de error.
 *
 * @param string $error Mensaje de error
 * @param string $code Código de error
 * @param int $httpCode Código HTTP
 * @return void
 */
function sendJsonError(string $error, string $code = 'ERROR', int $httpCode = 400): void
{
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code($httpCode);
    }

    echo json_encode([
        'ok' => false,
        'error' => $error,
        'code' => $code
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Envía una respuesta JSON exitosa.
 *
 * @param mixed $data Datos a devolver
 * @param int $httpCode Código HTTP (por defecto 200)
 * @return void
 */
function sendJsonSuccess($data = null, int $httpCode = 200): void
{
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code($httpCode);
    }

    echo json_encode([
        'ok' => true,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
