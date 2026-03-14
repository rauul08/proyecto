<?php

require_once '../config/database.php';
require_once 'clienteFunciones.php';

header('Content-Type: application/json');

$datos = [];

if(isset($_POST['action'])) {
    $action = $_POST['action'];

    $db = new Database();
    $con = $db->conectar();

    if($action === 'existeUsuario' && isset($_POST['usuario'])){

        $datos['ok'] = usuarioExiste(trim($_POST['usuario']), $con);
    } elseif ($action === 'existeEmail' && isset($_POST['email'])){
        $datos['ok']  = emailExiste(trim($_POST['email']), $con);
    } else {
        $datos['ok'] = false;
    }
}

echo json_encode($datos);