<?php 

function esNulo(array $parametros)
{
    foreach($parametros as $parametro) {
        if(strlen(trim($parametro)) < 1) {
        return true;
        }
    }
    return false;
}

function esEmail($email)
{
    if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    }
    return false;
}

function validaPassword($password, $repassword) {
    if(strcmp($password, $repassword) === 0) {
        return true;
    }
    return false;
}

function generarToken() 
{
    try {
        return bin2hex(random_bytes(32));
    } catch (Exception $e) {
        return hash('sha256', uniqid((string) mt_rand(), true));
    }
}

function registraCliente(array $datos, $con) 
{

    $sql = $con->prepare("INSERT INTO clientes (nombres, apellidos, email, telefono, direccion, estatus, fecha_alta) VALUES (?,?,?,?,?, 1, 
    now())");
    if ($sql->execute($datos)) {
        return $con->lastInsertId();
    }
    return 0;

}

function registraUsuario(array $datos, $con)
{

    $sql = $con->prepare("INSERT INTO usuarios (usuario, password, token, id_cliente) VALUES (?,?,?,?)");
    if ($sql->execute($datos)) {
        return $con->lastInsertId();
    }
    return 0;
}



function usuarioExiste($usuario, $con) 
{

    $sql = $con->prepare("SELECT id FROM usuarios WHERE usuario LIKE ? LIMIT 1");
    $sql->execute([$usuario]);
    if ($sql->fetchColumn() > 0) {
        return true;
    }
    return false;

}

function emailExiste($email, $con) 
{

    $sql = $con->prepare("SELECT id FROM clientes WHERE email LIKE ? LIMIT 1");
    $sql->execute([$email]);
    if ($sql->fetchColumn() > 0) {
        return true;
    }
    return false;

}

