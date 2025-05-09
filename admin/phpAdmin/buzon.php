<?php
require '../config/database.php';

$db = new Database();
$con = $db->conectar();

$sql = "SELECT id, nombre, email, usuario, asunto, comentario, fecha FROM comentarios ORDER BY fecha ASC";
$stmt = $con->prepare($sql);
$stmt->execute();
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include_once '../layoutAdmin/header.php'; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" style="width: 100%;" id="tblBuzon">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombres</th>
                        <th>Correo Electrónico</th>
                        <th>Usuario</th>
                        <th>Asunto</th>
                        <th>Comentario</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Verifica si hay resultados y los muestra en la tabla
                if (count($resultado) == 0) {
                    echo "<tr><td colspan='7'>No hay comentarios registrados</td></tr>";
                } else {
                    foreach ($resultado as $row) {
                        $id = $row['id'];
                        $nombre = $row['nombre'];
                        $email = $row['email'];
                        $usuario = $row['usuario'];
                        $asunto = $row['asunto'];
                        $comentario = $row['comentario'];
                        $fecha = $row['fecha'];
                ?>
                <tr data-id="<?php echo $id; ?>">
                    <td><?php echo $id; ?></td>
                    <td><?php echo $nombre; ?></td>
                    <td><?php echo $email; ?></td>
                    <td><?php echo $usuario; ?></td>
                    <td><?php echo $asunto; ?></td>
                    <td><?php echo $comentario; ?></td>
                    <td><?php echo $fecha; ?></td>
                </tr>
                <?php } ?>
                </tbody>
                <?php } ?>
            </table>
        </div>
    </div>
</div>

<?php include_once '../layoutAdmin/footer.php'; ?>