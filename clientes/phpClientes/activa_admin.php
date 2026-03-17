<?php

require '../config/database.php';

$id = trim((string)($_GET['id'] ?? ''));
$token = trim((string)($_GET['token'] ?? ''));

if (!ctype_digit($id) || $id === '' || !ctype_xdigit($token) || strlen($token) !== 40) {
    header('Location: index.php');
    exit;
}

$db = new Database();
$con = $db->conectar();

try {
    $con->beginTransaction();

    $sqlAdmin = $con->prepare('SELECT id FROM admin WHERE id = ? AND token_password = ? AND password_request = 1 LIMIT 1');
    $sqlAdmin->execute([(int)$id, $token]);

    if (!$sqlAdmin->fetchColumn()) {
        $con->rollBack();
        echo 'No existe un registro valido para activar al administrador.';
        exit;
    }

    $updateAdmin = $con->prepare("UPDATE admin SET activo = 1, token_password = '', password_request = 0 WHERE id = ?");
    $okAdmin = $updateAdmin->execute([(int)$id]);

    $updateAuth = $con->prepare("UPDATE auth_users SET is_active = 1, updated_at = NOW() WHERE role = 'admin' AND legacy_admin_id = ?");
    $okAuth = $updateAuth->execute([(int)$id]);

    if ($okAdmin && $okAuth) {
        $con->commit();
        echo 'Cuenta de administrador activada con exito.';
        exit;
    }

    $con->rollBack();
    echo 'Error al activar la cuenta de administrador.';
} catch (Throwable $e) {
    if ($con->inTransaction()) {
        $con->rollBack();
    }
    echo 'Error al activar la cuenta de administrador.';
}
