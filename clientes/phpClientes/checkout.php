<?php
require '../config/config.php';
require '../config/database.php';
$db = new Database();
$con = $db->conectar();

$productos = isset($_SESSION['compras']['productos']) ? $_SESSION['compras']['productos'] : null;
$paquetes = isset($_SESSION['compras_paquetes']['paquetes']) ? $_SESSION['compras_paquetes']['paquetes'] : null;

//print_r($_SESSION);
//session_destroy();
$lista_pedidos = array();
// Obtener productos
// Productos
if ($productos != null) {
    foreach ($productos as $clave => $cantidad) {
        $sql = $con->prepare("SELECT CONCAT('producto_', id) AS id, nombre, precio, :cantidad AS cantidad, 'producto' AS tipo FROM productos WHERE id=:id AND activo=1");
        $sql->bindParam(':id', $clave, PDO::PARAM_INT);
        $sql->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
        $sql->execute();
        $producto = $sql->fetch(PDO::FETCH_ASSOC);
        if ($producto) {
            $lista_pedidos[] = $producto;
        }
    }
}

// Paquetes
if ($paquetes != null) {
    foreach ($paquetes as $clave => $cantidad) {
        $sql = $con->prepare("SELECT CONCAT('paquete_', id) AS id, nombre, precio, descuento, :cantidad AS cantidad, 'paquete' AS tipo FROM paquetes WHERE id=:id AND activo=1");
        $sql->bindParam(':id', $clave, PDO::PARAM_INT);
        $sql->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
        $sql->execute();
        $paquete = $sql->fetch(PDO::FETCH_ASSOC);
        if ($paquete) {
            $lista_pedidos[] = $paquete;
        }
    }
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>JIREH | Pedidos</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/list_pedidos.css">
    <link rel="icon" type="images/jpeg" href="../images/jLeft.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anton&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fugaz+One&display=swap" rel="stylesheet">
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
    <header class="d-flex justify-content-between align-items-flex-start">
        <a class="navbar-brand" href="index.php" class="return"><svg xmlns="http://www.w3.org/2000/svg" width="60" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icon-tabler-outline icon-tabler-arrow-left"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg></a>
        <ul class="nav nav-underline align-items-flex-start">
            <li class="nav-item">
                <a class="nav-link" href="checkout.php">| Pedidos<span id="total_items" class="badge bg-danger"><?php echo $total_items;?></span> |</a>
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
                    <th>Articulo</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
<tbody>
    <?php if($lista_pedidos == null){
        echo '<tr><td colspan="5" class="text-center"><b>Lista vacía</b></td></tr>';
    } else {
        $total = 0;
        foreach($lista_pedidos as $inventario) {
            $_id = $inventario['id'];
            $nombre = $inventario['nombre'];
            $cantidad = $inventario['cantidad'];
            $precio = $inventario['precio'];
            $tipo = $inventario['tipo'];
            $subtotal = $cantidad * $precio;
            if ($tipo === 'paquete') {
                $descuento = $inventario['descuento'];
                $precio_desc = $precio - (($precio * $descuento) / 100);
                $subtotal = $cantidad * $precio_desc;
            }
            $total += $subtotal;
            $_SESSION['monto_total'] = $total;
        ?>
    <tr>
        <td><?php echo $nombre;  ?></td>
        <td><?php echo MONEDA . number_format($precio,2,'.',',');  ?></td>
        <td>
            <input type="number" min="1" max="10" step="1" value="<?php echo $cantidad ?>" size="5" id="cantidad_<?php echo $_id; ?>">
        </td>
        <td>
            <div id="subtotal_<?php echo $tipo ?><?php echo $_id; ?>" name="subtotal[]"><?php echo MONEDA . number_format($subtotal,2,'.',','); ?></div>
        </td>
        <td>
    <a href="#" id="delete" class="btn btn-warning btn-sm" 
       data-bs-id="<?php echo $_id; ?>" 
       data-bs-tipo="<?php echo $tipo; ?>" 
       data-bs-toggle="modal" 
       data-bs-target="#eliminaModal">
        <i class="fas fa-trash"></i>
    </a>
</td>

    </tr>
    <?php } ?>

    <tr>
        <td colspan="3"></td>
        <td colspan="2">
            <p class="h3" id="total"><?php echo MONEDA . number_format($total, 2, '.', ','); ?></p>
        </td>
    </tr>
</tbody>
<?php } ?>
        </table>
    </div>

    
<?php if (!empty($lista_pedidos)) { ?>
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

<!-- Modal de eliminación -->
<div class="modal fade" id="eliminaModal" tabindex="-1" aria-labelledby="eliminaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="eliminaModalLabel">Alerta</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        ¿Está seguro de eliminar este artículo de la lista?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button id="btn-elimina" type="button" class="btn btn-danger">Eliminar</button>
      </div>
    </div>
  </div>
</div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>

document.addEventListener('DOMContentLoaded', () => {
    const btnEliminar = document.getElementById('btn-elimina');
    let id, tipo;

    document.querySelectorAll('#delete').forEach(button => {
        button.addEventListener('click', function() {
            id = this.getAttribute('data-bs-id');
            tipo = this.getAttribute('data-bs-tipo');
        });
    });

    btnEliminar.addEventListener('click', () => {
        fetch('../clases/actualizar_compras.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'eliminar',
                id: id,
                tipo: tipo
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                location.reload();
            } else {
                alert('Error al eliminar el artículo');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el artículo');
        });
    });
});


</script>
</body>
</html>