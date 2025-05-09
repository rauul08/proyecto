<?php
require '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $usuario = isset($_POST['usuario']) ? $_POST['usuario'] : '';
    $asunto = isset($_POST['asunto']) ? $_POST['asunto'] : '';
    $comentario = $_POST['comentario'];

    $db = new Database();
    $con = $db->conectar();

    $sql = "INSERT INTO comentarios (nombre, email, usuario, asunto, comentario) VALUES (:nombre, :email, :usuario, :asunto, :comentario)";
    $stmt = $con->prepare($sql);

    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':asunto', $asunto);
    $stmt->bindParam(':comentario', $comentario);

    if ($stmt->execute()) {
        header("Location: ../../clientes/html/conócenos.html");
        exit();
    } else {
        echo "Error al enviar el comentario";
    }
}
?>
