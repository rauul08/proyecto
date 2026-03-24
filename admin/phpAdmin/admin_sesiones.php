<?php
require '../config/database.php';
require_once '../../shared/AuthGuards.php';
requireRoutePermission('admin/phpAdmin/admin_sesiones.php', ['redirect' => '../phpAdmin/loginAdmin.php']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JIREH Admin | Gestión de Sesiones</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="icon" type="image/jpeg" href="../../images/jLeft.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #8B4513;
            --secondary-color: #FFD700;
            --danger-color: #dc3545;
        }

        body {
            background-color: #f5f5f5;
            font-family: 'Arial', sans-serif;
        }

        .admin-header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .btn-action {
            padding: 4px 8px;
            font-size: 0.9em;
            margin: 0 2px;
        }

        .session-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: bold;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }

        table {
            font-size: 0.95em;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #6d3410;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid" style="padding: 20px;">
        <!-- Header -->
        <div class="admin-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h4><i class="fas fa-lock"></i> Gestión de Sesiones</h4>
                    <small>Administra y monitorea sesiones activas de usuarios</small>
                </div>
                <a href="inicio.php" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-body">
                <button class="btn btn-warning me-2" id="cleanupBtn">
                    <i class="fas fa-broom"></i> Limpiar Sesiones Expiradas
                </button>
                <div id="alertContainer" class="mt-2"></div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="search-box">
                            <input type="text" id="userSearch" class="form-control" placeholder="Buscar por ID o usuario...">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-primary" onclick="searchUser()">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div class="loading" id="loading">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando...</p>
        </div>

        <!-- Sessions Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="sessionsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Usuario ID</th>
                            <th>Device</th>
                            <th>IP Address</th>
                            <th>Estado</th>
                            <th>Creada</th>
                            <th>Última Actividad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="sessionsBody">
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">
                                Ingresa un ID de usuario y presiona buscar
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const API_ENDPOINT = '../clases/adminSessionManagementAjax.php';

        /**
         * Búsqueda de usuario
         */
        function searchUser() {
            const userId = document.getElementById('userSearch').value.trim();
            if (!userId) {
                showAlert('Ingresa un ID de usuario', 'warning');
                return;
            }

            showLoading(true);
            fetch(API_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=list_user_sessions&user_id=${encodeURIComponent(userId)}`
            })
            .then(response => response.json())
            .then(data => {
                showLoading(false);
                if (data.ok) {
                    renderSessions(data.data.sessions);
                } else {
                    showAlert('Error: ' + data.error, 'danger');
                }
            })
            .catch(error => {
                showLoading(false);
                showAlert('Error de conexión: ' + error.message, 'danger');
            });
        }

        /**
         * Renderiza la tabla de sesiones
         */
        function renderSessions(sessions) {
            const tbody = document.getElementById('sessionsBody');
            tbody.innerHTML = '';

            if (sessions.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">
                            No hay sesiones para este usuario
                        </td>
                    </tr>
                `;
                return;
            }

            sessions.forEach(session => {
                const isActive = session.is_active === 1;
                const statusClass = isActive ? 'status-active' : 'status-inactive';
                const statusText = isActive ? 'Activa' : 'Cerrada';
                const createdDate = new Date(session.created_at).toLocaleString('es-ES');
                const lastActivityDate = new Date(session.last_activity).toLocaleString('es-ES');

                const row = `
                    <tr>
                        <td>${session.auth_user_id}</td>
                        <td>${session.device_info || 'N/A'}</td>
                        <td><code>${session.ip_address}</code></td>
                        <td><span class="session-status ${statusClass}">${statusText}</span></td>
                        <td>${createdDate}</td>
                        <td>${lastActivityDate}</td>
                        <td>
                            ${isActive ? `
                                <button class="btn btn-sm btn-danger btn-action" onclick="closeSession('${session.session_id}')">
                                    <i class="fas fa-ban"></i> Cerrar
                                </button>
                            ` : ''}
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', row);
            });
        }

        /**
         * Cierra una sesión
         */
        function closeSession(sessionId) {
            if (confirm('¿Cerrar esta sesión?')) {
                fetch(API_ENDPOINT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `action=close_user_session&session_id=${encodeURIComponent(sessionId)}&reason=closed_by_admin`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.ok) {
                        showAlert('Sesión cerrada', 'success');
                        searchUser();
                    } else {
                        showAlert('Error: ' + data.error, 'danger');
                    }
                })
                .catch(error => showAlert('Error: ' + error.message, 'danger'));
            }
        }

        /**
         * Limpia sesiones expiradas
         */
        document.getElementById('cleanupBtn').addEventListener('click', function() {
            if (confirm('¿Limpiar todas las sesiones expiradas?')) {
                fetch(API_ENDPOINT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'action=cleanup_expired'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.ok) {
                        showAlert('Cleaned: ' + data.data.cleaned_count + ' sesiones', 'success');
                    } else {
                        showAlert('Error: ' + data.error, 'danger');
                    }
                })
                .catch(error => showAlert('Error: ' + error.message, 'danger'));
            }
        });

        /**
         * Muestra/oculta loading
         */
        function showLoading(show) {
            document.getElementById('loading').style.display = show ? 'block' : 'none';
        }

        /**
         * Muestra alerta
         */
        function showAlert(message, type = 'info') {
            const container = document.getElementById('alertContainer');
            const alertHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', alertHTML);

            setTimeout(() => {
                container.querySelector('.alert')?.remove();
            }, 4000);
        }

        // Enter para buscar
        document.getElementById('userSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') searchUser();
        });
    </script>
</body>
</html>
