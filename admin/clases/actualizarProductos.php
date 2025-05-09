<?php
require '../config/database.php';

header('Content-Type: application/json');

$response = ['ok' => false];

try {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $id = isset($_POST['id']) ? $_POST['id'] : 0;

        $db = new Database();
        $con = $db->conectar();

        if ($action == 'eliminar') {
            $response['ok'] = eliminar($con, $id);
        } elseif ($action == 'editar') {
            if (isset($_POST['nombre']) && isset($_POST['descripcion']) && isset($_POST['precio'])) {
                $nombre = $_POST['nombre'];
                $descripcion = $_POST['descripcion'];
                $precio = $_POST['precio'];
                $response['ok'] = editar($con, $id, $nombre, $descripcion, $precio);
            } else {
                $response['error'] = 'Faltan datos';
            }
        }
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);

function eliminar($con, $id) {
    $query = $con->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
    return $query->execute([$id]);
}

function editar($con, $id, $nombre, $descripcion, $precio) {
    $query = $con->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ? WHERE id = ?");
    return $query->execute([$nombre, $descripcion, $precio, $id]);
}
?>
