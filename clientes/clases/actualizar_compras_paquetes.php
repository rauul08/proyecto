<?php
require '../config/config.php';
require '../config/database.php';

if(isset($_POST['action'])) {

    $action = $_POST['action'];
    $id = isset($_POST['id']) ? $_POST['id'] : 0;

    if($action == 'added') {
        $cant = isset($_POST['cantidad']) ? $_POST['cantidad'] : 0;
        $respuesta = added($id, $cant);
        if($respuesta > 0) {
            $datos['ok'] = true; 
        } else {
            $datos['ok'] = false;
        }
        $datos['sub'] = MONEDA . number_format($respuesta, 2, '.', ',');
    } else if($action == 'eliminar') {
        $datos['ok']  = eliminar($id);

    } else {
        $datos['ok'] = false;
    }
} else {
    $datos['ok'] = false;
}

echo json_encode($datos);

function added($id, $cant) {
    $res = 0;
    if($id > 0 && $cant > 0 && is_numeric(($cant))) {
        if(isset($_SESSION['compras_paquetes']['paquetes'][$id])) {
            $_SESSION['compras_paquetes']['paquetes'][$id] = $cant;

            $db = new Database();
            $con = $db->conectar();


            $sql = $con->prepare("SELECT precio, descuento FROM paquetes WHERE id=? AND activo=1 LIMIT 1");
            $sql->execute([$id]);
            $row = $sql->fetch(PDO::FETCH_ASSOC);
            $precio = $row['precio'];
            $descuento = $row['descuento'];
            $precio_desc = $precio - (($precio * $descuento) / 100);
            $res = $cant * $precio_desc;

            return $res;
        }
    } else {
        return $res;
    }
}


function eliminar($id) {
    if($id > 0){
        if(isset($_SESSION['compras_paquetes']['paquetes'][$id])) {
            unset($_SESSION['compras_paquetes']['paquetes'][$id]);
            return true;
        }
    } else {
        return false;
    }
}