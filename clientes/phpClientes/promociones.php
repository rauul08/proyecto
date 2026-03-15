<?php

require '../config/config.php';
require '../config/database.php';
$db = new Database();
$con = $db->conectar();



$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $sql = $con->prepare("SELECT id, nombre, descripcion, precio FROM paquetes WHERE nombre LIKE ? OR descripcion LIKE ?  LIMIT 18");
    $searchParam = '%' . $search . '%';
    $sql->bindParam(1, $searchParam, PDO::PARAM_STR);
    $sql->bindParam(2, $searchParam, PDO::PARAM_STR);
    $sql->execute();
    $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sql = $con->prepare("SELECT id, nombre, precio FROM paquetes WHERE activo=1");
    $sql->execute();
    $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
}

//print_r($_SESSION);

//session_destroy();

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <title>JIREH | Paquetes</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="09f">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="icon" type="images/jpeg"  href="../images/jLeft.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anton&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fugaz+One&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <meta property="og:title" content="Tienda de comida rapida">
    <meta property="og:description" content="Tienda en linea de la empresa de comida rapida JIREH">
    <style>
    .dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #FFE1DE;
    min-width: 100px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1;
    border-radius:10px;
}

.dropdown-content a {
    color: black;
    padding: 4px 2px;
    text-decoration: none;
    display: block;
    border-radius: 10px;
    text-align:center;
}

.dropdown-content a:active {
    background-color: #FFD700;
}

.dropdown:hover .dropdown-content {
    display: block;
}

.dropdown-btn {
    background-color: #8B4513;
    color: white;
    font-size: 16px;
    border: none;
    cursor: pointer;
}

.dropdown-btn svg {
    vertical-align: middle;
    margin-right: 10px;
}

.fondo {
    background-color: #FFE1DE;
    justify-content: center;
}

.fondo a {
    color: #8B4513;
    font-family: 'Arial', sans-serif;
}

/* Propiedad del logo por el cual no se porque no funciona en main */
#logo {
    max-height: 100%;
    height: 40px;
    width: auto;
    margin-top: 4px;
}

.nav-underline .nav-item .nav-link:hover,
.nav-underline .nav-item .nav-link.active {
    border-bottom: 2px solid #FFD700;
    color: #8B4513;
}

.badge.bg-secondary {
    color: #FFA500;
}

.navbar-brand img {
    height: 40px;
}

.busqueda {
    display: flex;
    align-items: center;
    margin: 5px 10px;
}

.busqueda form {
    margin: 0;
}

.busqueda input[type="text"] {
    padding: 5px 10px;
    border: 1px solid #8B4513;
    border-radius: 20px;
    font-size: 14px;
    width: 300px;
    outline: none;
}

.busqueda input[type="text"]:focus {
    border-color: #FFD700;
    box-shadow: 0 0 5px rgba(255, 215, 0, 0.5);
}

.busqueda .invincible {
    background: none;
    border: none;
    cursor: pointer;
    color: #8B4513;
    font-size: 16px;
    padding: 5px;
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
                    <a class="nav-link active" aria-current="page" href="promociones.php">Paquetes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../html/delivery.html">Delivery</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../html/conócenos.html">Conócenos</a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link" href="checkout_paquetes.php">| Pedidos<span id="num_card" class="badge bg-secondary"><?php echo $total_items;?></span> |</a>
                </li>
                <div class="bad" style="padding: 0 75px 0 265px;">
                <li class="busqueda">
                    <form action="promociones.php" method="GET" autocomplete="off">
                        <input type="text" name="search" id="seeker" placeholder=" calidad garantizada en todos tus pedidos" value="<?php echo htmlspecialchars($search); ?>">
                        <!--<button class="invincible" type="submit"><i class="fas fa-search"></i></button>-->
                    </form>
                </li>
                <?php if(isset($_SESSION['user_id'])) { ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="width: 20px; height: 20px;">
                            <path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"/>
                        </svg>
                        <?php echo $_SESSION['user_name']; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="perfil.php">Mi perfil</a>
                            <a class="dropdown-item" href="logout.php">Cerrar sesión</a>
                        </li>
                    </div>
                    </ul>
                </li>
                <?php } else { ?>
                <li class="nav-item" style="margin-right: 30px;">
                    <a class="nav-link" href="login.php"><span class="badge bg-secondary">Iniciar Sesión</span></a>
                </li>
                <?php } ?>
            </ul>
        </nav>  
    </header>
     <div id="conteiner">
     
     <div id="bienvenida">
        <article>Bienvenido seas al restaurante de comida rapida JIREH. Donde nuestra prioridad es tu satisfacción</article>
    </div>
     <seccion id="menu">
        <?php foreach($resultado as $row) { ?>
        <div>
            <div class="ground-white">
            <?php

            $id = $row['id'];
            $imagen = "../images/paquetes/" . $id . "/combos_$id.png";

            if(!file_exists($imagen)){
                $imagen = "../images/no-photo.png";
            }
            ?>
        <image src="<?php echo $imagen; ?>"></image>
    </div>
        <h1 class="textos"><?php echo $row['nombre'];?></h1>
        <h1 class="textos">-$<?php echo $row['precio'];?>-</h1>
        <div class="botones">
            <a href="detalles_paquetes.php?id=<?php echo $row['id']; ?>&token=<?php echo hash_hmac('sha256', $row['id'], KEY_TOKEN); ?>" class="btn-description"><i class="fas fa-info-circle"></i> Descripción</a>
            <button class="btn-primary" type="button" onclick="addPaquete(<?php echo $row['id']; ?>, '<?php echo hash_hmac('sha256', $row['id'], KEY_TOKEN); ?>')">Añadir a Pedidos</button>
            </div>
        </div>
        <?php } ?> 

     </seccion>

    </div>
    <script src="../js/main.js" charset="UTF-8"></script>

    <script>
        function addPaquete(id, token){
            let url = '../clases/compras_paquete.php'
            let formData = new FormData()
            formData.append('id', id)
            formData.append('token', token)

            fetch(url, {
                method: 'POST',
                body: formData,
                mode: 'cors'
            }).then(response => response.json())
            .then(data => {
                if(data.ok){
                    let elemento = document.getElementById("num_card")
                    elemento.innerHTML = data.numero
                }
            })
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
  <footer>
    <span class="footer-line">®2026 JIREH Y ASOCIADOS, INC. ALGUNOS PRODUCTOS ESTAN SUJETOS A DISPONIBILIDAD<span>
</footer>
</html>