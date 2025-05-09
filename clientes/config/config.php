<?php


//Datos envio de correo electronico
define("MAIL_HOST", "smtp.gmail.com");
define("MAIL_USER", "jiireh.bussines@gmail.com"); 
define("MAIL_PASS", "dsxhfqwjieaduyom");
define("MAIL_PORT", "465");

define("SITE_URL", "http://localhost/proyecto/clientes/phpClientes");
define("KEY_TOKEN", "Jireh-Port08");
define("MONEDA", "$");

session_start();

$num_cart = 0;
if(isset($_SESSION['compras']['productos'])){
    $num_cart = count($_SESSION['compras']['productos']);
}


$num_card = 0;

if(isset($_SESSION['compras_paquetes']['paquetes'])) {
    $num_card = count($_SESSION['compras_paquetes']['paquetes']);
}

$total_items = $num_cart + $num_card;


