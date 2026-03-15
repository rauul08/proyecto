<?php
require '../config/database.php';

// Obtener el tipo de error desde la URL
$tipo_error = isset($_GET['tipo']) ? $_GET['tipo'] : 'general';
$mensaje_personalizado = isset($_GET['mensaje']) ? urldecode($_GET['mensaje']) : '';

// Definir mensajes de error según el tipo
$errores = [
    'actualizacion' => [
        'titulo' => 'Error de Actualización',
        'icono' => 'fas fa-exclamation-triangle',
        'mensaje' => 'No se pudo actualizar la información.',
        'detalles' => 'Hubo un problema al guardar los cambios. Por favor, verifica los datos e intenta nuevamente.',
        'acciones' => [
            ['texto' => 'Volver', 'url' => 'javascript:history.back()', 'clase' => 'btn-primary'],
            ['texto' => 'Ir al Inicio', 'url' => 'inicio.php', 'clase' => 'btn-secondary']
        ]
    ],
    'eliminacion' => [
        'titulo' => 'Error al Eliminar',
        'icono' => 'fas fa-trash-alt',
        'mensaje' => 'No se pudo eliminar el registro.',
        'detalles' => 'El elemento no pudo ser eliminado. Puede que esté siendo usado en otros registros.',
        'acciones' => [
            ['texto' => 'Volver', 'url' => 'javascript:history.back()', 'clase' => 'btn-primary'],
            ['texto' => 'Ver Dashboard', 'url' => 'inicio.php', 'clase' => 'btn-secondary']
        ]
    ],
    'login_admin' => [
        'titulo' => 'Error de Autenticación',
        'icono' => 'fas fa-user-lock',
        'mensaje' => 'Acceso denegado.',
        'detalles' => 'Las credenciales de administrador son incorrectas o no tienes permisos suficientes.',
        'acciones' => [
            ['texto' => 'Iniciar Sesión', 'url' => 'loginAdmin.php', 'clase' => 'btn-primary']
        ]
    ],
    'permisos' => [
        'titulo' => 'Permisos Insuficientes',
        'icono' => 'fas fa-ban',
        'mensaje' => 'No tienes permiso para realizar esta acción.',
        'detalles' => 'Se requieren privilegios de administrador para acceder a este recurso.',
        'acciones' => [
            ['texto' => 'Ir al Inicio', 'url' => 'inicio.php', 'clase' => 'btn-primary'],
            ['texto' => 'Cerrar Sesión', 'url' => 'logout.php', 'clase' => 'btn-secondary']
        ]
    ],
    'base_datos' => [
        'titulo' => 'Error de Base de Datos',
        'icono' => 'fas fa-database',
        'mensaje' => 'Error al conectar con la base de datos.',
        'detalles' => 'No se pudo establecer conexión con el servidor de base de datos. Contacta al soporte técnico.',
        'acciones' => [
            ['texto' => 'Reintentar', 'url' => 'inicio.php', 'clase' => 'btn-primary']
        ]
    ],
    'subida_archivo' => [
        'titulo' => 'Error al Subir Archivo',
        'icono' => 'fas fa-upload',
        'mensaje' => 'No se pudo subir el archivo.',
        'detalles' => 'El archivo no cumple con los requisitos o hubo un error en la carga. Verifica el formato y tamaño.',
        'acciones' => [
            ['texto' => 'Volver', 'url' => 'javascript:history.back()', 'clase' => 'btn-primary'],
            ['texto' => 'Ver Ayuda', 'url' => 'inicio.php', 'clase' => 'btn-secondary']
        ]
    ],
    'validacion' => [
        'titulo' => 'Error de Validación',
        'icono' => 'fas fa-check-circle',
        'mensaje' => 'Los datos no son válidos.',
        'detalles' => 'Algunos campos contienen información incorrecta o incompleta. Por favor, revisa el formulario.',
        'acciones' => [
            ['texto' => 'Volver', 'url' => 'javascript:history.back()', 'clase' => 'btn-primary']
        ]
    ],
    'general' => [
        'titulo' => 'Error del Sistema',
        'icono' => 'fas fa-exclamation-circle',
        'mensaje' => 'Ha ocurrido un error inesperado.',
        'detalles' => 'Lo sentimos, algo salió mal en el sistema. El error ha sido registrado.',
        'acciones' => [
            ['texto' => 'Volver al Dashboard', 'url' => 'inicio.php', 'clase' => 'btn-primary']
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
    <title>Admin JIREH | <?php echo htmlspecialchars($info_error['titulo']); ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/error_admin.css">    
    <link rel="icon" type="images/jpeg"  href="../images/jLeft.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="admin-error-body">
    <div class="admin-error-container">
        <div class="admin-error-content">
            <!-- Logo o Branding -->
            <div class="admin-error-brand">
                <img src="../images/jLeft.png" alt="JIREH Logo" class="admin-error-logo">
                <span class="admin-badge">Panel de Administración</span>
            </div>
            
            <!-- Icono de error -->
            <div class="admin-error-icon-wrapper">
                <div class="admin-error-circle">
                    <i class="<?php echo htmlspecialchars($info_error['icono']); ?> admin-error-icon"></i>
                </div>
            </div>
            
            <!-- Título del error -->
            <h1 class="admin-error-title"><?php echo htmlspecialchars($info_error['titulo']); ?></h1>
            
            <!-- Mensaje principal -->
            <p class="admin-error-message"><?php echo htmlspecialchars($info_error['mensaje']); ?></p>
            
            <!-- Detalles del error -->
            <div class="admin-error-details">
                <i class="fas fa-info-circle"></i>
                <p><?php echo htmlspecialchars($info_error['detalles']); ?></p>
            </div>
            
            <!-- Botones de acción -->
            <div class="admin-error-actions">
                <?php foreach ($info_error['acciones'] as $accion): ?>
                    <a href="<?php echo htmlspecialchars($accion['url']); ?>" class="admin-error-btn <?php echo htmlspecialchars($accion['clase']); ?>">
                        <i class="fas fa-arrow-left"></i>
                        <?php echo htmlspecialchars($accion['texto']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Información adicional -->
            <div class="admin-error-footer">
                <p class="admin-error-code">
                    <i class="fas fa-hashtag"></i>
                    Código de referencia: ADMIN-<?php echo strtoupper($tipo_error); ?>-<?php echo date('YmdHis'); ?>
                </p>
                <p class="admin-error-timestamp">
                    <i class="fas fa-clock"></i>
                    <?php echo date('d/m/Y H:i:s'); ?>
                </p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // Animación de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const errorContent = document.querySelector('.admin-error-content');
            errorContent.style.opacity = '0';
            errorContent.style.transform = 'translateY(-30px)';
            
            setTimeout(() => {
                errorContent.style.transition = 'all 0.5s ease-out';
                errorContent.style.opacity = '1';
                errorContent.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>
