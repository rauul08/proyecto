<?php

require '../config/config.php';

if(isset($_POST['id'])){

    $id = $_POST['id'];
    $token = $_POST['token'];

    $token_tmp = hash_hmac('sha1', $id, KEY_TOKEN);

    if($token == $token_tmp) {

        if(isset($_SESSION['compras']['productos'][$id])){
            $_SESSION['compras']['productos'][$id] += 1;
        } else {

        $_SESSION['compras']['productos'][$id] = 1;
        }

        $datos['numero'] = count($_SESSION['compras']['productos']);
        $datos['ok'] = true;


    } else {
        $datos['ok'] = false;
    }
} else {
    $datos['ok'] = false;

}

echo json_encode($datos);