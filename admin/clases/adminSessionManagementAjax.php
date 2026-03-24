<?php
declare(strict_types=1);

require_once '../config/database.php';
require_once '../../shared/AuthGuards.php';
require_once '../../shared/AuthService.php';
require_once '../../shared/SessionManager.php';

// Solo administradores
requireAdminAjaxAuth('Solo administradores pueden gestionar sesiones de usuarios');

// Obtener conexión
$db = (new Database())->conectar();
$sessionManager = new SessionManager($db);

$action = $_POST['action'] ?? '';

try {
    match ($action) {
        'list_user_sessions' => handleListUserSessions($sessionManager),
        'close_user_session' => handleCloseUserSession($sessionManager),
        'cleanup_expired' => handleCleanupExpired($sessionManager),
        default => sendJsonError("Acción no válida: $action", 'INVALID_ACTION', 400)
    };
} catch (Throwable $e) {
    error_log("adminSessionManagementAjax - Error: " . $e->getMessage());
    sendJsonError('Error en el servidor: ' . $e->getMessage(), 'SERVER_ERROR', 500);
}

/**
 * Obtiene todas las sesiones de un usuario específico (solo admin)
 */
function handleListUserSessions(SessionManager $sessionManager): void
{
    $userId = $_POST['user_id'] ?? '';

    if (!$userId) {
        sendJsonError('ID de usuario requerido', 'MISSING_PARAM', 400);
    }

    $sessions = $sessionManager->getAllSessions($userId, 100);
    sendJsonSuccess([
        'user_id' => $userId,
        'session_count' => count($sessions),
        'sessions' => $sessions
    ]);
}

/**
 * Cierra una sesión específica (admin override)
 */
function handleCloseUserSession(SessionManager $sessionManager): void
{
    $sessionId = $_POST['session_id'] ?? '';
    $reason = $_POST['reason'] ?? 'closed_by_admin';

    if (!$sessionId) {
        sendJsonError('ID de sesión requerido', 'MISSING_PARAM', 400);
    }

    $result = $sessionManager->closeSession($sessionId, $reason);

    if ($result) {
        sendJsonSuccess(['message' => 'Sesión cerrada por administrador']);
    } else {
        sendJsonError('No se pudo cerrar la sesión', 'SERVER_ERROR', 500);
    }
}

/**
 * Limpia todas las sesiones expiradas (admin cleanup)
 */
function handleCleanupExpired(SessionManager $sessionManager): void
{
    $cleaned = $sessionManager->cleanupExpiredSessions();

    sendJsonSuccess([
        'message' => "Se limpiaron $cleaned sesiones expiradas",
        'cleaned_count' => $cleaned
    ]);
}
