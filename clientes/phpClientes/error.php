<?php
require '../config/config.php';

// Obtener el tipo de error desde la URL
$tipo_error = isset($_GET['tipo']) ? $_GET['tipo'] : 'general';
$mensaje_personalizado = isset($_GET['mensaje']) ? urldecode($_GET['mensaje']) : '';

// Definir mensajes de error según el tipo
$errores = [
    'compra' => [
        'titulo' => 'Error en la Compra',
        'icono' => 'fas fa-shopping-cart',
        'mensaje' => 'Lo sentimos, ha ocurrido un error al procesar tu compra.',
        'detalles' => 'No se pudo completar tu pedido. Por favor, verifica tu carrito e intenta nuevamente.',
        'acciones' => [
            ['texto' => 'Ver Carrito', 'url' => 'checkout.php', 'clase' => 'btn-primary'],
            ['texto' => 'Ir al Menú', 'url' => 'index.php', 'clase' => 'btn-secondary']
        ]
    ],
    'pago' => [
        'titulo' => 'Error en el Pago',
        'icono' => 'fas fa-credit-card',
        'mensaje' => 'No se pudo procesar el pago.',
        'detalles' => 'Hubo un problema al procesar tu método de pago. Por favor, verifica tus datos e intenta nuevamente.',
        'acciones' => [
            ['texto' => 'Reintentar Pago', 'url' => 'proceso_pago.php', 'clase' => 'btn-primary'],
            // ['texto' => 'Contactar Soporte', 'url' => 'contacto.php', 'clase' => 'btn-secondary']
        ]
    ],
    'registro' => [
        'titulo' => 'Error en el Registro',
        'icono' => 'fas fa-user-plus',
        'mensaje' => 'No se pudo completar tu registro.',
        'detalles' => 'Ha ocurrido un error al crear tu cuenta. Por favor, verifica tus datos e intenta nuevamente.',
        'acciones' => [
            ['texto' => 'Intentar Nuevamente', 'url' => 'registro.php', 'clase' => 'btn-primary'],
            ['texto' => 'Iniciar Sesión', 'url' => 'login.php', 'clase' => 'btn-secondary']
        ]
    ],
    'login' => [
        'titulo' => 'Error de Autenticación',
        'icono' => 'fas fa-lock',
        'mensaje' => 'No se pudo iniciar sesión.',
        'detalles' => 'Las credenciales proporcionadas son incorrectas o ha ocurrido un error en el sistema.',
        'acciones' => [
            ['texto' => 'Intentar Nuevamente', 'url' => 'login.php', 'clase' => 'btn-primary'],
            ['texto' => 'Recuperar Contraseña', 'url' => 'recupera.php', 'clase' => 'btn-secondary']
        ]
    ],
    'base_datos' => [
        'titulo' => 'Error de Conexión',
        'icono' => 'fas fa-database',
        'mensaje' => 'Error al conectar con la base de datos.',
        'detalles' => 'No se pudo establecer conexión con el servidor. Por favor, intenta más tarde.',
        'acciones' => [
            ['texto' => 'Reintentar', 'url' => 'index.php', 'clase' => 'btn-primary'],
            // ['texto' => 'Contactar Soporte', 'url' => 'contacto.php', 'clase' => 'btn-secondary']
        ]
    ],
    'zona_entrega' => [
        'titulo' => 'Zona de Entrega No Disponible',
        'icono' => 'fas fa-map-marker-alt',
        'mensaje' => 'No ofrecemos servicio en tu zona.',
        'detalles' => 'Lo sentimos, actualmente no realizamos entregas en la dirección proporcionada.',
        'acciones' => [
            ['texto' => 'Ver Zonas de Entrega', 'url' => '../html/delivery.html', 'clase' => 'btn-primary'],
            ['texto' => 'Ir al Menú', 'url' => 'index.php', 'clase' => 'btn-secondary']
        ]
    ],
    'producto_no_disponible' => [
        'titulo' => 'Producto No Disponible',
        'icono' => 'fas fa-exclamation-triangle',
        'mensaje' => 'El producto no está disponible.',
        'detalles' => 'El producto que buscas no está disponible en este momento o ya no existe.',
        'acciones' => [
            ['texto' => 'Ver Menú', 'url' => 'index.php', 'clase' => 'btn-primary'],
            ['texto' => 'Ver Promociones', 'url' => 'promociones.php', 'clase' => 'btn-secondary']
        ]
    ],
    'sesion_expirada' => [
        'titulo' => 'Sesión Expirada',
        'icono' => 'fas fa-clock',
        'mensaje' => 'Tu sesión ha expirado.',
        'detalles' => 'Por seguridad, tu sesión ha finalizado. Por favor, inicia sesión nuevamente.',
        'acciones' => [
            ['texto' => 'Iniciar Sesión', 'url' => 'login.php', 'clase' => 'btn-primary'],
            ['texto' => 'Ir al Inicio', 'url' => 'index.php', 'clase' => 'btn-secondary']
        ]
    ],
    'general' => [
        'titulo' => 'Error',
        'icono' => 'fas fa-exclamation-circle',
        'mensaje' => 'Ha ocurrido un error inesperado.',
        'detalles' => 'Lo sentimos, algo salió mal. Por favor, intenta nuevamente más tarde.',
        'acciones' => [
            ['texto' => 'Volver al Inicio', 'url' => '../html/index.html', 'clase' => 'btn-primary'],
            // ['texto' => 'Contactar Soporte', 'url' => 'contacto.php', 'clase' => 'btn-secondary']
        ]
    ]
];

