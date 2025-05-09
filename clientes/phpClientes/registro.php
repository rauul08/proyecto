<?php

require '../config/config.php';
require '../config/database.php';
require '../clases/clienteFunciones.php';
$db = new Database();
$con = $db->conectar();


$errors = [];

if(!empty($_POST)){

    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    $repassword = trim($_POST['repassword']);

    if(esNulo([$nombres, $apellidos, $email, $telefono, $direccion, $usuario, $password, $repassword])){
        $errors[] = "Debe llenar todos los campos";
    }

    if(!esEmail($email)) {
        $errors[] = "La dirección de correo no es valida";
    }

    if(!validaPassword($password, $repassword)) {
        $errors[] = "Las contraseñas no coinciden";
    }

    if(usuarioExiste($usuario, $con)) {
        $errors[] = "El nombre de usuario $usuario ya existe";
    }

    if(emailExiste($email, $con)) {
        $errors[] = "El correo electrónico $email ya esta registrado";
    }

    if(count($errors) == 0) {

    $id = registraCliente([$nombres, $apellidos, $email, $telefono, $direccion], $con);

    if ($id > 0) {

        require '../clases/Mailer.php';
        $mailer = new Mailer();
        $token = generarToken();

        $pass_hash = password_hash($password, PASSWORD_DEFAULT);

        $idUsuario = registraUsuario([$usuario, $pass_hash, $token, $id], $con);

        if ($idUsuario > 0) {

            $url = SITE_URL . '/activa_cliente.php?id=' . $idUsuario . '&token='. $token;
            $asunto = "Activar cuenta - JIREH FODS"; 
            $cuerpo = "Estimado $nombres: <br> Para continuar con el proceso de registro es indispensable de click
            en la siguiente liga <a href='$url'>Activar Cuenta</a>";

            if($mailer->enviarEmail($email, $asunto, $cuerpo)) {
                echo "Para terminar el proceso de registro siga las instrucciones que le hemos enviado
                a la dirección de correo electrónico $email";
                exit;
            }

        } else {
            $errors[] = "Error al registrar usuario";
        }
    } else {
        $errors[] = "Error al registrar cliente";
      }
    }

}

?>



<!DOCTYPE html>
<html lang="es">
<head>
    <title>JIREH | Registro</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="09f">
    <link rel="stylesheet" href="../css/inicio.css">    
    <link rel="icon" type="images/jpeg"  href="../images/jLeft.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Fugaz+One&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <meta property="og:title" content="Tienda de comida rapida">
    <meta property="og:description" content="Tienda en linea de la empresa de comida rapida JIREH">  
    <style>
        body {
            background-color: ghostwhite;
            font-family: 'Arial', sans-serif; /* Fuente genérica para el contenido */
        }
        .form-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-container .btn-primary {
            background-color: #8B4513;
            border-color: #8B4513;
            margin-top: 10px;
            font-family: 'Fugaz One', sans-serif;
        }
        .form-container .btn-primary:hover {
            background-color: #BE1622;
            border-color: #BE1622;
        }
        .form-container h2 {
            color: #FFA500;
            font-family: 'Fugaz One', cursive;
        }
        .form-container label {
            font-family: 'Anton', sans-serif;
            font-size: 17px;
        }

        .form-group {
            padding-bottom: 5px;
        }



        .fondo {
            background-color: #FFE1DE;
        }
        .fondo a {
            color: #8B4513;
        }
        .nav-underline .nav-item .nav-link {
            border-bottom: 2px solid transparent;
            transition: border-color 0.2s;
            
        }
        .nav-underline .nav-item .nav-link:hover,
        .nav-underline .nav-item .nav-link.active {
            border-color: #FFD700;
            color: #8B4513;
        }

        .badge.bg-secondary {
            border-color: #FFD700;
            color: #FFA500;
        }
        .navbar-brand img {
            height: 40px;
        }
    </style>
</head>
<body>
    <header>
        <nav class="fondo">
            <ul class="nav nav-underline">
                <a class="navbar-brand" href="../html/index.html">
                    <img id="logo" src="../images/jLeft.png" alt="Logotipo de la Empresa">
                </a>
                <li class="nav-item">
                    <a class="nav-link" href="../html/index.html">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Menú</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="promociones.php">Paquetes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../html/delivery.html">Delivery</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../html/conócenos.html">Conócenos</a>
                </li>
                <li class="nav-item">
                <a class="nav-link disabled" aria-disabled="true">| Social Media <li><i class="fa fa-phone fa-rotate-90"></i><li><i class="fa fa-envelope" aria-hidden="true"></i> |</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="#" aria-current="page"><span class="badge bg-secondary">Registrarse</span></a>
                </li>
            </ul>
        </nav>  
    </header>

    <div class="container">
        <div class="form-container">
            <h2 class="text-center">Registro de Sesión</h2>
            <?php mostrarMensajes($errors); ?>
            <form method="post" action="registro.php" autocomplete="off">
                <div class="form-group">
                    <label for="nombres">Nombres</label>
                    <input type="text" class="form-control" id="nombres" name="nombres" placeholder="Ingresa tus nombres" required>
                </div>
                <div class="form-group">
                    <label for="apellidos">Apellidos</label>
                    <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="Ingresa tus apellidos" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Ingresa tu correo electrónico" required>
                    <span id="validaEmail" class="text-danger"></span>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Ingresa tu teléfono" required>
                </div>
                <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Ingresa tu dirección" required>
                </div>
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Crea tu nombre de usuario" required>
                    <span id="validaUsuario" class="text-danger"></span>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Crea tu contraseña" required>
                </div>
                <div class="form-group">
                    <label for="repassword">Repetir contraseña</label>
                    <input type="password" class="form-control" id="repassword" name="repassword" placeholder="Comprueba tu contraseña" required>
                </div>
                <button type="submit" class="btn btn-primary">Registrar</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        let txtUsuario = document.getElementById('usuario')
        txtUsuario.addEventListener("blur", function(){
            existeUsuario(txtUsuario.value)
        }, false)

        let txtEmail = document.getElementById('email')
        txtEmail.addEventListener("blur", function(){
            existeEmail(txtEmail.value)
        }, false)

        function existeEmail(email) {
            let url = "../clases/clienteAjax.php"
            let formData = new FormData()
            formData.append("action", "existeEmail")
            formData.append("email", email)

            fetch(url, {
                method: 'POST',
                body: formData
            }).then(response => response.json())
            .then(data => {

                if(data.ok) {
                    document.getElementById('email').value = ''
                    document.getElementById('validaEmail').innerHTML = 'Email no disponible'
                } else {
                    document.getElementById('validaEmail').innerHTML = ''
                }
            })
        }

        function existeUsuario(usuario) {
            let url = "../clases/clienteAjax.php"
            let formData = new FormData()
            formData.append("action", "existeUsuario")
            formData.append("usuario", usuario)

            fetch(url, {
                method: 'POST',
                body: formData
            }).then(response => response.json())
            .then(data => {

                if(data.ok) {
                    document.getElementById('usuario').value = ''
                    document.getElementById('validaUsuario').innerHTML = 'Usuario no disponible'
                } else {
                    document.getElementById('validaUsuario').innerHTML = ''
                }
            })
        }
    </script>
</body>
</html>


