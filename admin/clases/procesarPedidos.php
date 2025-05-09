<?php
require '../config/database.php';

if (isset($_POST['id']) && isset($_POST['proceso'])) {
    $id = $_POST['id'];
    $proceso = $_POST['proceso'];

    $db = new Database();
    $con = $db->conectar();

    if ($proceso == 3) {
        $sql = $con->prepare("UPDATE pedidos SET proceso = ?, fecha_finaliza = NOW() WHERE id = ?");
    } elseif ($proceso == 2) {
        $sql = $con->prepare("UPDATE pedidos SET proceso = ?, fecha_procesa = NOW() WHERE id = ?");
    } else {
        $sql = $con->prepare("UPDATE pedidos SET proceso = ? WHERE id = ?");
    }
    $sql->execute([$proceso, $id]);

    echo "proceso del pedido actualizado correctamente.";
} else {
    echo "Error al actualizar el proceso del pedido.";
}
?>

