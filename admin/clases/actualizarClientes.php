<?php
require '../config/database.php';
require_once '../../shared/AuthGuards.php';
requireAdminAuth([
    'response_mode' => 'json',
    'redirect' => '../phpAdmin/loginAdmin.php'
]);

header('Content-Type: application/json');

$datos = ['ok' => false];

if (isset($_POST['action'])) {
    $action = (string)$_POST['action'];
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($action === 'eliminar') {
        $datos['ok'] = eliminar($id);
    } elseif ($action === 'modificar') {
        $nombres = isset($_POST['nombres']) ? trim((string)$_POST['nombres']) : '';
        $apellidos = isset($_POST['apellidos']) ? trim((string)$_POST['apellidos']) : '';
        $email = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
        $telefono = isset($_POST['telefono']) ? trim((string)$_POST['telefono']) : '';
        $direccion = isset($_POST['direccion']) ? trim((string)$_POST['direccion']) : '';
        $datos['ok'] = modificar($id, $nombres, $apellidos, $email, $telefono, $direccion);
    } elseif ($action === 'alta') {
        $datos['ok'] = alta($id);
    }
}

echo json_encode($datos);

function eliminar($id) {
    if ($id <= 0) {
        return false;
    }

    $db = new Database();
    $con = $db->conectar();

    try {
        $con->beginTransaction();

        $contexto = obtenerContextoCliente($id, $con);
        if (!$contexto) {
            $con->rollBack();
            return false;
        }

        $queryCliente = $con->prepare("UPDATE clientes SET estatus = 0, fecha_baja = NOW(), fecha_modifica = NOW() WHERE id = ?");
        $okCliente = $queryCliente->execute([$id]);

        $queryUsuario = $con->prepare("UPDATE usuarios SET activacion = 0 WHERE id_cliente = ?");
        $okUsuario = $queryUsuario->execute([$id]);

        $okAuth = actualizarAuthEstatus($contexto, 0, $con);

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
    if ($id <= 0) {
        return false;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $db = new Database();
    $con = $db->conectar();

    $contexto = obtenerContextoCliente($id, $con);
    if (!$contexto) {
        return false;
    }

    if (emailExiste($email, $id, $contexto, $con)) {
        return false;
    }

    try {
        $con->beginTransaction();

        $query = $con->prepare("UPDATE clientes SET nombres = ?, apellidos = ?, email = ?, telefono = ?, direccion = ?, fecha_modifica = NOW() WHERE id = ?");
        $okCliente = $query->execute([$nombres, $apellidos, $email, $telefono, $direccion, $id]);

        $okAuth = actualizarAuthEmail($contexto, $email, $con);

        if ($okCliente && $okAuth) {
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

function alta($id) {
    if ($id <= 0) {
        return false;
    }

    $db = new Database();
    $con = $db->conectar();

    try {
        $con->beginTransaction();

        $contexto = obtenerContextoCliente($id, $con);
        if (!$contexto) {
            $con->rollBack();
            return false;
        }

        $queryCliente = $con->prepare("UPDATE clientes SET estatus = 1, fecha_modifica = NOW() WHERE id = ?");
        $okCliente = $queryCliente->execute([$id]);

        $queryUsuario = $con->prepare("UPDATE usuarios SET activacion = 1 WHERE id_cliente = ?");
        $okUsuario = $queryUsuario->execute([$id]);

        $okAuth = actualizarAuthEstatus($contexto, 1, $con);

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

function emailExiste($email, $id, array $contexto, PDO $con) {
    $query = $con->prepare("SELECT id FROM clientes WHERE email = ? AND id != ?");
    $query->execute([$email, $id]);

    if ($query->fetchColumn()) {
        return true;
    }

    $excludeIds = array_map('intval', $contexto['auth_ids']);
    $sqlAuth = "SELECT id FROM auth_users WHERE role = 'customer' AND email_login = ?";
    $params = [$email];

    if (count($excludeIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
        $sqlAuth .= " AND id NOT IN ($placeholders)";
        foreach ($excludeIds as $authId) {
            $params[] = $authId;
        }
    }

    $sqlAuth .= " LIMIT 1";
    $queryAuth = $con->prepare($sqlAuth);
    $queryAuth->execute($params);

    return $queryAuth->fetchColumn() ? true : false;
}

function obtenerContextoCliente(int $clienteId, PDO $con): ?array
{
    $sqlCliente = $con->prepare("SELECT id, email FROM clientes WHERE id = ? LIMIT 1");
    $sqlCliente->execute([$clienteId]);
    $cliente = $sqlCliente->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        return null;
    }

    $sqlUsuarios = $con->prepare("SELECT id FROM usuarios WHERE id_cliente = ?");
    $sqlUsuarios->execute([$clienteId]);
    $userIds = $sqlUsuarios->fetchAll(PDO::FETCH_COLUMN);
    $userIds = array_values(array_filter(array_map('intval', $userIds), function ($val) {
        return $val > 0;
    }));

    $email = (string)($cliente['email'] ?? '');

    $whereParts = ["legacy_cliente_id = ?"];
    $params = [$clienteId];

    if (count($userIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $whereParts[] = "legacy_usuario_id IN ($placeholders)";
        foreach ($userIds as $userId) {
            $params[] = $userId;
        }
    }

    if ($email !== '') {
        $whereParts[] = "email_login = ?";
        $params[] = $email;
    }

    $sqlAuth = "SELECT id FROM auth_users WHERE role = 'customer' AND (" . implode(' OR ', $whereParts) . ")";
    $queryAuth = $con->prepare($sqlAuth);
    $queryAuth->execute($params);
    $authIds = $queryAuth->fetchAll(PDO::FETCH_COLUMN);
    $authIds = array_values(array_filter(array_map('intval', $authIds), function ($val) {
        return $val > 0;
    }));

    return [
        'cliente_id' => $clienteId,
        'email' => $email,
        'user_ids' => $userIds,
        'auth_ids' => $authIds
    ];
}

function actualizarAuthEstatus(array $contexto, int $isActive, PDO $con): bool
{
    $whereParts = ["legacy_cliente_id = ?"];
    $params = [(int)$contexto['cliente_id']];

    $userIds = $contexto['user_ids'];
    if (count($userIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $whereParts[] = "legacy_usuario_id IN ($placeholders)";
        foreach ($userIds as $userId) {
            $params[] = (int)$userId;
        }
    }

    $email = (string)$contexto['email'];
    if ($email !== '') {
        $whereParts[] = "email_login = ?";
        $params[] = $email;
    }

    $sql = "UPDATE auth_users SET is_active = ?, updated_at = NOW() WHERE role = 'customer' AND (" . implode(' OR ', $whereParts) . ")";
    array_unshift($params, $isActive);

    $query = $con->prepare($sql);
    return $query->execute($params);
}

function actualizarAuthEmail(array $contexto, string $nuevoEmail, PDO $con): bool
{
    $whereParts = ["legacy_cliente_id = ?"];
    $params = [(int)$contexto['cliente_id']];

    $userIds = $contexto['user_ids'];
    if (count($userIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $whereParts[] = "legacy_usuario_id IN ($placeholders)";
        foreach ($userIds as $userId) {
            $params[] = (int)$userId;
        }
    }

    $emailActual = (string)$contexto['email'];
    if ($emailActual !== '') {
        $whereParts[] = "email_login = ?";
        $params[] = $emailActual;
    }

    $sql = "UPDATE auth_users SET email_login = ?, updated_at = NOW() WHERE role = 'customer' AND (" . implode(' OR ', $whereParts) . ")";
    array_unshift($params, $nuevoEmail);

    $query = $con->prepare($sql);
    return $query->execute($params);
}
?>

