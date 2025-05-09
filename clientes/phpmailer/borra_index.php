<?php
require 'config/config.php';
require 'config/database.php';
$db = new Database();
$con = $db->conectar();

$productos = isset($_SESSION['compras']['productos']) ? $_SESSION['compras']['productos'] : null;
$paquetes = isset($_SESSION['compras_paquetes']['paquetes']) ? $_SESSION['compras_paquetes']['paquetes'] : null;

$lista_compras = array();

// Obtener productos
if ($productos != null) {
    foreach ($productos as $clave => $cantidad) { 
        $sql = $con->prepare("SELECT id, nombre, precio, $cantidad AS cantidad, 'producto' AS tipo FROM productos WHERE id=? AND activo=1");
        $sql->execute([$clave]);
        $lista_compras[] = $sql->fetch(PDO::FETCH_ASSOC);
    }
}

// Obtener paquetes
if ($paquetes != null) {
    foreach ($paquetes as $clave => $cantidad) {
        $sql = $con->prepare("SELECT id, nombre, precio, descuento, $cantidad AS cantidad, 'paquete' AS tipo FROM paquetes WHERE id=? AND activo=1");
        $sql->execute([$clave]);
        $lista_compras[] = $sql->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>JIREH | Fods</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="09f">
    <link rel="stylesheet" href="list_pedidos.css">
    <link rel="icon" type="images/jpeg" href="images/jLeft.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anton&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Fugaz+One&display=swap" rel="stylesheet">
    <meta property="og:title" content="Tienda de comida rapida">
    <meta property="og:description" content="Tienda en linea de la empresa de comida rapida JIREH">
</head>
<body>
<div id="conteiner"> 
    <header class="d-flex justify-content-between align-items-flex-start">
        <a class="navbar-brand" href="index.php" class="return">
            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-arrow-left">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M5 12l14 0" />
                <path d="M5 12l6 6" />
                <path d="M5 12l6 -6" />
            </svg>
        </a>
        <ul class="nav nav-underline align-items-flex-start">
            <li class="nav-item">
                <a class="nav-link" href="checkout.php">| Pedidos<span id="num_cart" class="badge bg-secondary"><?php echo $num_cart;?></span> |</a>
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
                <a class="nav-link" href="login.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"/>
                    </svg>
                    Ingresar
                </a>
                <?php } ?>
            </li>
        </ul>
    </header>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($lista_compras)) {
                    echo '<tr><td colspan="5" class="text-center"><b>Lista vacía</b></td></tr>';
                } else {
                    $total = 0;
                    foreach($lista_compras as $item) {
                        $_id = $item['id'];
                        $nombre = $item['nombre'];
                        $cantidad = $item['cantidad'];
                        $precio = $item['precio'];
                        $subtotal = $cantidad * $precio;
                        if ($item['tipo'] === 'paquete') {
                            $descuento = $item['descuento'];
                            $precio_desc = $precio - (($precio * $descuento) / 100);
                            $subtotal = $cantidad * $precio_desc;
                        }
                        $total += $subtotal;
                ?>
                <tr>
                    <td><?php echo $nombre; ?></td>
                    <td><?php echo MONEDA . number_format($precio,2,'.',','); ?></td>
                    <td>
                        <input type="number" min="1" max="10" step="1" value="<?php echo $cantidad ?>" size="5" id="cantidad_<?php echo $_id; ?>" onchange="actualizaCantidad(this.value, <?php echo $_id; ?>)">
                    </td>
                    <td>
                        <div id="subtotal_<?php echo $_id; ?>" name="subtotal[]"><?php echo MONEDA . number_format($subtotal,2,'.',','); ?></div>
                    </td>
                    <td><a href="#" id="delete" class="btn btn-warning btn-sm" data-bs-id="<?php echo $_id; ?>" data-bs-toggle="modal" data-bs-target="#deleteModal"><i class="fas fa-trash"></i></a></td>
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

    <!-- Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Eliminar Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar este item de tu lista?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9g1TcIYBntAXspE/gGNHcbM2CHiBNO5V01D6P0PiS0Y6+DQ1B2s" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-w76A6Q/2M8zQJfXekQ8b0+KmeQF3A9ltgI6A1FS0VdD/2E06g2zHBe1h9nZSlbEkE" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var deleteButtons = document.querySelectorAll('#delete');
            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var itemId = this.getAttribute('data-bs-id');
                    var confirmButton = document.getElementById('confirm-delete');
                    confirmButton.onclick = function() {
                        window.location.href = 'eliminar_item.php?id=' + itemId;
                    };
                });
            });
        });

        function actualizaCantidad(cantidad, id) {
            // Implementa la lógica para actualizar la cantidad del producto o paquete
        }
    </script>
</div>
</body>
</html>
