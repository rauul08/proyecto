<?php
declare(strict_types=1);

/**
 * Gestor de sesiones múltiples por usuario.
 * Permite rastrear, gestionar y limpiar sesiones activas.
 */
class SessionManager
{
    private PDO $db;
    private int $sessionTimeoutMinutes = 1440; // 24 horas por defecto

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Registra una nueva sesión en la base de datos.
     *
     * @param string $authUserId ID del usuario autenticado
     * @param string $sessionId PHP session_id()
     * @param string $ipAddress IP del cliente
     * @param string|null $userAgent User-Agent del navegador
     * @return bool true si se registró exitosamente
     */
    public function createSession(
        string $authUserId,
        string $sessionId,
        string $ipAddress,
        ?string $userAgent = null
    ): bool {
        try {
            // Obtener timeout configurado para el rol del usuario
            $this->sessionTimeoutMinutes = $this->getSessionTimeoutForUser($authUserId);

            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$this->sessionTimeoutMinutes} minutes"));
            $deviceInfo = $this->extractDeviceInfo($userAgent);

            $sql = $this->db->prepare(
                "INSERT INTO user_sessions
                (auth_user_id, session_id, ip_address, user_agent, device_info, is_active, created_at, last_activity, expires_at)
                VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW(), ?)"
            );

