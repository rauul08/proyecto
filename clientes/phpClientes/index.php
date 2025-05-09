<?php

require '../config/config.php';
require '../config/database.php';
$db = new Database();
$con = $db->conectar();


$search = "";
if(isset($_GET['search'])) {
    $search = $_GET['search'];
    $sql = $con->prepare("SELECT id, nombre, descripcion, precio FROM productos WHERE nombre LIKE ? OR descripcion LIKE ?  LIMIT 15");
    $searchParam = '%' . $search . '%';
    $sql->bindParam(1, $searchParam, PDO::PARAM_STR);
    $sql->bindParam(2, $searchParam, PDO::PARAM_STR);
    $sql->execute();
    $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
} else {
$sql = $con->prepare("SELECT id, nombre, precio FROM productos WHERE activo=1");
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
}

/*/session_destroy();
print_r($_SESSION); /*/

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>JIREH | Menú</title>
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
    <?php include 'menu.php'; ?>
     <div id="bienvenida">
        <article>Bienvenido seas al restaurante de comida rapida JIREH. Donde nuestra prioridad es tu satisfacción</article>
    </div>
     <seccion id="menu">
        <?php foreach($resultado as $row) { ?>
        <div>
            <div class="ground-white">
            <?php

            $id = $row['id'];
            $imagen = "../images/productos/" . $id . "/principal_$id.png";

            if(!file_exists($imagen)){
                $imagen = "../images/no-photo.png";
            }
            ?>
        <image src="<?php echo $imagen; ?>"></image>
    </div>
        <h1 class="textos"><?php echo $row['nombre'];?></h1>
        <h1 class="textos">-$<?php echo $row['precio'];?>-</h1>
        <div class="botones">
            <a href="details.php?id=<?php echo $row['id']; ?>&token=<?php echo hash_hmac('sha1', $row['id'], KEY_TOKEN); ?>" class="btn-description"><i class="fas fa-info-circle"></i> Descripción</a>
            <button class="btn-primary" type="button" onclick="addProducto(<?php echo $row['id']; ?>, '<?php echo hash_hmac('sha1', $row['id'], KEY_TOKEN); ?>')">Añadir a Pedidos</button>
            </div>
        </div>
        <?php } ?> 

     </seccion>

    </div>
    <script src="../js/main.js" charset="UTF-8"></script>
    
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