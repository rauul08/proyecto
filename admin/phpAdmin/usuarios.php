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
        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearUsuarioModal">
                <i class="fa-solid fa-user-plus"></i> Crear usuario
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" style="width: 100%;" id="tblUsuarios">
                <thead>
                    <tr>
                        <!-- <th>#</th> -->
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
                    <!-- <td><?php echo $id; ?></td> -->
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

<!-- Modal Crear Usuario -->
<div class="modal fade" id="crearUsuarioModal" tabindex="-1" aria-labelledby="crearUsuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="crearUsuarioModalLabel">Crear Usuario</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCrearUsuario" autocomplete="off">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="crear-rol" class="form-label">Rol</label>
                            <select class="form-select" id="crear-rol" name="rol" required>
                                <option value="customer" selected>Cliente</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="crear-usuario" class="form-label">Usuario</label>
                            <input type="text" class="form-control" id="crear-usuario" name="usuario" required>
                        </div>
                        <div class="col-md-4">
                            <label for="crear-email" class="form-label">Correo electronico</label>
                            <input type="email" class="form-control" id="crear-email" name="email" required>
                        </div>

                        <div class="col-md-6">
                            <label for="crear-password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="crear-password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="crear-repassword" class="form-label">Confirmar contraseña</label>
                            <input type="password" class="form-control" id="crear-repassword" name="repassword" required>
                        </div>
                    </div>

                    <div id="camposCliente" class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label for="crear-nombres" class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="crear-nombres" name="nombres">
                        </div>
                        <div class="col-md-6">
                            <label for="crear-apellidos" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="crear-apellidos" name="apellidos">
                        </div>
                        <div class="col-md-6">
                            <label for="crear-telefono" class="form-label">Telefono</label>
                            <input type="text" class="form-control" id="crear-telefono" name="telefono">
                        </div>
                        <div class="col-md-6">
                            <label for="crear-direccion" class="form-label">Direccion</label>
                            <input type="text" class="form-control" id="crear-direccion" name="direccion">
                        </div>
                    </div>

                    <div id="camposAdmin" class="row g-3 mt-1 d-none">
                        <div class="col-md-6">
                            <label for="crear-nombre-admin" class="form-label">Nombre para administrador (opcional)</label>
                            <input type="text" class="form-control" id="crear-nombre-admin" name="nombre_admin">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button id="btn-crear-usuario" type="button" class="btn btn-primary">Crear y enviar activacion</button>
            </div>
        </div>
    </div>
</div>

<?php include_once '../layoutAdmin/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad del Modal de Creacion
    var crearUsuarioModal = document.getElementById('crearUsuarioModal');
    var formCrearUsuario = document.getElementById('formCrearUsuario');
    var btnCrearUsuario = document.getElementById('btn-crear-usuario');
    var rolSelect = document.getElementById('crear-rol');
    var camposCliente = document.getElementById('camposCliente');
    var camposAdmin = document.getElementById('camposAdmin');

    function actualizarCamposPorRol() {
        var esCliente = rolSelect.value === 'customer';
        camposCliente.classList.toggle('d-none', !esCliente);
        camposAdmin.classList.toggle('d-none', esCliente);

        document.getElementById('crear-nombres').required = esCliente;
        document.getElementById('crear-apellidos').required = esCliente;
        document.getElementById('crear-telefono').required = esCliente;
        document.getElementById('crear-direccion').required = esCliente;
    }

    if (crearUsuarioModal && formCrearUsuario && btnCrearUsuario && rolSelect) {
        rolSelect.addEventListener('change', actualizarCamposPorRol);
        actualizarCamposPorRol();

        crearUsuarioModal.addEventListener('hidden.bs.modal', function() {
            formCrearUsuario.reset();
            actualizarCamposPorRol();
        });

        btnCrearUsuario.addEventListener('click', async function() {
            try {
                if (!formCrearUsuario.checkValidity()) {
                    formCrearUsuario.reportValidity();
                    return;
                }

                const response = await fetch('../clases/crearUsuarioAdmin.php', {
                    method: 'POST',
                    body: new FormData(formCrearUsuario)
                });
                const data = await response.json();

                if (data.ok) {
                    alert(data.message || 'Usuario creado correctamente.');
                    window.location.reload();
                } else {
                    alert(data.error || 'No fue posible crear el usuario.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error inesperado al crear usuario.');
            }
        });
    }

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

        btnGuardar.addEventListener('click', async function() {
            try {
                var formData = new FormData(formEditar);
                formData.append('action', 'modificar');

                const response = await fetch('../clases/actualizarClientes.php', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                });
                const data = await response.json();

                if (data.ok) {
                    window.location.reload();
                } else {
                    alert('Error al modificar correo electrónico existente');
                }
            } catch (error) {
                console.error('Error:', error);
            }
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

        btnElimina.addEventListener('click', async function() {
            try {
                const response = await fetch('../clases/actualizarClientes.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=eliminar&id=' + clienteIdEliminar
                });
                const data = await response.json();

                if (data.ok) {
                    window.location.reload();
                } else {
                    alert('Error al dar de baja al cliente');
                }
            } catch (error) {
                console.error('Error:', error);
            }
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

        btnAlta.addEventListener('click', async function() {
            try {
                const response = await fetch('../clases/actualizarClientes.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=alta&id=' + clienteIdGuardar
                });
                const data = await response.json();

                if (data.ok) {
                    window.location.reload();
                } else {
                    alert('Error al dar de alta al cliente');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    } else {
        console.error('Error: Los elementos necesarios para la alta no están presentes en el DOM.');
    }
});
</script>