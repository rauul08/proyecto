<?php

require '../config/config.php';

unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['user_cliente']);
unset($_SESSION['user_type']);
unset($_SESSION['auth_user_id']);
unset($_SESSION['auth_role']);

if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();


header("Location: ../html/index.html");
