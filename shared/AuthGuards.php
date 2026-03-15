<?php

require_once __DIR__ . '/AuthService.php';

function currentAuthRole(): ?string
{
    ensureSessionStarted();

    if (!empty($_SESSION['auth_role'])) {
        return normalizeRole((string) $_SESSION['auth_role']);
    }

    if (!empty($_SESSION['user_type']) && strtolower((string) $_SESSION['user_type']) === 'admin') {
        return 'admin';
    }

    if (!empty($_SESSION['user_cliente'])) {
        return 'customer';
    }

    return null;
}

function isAdminAuthenticated(): bool
{
    return currentAuthRole() === 'admin';
}

function isCustomerAuthenticated(): bool
{
    return currentAuthRole() === 'customer';
}

function denyUnauthorized(string $responseMode, string $redirectUrl, string $message = 'No autenticado'): void
{
    if ($responseMode === 'json') {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(401);
        }
        echo json_encode([
            'ok' => false,
            'error' => $message
        ]);
        exit;
    }

    header('Location: ' . $redirectUrl);
    exit;
}

function requireAdminAuth(array $options = []): void
{
    $responseMode = $options['response_mode'] ?? 'redirect';
    $redirectUrl = $options['redirect'] ?? '../phpAdmin/loginAdmin.php';

    if (isAdminAuthenticated()) {
        return;
    }

    denyUnauthorized($responseMode, $redirectUrl, 'Acceso de administrador requerido');
}

function requireCustomerAuth(array $options = []): void
{
    $responseMode = $options['response_mode'] ?? 'redirect';
    $redirectUrl = $options['redirect'] ?? 'login.php';

    if (isCustomerAuthenticated()) {
        return;
    }

    denyUnauthorized($responseMode, $redirectUrl, 'Debe iniciar sesion para continuar');
}
