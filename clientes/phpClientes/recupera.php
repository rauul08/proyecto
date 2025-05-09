<?php

require '../config/config.php';
require '../config/database.php';
require '../clases/clienteFunciones.php';
$db = new Database();
$con = $db->conectar();


$errors = [];

if(!empty($_POST)){

    $email = trim($_POST['email']);

    if(esNulo([$email])){
        $errors[] = "Debe llenar todos los campos";
    }

    if(!esEmail($email)) {
        $errors[] = "La dirección de correo no es valida";
    }

    if(count($errors) == 0) {
    if(emailExiste($email, $con)) {
        $sql = $con->prepare("SELECT usuarios.id, clientes.nombres FROM usuarios
        INNER JOIN clientes ON usuarios.id_cliente=clientes.id WHERE clientes.email LIKE ? LIMIT 1");
        $sql->execute([$email]);
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        $user_id = $row['id'];
        $nombres = $row['nombres'];

        $token = solicitaPassword($user_id, $con);

        if($token !== null) {
        
        require '../clases/Mailer.php';
        $mailer = new Mailer();

        $url = SITE_URL . '/reset_password.php?id=' . $user_id . '&token='. $token;
        $asunto = "Recuperar cuenta - JIREH FODS"; 
        $cuerpo = "Estimado $nombres: <br> Si has solicitado el cambio de tu contraseña da click en el siguiente link
        <a href='$url'>$url</a>.";
        $cuerpo.= "<br> Si no hiciste esta solicitud puedes ignorar este mensaje.";

        if($mailer->enviarEmail($email, $asunto, $cuerpo)) {
            echo "<p><b>Correo enviado</b></p>";
            echo "<p>Se ha enviado un correo electrónico a la dirección $email para reestablecer 
            la contraseña.</p>";
            exit;
        }


        }

      } else {
        $errors[] = "No existe una cuenta asociada a esta dirección de correo";
      }
    }
 }

?>



<!DOCTYPE html>
<html lang="es">
<head>
    <title>JIREH | Recupera</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="09f">
    <link rel="stylesheet" href="../css/login.css">    
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
           /* font-family: 'Arial', sans-serif;  Fuente genérica para el contenido */
        }
        
        .form-container label {
            font-family: 'Anton', sans-serif;
            font-size: 17px;
        }

        .form-group {
            padding-bottom: 5px;
        }


        .fondo a {
            color: #8B4513;
        }
        .nav-underline {
            padding:5px;
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

        .fondo {
    display:flex;
justify-content: center;
align-items: flex-start; /* Cambio de align-items */
height: 60px;
margin-top: 0px;
background-color: #FFE1DE;
}

#logo {
    max-height: 100%;
    height: 40px; /* O puedes usar max-height: 60px; para asegurar que se mantenga dentro del navbar */
    width: auto;
    margin-top:4px;
}

.slide img {
    height: 91.9vh;
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
}
.form-container .btn-primary:hover {
    background-color: #BE1622;
    border-color: #BE1622;
}
.form-container h2 {
    color: #FFA500;
    font-family: 'Fugaz One', cursive;
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
                    <a class="nav-link active" href="#" aria-current="page"><span class="badge bg-secondary">Recuperar</span></a>
                </li>
            </ul>
        </nav>  
    </header>
    <main class="form-login m-auto pt-4">
        <h3>Recuperar contraseña</h3>

        <?php	mostrarMensajes($errors); ?>

        <form class="row g-3" action="recupera.php" method="post" autocomplete="off">
            <div class="form-floating">
                <input class="form-control" type="email" name="email" id="email" placeholder="Correo electrónico" required>
                <label for="email">Correo electrónico</label>
            </div>

            <div class="d-grid gap-3 col-12">
                <button type="submit" class="btn btn-primary">Solicitar</button>
            </div>

            <hr>

            <div class="col-12">
                ¿No tiene cuenta? <a href="registro.php">Registrarse aquí</a>
            </div>
            
        </form>
    </main>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>