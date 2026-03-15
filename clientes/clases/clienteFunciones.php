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
    return $sql->execute([$id]);
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
    $sql = $con->prepare("UPDATE usuarios SET password=?, token_password='', password_request=0 WHERE id= ?");
    if($sql->execute([$password, $user_id])) {
        return true;
    }
    return false;
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
    $sql = $con->prepare("SELECT id, nombres, apellidos, email, telefono, direccion, estatus, fecha_alta, fecha_modifica FROM clientes WHERE id = ? LIMIT 1");
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

function verificarPasswordActual(int $userId, string $password, $con): bool
{
    $sql = $con->prepare("SELECT password FROM usuarios WHERE id = ? LIMIT 1");
    $sql->execute([$userId]);
    $hash = $sql->fetchColumn();

    if (!$hash) {
        return false;
    }

    return password_verify($password, $hash);
}

function cambiarPasswordPerfil(int $userId, string $newPasswordHash, $con): bool
{
    $sql = $con->prepare("UPDATE usuarios SET password = ?, token_password = '', password_request = 0 WHERE id = ?");
    return $sql->execute([$newPasswordHash, $userId]);
}

function desactivarCuentaCliente(int $userId, int $clienteId, $con): bool
{
    try {
        $con->beginTransaction();

        $sqlCliente = $con->prepare("UPDATE clientes SET estatus = 0, fecha_baja = NOW(), fecha_modifica = NOW() WHERE id = ?");
        $sqlUsuario = $con->prepare("UPDATE usuarios SET activacion = 0 WHERE id = ? AND id_cliente = ?");

        $okCliente = $sqlCliente->execute([$clienteId]);
        $okUsuario = $sqlUsuario->execute([$userId, $clienteId]);

        if ($okCliente && $okUsuario) {
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