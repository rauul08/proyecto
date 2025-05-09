<?php
require '../config/config.php';
require '../config/database.php';
$db = new Database();
$con = $db->conectar();


//¡Vista y modificación de combos!

$paquetes = isset($_SESSION['compras_paquetes']['paquetes']) ? ($_SESSION['compras_paquetes']['paquetes']) : null;

//print_r($_SESSION);

$order_list = array();

if($paquetes != null){
    foreach($paquetes as $clue => $cant){


        $sql = $con->prepare("SELECT id, nombre, precio, descuento, $cant AS cantidad FROM paquetes WHERE id=? AND activo=1");
        $sql->execute([$clue]);
        $order_list[] = $sql->fetch(PDO::FETCH_ASSOC);
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>JIREH | Pedidos</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="09f">
    <link rel="stylesheet" href="../css/list_pedidos.css">
    <link rel="icon" type="images/jpeg"  href="images/jLeft.png">
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
     margin: 0 40px;
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
        min-width: 100px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
        border-radius:10px;
    }

    .dropdown-content a {
        padding: 4px 2px;
        text-decoration: none;
        display: block;
        border-radius: 10px;
        text-align:center;
    }

    .dropdown-content a:active {
        background-color: #fff;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    .dropdown-btn {
        background-color: #fff;
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
        <header class="d-flex justify-content-between align-items-flex-start" >
     <a class="navbar-brand" href="promociones.php" class="return"><svg  xmlns="http://www.w3.org/2000/svg"  width="60"  height="50"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg></a>
        <ul class="nav nav-underline align-items-flex-start">
                <li class="nav-item">
                    <a class="nav-link" href="#">| Pedidos<span id="num_cart" class="badge bg-secondary"><?php echo $total_items;?></span> |</a>
                    <?php if(isset($_SESSION['user_id'])) { ?>
            <div class="dropdown">
                <button class="nav-link">
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
<div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Paquete</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if($order_list == null){
                    echo '<tr><td colspan="5" class="text-center"><b>Lista vacía</b></td></tr>';
                } else {

                    $total = 0;
                    foreach($order_list as $paquete){
                        $_id = $paquete['id'];
                        $nombre = $paquete['nombre'];
                        $cant = $paquete['cantidad'];
                        $precio = $paquete['precio'];
                        $descuento = $paquete['descuento'];
                        $precio_desc = $precio - (($precio * $descuento) / 100);
                        $subtotal = $cant* $precio_desc;
                        $total += $subtotal;
                        ?>

                <tr>
                    <td><?php echo $nombre;  ?></td>
                    <td><?php echo MONEDA . number_format($precio_desc,2,'.',','  )?></td>
                    <td>
                    <input type="number" min="1" max="10" step="1" value="<?php echo $cant ?>" size="5" id="cant_<?php echo $_id; ?>" onchange="actualizaCant(this.value, <?php echo $_id; ?>)"></input>
                    </td>
                    <td>
                    <div id="sub_<?php echo $_id; ?>" name="sub[]"><?php echo MONEDA . number_format($subtotal,2,'.',','); ?></div>
                    </td>
                    <td><a href="#" id="eliminar" class="btn btn-warning btn-sm" data-bs-id="<?php echo $_id; ?>" data-bs-toggle="modal"
                    data-bs-target="#eliminaModal"><i class="fas fa-trash"></a></td>
                </tr>
                <?php } ?>
                <tr>
                    <td colspan="3"></td>
                    <td colspan="2">
                        <p class="h3" id="totale"><?php echo MONEDA . number_format($total, 2, '.', ','); ?></p>
                    </td>
                </tr>
            </tbody>
            <?php } ?>
                    </table>
    </div>
    
    
    <?php if (!empty($order_list)) { ?>
        <div class="row">
            <div class="col-md-5 offset-md-7 d-grid gap-2">
                <?php if (isset($_SESSION['user_cliente'])) { ?>
                    <a href="proceso_pago.php" class="btn btn-primary btn-lg">Realizar pago</a>
                <?php } else { ?>
                    <a href="login.php" class="btn btn-primary btn-lg">Realizar pago</a>
                <?php } ?> 
            </div>
        </div>
    <?php } ?>

    </div>

        <!-- Modal -->
<div class="modal fade" id="eliminaModal" tabindex="-1" aria-labelledby="eliminaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="eliminaModalLabel">Alerta</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        ¿Está seguro de eliminar este paquete de la lista?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button id="btn-elimina"type="button" class="btn btn-danger" onclick="eliminar()">Eliminar</button>
      </div>
    </div>
  </div>
</div>

    <script>
        let eliminaModal = document.getElementById('eliminaModal')
        eliminaModal.addEventListener('show.bs.modal', function(event) {
            let button = event.relatedTarget
            let id = button.getAttribute('data-bs-id')
            let buttonElimina = eliminaModal.querySelector('.modal-footer #btn-elimina')
            buttonElimina.value = id
        })

        function actualizaCant(cantidad, id){
            let url = '../clases/actualizar_compras_paquetes.php'
            let formData = new FormData()
            formData.append('id', id)
            formData.append('action', 'added')
            formData.append('cantidad', cantidad)

            fetch(url, {
                method: 'POST',
                body: formData,
                mode: 'cors'
            }).then(response => response.json())
            .then(data => {
                if(data.ok){
                    let divsub = document.getElementById('sub_' + id) 
                    divsub.innerHTML = data.sub

                    let totale = 0.00
                    let list = document.getElementsByName('sub[]')

                    for(let x = 0; x < list.length; x++) {
                        totale += parseFloat(list[x].innerHTML.replace(/[$,]/g, ''))
                    }
                    totale = new Intl.NumberFormat('en-US', {
                        minimumFractionDigits: 2
                    }).format(totale)
                    document.getElementById('totale').innerHTML = '<?php echo MONEDA; ?>' + totale
                }
            })
        }


        function eliminar(){

            let botonElimina = document.getElementById('btn-elimina')
            let id = botonElimina.value

            let url = '../clases/actualizar_compras_paquetes.php'
            let formData = new FormData()
            formData.append('id', id)
            formData.append('action', 'eliminar')
            

            fetch(url, {
                method: 'POST',
                body: formData,
                mode: 'cors'
            }).then(response => response.json())
            .then(data => {
                if(data.ok){
                   location.reload()
                }
            })
        }

    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
</body>
</html>