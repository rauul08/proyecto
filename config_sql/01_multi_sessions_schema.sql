-- Script SQL para permitir múltiples sesiones simultáneas
-- Ejecuta este script en tu base de datos 'tienda_online'

-- Crear tabla para almacenar información de sesiones activas
CREATE TABLE
IF NOT EXISTS user_sessions
(
    id CHAR
(36) PRIMARY KEY DEFAULT
(UUID
()),
    auth_user_id CHAR
(36) NOT NULL COMMENT 'ID del usuario en auth_users',
    session_id VARCHAR
(128) NOT NULL UNIQUE COMMENT 'PHP session_id()',
    ip_address VARCHAR
(45) NOT NULL COMMENT 'IP del cliente (IPv4 o IPv6)',
    user_agent VARCHAR
(500) COMMENT 'User-Agent del navegador',
    device_info VARCHAR
(255) COMMENT 'Identificación del dispositivo (ej: Chrome/Windows)',
    is_active TINYINT
(1) DEFAULT 1 COMMENT '1 = activa, 0 = cerrada',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha/hora login',
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON
UPDATE CURRENT_TIMESTAMP COMMENT 'Última actividad',
    expires_at TIMESTAMP
NULL COMMENT 'Cuándo expira la sesión (inactividad)',
    closed_at TIMESTAMP NULL COMMENT 'Cuándo se cerró manualmente (si aplica)',

    CONSTRAINT fk_user_sessions_auth_users
        FOREIGN KEY
(auth_user_id)
        REFERENCES auth_users
(id)
        ON
DELETE CASCADE,

    INDEX idx_auth_user_id (auth_user_id),
    INDEX idx_is_active
(is_active),
    INDEX idx_created_at
(created_at),
    INDEX idx_expires_at
(expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rastreo de sesiones activas por usuario';

-- Crear tabla de auditoría (opcional) para historial de sesiones
CREATE TABLE
IF NOT EXISTS user_sessions_audit
(
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    auth_user_id CHAR
(36) NOT NULL,
    session_id VARCHAR
(128),
    action VARCHAR
(50) COMMENT 'login, logout, expired, etc.',
    ip_address VARCHAR
(45),
    user_agent VARCHAR
(500),
    reason VARCHAR
(255) COMMENT 'Motivo del cierre (manual, timeout, logout, etc.)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_audit_auth_users
        FOREIGN KEY
(auth_user_id)
        REFERENCES auth_users
(id)
        ON
DELETE CASCADE,

    INDEX idx_auth_user_id (auth_user_id),
    INDEX idx_created_at
(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Auditoría de sesiones para historial';

-- Agregar configurable de máximo de sesiones por usuario (opcional)
CREATE TABLE
IF NOT EXISTS user_session_limits
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR
(50) NOT NULL UNIQUE COMMENT 'admin, customer',
    max_concurrent_sessions INT DEFAULT 5 COMMENT 'Máximo de sesiones simultáneas',
    session_timeout_minutes INT DEFAULT 1440 COMMENT 'Timeout de inactividad (minutos)',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON
UPDATE CURRENT_TIMESTAMP,

    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Límites de sesiones por rol';

-- Insertar valores por defecto para límites
INSERT IGNORE
INTO user_session_limits
(role, max_concurrent_sessions, session_timeout_minutes) VALUES
('customer', 5, 1440),
('admin', 3, 1440);

-- Procedimiento almacenado para limpiar sesiones expiradas (ejecutar periódicamente)
DELIMITER $$

CREATE PROCEDURE
IF NOT EXISTS cleanup_expired_sessions
()
BEGIN
    DECLARE expired_session_ids VARCHAR
    (500);

-- Registrar en auditoría antes de eliminar
INSERT INTO user_sessions_audit
    (auth_user_id, session_id, action, reason)
SELECT auth_user_id, session_id, 'expired', 'Sesión expirada por inactividad'
FROM user_sessions
WHERE is_active = 1
    AND expires_at IS NOT NULL
    AND expires_at < NOW();

-- Marcar como cerradas
UPDATE user_sessions
    SET is_active = 0, closed_at = NOW()
    WHERE is_active = 1
    AND expires_at IS NOT NULL
    AND expires_at < NOW();
END$$

DELIMITER ;

-- Índices adicionales para optimización de consultas
ALTER TABLE user_sessions ADD INDEX idx_session_active_expires (is_active, expires_at);
