<?php
require '../config/config.php';
require_once '../../shared/AuthGuards.php';
requireRoutePermission('clientes/phpClientes/mis_sesiones.php', ['redirect' => 'login.php']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JIREH | Mis Sesiones Activas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="icon" type="image/jpeg" href="../images/jLeft.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #8B4513;
            --secondary-color: #FFD700;
            --danger-color: #dc3545;
        }

        body {
            background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
            font-family: 'Arial', sans-serif;
        }

        .container-main {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 20px;
        }

        .card-header h5 {
            margin: 0;
            font-weight: bold;
        }

        .session-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
            transition: box-shadow 0.3s ease;
        }

        .session-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .session-item.current {
            border-left: 4px solid var(--secondary-color);
            background-color: #fffbf0;
        }

        .session-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .device-info {
            font-weight: bold;
            color: var(--primary-color);
            font-size: 1.1em;
        }

        .session-badge {
            display: inline-block;
            background-color: var(--secondary-color);
            color: var(--primary-color);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }

        .session-details {
            font-size: 0.95em;
            color: #666;
            margin: 8px 0;
        }

        .session-details span {
            display: inline-block;
            margin-right: 20px;
        }

        .btn-close-session {
            background-color: var(--danger-color);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-close-session:hover {
            background-color: #c82333;
            color: white;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            margin-top: 10px;
        }

        .btn-primary:hover {
            background-color: #6d3410;
            border-color: #6d3410;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .alert {
            border-radius: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 3em;
            color: #ddd;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container-main">
        <!-- Header -->
        <div class="card">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h5><i class="fas fa-lock"></i> Mis Sesiones Activas</h5>
                        <small>Gestiona tus sesiones en diferentes dispositivos</small>
                    </div>
                    <a href="perfil.php" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Alert Area -->
        <div id="alertContainer"></div>

        <!-- Loading -->
        <div class="loading" id="loading">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando sesiones...</p>
        </div>

        <!-- Sessions List -->
        <div id="sessionsContainer"></div>

        <!-- Actions -->
        <div class="card">
            <div class="card-body">
                <button class="btn btn-primary w-100" id="closeAllOtherBtn">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Todas las Otras Sesiones
                </button>
                <small class="d-block text-muted mt-2">
                    Esta acción cerrará todas tus sesiones excepto la actual
                </small>
            </div>
        </div>
    </div>

    <script>
        const API_ENDPOINT = 'sessionManagementAjax.php';

        // Cargar sesiones al abrir la página
        document.addEventListener('DOMContentLoaded', loadSessions);

        /**
         * Carga la lista de sesiones activas
         */
        function loadSessions() {
            showLoading(true);
            fetch(API_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'action=list_sessions'
            })
            .then(response => response.json())
            .then(data => {
                showLoading(false);
                if (data.ok) {
                    renderSessions(data.data.sessions);
                } else {
                    showAlert('Error al cargar sesiones: ' + data.error, 'danger');
                }
            })
            .catch(error => {
                showLoading(false);
                showAlert('Error de conexión: ' + error.message, 'danger');
            });
        }

        /**
         * Renderiza la lista de sesiones
         */
        function renderSessions(sessions) {
            const container = document.getElementById('sessionsContainer');

            if (sessions.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No hay sesiones activas</p>
                    </div>
                `;
                return;
            }

            let html = '<div class="card"><div class="card-body">';

            sessions.forEach((session, index) => {
                const isCurrent = session.is_current;
                const createdDate = new Date(session.created_at).toLocaleString('es-ES');
                const lastActivityDate = new Date(session.last_activity).toLocaleString('es-ES');

                html += `
                    <div class="session-item ${isCurrent ? 'current' : ''}">
                        <div class="session-header">
                            <div>
                                <div class="device-info">
                                    <i class="fas fa-${getDeviceIcon(session.device_info)}"></i>
                                    ${session.device_info}
                                </div>
                                ${isCurrent ? '<span class="session-badge"><i class="fas fa-star"></i> Sesión Actual</span>' : ''}
                            </div>
                            ${!isCurrent ? `<button class="btn-close-session" onclick="closeSession('${session.session_id}')">
                                <i class="fas fa-times"></i> Cerrar
                            </button>` : ''}
                        </div>
                        <div class="session-details">
                            <span><i class="fas fa-globe"></i> <strong>IP:</strong> ${session.ip_address}</span>
                            <span><i class="fas fa-calendar"></i> <strong>Inicio:</strong> ${createdDate}</span>
                            <span><i class="fas fa-clock"></i> <strong>Última actividad:</strong> ${lastActivityDate}</span>
                        </div>
                    </div>
                `;
            });

            html += '</div></div>';
            container.innerHTML = html;
        }

        /**
         * Cierra una sesión específica
         */
        function closeSession(sessionId) {
            if (confirm('¿Estás seguro de que deseas cerrar esta sesión?')) {
                fetch(API_ENDPOINT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `action=close_session&session_id=${encodeURIComponent(sessionId)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.ok) {
                        showAlert('Sesión cerrada exitosamente', 'success');
                        setTimeout(loadSessions, 1000);
                    } else {
                        showAlert('Error: ' + data.error, 'danger');
                    }
                })
                .catch(error => showAlert('Error de conexión: ' + error.message, 'danger'));
            }
        }

        /**
         * Cierra todas las otras sesiones
         */
        document.getElementById('closeAllOtherBtn').addEventListener('click', function() {
            if (confirm('¿Cerrar todas las otras sesiones? Solo tu sesión actual permanecerá abierta.')) {
                fetch(API_ENDPOINT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'action=close_all_other'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.ok) {
                        showAlert('Sesiones cerradas: ' + data.data.closed_count, 'success');
                        setTimeout(loadSessions, 1000);
                    } else {
                        showAlert('Error: ' + data.error, 'danger');
                    }
                })
                .catch(error => showAlert('Error de conexión: ' + error.message, 'danger'));
            }
        });

        /**
         * Muestra/oculta el indicador de carga
         */
        function showLoading(show) {
            document.getElementById('loading').style.display = show ? 'block' : 'none';
        }

        /**
         * Muestra una alerta
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

            // Auto-dismiss después de 4 segundos
            setTimeout(() => {
                container.querySelector('.alert')?.remove();
            }, 4000);
        }

        /**
         * Obtiene el ícono del dispositivo según el User-Agent
         */
        function getDeviceIcon(deviceInfo) {
            if (deviceInfo.includes('Windows')) return 'desktop';
            if (deviceInfo.includes('macOS')) return 'apple';
            if (deviceInfo.includes('Linux')) return 'linux';
            if (deviceInfo.includes('Android')) return 'android';
            if (deviceInfo.includes('iOS')) return 'mobile';
            return 'device-desktop';
        }

        // Recargar sesiones cada 30 segundos
        setInterval(loadSessions, 30000);
    </script>
</body>
</html>
