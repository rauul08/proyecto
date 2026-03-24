<?php
require '../config/database.php';
require_once '../../shared/AuthGuards.php';
requireAdminAjaxAuth();

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonError('Metodo no permitido.', 'METHOD_NOT_ALLOWED', 405);
}

if (!isset($_POST['id']) || !isset($_POST['proceso'])) {
    sendJsonError('Parámetros faltantes', 'MISSING_PARAMETERS', 400);
}

$id = (int) $_POST['id'];
$proceso = (int) $_POST['proceso'];

try {
    $db = new Database();
    $con = $db->conectar();

    if ($proceso === 3) {
        $sql = $con->prepare('UPDATE pedidos SET proceso = ?, fecha_finaliza = NOW() WHERE id = ?');
    } elseif ($proceso === 2) {
        $sql = $con->prepare('UPDATE pedidos SET proceso = ?, fecha_procesa = NOW() WHERE id = ?');
    } else {
        $sql = $con->prepare('UPDATE pedidos SET proceso = ? WHERE id = ?');
    }

    $updated = $sql->execute([$proceso, $id]);

    if ($updated) {
        sendJsonSuccess(['message' => 'Proceso del pedido actualizado correctamente']);
    }

    sendJsonError('Error al actualizar el proceso del pedido', 'UPDATE_FAILED', 500);
} catch (Exception $e) {
    error_log('procesarPedidos error: ' . $e->getMessage());
    sendJsonError('Error interno del servidor', 'SERVER_ERROR', 500);
}
?>
