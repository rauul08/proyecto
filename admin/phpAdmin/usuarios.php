<?php
require '../config/database.php';

$db = new Database();
$con = $db->conectar();
$sql = "SELECT id, nombres, apellidos, email, telefono, direccion, estatus, registro_pedidos FROM clientes";
$stmt = $con->prepare($sql);
$stmt->execute();
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include_once '../layoutAdmin/header.php'; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" style="width: 100%;" id="tblUsuarios">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Correo Electrónico</th>
                        <th>Telefono</th>
                        <th>Dirección</th>
                        <th>Estatus</th>
                        <th># Pedidos</th>
                        <th>Editar</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Verifica si hay resultados y los muestra en la tabla
                if (count($resultado) == 0) {
                    echo "<tr><td colspan='7'>No hay clientes registrados</td></tr>";
                } else {
                    foreach ($resultado as $row) {
                        $id = $row['id'];
                        $nombres = $row['nombres'];
                        $apellidos = $row['apellidos'];
                        $email = $row['email'];
                        $telefono = $row['telefono'];
                        $direccion = $row['direccion'];
                        $estatus = $row['estatus'];
                        $registro_pedidos = $row['registro_pedidos'];
                ?>
                <tr data-id="<?php echo $id; ?>">
                    <td><?php echo $id; ?></td>
                    <td><?php echo $nombres; ?></td>
                    <td><?php echo $apellidos; ?></td>
                    <td><?php echo $email; ?></td>
                    <td><?php echo $telefono; ?></td>
                    <td><?php echo $direccion; ?></td>
                    <td><?php echo $estatus; ?></td>
                    <td><?php echo $registro_pedidos; ?></td>
                    <td>
                        <a href="#" class="btn btn-warning btn-sm" data-bs-id="<?php echo $id; ?>" data-bs-nombres="<?php echo $nombres; ?>" data-bs-apellidos="<?php echo $apellidos; ?>" data-bs-email="<?php echo $email; ?>" data-bs-telefono="<?php echo $telefono; ?>" data-bs-direccion="<?php echo $direccion; ?>" data-bs-toggle="modal" data-bs-target="#editarModal">
                            <i class="fa-solid fa-user-pen"></i>
                        </a>
                        <a href="#" class="btn btn-danger btn-sm" data-bs-id="<?php echo $id; ?>" data-bs-toggle="modal" data-bs-target="#eliminaModal"><i class="fas fa-trash"></i></a>
                        <a href="#" class="btn btn-success btn-sm" data-bs-id="<?php echo $id; ?>" data-bs-toggle="modal" data-bs-target="#altaModal">
                            <i class="fa-solid fa-user-check"></i>
                        </a>

                    </td>
                </tr>
                <?php } ?>
                </tbody>
                <?php } ?>
            </table>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="eliminaModal" tabindex="-1" aria-labelledby="eliminaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="eliminaModalLabel">Alerta</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro de dar de baja a este cliente?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button id="btn-elimina" type="button" class="btn btn-danger">Dar de Baja</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editarModalLabel">Modificar Cliente</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditar">
                    <input type="hidden" id="editar-id" name="id">
                    <div class="mb-3">
                        <label for="editar-nombres" class="col-form-label">Nombres:</label>
                        <input type="text" class="form-control" id="editar-nombres" name="nombres">
                    </div>
                    <div class="mb-3">
                        <label for="editar-apellidos" class="col-form-label">Apellidos:</label>
                        <input type="text" class="form-control" id="editar-apellidos" name="apellidos">
                    </div>
                    <div class="mb-3">
                        <label for="editar-email" class="col-form-label">Correo Electrónico:</label>
                        <input type="email" class="form-control" id="editar-email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="editar-telefono" class="col-form-label">Teléfono:</label>
                        <input type="text" class="form-control" id="editar-telefono" name="telefono">
                    </div>
                    <div class="mb-3">
                        <label for="editar-direccion" class="col-form-label">Dirección:</label>
                        <input type="text" class="form-control" id="editar-direccion" name="direccion">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button id="btn-guardar" type="button" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Alta -->
<div class="modal fade" id="altaModal" tabindex="-1" aria-labelledby="altaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="altaModalLabel">Activar Cliente</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro de activar a este cliente?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button id="btn-alta" type="button" class="btn btn-primary">Activar</button>
            </div>
        </div>
    </div>
</div>

<?php include_once '../layoutAdmin/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad del Modal de Edición
    var editarModal = document.getElementById('editarModal');
    var btnGuardar = document.getElementById('btn-guardar');
    var formEditar = document.getElementById('formEditar');
    var clienteId;

    if (editarModal && btnGuardar && formEditar) {
        editarModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            clienteId = button.getAttribute('data-bs-id');
            
            document.getElementById('editar-id').value = clienteId;
            document.getElementById('editar-nombres').value = button.getAttribute('data-bs-nombres');
            document.getElementById('editar-apellidos').value = button.getAttribute('data-bs-apellidos');
            document.getElementById('editar-email').value = button.getAttribute('data-bs-email');
            document.getElementById('editar-telefono').value = button.getAttribute('data-bs-telefono');
            document.getElementById('editar-direccion').value = button.getAttribute('data-bs-direccion');
        });

        btnGuardar.addEventListener('click', function() {
            var formData = new FormData(formEditar);
            formData.append('action', 'modificar');

            fetch('../clases/actualizarClientes.php', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    window.location.reload();
                } else {
                    alert('Error al modificar correo electrónico existente');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    } else {
        console.error('Error: Los elementos necesarios para la edición no están presentes en el DOM.');
    }

    // Funcionalidad del Modal de Eliminación
    var eliminaModal = document.getElementById('eliminaModal');
    var btnElimina = document.getElementById('btn-elimina');
    var clienteIdEliminar;

    if (eliminaModal && btnElimina) {
        eliminaModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            clienteIdEliminar = button.getAttribute('data-bs-id');
        });

        btnElimina.addEventListener('click', function() {
            fetch('../clases/actualizarClientes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=eliminar&id=' + clienteIdEliminar
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    window.location.reload();
                } else {
                    alert('Error al dar de baja al cliente');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    } else {
        console.error('Error: Los elementos necesarios para la eliminación no están presentes en el DOM.');
    }

    // Funcionalidad del Modal de Alta
    var altaModal = document.getElementById('altaModal');
    var btnAlta = document.getElementById('btn-alta');
    var clienteIdGuardar;

    if (altaModal && btnAlta) {
        altaModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            clienteIdGuardar = button.getAttribute('data-bs-id');
        });

        btnAlta.addEventListener('click', function() {
            fetch('../clases/actualizarClientes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=alta&id=' + clienteIdGuardar
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    window.location.reload();
                } else {
                    alert('Error al dar de alta al cliente');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    } else {
        console.error('Error: Los elementos necesarios para la alta no están presentes en el DOM.');
    }
});
</script>