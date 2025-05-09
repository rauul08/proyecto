<?php
require '../config/database.php';

$db = new Database();
$con = $db->conectar();
$sql = $con->prepare("SELECT id, nombre, email, direccion_entrega, instrucciones_adicionales, metodo_pago, monto_total, proceso, fecha_finaliza FROM pedidos WHERE proceso = 3");
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include_once '../layoutAdmin/header.php'; ?>
<span>¨</span>
<ul class="nav nav-tabs">
  <li class="nav-item">
    <a class="nav-link"  href="pedidos.php">Pedidos</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="pedidosPendientes.php">En Proceso</a>
  </li>
  <li class="nav-item">
    <a class="nav-link active" aria-current="page" href="pedidosFinalizados.php">Finalizados</a>
  </li>
</ul>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" style="width: 100%;" id="tblPedidos">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Correo Electrónico</th>
                        <th>Dirección de Entrega</th>
                        <th>Instrucciones Adicionales</th>
                        <th>Metodo de Pago</th>
                        <th>Monto</th>
                        <!--<th>Estatus</th>!-->
                        <th>Fecha Finalizada</th>

                    </tr>
                </thead>
                <tbody> 
                <?php
                // Verifica si hay resultados y los muestra en la tabla
                if (count($resultado) == 0) {
                    echo "<tr><td colspan='7'>No hay pedidos registrados</td></tr>";
                } else {
                    foreach ($resultado as $row) {
                        $id = $row['id'];
                        $nombre = $row['nombre'];
                        $email = $row['email'];
                        $direccion_entrega = $row['direccion_entrega'];
                        $instrucciones_adicionales = $row['instrucciones_adicionales'];
                        $metodo_pago = $row['metodo_pago'];
                        $monto_total = $row['monto_total'];
                        //$proceso = $row['proceso'];
                        $fecha_finaliza = $row['fecha_finaliza'];
                ?>
                <tr data-id="<?php echo $id; ?>">
                    <td><?php echo $id; ?></td>
                    <td><?php echo $nombre; ?></td>
                    <td><?php echo $email; ?></td>
                    <td><?php echo $direccion_entrega; ?></td>
                    <td><?php echo $instrucciones_adicionales; ?></td>
                    <td><?php echo $metodo_pago; ?></td>
                    <td><?php echo $monto_total; ?></td>
                    <td><?php echo $fecha_finaliza; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
                <?php } ?>
            </table>
        </div>
    </div>
</div>


<?php include_once '../layoutAdmin/footer.php'; ?>