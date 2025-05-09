<?php
require '../config/config.php';
require '../config/database.php';
$db = new Database();
$con = $db->conectar();

// Verificar si la sesión ya está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar los datos del formulario
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $direccion_entrega = trim($_POST['direccion_entrega']);
    $instrucciones_adicionales = trim($_POST['instrucciones_adicionales']);
    $metodo_pago = trim($_POST['payment_method']);
    
    // Inicializar el coste de envío
    $coste_envio = 0;

    // Validar los datos (opcional)
    $direcciones_permitidas = [
        'carlos pellicer camara',
        'santo domingo',
        'medellin y madero',
        'las rosas',
        'km. 15'
    ]; 
    
    // Convertir la dirección ingresada a minúsculas
    $direccion_entrega_lower = strtolower($direccion_entrega);
    
    // Validación de la dirección de entrega
    if (in_array($direccion_entrega_lower, $direcciones_permitidas)) {
        // La dirección está en la lista de permitidas
        if ($direccion_entrega_lower == 'carlos pellicer camara') {
            $coste_envio = 0; // Entrega gratuita para esta zona
        } else {
            $coste_envio = 20; // Costo de envío para otras zonas permitidas
        }
    } else {
        // La dirección no está en la lista de permitidas
        echo "Lo sentimos, no ofrecemos servicio de entrega en su zona.";
        exit; // Detiene la ejecución del script
    }

    // Guardar el coste de envío en la sesión
    $_SESSION['coste_envio'] = $coste_envio;

    // Obtener el monto total de la sesión
    $monto_total = isset($_SESSION['monto_total']) ? $_SESSION['monto_total'] : 0;
    $monto_total += $coste_envio;

    // Preparar la consulta de inserción
    $sql = "INSERT INTO pedidos (nombre, email, direccion_entrega, instrucciones_adicionales, metodo_pago, monto_total, coste_envio, proceso) VALUES (:nombre, :email, :direccion_entrega, :instrucciones_adicionales, :metodo_pago, :monto_total, :coste_envio, 1)";
    $stmt = $con->prepare($sql);
    
    // Asignar los valores a los parámetros
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':direccion_entrega', $direccion_entrega);
    $stmt->bindParam(':instrucciones_adicionales', $instrucciones_adicionales);
    $stmt->bindParam(':metodo_pago', $metodo_pago);
    $stmt->bindParam(':monto_total', $monto_total);
    $stmt->bindParam(':coste_envio', $coste_envio);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        // Pedido guardado con éxito, ahora actualizar registro_pedidos
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id']; // ID del cliente almacenado en la sesión

            // Obtener el registro actual de pedidos
            $sql_select = "SELECT registro_pedidos FROM clientes WHERE id = :user_id";
            $stmt_select = $con->prepare($sql_select);
            $stmt_select->bindParam(':user_id', $user_id);
            $stmt_select->execute();
            $cliente = $stmt_select->fetch(PDO::FETCH_ASSOC);

            if ($cliente) {
                // Incrementar registro_pedidos
                $nuevo_registro_pedidos = $cliente['registro_pedidos'] + 1;
                $sql_update = "UPDATE clientes SET registro_pedidos = :nuevo_registro_pedidos WHERE id = :user_id";
                $stmt_update = $con->prepare($sql_update);
                $stmt_update->bindParam(':nuevo_registro_pedidos', $nuevo_registro_pedidos);
                $stmt_update->bindParam(':user_id', $user_id);

                if ($stmt_update->execute()) {
                    echo "<h1>¡Gracias por tu compra!</h1>
<p>Tu pedido ha sido recibido y está en proceso. Te notificaremos cuando esté listo para ser enviado.</p>";
                    unset($_SESSION['compras']['productos']);
                    unset($_SESSION['compras_paquetes']['paquetes']);
                    unset($_SESSION['monto_total']);
                    unset($_SESSION['coste_envio']);
                    // Redirigir a una página de confirmación o similar
                    exit;
                } else {
                    echo "Error al actualizar el registro de pedidos.";
                }
            } else {
                echo "Cliente no encontrado.";
            }
        } else {
            echo "ID de cliente no encontrado en la sesión.";
        }
    } else {
        echo "Error al guardar el pedido.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>JIREH | Pago</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#09f">
    <link rel="stylesheet" href="../css/inicio.css">    
    <link rel="icon" type="image/jpeg" href="../images/jLeft.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Fugaz+One&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <meta property="og:title" content="Tienda de comida rápida">
    <meta property="og:description" content="Tienda en línea de la empresa de comida rápida JIREH">  
</head>
<body>
<div class="container">
    <div class="form-container">
    <div id="error-message" class="alert alert-danger" style="display:none;"></div>
        <form action="proceso_pago.php" method="POST">
        <div id="delivery-options" class="section">
                <h2 class="text-center">Opciones de Envío</h2>
                <div class="form-group">
                    <label>
                        <input type="radio" name="delivery_options" value="domicilio" required>
                        A domicilio
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="radio" name="delivery_options" value="recoger">
                        Recoger en tienda
                    </label>
                </div>
                <button type="button" class="btn btn-primary" onclick="showSection('delivery-info')">Siguiente</button>
            </div>

            <div id="delivery-info" class="section" style="display:none;">
                <h2 class="text-center">Información de Entrega</h2>
                <div class="form-group">
                    <label for="nombre">Nombre de quien recibe:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="direccion_entrega">Dirección:</label>
                    <input type="text" placeholder="Carlos Pellicer Camara, Santo Domingo, Km.15, Las Rosas, Medellin y Madero." id="direccion_entrega" name="direccion_entrega" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="instrucciones_adicionales">Instrucciones adicionales:</label>
                    <textarea id="instrucciones_adicionales" placeholder="Calle, manzana y lote." name="instrucciones_adicionales" class="form-control"></textarea>
                </div>
                <button type="button" class="btn btn-primary" onclick="showSection('payment-methods')">Método de Pago</button>
            </div>

            <div id="payment-methods" class="section" style="display:none;">
                <h2 class="text-center">Método de Pago</h2>
                <div class="form-group">
                    <label>
                        <input type="radio" name="payment_method" value="efectivo" required>
                        Efectivo
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="radio" name="payment_method" value="tarjeta">
                        Tarjeta de Crédito/Débito
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="radio" name="payment_method" value="transferencia">
                        Transferencia Bancaria
                    </label>
                </div>
                <button type="button" class="btn btn-primary" onclick="showSection('order-summary')">Siguiente: Confirmar Pedido</button>
            </div>

            <div id="order-summary" class="section" style="display:none;">
                <h2 class="text-center">Resumen del Pedido</h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Artículo</th>
                                <th>Precio Unitario</th>
                                <th>Cantidad</th>
                                <th>Descuento</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Verificación de la sesión y recuperación de datos
                            $monto_total = isset($_SESSION['monto_total']) ? $_SESSION['monto_total'] : 0;
                            $coste_envio = isset($_SESSION['coste_envio']) ? $_SESSION['coste_envio'] : 20;
                            $lista_pedidos = [];

                            // Obtener detalles de los productos
                            if (isset($_SESSION['compras']['productos']) && !empty($_SESSION['compras']['productos'])) {
                                foreach ($_SESSION['compras']['productos'] as $clave => $cantidad) {
                                    $sql = $con->prepare("SELECT nombre, precio FROM productos WHERE id=:id AND activo=1");
                                    $sql->bindParam(':id', $clave, PDO::PARAM_INT);
                                    $sql->execute();
                                    $producto = $sql->fetch(PDO::FETCH_ASSOC);
                                    if ($producto) {
                                        $lista_pedidos[] = [
                                            'nombre' => $producto['nombre'],
                                            'precio' => $producto['precio'],
                                            'cantidad' => $cantidad,
                                            'subtotal' => $producto['precio'] * $cantidad
                                        ];
                                    }
                                }
                            }

                           // Obtener detalles de los paquetes
                        if (isset($_SESSION['compras_paquetes']['paquetes']) && !empty($_SESSION['compras_paquetes']['paquetes'])) {
                            foreach ($_SESSION['compras_paquetes']['paquetes'] as $clave => $cantidad) {
                                $sql = $con->prepare("SELECT nombre, precio, descuento FROM paquetes WHERE id=:id AND activo=1");
                                $sql->bindParam(':id', $clave, PDO::PARAM_INT);
                                $sql->execute();
                                $paquete = $sql->fetch(PDO::FETCH_ASSOC);
                                if ($paquete) {
                                    // Asegurarse de que descuento sea válido
                                    $descuento = isset($paquete['descuento']) ? $paquete['descuento'] : 0;
                                    $precio = $paquete['precio']; // Asignar precio del paquete

                                    // Calcular el precio con descuento
                                    $precio_desc = $precio - (($precio * $descuento) / 100);

                                    // Agregar detalles del paquete a la lista de pedidos
                                    $lista_pedidos[] = [
                                        'nombre' => $paquete['nombre'],
                                        'precio' => $precio,
                                        'cantidad' => $cantidad,
                                        'descuento' => $descuento, // Agregar el descuento
                                        'subtotal' => $cantidad * $precio_desc
                                    ];
                                }
                            }
                        }

                        // Mostrar lista de pedidos
                        if (!empty($lista_pedidos)) {
                            foreach ($lista_pedidos as $item) {
                                $precio_total = $item['subtotal'];
                                $descuento = isset($item['descuento']) ? $item['descuento'] : '0'; // Manejar el caso donde no hay descuento

                                echo "<tr>
                                    <td>{$item['nombre']}</td>
                                    <td>\${$item['precio']}</td>
                                    <td>{$item['cantidad']}</td>
                                    <td>{$descuento}%</td>
                                    <td>\${$precio_total}</td>
                                </tr>";
                            }

                            // Calcular el monto total
                            $monto_total = array_sum(array_column($lista_pedidos, 'subtotal')) + $coste_envio;

                            // Mostrar el coste de envío
                            echo "<tr>
                                <td colspan='4'><strong>Coste de Envío:</strong></td>
                                <td>\${$coste_envio}</td>
                            </tr>";

                            // Mostrar el total
                            echo "<tr>
                                <td colspan='4'><strong>Total:</strong></td>
                                <td>\${$monto_total}</td>
                            </tr>";
                        } else {
                            echo "<tr><td colspan='5'>No hay productos en el carrito.</td></tr>";
                        }

                        ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-success">Confirmar Pedido</button>
                <button type="button" class="btn btn-secondary" onclick="showSection('delivery-options')">Volver</button>
            </div>
        </form>
    </div>
</div>
<script>
    function showSection(sectionId) {
        const currentSection = document.querySelector('.section:not([style*="display: none"])');
        let allFilled = true;
        let errorMessage = document.getElementById('error-message');
        errorMessage.style.display = 'none';
        errorMessage.textContent = '';

        const inputs = currentSection.querySelectorAll('input[required], textarea[required]');
        inputs.forEach(input => {
            if (!input.value) {
                allFilled = false;
                input.classList.add('is-invalid'); 
            } else {
                input.classList.remove('is-invalid');
            }
        });

        if (sectionId === 'delivery-info') {
            const deliveryOptionsChecked = document.querySelector('input[name="delivery_options"]:checked');
            if (!deliveryOptionsChecked) {
                allFilled = false;
                errorMessage.textContent = "Por favor, selecciona un método de envío.";
                errorMessage.style.display = 'block';
            }
        }

        if (sectionId === 'order-summary') {
            const paymentMethodChecked = document.querySelector('input[name="payment_method"]:checked');
            if (!paymentMethodChecked) {
                allFilled = false;
                errorMessage.textContent = "Por favor, selecciona un método de pago.";
                errorMessage.style.display = 'block';
            }
        }

        if (allFilled) {
            document.querySelectorAll('.section').forEach(section => section.style.display = 'none');
            document.getElementById(sectionId).style.display = 'block';
        } else if (!errorMessage.textContent) {
            errorMessage.textContent = "Por favor, rellena todos los campos obligatorios.";
            errorMessage.style.display = 'block';
        }
    }
</script>
</body>
</html>
