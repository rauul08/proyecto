<?php
declare(strict_types=1);

require_once '../config/config.php';
require_once '../../shared/AuthGuards.php';
require_once '../../shared/AuthService.php';
require_once '../../shared/SessionManager.php';

// Validar que es AJAX
requireAjaxAuth();

// Obtener conexión
$db = (new Database())->conectar();
$sessionManager = new SessionManager($db);
$authUserId = getCurrentAuthUserId();

if (!$authUserId) {
    sendJsonError('Usuario no autenticado', 'UNAUTHORIZED', 401);
}

$action = $_POST['action'] ?? '';

try {
    match ($action) {
        'list_sessions' => handleListSessions($sessionManager, $authUserId),
        'close_session' => handleCloseSession($sessionManager, $authUserId),
        'close_all_other' => handleCloseAllOther($sessionManager, $authUserId),
        default => sendJsonError("Acción no válida: $action", 'INVALID_ACTION', 400)
    };
} catch (Throwable $e) {
    error_log("sessionManagementAjax - Error: " . $e->getMessage());
    sendJsonError('Error en el servidor: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}

/**
 * Obtiene lista de sesiones activas del usuario
 */
function handleListSessions(SessionManager $sessionManager, string $authUserId): void
{
    $sessions = $sessionManager->getActiveSessions($authUserId);
    $currentSessionId = session_id();

    // Mapear información
    $mapped = array_map(function ($session) use ($currentSessionId) {
        return [
            'id' => $session['id'],
            'session_id' => $session['session_id'],
            'device_info' => $session['device_info'],
            'ip_address' => $session['ip_address'],
            'is_current' => $session['session_id'] === $currentSessionId,
            'created_at' => $session['created_at'],
            'last_activity' => $session['last_activity'],
            'expires_at' => $session['expires_at']
        ];
    }, $sessions);

    sendJsonSuccess([
        'session_count' => count($mapped),
        'sessions' => $mapped
    ]);
}

/**
 * Cierra una sesión específica (remota)
 */
function handleCloseSession(SessionManager $sessionManager, string $authUserId): void
{
    $sessionIdToClose = $_POST['session_id'] ?? '';
    $currentSessionId = session_id();

    if (!$sessionIdToClose) {
        sendJsonError('ID de sesión requerido', 'MISSING_PARAM', 400);
    }

    // No permitir cerrar la sesión actual
    if ($sessionIdToClose === $currentSessionId) {
        sendJsonError('No puedes cerrar tu sesión actual. Usa logout en su lugar.', 'INVALID_ACTION', 400);
    }

    $result = $sessionManager->closeRemoteSession($authUserId, $sessionIdToClose, $currentSessionId);

    if ($result) {
        sendJsonSuccess(['message' => 'Sesión cerrada exitosamente']);
    } else {
        sendJsonError('No se pudo cerrar la sesión', 'SERVER_ERROR', 500);
    }
}

/**
 * Cierra todas las demás sesiones (mantiene la actual abierta)
 */
function handleCloseAllOther(SessionManager $sessionManager, string $authUserId): void
{
    $currentSessionId = session_id();
    $closed = $sessionManager->closeAllOtherSessions($authUserId, $currentSessionId);

    sendJsonSuccess([
        'message' => "Se cerraron $closed sesiones",
        'closed_count' => $closed
    ]);
}
