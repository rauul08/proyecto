<?php
require '../config/database.php';

$db = new Database();
$con = $db->conectar();
$sql = $con->prepare("SELECT id, nombre, email, direccion_entrega, instrucciones_adicionales, metodo_pago, monto_total, proceso, fecha_procesa FROM pedidos WHERE proceso = 2");
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
    <a class="nav-link active"  aria-current="page" href="pedidosPendientes.php">En Proceso</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="pedidosFinalizados.php">Finalizados</a>
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
                        <th>Fecha Procesada</th>
                        <th>Finalizar Pedido</th>
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
                        $fecha_procesa = $row['fecha_procesa'];
                ?>
                <tr data-id="<?php echo $id; ?>">
                    <td><?php echo $id; ?></td>
                    <td><?php echo $nombre; ?></td>
                    <td><?php echo $email; ?></td>
                    <td><?php echo $direccion_entrega; ?></td>
                    <td><?php echo $instrucciones_adicionales; ?></td>
                    <td><?php echo $metodo_pago; ?></td>
                    <td><?php echo $monto_total; ?></td>
                    <td><?php echo $fecha_procesa; ?></td>
                    <td><a href="#" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#finalizarPedido" data-id="<?php echo $id; ?>"><i class="fa-solid fa-circle-up"></i></a></td>
                </tr>
                <?php } ?>
                </tbody>
                <?php } ?>
            </table>
        </div>
    </div>
</div>

<!-- Modal Finaliza -->
<div class="modal fade" id="finalizarPedido" tabindex="-1" aria-labelledby="finalizarPedidoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="finalizarPedidoLabel">Finalizar Pedido</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        ¿Estás seguro de que deseas cambiar el estado de este pedido a "Finalizado"?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="confirmarFinalizacion">Confirmar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var finalizarPedido = document.getElementById('finalizarPedido');
  var confirmarFinalizacion = document.getElementById('confirmarFinalizacion');
  var pedidoId;

  finalizarPedido.addEventListener('show.bs.modal', function(event) {
    var button = event.relatedTarget;
    pedidoId = button.getAttribute('data-id');
  });

  confirmarFinalizacion.addEventListener('click', function() {
    // Hacer la solicitud AJAX para actualizar el estado del pedido
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../clases/procesarPedidos.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        // Manejar la respuesta del servidor
        console.log(xhr.responseText);
        // Cerrar el modal
        var modalInstance = bootstrap.Modal.getInstance(finalizarPedido);
        modalInstance.hide();
        // Actualizar la tabla de pedidos
        location.reload();
      }
    };
    xhr.send('id=' + pedidoId + '&proceso=3');
  });
});
</script>

<?php include_once '../layoutAdmin/footer.php'; ?>