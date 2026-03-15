<?php

require_once '../../shared/AuthService.php';

ensureSessionStarted();
clearLegacyAuthSession();
unset($_SESSION['login_attempts'], $_SESSION['login_lock_until']);

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();

header('Location: ../../clientes/html/index.html');
exit;
