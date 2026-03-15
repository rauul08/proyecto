<?php

require '../config/config.php';
require '../config/database.php';
require 'clienteFunciones.php';
require_once '../../shared/AuthGuards.php';

header('Content-Type: application/json');
requireCustomerAuth([
    'response_mode' => 'json',
    'redirect' => '../phpClientes/login.php'
]);

$db = new Database();
$con = $db->conectar();

$authUserId = (int) ($_SESSION['auth_user_id'] ?? 0);
$userId = (int) $_SESSION['user_id'];
$clienteId = (int) $_SESSION['user_cliente'];
$action = $_POST['action'] ?? $_GET['action'] ?? 'obtener';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'csrf') {
    echo json_encode([
        'ok' => true,
        'csrf_token' => csrfToken('perfil_endpoint')
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'obtener') {
    $perfil = obtenerPerfilCliente($clienteId, $con);

    if ($perfil === null) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Perfil no encontrado']);
        exit;
    }

    echo json_encode([
        'ok' => true,
        'perfil' => $perfil
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Metodo no permitido']);
    exit;
}

if (!validaCsrfToken('perfil_endpoint', $_POST['csrf_token'] ?? null)) {
    http_response_code(419);
    echo json_encode(['ok' => false, 'error' => 'Token CSRF invalido']);
    exit;
}

if ($action === 'actualizar') {
    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if (esNulo([$nombres, $apellidos, $email, $telefono, $direccion])) {
        echo json_encode(['ok' => false, 'error' => 'Todos los campos son obligatorios']);
        exit;
    }

    if (!esEmail($email)) {
        echo json_encode(['ok' => false, 'error' => 'Correo electronico no valido']);
        exit;
    }

    if (!emailPerfilDisponible($email, $clienteId, $con)) {
        echo json_encode(['ok' => false, 'error' => 'El correo electronico ya esta en uso']);
        exit;
    }

    $ok = actualizarPerfilCliente($clienteId, [
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'email' => $email,
        'telefono' => $telefono,
        'direccion' => $direccion
    ], $con);

    echo json_encode(['ok' => $ok]);
    exit;
}

if ($action === 'cambiarPassword') {
    $passwordActual = trim($_POST['password_actual'] ?? '');
    $passwordNueva = trim($_POST['password_nueva'] ?? '');
    $passwordConfirmar = trim($_POST['password_confirmar'] ?? '');

    if (esNulo([$passwordActual, $passwordNueva, $passwordConfirmar])) {
        echo json_encode(['ok' => false, 'error' => 'Todos los campos son obligatorios']);
        exit;
    }

    if (!validaPassword($passwordNueva, $passwordConfirmar)) {
        echo json_encode(['ok' => false, 'error' => 'Las contrasenas no coinciden']);
        exit;
    }

    if (strlen($passwordNueva) < 8) {
        echo json_encode(['ok' => false, 'error' => 'La nueva contrasena debe tener al menos 8 caracteres']);
        exit;
    }

    if (!verificarPasswordActual($authUserId, $userId, $clienteId, $passwordActual, $con)) {
        echo json_encode(['ok' => false, 'error' => 'Contrasena actual incorrecta']);
        exit;
    }

    $ok = cambiarPasswordPerfil($authUserId, $userId, $clienteId, password_hash($passwordNueva, PASSWORD_DEFAULT), $con);
    echo json_encode(['ok' => $ok]);
    exit;
}

if ($action === 'desactivar') {
    $passwordActual = trim($_POST['password_actual'] ?? '');

    if ($passwordActual === '') {
        echo json_encode(['ok' => false, 'error' => 'Debes confirmar tu contrasena']);
        exit;
    }

    if (!verificarPasswordActual($authUserId, $userId, $clienteId, $passwordActual, $con)) {
        echo json_encode(['ok' => false, 'error' => 'Contrasena actual incorrecta']);
        exit;
    }

    $ok = desactivarCuentaCliente($authUserId, $userId, $clienteId, $con);

    if ($ok) {
        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_cliente']);
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    echo json_encode(['ok' => $ok]);
    exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Accion no valida']);
