<?php

require '../config/config.php';
require '../config/database.php';
$db = new Database();
$con = $db->conectar();

$id = isset($_GET['id']) ? $_GET['id'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

if($id == '' || $token == '') {
    echo 'Oops! Error 404 Page Not Found';
    exit;
} else {

    $token_tmp = hash_hmac('sha1', $id, KEY_TOKEN);

    if($token == $token_tmp) {

        $sql = $con->prepare("SELECT count(id) FROM productos WHERE id=? AND activo=1");
        $sql->execute([$id]);
        if($sql->fetchColumn() > 0) {

        $sql = $con->prepare("SELECT nombre, descripcion, precio FROM productos WHERE id=? AND activo=1
        LIMIT 1");
        $sql->execute([$id]);
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        $nombre = $row['nombre'];
        $descripcion = $row['descripcion'];
        $precio = $row['precio'];

        $dir_images = '../images/productos/' . $id . '/';

        $rutaImg = $dir_images . "principal_$id.png";

        if(!file_exists($rutaImg)) {
            $rutaImg = '../images/no-photo.png';
        }

        $imagenes = array();
        $dir = dir($dir_images);

        while(($archivo = $dir->read()) != false) {
            if($archivo != "principal_$id.png" && (strpos($archivo, 'png') || strpos($archivo, 'jpeg'))){
                $imagenes[] = $dir_images . $archivo;
            }
        }
        $dir->close();


        }

    } else {
        echo 'Oops! Error 404 Page Not Found';
    exit;
    }
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>JIREH | Detalles</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="09f">
    <link rel="stylesheet" href="../css/details.css">
    <link rel="icon" type="images/jpeg"  href="../images/jLeft.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anton&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Fugaz+One&display=swap" rel="stylesheet">
    <meta property="og:title" content="Tienda de comida rapida">
    <meta property="og:description" content="Tienda en linea de la empresa de comida rapida JIREH">
    <style>
    .nav-item {
     display: flex;
     margin: 0 29px;
     max-width: 100%;
     justify-content: center;
     align-items: center;
    }
    .nav-link svg {
     width: 20px;
     height: 20px;
     margin-right: 5px;
    }
    .nav-link {
     color: brown;
     text-decoration: none;
     margin-left: 6px;
     display: flex;
     align-items: center;
    }
    .fondo {
     display:flex;
     justify-content: space-between;
     height: 60px;
     margin-top: 0px;
     background-color: #FFE1DE;
}
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
    background-color: #FFA500;
}

.dropdown:hover .dropdown-content {
    display: block;
}

.dropdown-btn {
    background-color: #FFE1DE;
    color: brown;
    font-size: 16px;
    border: none;
    cursor: pointer;
}

.dropdown-btn svg {
    vertical-align: middle;
    margin-right: 10px;
}
    </style>
</head>
  <body>
    <div id="conteiner"> 
     <header class="d-flex justify-content-between align-items-flex-start">
     <a class="navbar-brand" href="index.php" class="return"><svg  xmlns="http://www.w3.org/2000/svg"  width="60"  height="50"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg></a>
        <ul class="nav nav-underline align-items-flex-start">
                <li class="nav-item">
                    <a class="nav-link" href="checkout.php">| Pedidos<span id="num_cart" class="badge bg-secondary"><?php echo $total_items;?></span> |</a>
                    <?php if(isset($_SESSION['user_id'])) { ?>
            <div class="dropdown">
                <button class="dropdown-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="width: 20px; height: 20px;">
                        <path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"/>
                    </svg>
                    <?php echo $_SESSION['user_name']; ?>
                </button>
                <div class="dropdown-content">
                    <a href="logout.php">Cerrar sesión</a>
                </div>
            </div>
                
            <?php } else { ?>
                <a class="nav-link" href="login.php"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"/></svg>
                Ingresar</a>
                <?php } ?>
                </li>
            </ul>
     </header>
     <div class="row">
        <div class="col-md-6 order-md-1">
        <div id="carouselImages" class="carousel slide">
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="<?php echo $rutaImg; ?> " class="d-block w-100">
    </div>
    <?php foreach ($imagenes as $img) { ?>
    <div class="carousel-item">
    <img src="<?php echo $img; ?> " class="d-block w-100">
    </div>
    <?php } ?>

  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#carouselImages" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carouselImages" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
</div>
</div>
        <div class="col-md-6 order-md-2">
            <div class="bg-warning ground-color">
            <h2><?php echo $nombre; ?></h2>
            <h2><?php echo MONEDA . number_format($precio, 2, '.', ','); ?></h2>
            <p class="lead">
                <?php echo $descripcion; ?>
            </p>

            <div class="d-grid gap-3 col-10 mx-auto">
                <a class="btn btn-outline-secondary" type="button" href="checkout.php">Comprar ahora</a>
                <button class="btn btn-danger" type="button" onclick="addProducto(<?php echo $id; ?>, '<?php echo $token_tmp; ?>')">Añadir a Pedidos</button>
         </div>
        </div>
     </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
            function addProducto(id, token) {
            let url = '../clases/compras.php'
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
                    let elemento = document.getElementById("num_cart")
                    elemento.innerHTML = data.numero
                }
            })
        }
    </script>
  </body>
  <footer>
    <span class="footer-line">®2024 JIREH Y ASOCIADOS, INC. ALGUNOS PRODUCTOS ESTAN SUJETOS A DISPONIBILIDAD<span>
</footer>
</html>