// Obtener información del error
$info_error = isset($errores[$tipo_error]) ? $errores[$tipo_error] : $errores['general'];

// Si hay un mensaje personalizado, usarlo
if (!empty($mensaje_personalizado)) {
    $info_error['detalles'] = $mensaje_personalizado;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>JIREH | <?php echo htmlspecialchars($info_error['titulo']); ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#ff6b6b">
    <link rel="stylesheet" href="../css/error.css">    
    <link rel="icon" type="images/jpeg"  href="../images/jLeft.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="error-container">
        <div class="error-content">
            <!-- Icono de error animado -->
            <div class="error-icon-wrapper">
                <i class="<?php echo htmlspecialchars($info_error['icono']); ?> error-icon"></i>
            </div>
            
            <!-- Título del error -->
            <h1 class="error-title"><?php echo htmlspecialchars($info_error['titulo']); ?></h1>
            
            <!-- Mensaje principal -->
            <p class="error-message"><?php echo htmlspecialchars($info_error['mensaje']); ?></p>
            
            <!-- Detalles del error -->
            <div class="error-details">
                <p><?php echo htmlspecialchars($info_error['detalles']); ?></p>
            </div>
            
            <!-- Separador decorativo -->
            <div class="error-divider"></div>
            
            <!-- Botones de acción -->
            <div class="error-actions">
                <?php foreach ($info_error['acciones'] as $accion): ?>
                    <a href="<?php echo htmlspecialchars($accion['url']); ?>" class="error-btn <?php echo htmlspecialchars($accion['clase']); ?>">
                        <?php echo htmlspecialchars($accion['texto']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Información adicional -->
            <div class="error-help">
                <p>¿Necesitas ayuda? <a href="contacto.php">Contáctanos</a></p>
                <p class="error-code">Código de error: <?php echo strtoupper($tipo_error); ?>-<?php echo date('YmdHis'); ?></p>
            </div>
        </div>
        
       
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // Animación de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const errorContent = document.querySelector('.error-content');
            errorContent.style.opacity = '0';
            errorContent.style.transform = 'translateY(-20px)';
            
            setTimeout(() => {
                errorContent.style.transition = 'all 0.6s ease-out';
                errorContent.style.opacity = '1';
                errorContent.style.transform = 'translateY(0)';
            }, 100);
            
            // Animación del icono
            const errorIcon = document.querySelector('.error-icon');
            errorIcon.style.animation = 'bounce 1s ease-in-out';
        });
    </script>
</body>
</html>
