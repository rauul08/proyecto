<?php
require '../config/database.php';

$db = new Database();
$con = $db->conectar();
$sql = $con->prepare("SELECT id, nombre, descripcion, precio, activo FROM productos");
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include_once '../layoutAdmin/header.php'; ?>
<span>¨</span>
<ul class="nav nav-tabs">
  <li class="nav-item">
    <a class="nav-link active" aria-current="page" href="productos.php">Productos</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="paquetes.php">Paquetes</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="añadirProductos.php">Añadir Producto</a>
  </li>
</ul>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" style="width: 100%;" id="tblProductos">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (count($resultado) == 0) {
                        echo "<tr><td colspan='7'>No hay productos registrados</td></tr>";
                    } else {
                        foreach($resultado as $row) {
                        $id = $row['id'];
                        $nombre = $row['nombre'];
                        $descripcion = $row['descripcion'];
                        $precio = $row['precio'];
                        $activo = $row['activo'];
                    ?>
                    <tr>
                    <td><?php echo $id; ?></td>
                    <td><?php echo $nombre; ?></td>
                    <td><?php echo $descripcion; ?></td>
                    <td><?php echo $precio; ?></td>
                    <td><?php echo $activo; ?></td>
                    <td>
                    <a href="#" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editarModal" data-bs-id="<?php echo $id; ?>" data-bs-nombre="<?php echo $nombre; ?>" data-bs-descripcion="<?php echo $descripcion; ?>" data-bs-precio="<?php echo $precio; ?>">
                    <i class="fas fa-edit"></i>
                    </a>
                    <a href="#" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#quitaModal" data-bs-id="<?php echo $id; ?>"><i class="fas fa-trash"></i></a>
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
                <h1 class="modal-title fs-5" id="editarModalLabel">Editar Producto</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditar">
                    <input type="hidden" id="editar-id">
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-guardar">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="quitaModal" tabindex="-1" aria-labelledby="quitaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="quitaModalLabel">Quitar Producto</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro de quitar este producto de la tienda?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button id="btn-quita" type="button" class="btn btn-danger">Quitar</button>
            </div>
        </div>
    </div>
</div>


<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Script para el modal de eliminar
    var quitaModal = document.getElementById('quitaModal');
    var btnQuita = document.getElementById('btn-quita');
    var productoIdQuitar;

    quitaModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        productoIdQuitar = button.getAttribute('data-bs-id');
    });

    btnQuita.addEventListener('click', function() {
        fetch('../clases/actualizarProductos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                action: 'eliminar',
                id: productoIdQuitar
            })
        })
        .then(response => response.text())  // Cambiado a text() para depurar
        .then(text => {
            try {
                const data = JSON.parse(text);  // Intentar parsear el texto como JSON
                if (data.ok) {
                    window.location.reload();
                } else {
                    alert('Error al quitar el producto');
                }
            } catch (e) {
                console.error('Error al parsear JSON:', e);
                console.log('Respuesta del servidor:', text);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // Script para el modal de editar
    var editarModal = document.getElementById('editarModal');
    var btnGuardar = document.getElementById('btn-guardar');
    var formEditar = document.getElementById('formEditar');
    var productoIdEditar;

    editarModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        productoIdEditar = button.getAttribute('data-bs-id');

        document.getElementById('editar-id').value = productoIdEditar;
        document.getElementById('editar-nombre').value = button.getAttribute('data-bs-nombre');
        document.getElementById('editar-descripcion').value = button.getAttribute('data-bs-descripcion');
        document.getElementById('editar-precio').value = button.getAttribute('data-bs-precio');
    });

    btnGuardar.addEventListener('click', function() {
        var formData = new FormData(formEditar);
        formData.append('id', productoIdEditar);
        formData.append('action', 'editar');

        fetch('../clases/actualizarProductos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(formData)  // Asegúrate de que los datos estén en el formato correcto
        })
        .then(response => response.text())  // Cambiado a text() para depurar
        .then(text => {
            try {
                const data = JSON.parse(text);  // Intentar parsear el texto como JSON
                if (data.ok) {
                    window.location.reload();
                } else {
                    alert('Error al editar el producto');
                }
            } catch (e) {
                console.error('Error al parsear JSON:', e);
                console.log('Respuesta del servidor:', text);
            }
        })
        .catch(error => console.error('Error:', error));
    });
});
</script>

<?php include_once '../layoutAdmin/footer.php'; ?>