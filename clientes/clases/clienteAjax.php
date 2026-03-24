<?php

require_once '../config/database.php';
require_once '../config/config.php';
require_once 'clienteFunciones.php';
require_once '../../shared/AuthGuards.php';

// Valida que sea una solicitud AJAX válida
requireAjaxRequest();

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_POST['action'])) {
    sendJsonError('Acción no especificada', 'MISSING_ACTION', 400);
}

$action = trim($_POST['action']);
$db = new Database();
$con = $db->conectar();

/**
 * Acciones públicas que NO requieren autenticación
 */
$publicActions = ['existeUsuario', 'existeEmail'];

/**
 * Acciones que requieren autenticación de cliente
 */
$protectedActions = [];

// Validar autenticación para acciones protegidas
if (in_array($action, $protectedActions, true)) {
    requireCustomerAjax('Debe iniciar sesión para esta acción');
}

try {
    if ($action === 'existeUsuario' && isset($_POST['usuario'])) {
        $usuario = trim($_POST['usuario']);

        if (strlen($usuario) < 3) {
            sendJsonError('El usuario debe tener al menos 3 caracteres', 'INVALID_INPUT', 400);
        }

        $existe = usuarioExiste($usuario, $con);
        sendJsonSuccess(['existe' => (bool) $existe]);

    } elseif ($action === 'existeEmail' && isset($_POST['email'])) {
        $email = trim($_POST['email']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendJsonError('Email inválido', 'INVALID_EMAIL', 400);
        }

        $existe = emailExiste($email, $con);
        sendJsonSuccess(['existe' => (bool) $existe]);

    } else {
        sendJsonError('Acción no reconocida o parámetros faltantes', 'INVALID_ACTION', 400);
    }
} catch (Exception $e) {
    // Log del error (en producción, escribir a archivo de logs)
    error_log('Error en clienteAjax.php - Acción: ' . $action . ' - Error: ' . $e->getMessage());
    sendJsonError('Error al procesar la solicitud', 'SERVER_ERROR', 500);
}