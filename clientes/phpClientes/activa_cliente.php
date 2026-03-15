<?php

require '../config/config.php';
require '../config/database.php';
require '../clases/clienteFunciones.php';

$id    = trim((string) ($_GET['id']    ?? ''));
$token = trim((string) ($_GET['token'] ?? ''));

if (!ctype_digit($id) || $id === '' || !ctype_xdigit($token) || strlen($token) !== 64) {
    header("Location: index.php");
    exit;
}

$db = new Database();
$con = $db->conectar();

echo validaToken($id, $token, $con);

?>