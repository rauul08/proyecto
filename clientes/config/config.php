<?php

require_once __DIR__ . '/env.php';
loadEnvFile(dirname(__DIR__) . '/.env');


//Datos envio de correo electronico
define("MAIL_HOST", env("MAIL_HOST", "smtp.gmail.com"));
define("MAIL_USER", env("MAIL_USER", ""));
define("MAIL_PASS", env("MAIL_PASS", ""));
define("MAIL_PORT", (int) env("MAIL_PORT", "465"));
define("MAIL_SMTP_DEBUG", (int) env("MAIL_SMTP_DEBUG", "0"));

define("SITE_URL", env("SITE_URL", "http://localhost/proyecto/clientes/phpClientes"));
define("KEY_TOKEN", env("KEY_TOKEN", "Jireh-Port08"));
define("MONEDA", env("MONEDA", "$") ?: "$");

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


