<?php
require 'config/config.php';
require 'config/database.php';
$db = new Database();
$con = $db->conectar();

print_r($_SESSION);

$response = ['ok' => false]; // Respuesta por defecto

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'eliminar') {
    $id = $_POST['id'];
    $tipo = $_POST['tipo'];

    try {
        if ($tipo === 'producto') {
            // Eliminar producto
            unset($_SESSION['compras']['productos'][$id]);
        } elseif ($tipo === 'paquete') {
            // Eliminar paquete
            unset($_SESSION['compras_paquetes']['paquetes'][$id]);
        }

        // Actualizar el total de artículos en la sesión
        $num_cart = isset($_SESSION['compras']['productos']) ? count($_SESSION['compras']['productos']) : 0;
        $num_card = isset($_SESSION['compras_paquetes']['paquetes']) ? count($_SESSION['compras_paquetes']['paquetes']) : 0;
        $total_items = $num_cart + $num_card;

        // Establecer la respuesta como exitosa
        $response['ok'] = true;
        $response['total_items'] = $total_items;

    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
