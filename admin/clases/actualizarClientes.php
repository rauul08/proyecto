<?php
require '../config/database.php';
require_once '../../shared/AuthGuards.php';
requireAdminAuth([
    'response_mode' => 'json',
    'redirect' => '../phpAdmin/loginAdmin.php'
]);

if(isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = isset($_POST['id']) ? $_POST['id'] : 0;

    if($action == 'eliminar') {
        $datos['ok'] = eliminar($id);
    } elseif ($action == 'modificar') {
        $nombres = isset($_POST['nombres']) ? $_POST['nombres'] : '';
        $apellidos = isset($_POST['apellidos']) ? $_POST['apellidos'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $telefono = isset($_POST['telefono']) ? $_POST['telefono'] : '';
        $direccion = isset($_POST['direccion']) ? $_POST['direccion'] : '';
        $datos['ok'] = modificar($id, $nombres, $apellidos, $email, $telefono, $direccion);
    } elseif ($action == 'alta') {
        $datos['ok'] = alta($id);
    } else {
        $datos['ok'] = false;
    }
} else {
    $datos['ok'] = false;
}

echo json_encode($datos);

function eliminar($id) {
    $db = new Database();
    $con = $db->conectar();

    try {
        $con->beginTransaction();

        $queryCliente = $con->prepare("UPDATE clientes SET estatus = 0, fecha_baja = NOW(), fecha_modifica = NOW() WHERE id = ?");
        $okCliente = $queryCliente->execute([$id]);

        $queryUsuario = $con->prepare("UPDATE usuarios SET activacion = 0 WHERE id_cliente = ?");
        $okUsuario = $queryUsuario->execute([$id]);

        $queryAuth = $con->prepare("UPDATE auth_users SET is_active = 0, updated_at = NOW() WHERE role = 'customer' AND legacy_cliente_id = ?");
        $okAuth = $queryAuth->execute([$id]);

        if ($okCliente && $okUsuario && $okAuth) {
            $con->commit();
            return true;
        }

        $con->rollBack();
        return false;
    } catch (Exception $e) {
        if ($con->inTransaction()) {
            $con->rollBack();
        }
        return false;
    }
}

function modificar($id, $nombres, $apellidos, $email, $telefono, $direccion) {
    if (emailExiste($email, $id)) {
        return false;
    }

    $db = new Database();
    $con = $db->conectar();

    $query = $con->prepare("UPDATE clientes SET nombres = ?, apellidos = ?, email = ?, telefono = ?, direccion = ?, fecha_modifica = NOW() WHERE id = ?");
    return $query->execute([$nombres, $apellidos, $email, $telefono, $direccion, $id]);
}

function alta($id) {
    $db = new Database();
    $con = $db->conectar();

    try {
        $con->beginTransaction();

        $queryCliente = $con->prepare("UPDATE clientes SET estatus = 1, fecha_modifica = NOW() WHERE id = ?");
        $okCliente = $queryCliente->execute([$id]);

        $queryUsuario = $con->prepare("UPDATE usuarios SET activacion = 1 WHERE id_cliente = ?");
        $okUsuario = $queryUsuario->execute([$id]);

        $queryAuth = $con->prepare("UPDATE auth_users SET is_active = 1, updated_at = NOW() WHERE role = 'customer' AND legacy_cliente_id = ?");
        $okAuth = $queryAuth->execute([$id]);

        if ($okCliente && $okUsuario && $okAuth) {
            $con->commit();
            return true;
        }

        $con->rollBack();
        return false;
    } catch (Exception $e) {
        if ($con->inTransaction()) {
            $con->rollBack();
        }
        return false;
    }
}

function emailExiste($email, $id) {
    $db = new Database();
    $con = $db->conectar();

    $query = $con->prepare("SELECT id FROM clientes WHERE email = ? AND id != ?");
    $query->execute([$email, $id]);
    return $query->fetch() ? true : false;
}
?>

