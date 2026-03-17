<?php
require '../config/database.php';
require_once '../../shared/AuthGuards.php';
require_once '../../clientes/clases/Mailer.php';
require_once '../../clientes/config/config.php';

requireAdminAuth([
    'response_mode' => 'json',
    'redirect' => '../phpAdmin/loginAdmin.php'
]);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'ok' => false,
        'error' => 'Metodo no permitido.'
    ]);
    exit;
}

$rol = strtolower(trim((string)($_POST['rol'] ?? '')));
$usuario = trim((string)($_POST['usuario'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$password = trim((string)($_POST['password'] ?? ''));
$repassword = trim((string)($_POST['repassword'] ?? ''));

$nombres = trim((string)($_POST['nombres'] ?? ''));
$apellidos = trim((string)($_POST['apellidos'] ?? ''));
$telefono = trim((string)($_POST['telefono'] ?? ''));
$direccion = trim((string)($_POST['direccion'] ?? ''));
$nombreAdmin = trim((string)($_POST['nombre_admin'] ?? ''));

$errors = [];

if ($rol !== 'customer' && $rol !== 'admin') {
    $errors[] = 'Debe seleccionar un rol valido.';
}

if ($usuario === '' || $email === '' || $password === '' || $repassword === '') {
    $errors[] = 'Usuario, correo y contrasena son obligatorios.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'La direccion de correo no es valida.';
}

if ($password !== $repassword) {
    $errors[] = 'Las contrasenas no coinciden.';
}

if ($rol === 'customer') {
    if ($nombres === '' || $apellidos === '' || $telefono === '' || $direccion === '') {
        $errors[] = 'Para clientes debe capturar nombres, apellidos, telefono y direccion.';
    }
}

if (count($errors) > 0) {
    echo json_encode([
        'ok' => false,
        'error' => implode(' ', $errors)
    ]);
    exit;
}

$db = new Database();
$con = $db->conectar();

if (usuarioExisteSistema($usuario, $con)) {
    echo json_encode([
        'ok' => false,
        'error' => 'El nombre de usuario ya existe.'
    ]);
    exit;
}

if (emailExisteSistema($email, $con)) {
    echo json_encode([
        'ok' => false,
        'error' => 'El correo electronico ya esta registrado.'
    ]);
    exit;
}

$mailer = new Mailer();
$passHash = password_hash($password, PASSWORD_DEFAULT);

try {
    $con->beginTransaction();

    if ($rol === 'customer') {
        $token = generarTokenHex(64);

        $sqlCliente = $con->prepare(
            'INSERT INTO clientes (uid, nombres, apellidos, email, telefono, direccion, estatus, registro_pedidos, fecha_alta) VALUES (UUID(), ?,?,?,?,?,1,0,NOW())'
        );
        $okCliente = $sqlCliente->execute([$nombres, $apellidos, $email, $telefono, $direccion]);

        if (!$okCliente) {
            throw new Exception('No fue posible registrar el cliente.');
        }

        $clienteId = (int)$con->lastInsertId();

        $sqlUsuario = $con->prepare('INSERT INTO usuarios (usuario, password, token, id_cliente) VALUES (?,?,?,?)');
        $okUsuario = $sqlUsuario->execute([$usuario, $passHash, $token, $clienteId]);

        if (!$okUsuario) {
            throw new Exception('No fue posible registrar el usuario cliente.');
        }

        $usuarioId = (int)$con->lastInsertId();

        $sqlAuth = $con->prepare(
            "INSERT INTO auth_users (uid, username, email_login, password_hash, role, is_active, failed_attempts, legacy_usuario_id, legacy_cliente_id, created_at, updated_at) VALUES (UUID(), ?, ?, ?, 'customer', 0, 0, ?, ?, NOW(), NOW())"
        );
        $okAuth = $sqlAuth->execute([$usuario, $email, $passHash, $usuarioId, $clienteId]);

        if (!$okAuth) {
            throw new Exception('No fue posible registrar al cliente en auth_users.');
        }

        $url = SITE_URL . '/activa_cliente.php?id=' . $usuarioId . '&token=' . $token;
        $nombreDestino = $nombres;
        $asunto = 'Activar cuenta - JIREH FOODS';
    } else {
        $token = generarTokenHex(40);
        if ($nombreAdmin === '') {
            $nombreAdmin = $usuario;
        }

        $sqlAdmin = $con->prepare(
            'INSERT INTO admin (usuario, password, nombre, email, token_password, password_request, activo, fecha_alta) VALUES (?,?,?,?,?,1,0,NOW())'
        );
        $okAdmin = $sqlAdmin->execute([$usuario, $passHash, $nombreAdmin, $email, $token]);

        if (!$okAdmin) {
            throw new Exception('No fue posible registrar el usuario administrador.');
        }

        $adminId = (int)$con->lastInsertId();

        $sqlAuth = $con->prepare(
            "INSERT INTO auth_users (uid, username, email_login, password_hash, role, is_active, failed_attempts, legacy_admin_id, created_at, updated_at) VALUES (UUID(), ?, ?, ?, 'admin', 0, 0, ?, NOW(), NOW())"
        );
        $okAuth = $sqlAuth->execute([$usuario, $email, $passHash, $adminId]);

        if (!$okAuth) {
            throw new Exception('No fue posible registrar al administrador en auth_users.');
        }

        $url = SITE_URL . '/activa_admin.php?id=' . $adminId . '&token=' . $token;
        $nombreDestino = $nombreAdmin;
        $asunto = 'Activar cuenta de administrador - JIREH FOODS';
    }

    $cuerpo = "Estimado/a {$nombreDestino}:<br>Para activar la cuenta creada desde administracion, de click en la siguiente liga: <a href='{$url}'>Activar Cuenta</a>";

    if (!$mailer->enviarEmail($email, $asunto, $cuerpo)) {
        throw new Exception('No fue posible enviar el correo de activacion.');
    }

    $con->commit();

    echo json_encode([
        'ok' => true,
        'message' => 'Usuario creado correctamente. Se envio el correo de activacion.'
    ]);
} catch (Throwable $e) {
    if ($con->inTransaction()) {
        $con->rollBack();
    }

    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}

function usuarioExisteSistema(string $usuario, PDO $con): bool
{
    $sqlAuth = $con->prepare('SELECT id FROM auth_users WHERE username = ? LIMIT 1');
    $sqlAuth->execute([$usuario]);
    if ($sqlAuth->fetchColumn()) {
        return true;
    }

    $sqlUser = $con->prepare('SELECT id FROM usuarios WHERE usuario = ? LIMIT 1');
    $sqlUser->execute([$usuario]);
    if ($sqlUser->fetchColumn()) {
        return true;
    }

    $sqlAdmin = $con->prepare('SELECT id FROM admin WHERE usuario = ? LIMIT 1');
    $sqlAdmin->execute([$usuario]);
    return (bool)$sqlAdmin->fetchColumn();
}

function emailExisteSistema(string $email, PDO $con): bool
{
    $sqlAuth = $con->prepare('SELECT id FROM auth_users WHERE email_login = ? LIMIT 1');
    $sqlAuth->execute([$email]);
    if ($sqlAuth->fetchColumn()) {
        return true;
    }

    $sqlClientes = $con->prepare('SELECT id FROM clientes WHERE email = ? LIMIT 1');
    $sqlClientes->execute([$email]);
    if ($sqlClientes->fetchColumn()) {
        return true;
    }

    $sqlAdmin = $con->prepare('SELECT id FROM admin WHERE email = ? LIMIT 1');
    $sqlAdmin->execute([$email]);
    return (bool)$sqlAdmin->fetchColumn();
}

function generarTokenHex(int $length): string
{
    if ($length < 8) {
        $length = 8;
    }

    $bytes = (int)ceil($length / 2);

    try {
        $token = bin2hex(random_bytes($bytes));
    } catch (Exception $e) {
        $token = hash('sha256', uniqid((string)mt_rand(), true));
    }

    return substr($token, 0, $length);
}
