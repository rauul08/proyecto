<?php
require '../config/database.php';

$db = new Database();
$con = $db->conectar();

$sql = $con->prepare("SELECT id, nombre, descripcion, precio, descuento, activo FROM paquetes");
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include_once '../layoutAdmin/header.php'; ?>
<br>
<ul class="nav nav-tabs">
  <li class="nav-item">
    <a class="nav-link" href="productos.php">Productos</a>
  </li>
  <li class="nav-item">
    <a class="nav-link active" aria-current="page" href="paquetes.php">Paquetes</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="añadirPaquetes.php">Añadir Paquete</a>
  </li>
</ul>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" style="width: 100%;" id="tblPaquetes">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Descuento</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (count($resultado) == 0) {
                        echo "<tr><td colspan='7'>No hay paquetes registrados</td></tr>";
                    } else {
                        foreach($resultado as $row) {
                        $id = $row['id'];
                        $nombre = $row['nombre'];
                        $descripcion = $row['descripcion'];
                        $precio = $row['precio'];
                        $descuento = $row['descuento'];
                        $activo = $row['activo'];
                    ?>
                    <tr>
                    <td><?php echo $id; ?></td>
                    <td><?php echo $nombre; ?></td>
                    <td><?php echo $descripcion; ?></td>
                    <td><?php echo $precio; ?></td>
                    <td><?php echo $descuento; ?>%</td>
                    <td><?php echo $activo; ?></td>
                    <td>
                    <a href="#" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editarModal" data-bs-id="<?php echo $id; ?>" data-bs-nombre="<?php echo $nombre; ?>" data-bs-descripcion="<?php echo $descripcion; ?>" data-bs-precio="<?php echo $precio; ?>" data-bs-descuento="<?php echo $descuento; ?>">
                    <i class="fas fa-edit"></i>
                    </a>
                    <a href="#" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#eliminarModal" data-bs-id="<?php echo $id; ?>"><i class="fas fa-trash"></i></a>
                    </td>
                    </tr>
                    <?php } ?>
                </tbody>
                <?php } ?>
            </table>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editarModalLabel">Editar Paquete</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditar">
                    <input type="hidden" id="editar-id" name="id">
                    <div class="mb-3">
                        <label for="editar-nombre" class="col-form-label">Nombre:</label>
                        <input type="text" class="form-control" id="editar-nombre" name="nombre">
                    </div>
                    <div class="mb-3">
                        <label for="editar-descripcion" class="col-form-label">Descripción:</label>
                        <textarea class="form-control" id="editar-descripcion" name="descripcion"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editar-precio" class="col-form-label">Precio:</label>
                        <input type="number" step="0.01" class="form-control" id="editar-precio" name="precio">
                    </div>
                    <div class="mb-3">
                        <label for="editar-descuento" class="col-form-label">Descuento:</label>
                        <input type="number" step="0.01" class="form-control" id="editar-descuento" name="descuento">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button id="btn-guardar" type="button" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="eliminarModal" tabindex="-1" aria-labelledby="eliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="eliminarModalLabel">Quitar Paquete</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro de quitar este paquete?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button id="btn-eliminar" type="button" class="btn btn-danger">Quitar</button>
            </div>
        </div>
    </div>
</div>

<?php include_once '../layoutAdmin/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Eliminar
    var eliminarModal = document.getElementById('eliminarModal');
    var btnEliminar = document.getElementById('btn-eliminar');
    var paqueteIdEliminar;

    eliminarModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        paqueteIdEliminar = button.getAttribute('data-bs-id');
    });

    btnEliminar.addEventListener('click', function() {
        fetch('../clases/actualizarPaquetes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'action=eliminar&id=' + paqueteIdEliminar
        })
        .then(response => response.text())  // Use .text() instead of .json() to see the full response
        .then(data => {
            try {
                var json = JSON.parse(data);
                if (json.ok) {
                    window.location.reload();
                } else {
                    alert('Error al quitar el paquete');
                }
            } catch (e) {
                console.error('Error:', data);  // Log the full response if JSON.parse fails
                alert('Error al procesar la respuesta del servidor');
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // Editar
    var editarModal = document.getElementById('editarModal');
    var btnGuardar = document.getElementById('btn-guardar');
    var formEditar = document.getElementById('formEditar');
    var paqueteIdEditar;

    editarModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        paqueteIdEditar = button.getAttribute('data-bs-id');

        document.getElementById('editar-id').value = paqueteIdEditar;
        document.getElementById('editar-nombre').value = button.getAttribute('data-bs-nombre');
        document.getElementById('editar-descripcion').value = button.getAttribute('data-bs-descripcion');
        document.getElementById('editar-precio').value = button.getAttribute('data-bs-precio');
        document.getElementById('editar-descuento').value = button.getAttribute('data-bs-descuento');
    });

    btnGuardar.addEventListener('click', function() {
        var formData = new FormData(formEditar);
        formData.append('id', paqueteIdEditar);
        formData.append('action', 'editar');

        fetch('../clases/actualizarPaquetes.php', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.text())  // Use .text() instead of .json() to see the full response
        .then(data => {
            try {
                var json = JSON.parse(data);
                if (json.ok) {
                    window.location.reload();
                } else {
                    alert('Error al editar el paquete');
                }
            } catch (e) {
                console.error('Error:', data);  // Log the full response if JSON.parse fails
                alert('Error al procesar la respuesta del servidor');
            }
        })
        .catch(error => console.error('Error:', error));
    });
});
</script>