function mostrarMensajes(array $errors) {
    if(count($errors) > 0) {
        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert"><ul>';
        foreach($errors as $error) {
            echo '<li>'. $error .'</li>'; 
        }
        echo '<ul>';
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
}



function validaToken($id, $token, $con) 
{
    $msg = "";
    $sql = $con->prepare("SELECT id FROM usuarios WHERE id = ? AND token LIKE ? LIMIT 1");
    $sql->execute([$id, $token]);
    if ($sql->fetchColumn() > 0) {
        if(activarUsuario($id, $con)) {
    $msg = "Cuenta activada con éxito.";
        } else {
            $msg = "Error al activar cuenta.";
        }
    } else {
        $msg = "No existe el registro del cliente.";
    }
    return $msg;

}

function activarUsuario($id, $con) {
    $sql = $con->prepare("UPDATE usuarios SET activacion = 1, token = '' WHERE id = ?");
    if (!$sql->execute([$id])) {
        return false;
    }
    
    $sqlAuth = $con->prepare("UPDATE auth_users SET is_active = 1, updated_at = NOW() WHERE legacy_usuario_id = ? AND role = 'customer'");
    $sqlAuth->execute([$id]);
    return true;
}

function registraAuthUser(string $usuario, string $email, string $passHash, int $usuarioId, int $clienteId, PDO $con): bool
{
    $sql = $con->prepare("
        INSERT INTO auth_users
            (uid, username, email_login, password_hash, role, is_active,
             failed_attempts, legacy_usuario_id, legacy_cliente_id, created_at, updated_at)
        VALUES (UUID(), ?, ?, ?, 'customer', 0, 0, ?, ?, NOW(), NOW())
    ");
    return $sql->execute([$usuario, $email, $passHash, $usuarioId, $clienteId]);
}

function solicitaPassword($user_id, $con) {

    $token = generarToken();

    $sql = $con->prepare("UPDATE usuarios SET token_password=?, password_request=1 WHERE id= ?");
    if($sql->execute([$token, $user_id])) {
        return $token;
    }
    return null;
}

function verificaTokenRequest($user_id, $token, $con) {
    $sql = $con->prepare("SELECT id FROM usuarios WHERE id = ? AND token_password LIKE ?
    AND password_request = 1 LIMIT 1");
    $sql->execute([$user_id, $token]);
    if($sql->fetchColumn() > 0) {
        return true;
    }
    return false;
}

function actualizaPassword($user_id, $password, $con) {
    try {
        $con->beginTransaction();

        $sql = $con->prepare("UPDATE usuarios SET password=?, token_password='', password_request=0 WHERE id= ?");
        $okLegacy = $sql->execute([$password, $user_id]);

        $sqlAuth = $con->prepare("UPDATE auth_users SET password_hash = ?, updated_at = NOW() WHERE legacy_usuario_id = ? AND role = 'customer'");
        $okAuth = $sqlAuth->execute([$password, $user_id]);

        if ($okLegacy && $okAuth) {
            $con->commit();
            return true;
        }

        $con->rollBack();
        return false;
    } catch (Exception $e) {
        if ($con->inTransaction()) {
            $con->rollBack();
        }
        return false;
    }
}

function csrfToken(string $formKey): string
{
    if (!isset($_SESSION['csrf_tokens']) || !is_array($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }

    if (empty($_SESSION['csrf_tokens'][$formKey])) {
        $_SESSION['csrf_tokens'][$formKey] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_tokens'][$formKey];
}

function csrfInput(string $formKey): string
{
    $token = csrfToken($formKey);
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function validaCsrfToken(string $formKey, ?string $token): bool
{
    if (!isset($_SESSION['csrf_tokens'][$formKey])) {
        return false;
    }

    if (!is_string($token) || $token === '') {
        return false;
    }

    $isValid = hash_equals($_SESSION['csrf_tokens'][$formKey], $token);

    if ($isValid) {
        unset($_SESSION['csrf_tokens'][$formKey]);
    }

    return $isValid;
}

function obtenerPerfilCliente(int $clienteId, $con): ?array
{
    $sql = $con->prepare("SELECT c.id, c.nombres, c.apellidos, c.email, c.telefono, c.direccion, c.estatus, c.fecha_alta, c.fecha_modifica, u.usuario FROM clientes c INNER JOIN usuarios u ON u.id_cliente = c.id WHERE c.id = ? LIMIT 1");
    $sql->execute([$clienteId]);
    $row = $sql->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function emailPerfilDisponible(string $email, int $clienteId, $con): bool
{
    $sql = $con->prepare("SELECT id FROM clientes WHERE email = ? AND id <> ? LIMIT 1");
    $sql->execute([$email, $clienteId]);
    return $sql->fetchColumn() === false;
}

function actualizarPerfilCliente(int $clienteId, array $datos, $con): bool
{
    $sql = $con->prepare("UPDATE clientes SET nombres = ?, apellidos = ?, email = ?, telefono = ?, direccion = ?, fecha_modifica = NOW() WHERE id = ?");
    return $sql->execute([
        $datos['nombres'],
        $datos['apellidos'],
        $datos['email'],
        $datos['telefono'],
        $datos['direccion'],
        $clienteId
    ]);
}

function obtenerAuthUserContext(int $authUserId, int $legacyUserId, int $clienteId, $con): ?array
{
    if ($authUserId > 0) {
        $sql = $con->prepare("SELECT id, legacy_usuario_id, legacy_cliente_id FROM auth_users WHERE id = ? AND role = 'customer' LIMIT 1");
        $sql->execute([$authUserId]);
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }
    }

    if ($legacyUserId > 0) {
        $sql = $con->prepare("SELECT id, legacy_usuario_id, legacy_cliente_id FROM auth_users WHERE legacy_usuario_id = ? AND role = 'customer' LIMIT 1");
        $sql->execute([$legacyUserId]);
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }
    }

    if ($clienteId > 0) {
        $sql = $con->prepare("SELECT id, legacy_usuario_id, legacy_cliente_id FROM auth_users WHERE legacy_cliente_id = ? AND role = 'customer' LIMIT 1");
        $sql->execute([$clienteId]);
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }
    }

    return null;
}

function verificarPasswordActual(int $authUserId, int $legacyUserId, int $clienteId, string $password, $con): bool
{
    $sql = $con->prepare("SELECT password_hash FROM auth_users WHERE id = ? AND role = 'customer' LIMIT 1");
    $context = obtenerAuthUserContext($authUserId, $legacyUserId, $clienteId, $con);

    if (!$context) {
        return false;
    }

    $sql->execute([(int) $context['id']]);
    $hash = $sql->fetchColumn();

    if (!$hash) {
        return false;
    }

    return password_verify($password, $hash);
}

function cambiarPasswordPerfil(int $authUserId, int $legacyUserId, int $clienteId, string $newPasswordHash, $con): bool
{
    try {
        $context = obtenerAuthUserContext($authUserId, $legacyUserId, $clienteId, $con);
        if (!$context) {
            return false;
        }

        $con->beginTransaction();

        $sqlAuth = $con->prepare("UPDATE auth_users SET password_hash = ?, updated_at = NOW() WHERE id = ? AND role = 'customer'");
        $okAuth = $sqlAuth->execute([$newPasswordHash, (int) $context['id']]);

        $okLegacy = true;
        $legacyId = (int) ($context['legacy_usuario_id'] ?? 0);
        if ($legacyId > 0) {
            $sqlLegacy = $con->prepare("UPDATE usuarios SET password = ?, token_password = '', password_request = 0 WHERE id = ?");
            $okLegacy = $sqlLegacy->execute([$newPasswordHash, $legacyId]);
        }

        if ($okAuth && $okLegacy) {
            $con->commit();
            return true;
        }

        $con->rollBack();
        return false;
    } catch (Exception $e) {
        if ($con->inTransaction()) {
            $con->rollBack();
        }
        return false;
    }
}

function desactivarCuentaCliente(int $authUserId, int $legacyUserId, int $clienteId, $con): bool
{
    try {
        $context = obtenerAuthUserContext($authUserId, $legacyUserId, $clienteId, $con);
        if (!$context) {
            return false;
        }

        $con->beginTransaction();

        $sqlCliente = $con->prepare("UPDATE clientes SET estatus = 0, fecha_baja = NOW(), fecha_modifica = NOW() WHERE id = ?");
        $sqlAuth = $con->prepare("UPDATE auth_users SET is_active = 0, updated_at = NOW() WHERE id = ? AND role = 'customer'");

        $okLegacy = true;
        $legacyId = (int) ($context['legacy_usuario_id'] ?? 0);
        if ($legacyId > 0) {
            $sqlUsuario = $con->prepare("UPDATE usuarios SET activacion = 0 WHERE id = ? AND id_cliente = ?");
            $okLegacy = $sqlUsuario->execute([$legacyId, $clienteId]);
        }

        $okCliente = $sqlCliente->execute([$clienteId]);
        $okAuth = $sqlAuth->execute([(int) $context['id']]);

        if ($okCliente && $okAuth && $okLegacy) {
            $con->commit();
            return true;
        }

        $con->rollBack();
        return false;
    } catch (Exception $e) {
        if ($con->inTransaction()) {
            $con->rollBack();
        }
        return false;
    }
}