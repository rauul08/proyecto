<?php
require '../config/config.php';
require_once '../../shared/AuthGuards.php';
requireCustomerAuth(['redirect' => 'login.php']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JIREH | Perfil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="icon" type="images/jpeg"  href="../images/jLeft.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anton&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fugaz+One&display=swap" rel="stylesheet">
    <meta property="og:title" content="Tienda de comida rapida">
    <meta property="og:description" content="Tienda en linea de la empresa de comida rapida JIREH">
    <link rel="stylesheet" href="../css/perfil.css">
    <style>
        .fondo a {
        color: #8B4513;
        font-family: 'Arial', sans-serif;
        }
        
        .nav-underline .nav-item .nav-link:hover,
        .nav-underline .nav-item .nav-link.active {
            border-bottom: 2px solid #FFD700;
            color: #8B4513;
        }

        .badge.bg-secondary {
            color: #FFA500;
        }

        .navbar-brand img {
            height: 40px;
        }
    </style>
</head>
<body>
    <header>
        <nav class="fondo">
            <ul class="nav nav-underline">
                <a class="navbar-brand" href="../html/index.html">
                    <img id="logo" src="../images/jLeft.png" alt="Logotipo de la Empresa">
                </a>
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="index.html">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../phpClientes/index.php">Menú</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../phpClientes/promociones.php">Paquetes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../html/delivery.html">Delivery</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../html/conócenos.html">Conócenos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" aria-disabled="true">| Social Media <li><i class="fa fa-phone fa-rotate-90"></i><li><i class="fa fa-envelope" aria-hidden="true"></i> |</a>
                </li>
                <?php if(isset($_SESSION['user_id'])) { ?>
                <li class="nav-item">
                    <a class="nav-link active" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="width: 20px; height: 20px;">
                            <path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"/>
                        </svg>
                        <?php echo $_SESSION['user_name']; ?>
                    </a>
                    <!-- <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="logout.php">Cerrar sesión</a></li>
                    </div>
                    </ul> -->
                </li>
                <!-- <?php } else { ?>
                <li class="nav-item" style="margin-right: 30px;">
                    <a class="nav-link" href="login.php"><span class="badge bg-secondary">Iniciar Sesión</span></a>
                </li> -->
                <?php } ?>
                <!-- <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="login.php"><span class="badge bg-secondary">Iniciar Sesión</span></a>
                </li> -->

    </header>
    <div class="container mt-5">
        <div id="perfilAlert" class="alert d-none" role="alert"></div>

        <div class="perfil-card">
            <div class="perfil-header">
                <h3>Mi Perfil</h3>
            </div>

            <div class="perfil-info mt-4">
                <p><strong>Nombre:</strong> <span id="perfilNombre">Cargando...</span></p>
                <p><strong>Usuario:</strong> <span id="perfilUsuario">Cargando...</span></p>
                <p><strong>Correo:</strong> <span id="perfilEmail">Cargando...</span></p>
                <p><strong>Teléfono:</strong> <span id="perfilTelefono">Cargando...</span></p>
                <p><strong>Dirección:</strong> <span id="perfilDireccion">Cargando...</span></p>
            </div>

            <div class="mt-4">
                <button class="btn btn-jireh me-2" data-bs-toggle="modal" data-bs-target="#editarPerfil">
                    Editar información
                </button>

                <button class="btn btn-jireh me-2" data-bs-toggle="modal" data-bs-target="#cambiarPassword">
                    Cambiar contraseña
                </button>

                <button class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#desactivarCuenta">
                    Desactivar cuenta
                </button>

                <!-- <a href="logout.php" class="btn btn-secondary">Cerrar sesión</a> -->
            </div>
        </div>
    </div>

    <div class="modal fade" id="editarPerfil" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar información</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="formEditarPerfil">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="nombres">Nombres</label>
                            <input type="text" class="form-control" id="nombres" name="nombres" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="apellidos">Apellidos</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="email">Correo</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="telefono">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="direccion">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-jireh">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cambiarPassword" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar contraseña</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="formCambiarPassword">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="password_actual">Contraseña actual</label>
                            <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password_nueva">Nueva contraseña</label>
                            <input type="password" class="form-control" id="password_nueva" name="password_nueva" minlength="8" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password_confirmar">Confirmar contraseña</label>
                            <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" minlength="8" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-jireh">Cambiar contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="desactivarCuenta" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Desactivar cuenta</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="formDesactivarCuenta">
                    <div class="modal-body">
                        <p>Esta acción desactivará tu cuenta y cerrará tu sesión.</p>
                        <p class="text-danger">Podrás ser reactivado desde el panel de administración.</p>
                        <div class="mb-3">
                            <label class="form-label" for="password_desactivar">Confirma tu contraseña</label>
                            <input type="password" class="form-control" id="password_desactivar" name="password_actual" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Desactivar cuenta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <footer>
        <span class="footer-line">®2026 JIREH Y ASOCIADOS, INC. ALGUNOS PRODUCTOS ESTAN SUJETOS A DISPONIBILIDAD<span>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const endpointPerfil = '../clases/perfilCliente.php';
        const perfilAlert = document.getElementById('perfilAlert');

        function showAlert(message, type = 'success') {
            perfilAlert.className = `alert alert-${type}`;
            perfilAlert.textContent = message;
            perfilAlert.classList.remove('d-none');
        }

        async function getCsrfToken() {
            const response = await fetch(`${endpointPerfil}?action=csrf`, { credentials: 'same-origin' });
            const data = await response.json();
            if (!data.ok || !data.csrf_token) {
                throw new Error('No se pudo obtener token CSRF');
            }
            return data.csrf_token;
        }

        async function postPerfilAction(action, payload) {
            const csrfToken = await getCsrfToken();
            const formData = new FormData();
            formData.append('action', action);
            formData.append('csrf_token', csrfToken);

            Object.keys(payload).forEach((key) => {
                formData.append(key, payload[key]);
            });

            const response = await fetch(endpointPerfil, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            return response.json();
        }

        async function cargarPerfil() {
            try {
                const response = await fetch(`${endpointPerfil}?action=obtener`, { credentials: 'same-origin' });
                const data = await response.json();

                if (!data.ok) {
                    showAlert(data.error || 'No se pudo cargar el perfil', 'danger');
                    return;
                }

                const perfil = data.perfil;
                document.getElementById('perfilNombre').textContent = `${perfil.nombres} ${perfil.apellidos}`.trim();
                document.getElementById('perfilUsuario').textContent = perfil.usuario || '<?php echo htmlspecialchars($_SESSION['user_name']); ?>';
                document.getElementById('perfilEmail').textContent = perfil.email || '';
                document.getElementById('perfilTelefono').textContent = perfil.telefono || '';
                document.getElementById('perfilDireccion').textContent = perfil.direccion || '';

                document.getElementById('nombres').value = perfil.nombres || '';
                document.getElementById('apellidos').value = perfil.apellidos || '';
                document.getElementById('email').value = perfil.email || '';
                document.getElementById('telefono').value = perfil.telefono || '';
                document.getElementById('direccion').value = perfil.direccion || '';
            } catch (error) {
                showAlert('Error al cargar el perfil', 'danger');
            }
        }

        document.getElementById('formEditarPerfil').addEventListener('submit', async function (event) {
            event.preventDefault();
            try {
                const data = await postPerfilAction('actualizar', {
                    nombres: document.getElementById('nombres').value.trim(),
                    apellidos: document.getElementById('apellidos').value.trim(),
                    email: document.getElementById('email').value.trim(),
                    telefono: document.getElementById('telefono').value.trim(),
                    direccion: document.getElementById('direccion').value.trim()
                });

                if (!data.ok) {
                    showAlert(data.error || 'No se pudo actualizar el perfil', 'danger');
                    return;
                }

                showAlert('Perfil actualizado correctamente');
                bootstrap.Modal.getInstance(document.getElementById('editarPerfil')).hide();
                await cargarPerfil();
            } catch (error) {
                showAlert('Error al actualizar el perfil', 'danger');
            }
        });

        document.getElementById('formCambiarPassword').addEventListener('submit', async function (event) {
            event.preventDefault();
            try {
                const data = await postPerfilAction('cambiarPassword', {
                    password_actual: document.getElementById('password_actual').value,
                    password_nueva: document.getElementById('password_nueva').value,
                    password_confirmar: document.getElementById('password_confirmar').value
                });

                if (!data.ok) {
                    showAlert(data.error || 'No se pudo cambiar la contraseña', 'danger');
                    return;
                }

                this.reset();
                showAlert('Contraseña actualizada correctamente');
                bootstrap.Modal.getInstance(document.getElementById('cambiarPassword')).hide();
            } catch (error) {
                showAlert('Error al cambiar la contraseña', 'danger');
            }
        });

        document.getElementById('formDesactivarCuenta').addEventListener('submit', async function (event) {
            event.preventDefault();
            try {
                const data = await postPerfilAction('desactivar', {
                    password_actual: document.getElementById('password_desactivar').value
                });

                if (!data.ok) {
                    showAlert(data.error || 'No se pudo desactivar la cuenta', 'danger');
                    return;
                }

                window.location.href = 'login.php';
            } catch (error) {
                showAlert('Error al desactivar la cuenta', 'danger');
            }
        });

        cargarPerfil();
    </script>
</body>
</html>