            return $sql->execute([
                $authUserId,
                $sessionId,
                $ipAddress,
                $userAgent ?? '',
                $deviceInfo,
                $expiresAt
            ]);
        } catch (Throwable $e) {
            error_log("SessionManager::createSession - Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra una nueva sesión y cierra sesiones anteriores si se excede el límite.
     * Útil para políticas de "mantener X sesiones recientes".
     *
     * @param string $authUserId ID del usuario autenticado
     * @param string $sessionId PHP session_id()
     * @param string $ipAddress IP del cliente
     * @param string|null $userAgent User-Agent
     * @return array ['ok' => bool, 'session_id' => string, 'closed_sessions' => int]
     */
    public function createSessionWithLimit(
        string $authUserId,
        string $sessionId,
        string $ipAddress,
        ?string $userAgent = null
    ): array {
        try {
            // Crear nueva sesión
            if (!$this->createSession($authUserId, $sessionId, $ipAddress, $userAgent)) {
                return [
                    'ok' => false,
                    'session_id' => $sessionId,
                    'closed_sessions' => 0,
                    'error' => 'No se pudo registrar la sesión'
                ];
            }

            // Obtener límite de sesiones para el usuario
            $limit = $this->getMaxConcurrentSessions($authUserId);

            // Contar sesiones activas
            $activeSessions = $this->countActiveSessions($authUserId);
            $closedSessions = 0;

            // Si se excede el límite, cerrar las sesiones más antiguas
            if ($activeSessions > $limit) {
                $toClose = $activeSessions - $limit;
                $closedSessions = $this->closeOldestSessions($authUserId, $toClose, 'limit_exceeded');
            }

            return [
                'ok' => true,
                'session_id' => $sessionId,
                'closed_sessions' => $closedSessions,
                'active_count' => $this->countActiveSessions($authUserId)
            ];
        } catch (Throwable $e) {
            error_log("SessionManager::createSessionWithLimit - Error: " . $e->getMessage());
            return [
                'ok' => false,
                'session_id' => $sessionId,
                'closed_sessions' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene todas las sesiones activas de un usuario.
     *
     * @param string $authUserId ID del usuario
     * @return array Lista de sesiones activas
     */
    public function getActiveSessions(string $authUserId): array
    {
        try {
            $sql = $this->db->prepare(
                "SELECT id, session_id, ip_address, device_info, created_at, last_activity, expires_at
                FROM user_sessions
                WHERE auth_user_id = ? AND is_active = 1
                ORDER BY created_at DESC"
            );
            $sql->execute([$authUserId]);
            return $sql->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log("SessionManager::getActiveSessions - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todas las sesiones (activas e inactivas) de un usuario.
     *
     * @param string $authUserId ID del usuario
     * @param int $limit Número máximo de sesiones a retornar
     * @return array Lista de sesiones
     */
    public function getAllSessions(string $authUserId, int $limit = 50): array
    {
        try {
            $sql = $this->db->prepare(
                "SELECT id, session_id, ip_address, device_info, is_active, created_at, last_activity, expires_at, closed_at
                FROM user_sessions
                WHERE auth_user_id = ?
                ORDER BY created_at DESC
                LIMIT ?"
            );
            $sql->bindValue(1, $authUserId, PDO::PARAM_STR);
            $sql->bindValue(2, $limit, PDO::PARAM_INT);
            $sql->execute();
            return $sql->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log("SessionManager::getAllSessions - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualiza la última actividad de una sesión.
     * Debe llamarse en cada request autenticado.
     *
     * @param string $sessionId PHP session_id()
     * @return bool true si se actualizó
     */
    public function updateLastActivity(string $sessionId): bool
    {
        try {
            // Extender el timeout al actualizar actividad
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$this->sessionTimeoutMinutes} minutes"));

            $sql = $this->db->prepare(
                "UPDATE user_sessions
                SET last_activity = NOW(), expires_at = ?
                WHERE session_id = ? AND is_active = 1"
            );

            return $sql->execute([$expiresAt, $sessionId]);
        } catch (Throwable $e) {
            error_log("SessionManager::updateLastActivity - Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cierra una sesión específica.
     *
     * @param string $sessionId PHP session_id()
     * @param string $reason Motivo del cierre (logout, manual, timeout, etc.)
     * @return bool true si se cerró
     */
    public function closeSession(string $sessionId, string $reason = 'logout'): bool
    {
        try {
            $sql = $this->db->prepare(
                "UPDATE user_sessions
                SET is_active = 0, closed_at = NOW()
                WHERE session_id = ? AND is_active = 1"
            );

            $result = $sql->execute([$sessionId]);

            // Registrar en auditoría
            if ($result && $sql->rowCount() > 0) {
                $this->logAudit($sessionId, 'logout', $reason);
            }

            return $result;
        } catch (Throwable $e) {
            error_log("SessionManager::closeSession - Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cierra una sesión de otro dispositivo (el usuario cierra sesión remota).
     *
     * @param string $authUserId ID del usuario
     * @param string $sessionIdToClose ID de sesión a cerrar
     * @param string $currentSessionId Sesión actual del usuario (para validar permisos)
     * @return bool true si se cerró
     */
    public function closeRemoteSession(
        string $authUserId,
        string $sessionIdToClose,
        string $currentSessionId
    ): bool {
        try {
            // Validar que la sesión a cerrar pertenece al usuario
            $sql = $this->db->prepare(
                "UPDATE user_sessions
                SET is_active = 0, closed_at = NOW()
                WHERE session_id = ? AND auth_user_id = ? AND is_active = 1 AND session_id != ?"
            );

            $result = $sql->execute([$sessionIdToClose, $authUserId, $currentSessionId]);

            if ($result && $sql->rowCount() > 0) {
                $this->logAudit($sessionIdToClose, 'logout', 'closed_remotely');
            }

            return $result;
        } catch (Throwable $e) {
            error_log("SessionManager::closeRemoteSession - Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cierra todas las sesiones de un usuario excepto la actual.
     *
     * @param string $authUserId ID del usuario
     * @param string $currentSessionId Sesión a mantener abierta
     * @return int Número de sesiones cerradas
     */
    public function closeAllOtherSessions(string $authUserId, string $currentSessionId): int
    {
        try {
            $sql = $this->db->prepare(
                "UPDATE user_sessions
                SET is_active = 0, closed_at = NOW()
                WHERE auth_user_id = ? AND session_id != ? AND is_active = 1"
            );

            $sql->execute([$authUserId, $currentSessionId]);
            return $sql->rowCount();
        } catch (Throwable $e) {
            error_log("SessionManager::closeAllOtherSessions - Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Verifica si una sesión es válida (activa y no expirada).
     *
     * @param string $sessionId PHP session_id()
     * @return bool true si es válida
     */
    public function isSessionValid(string $sessionId): bool
    {
        try {
            $sql = $this->db->prepare(
                "SELECT id FROM user_sessions
                WHERE session_id = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW())"
            );
            $sql->execute([$sessionId]);
            return $sql->rowCount() > 0;
        } catch (Throwable $e) {
            error_log("SessionManager::isSessionValid - Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Valida que una sesión pertenece a un usuario específico.
     *
     * @param string $sessionId PHP session_id()
     * @param string $authUserId ID del usuario esperado
     * @return bool true si la sesión es válida y pertenece al usuario
     */
    public function isSessionValidForUser(string $sessionId, string $authUserId): bool
    {
        try {
            $sql = $this->db->prepare(
                "SELECT id FROM user_sessions
                WHERE session_id = ? AND auth_user_id = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW())"
            );
            $sql->execute([$sessionId, $authUserId]);
            return $sql->rowCount() > 0;
        } catch (Throwable $e) {
            error_log("SessionManager::isSessionValidForUser - Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información de una sesión.
     *
     * @param string $sessionId PHP session_id()
     * @return array|null Información de la sesión o null
     */
    public function getSessionInfo(string $sessionId): ?array
    {
        try {
            $sql = $this->db->prepare(
                "SELECT id, auth_user_id, session_id, ip_address, device_info, is_active,
                        created_at, last_activity, expires_at
                FROM user_sessions
                WHERE session_id = ?"
            );
            $sql->execute([$sessionId]);
            return $sql->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {
            error_log("SessionManager::getSessionInfo - Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cuenta sesiones activas de un usuario.
     *
     * @param string $authUserId ID del usuario
     * @return int Número de sesiones activas
     */
    public function countActiveSessions(string $authUserId): int
    {
        try {
            $sql = $this->db->prepare(
                "SELECT COUNT(*) FROM user_sessions
                WHERE auth_user_id = ? AND is_active = 1"
            );
            $sql->execute([$authUserId]);
            return (int) $sql->fetchColumn();
        } catch (Throwable $e) {
            error_log("SessionManager::countActiveSessions - Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Limpia sesiones expiradas de toda la base de datos.
     * Llamar periódicamente (ej: via cronjob).
     *
     * @return int Número de sesiones limpias
     */
    public function cleanupExpiredSessions(): int
    {
        try {
            // Registrar en auditoría
            $this->db->exec(
                "INSERT INTO user_sessions_audit (auth_user_id, session_id, action, reason)
                SELECT auth_user_id, session_id, 'expired', 'Sesión expirada automáticamente'
                FROM user_sessions
                WHERE is_active = 1 AND expires_at IS NOT NULL AND expires_at < NOW()"
            );

            // Marcar como cerradas
            $sql = $this->db->exec(
                "UPDATE user_sessions
                SET is_active = 0, closed_at = NOW()
                WHERE is_active = 1 AND expires_at IS NOT NULL AND expires_at < NOW()"
            );

            return $sql;
        } catch (Throwable $e) {
            error_log("SessionManager::cleanupExpiredSessions - Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene límite de sesiones concurrentes para un usuario.
     *
     * @param string $authUserId ID del usuario
     * @return int Máximo de sesiones permitidas
     */
    private function getMaxConcurrentSessions(string $authUserId): int
    {
        try {
            // Obtener rol del usuario
            $sqlRole = $this->db->prepare("SELECT role FROM auth_users WHERE id = ?");
            $sqlRole->execute([$authUserId]);
            $role = $sqlRole->fetchColumn();

            if (!$role) {
                return 5; // Por defecto
            }

            // Obtener límite para el rol
            $sqlLimit = $this->db->prepare(
                "SELECT max_concurrent_sessions FROM user_session_limits WHERE role = ?"
            );
            $sqlLimit->execute([$role]);
            $limit = $sqlLimit->fetchColumn();

            return (int) $limit ?: 5;
        } catch (Throwable $e) {
            error_log("SessionManager::getMaxConcurrentSessions - Error: " . $e->getMessage());
            return 5;
        }
    }

    /**
     * Obtiene timeout de sesión para un usuario según su rol.
     *
     * @param string $authUserId ID del usuario
     * @return int Timeout en minutos
     */
    private function getSessionTimeoutForUser(string $authUserId): int
    {
        try {
            $sqlRole = $this->db->prepare("SELECT role FROM auth_users WHERE id = ?");
            $sqlRole->execute([$authUserId]);
            $role = $sqlRole->fetchColumn();

            if (!$role) {
                return 1440; // 24 horas por defecto
            }

            $sqlTimeout = $this->db->prepare(
                "SELECT session_timeout_minutes FROM user_session_limits WHERE role = ?"
            );
            $sqlTimeout->execute([$role]);
            $timeout = $sqlTimeout->fetchColumn();

            return (int) $timeout ?: 1440;
        } catch (Throwable $e) {
            error_log("SessionManager::getSessionTimeoutForUser - Error: " . $e->getMessage());
            return 1440;
        }
    }

    /**
     * Cierra N sesiones más antiguas de un usuario.
     *
     * @param string $authUserId ID del usuario
     * @param int $count Número de sesiones a cerrar
     * @param string $reason Motivo del cierre
     * @return int Número de sesiones cerradas
     */
    private function closeOldestSessions(string $authUserId, int $count, string $reason = 'limit_exceeded'): int
    {
        try {
            // Obtener IDs de las sesiones más antiguas
            $sqlSelect = $this->db->prepare(
                "SELECT id, session_id FROM user_sessions
                WHERE auth_user_id = ? AND is_active = 1
                ORDER BY created_at ASC
                LIMIT ?"
            );
            $sqlSelect->bindValue(1, $authUserId, PDO::PARAM_STR);
            $sqlSelect->bindValue(2, $count, PDO::PARAM_INT);
            $sqlSelect->execute();
            $sessions = $sqlSelect->fetchAll(PDO::FETCH_ASSOC);

            // Cerrar sesiones
            $sqlUpdate = $this->db->prepare(
                "UPDATE user_sessions
                SET is_active = 0, closed_at = NOW()
                WHERE id = ?"
            );

            $closed = 0;
            foreach ($sessions as $session) {
                if ($sqlUpdate->execute([$session['id']])) {
                    $this->logAudit($session['session_id'], 'closed_auto', $reason);
                    $closed++;
                }
            }

            return $closed;
        } catch (Throwable $e) {
            error_log("SessionManager::closeOldestSessions - Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Extrae información del dispositivo del User-Agent.
     *
     * @param string|null $userAgent User-Agent string
     * @return string Descripción del dispositivo
     */
    private function extractDeviceInfo(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'Unknown Device';
        }

        $info = '';

        // Detectar navegador
        if (strpos($userAgent, 'Chrome') !== false) {
            $info = 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $info = 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            $info = 'Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            $info = 'Edge';
        } else {
            $info = 'Other Browser';
        }

        // Detectar SO
        if (strpos($userAgent, 'Windows') !== false) {
            $info .= ' / Windows';
        } elseif (strpos($userAgent, 'Mac') !== false) {
            $info .= ' / macOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $info .= ' / Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $info .= ' / Android';
        } elseif (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
            $info .= ' / iOS';
        }

        return $info;
    }

    /**
     * Registra evento en tabla de auditoría.
     *
     * @param string $sessionId PHP session_id()
     * @param string $action Acción realizada
     * @param string|null $reason Razón/motivo
     * @return bool
     */
    private function logAudit(string $sessionId, string $action, ?string $reason = null): bool
    {
        try {
            $sql = $this->db->prepare(
                "INSERT INTO user_sessions_audit (auth_user_id, session_id, action, reason)
                SELECT auth_user_id, session_id, ?, ?
                FROM user_sessions
                WHERE session_id = ?"
            );

            return $sql->execute([$action, $reason, $sessionId]);
        } catch (Throwable $e) {
            error_log("SessionManager::logAudit - Error: " . $e->getMessage());
            return false;
        }
    }
}
