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
    </style>
</head>
  <body>
    <div id="conteiner">
        <div id="sidebar">
            <div class="toggle-btn">
                <span>&#9776;</span>
            </div>
            <a href="../html/index.html"><img src="../images/jLeft.png" alt="leftside"></a>
            <ul class="secciones">
                <li><a href="index.php">MENÚ</a></li>
                <li><a href="promociones.php">PAQUETES</a></li>
                <li><a href="../html/delivery.html">DELIVERY</a></li>
                <li><a href="../html/conócenos.html">CONÓCENOS</a></li>
            </ul>
        </div>  
     <header>
        <nav class="fondo">
            <ul>
                <li class="busqueda">
                
                <form action="promociones.php" method="GET" autocomplete="off">
                <input type="text" name="search" id="seeker"
                placeholder=" calidad garantizada en todos tus pedidos" value="<?php echo htmlspecialchars($search); ?>">
                <!--<button class="invincible" type="submit"><i class="fas fa-search"></i></button>-->
                </form>

               <a class="nav-ref" href="checkout_paquetes.php">| Pedidos<span id="num_card" class="badge bg-secondary"><?php echo $total_items;?></span>. |</a>
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
        </nav>  
     </header>
     
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
  </body>
  <footer>
    <span class="footer-line">®2024 JIREH Y ASOCIADOS, INC. ALGUNOS PRODUCTOS ESTAN SUJETOS A DISPONIBILIDAD<span>
</footer>
</html>