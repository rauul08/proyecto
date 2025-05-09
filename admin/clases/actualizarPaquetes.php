<?php
require '../config/database.php';

// Mostrar errores de PHP para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = isset($_POST['id']) ? $_POST['id'] : 0;

    if ($action == 'eliminar') {
        $datos['ok'] = eliminar($id);
    } elseif ($action == 'editar') {
        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
        $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
        $precio = isset($_POST['precio']) ? $_POST['precio'] : 0;
        $descuento = isset($_POST['descuento']) ? $_POST['descuento'] : 0;
        $datos['ok'] = editar($id, $nombre, $descripcion, $precio, $descuento);
    }

    header('Content-Type: application/json');
    echo json_encode($datos);
} else {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'No action specified']);
}

function eliminar($id) {
    $db = new Database();
    $con = $db->conectar();

    $sql = $con->prepare("UPDATE paquetes SET activo = 0 WHERE id = ?");
    return $sql->execute([$id]);
}

function editar($id, $nombre, $descripcion, $precio, $descuento) {
    $db = new Database();
    $con = $db->conectar();

    $sql = $con->prepare("UPDATE paquetes SET nombre = ?, descripcion = ?, precio = ?, descuento = ? WHERE id = ?");
    return $sql->execute([$nombre, $descripcion, $precio, $descuento, $id]);
}
?>
