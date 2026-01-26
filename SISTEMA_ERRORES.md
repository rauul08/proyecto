# 🚨 Sistema de Manejo de Errores - JIREH

Sistema completo y profesional para el manejo de errores en el sitio web de comida rápida JIREH. Proporciona páginas de error personalizadas tanto para clientes como para administradores.

## 📋 Contenido

- [Archivos Creados](#archivos-creados)
- [Características](#características)
- [Tipos de Errores](#tipos-de-errores)
- [Guía de Uso](#guía-de-uso)
- [Ejemplos de Implementación](#ejemplos-de-implementación)
- [Personalización](#personalización)
- [Testing](#testing)

---

## 📁 Archivos Creados

### Área de Clientes
1. **clientes/phpClientes/error.php** - Página principal de errores para clientes
2. **clientes/css/error.css** - Estilos para la página de errores de clientes
3. **clientes/phpClientes/ejemplos_integracion_errores.php** - Ejemplos de código de integración

### Área de Administración
4. **admin/phpAdmin/errorAdmin.php** - Página de errores para administradores
5. **admin/css/error_admin.css** - Estilos para la página de errores de admin

### Documentación
6. **GUIA_ERRORES.md** - Guía completa de uso del sistema
7. **SISTEMA_ERRORES.md** - Este archivo (documentación principal)

---

## ✨ Características

### 🎨 Diseño Visual
- ✅ Interfaz moderna y profesional
- ✅ Diseño responsive (móvil, tablet, desktop)
- ✅ Animaciones suaves y atractivas
- ✅ Gradientes y efectos visuales modernos
- ✅ Iconos distintivos para cada tipo de error
- ✅ Temas diferenciados para clientes y admin

### 🔧 Funcionalidad
- ✅ Mensajes personalizables por tipo de error
- ✅ Botones de acción contextuales
- ✅ Código único de referencia con timestamp
- ✅ Múltiples tipos de errores predefinidos
- ✅ Soporte para mensajes personalizados
- ✅ Integración sencilla con código existente

### 🛡️ Seguridad y UX
- ✅ Mensajes amigables para el usuario
- ✅ No expone información sensible del sistema
- ✅ Redirecciones inteligentes
- ✅ Código de error para rastreo
- ✅ Fecha y hora del error registrada

---

## 🎯 Tipos de Errores

### Para Clientes (error.php)

| Tipo | Descripción | Uso |
|------|-------------|-----|
| `compra` | Error al procesar una compra | Fallos en checkout, carrito vacío |
| `pago` | Error en el procesamiento de pago | Problemas con método de pago |
| `registro` | Error al registrar usuario | Fallo en creación de cuenta |
| `login` | Error de autenticación | Credenciales incorrectas |
| `base_datos` | Error de conexión a BD | Problemas de conexión |
| `zona_entrega` | Zona sin cobertura | Dirección fuera de área de entrega |
| `producto_no_disponible` | Producto no existe | Producto agotado o inexistente |
| `sesion_expirada` | Sesión caducada | Timeout de sesión |
| `general` | Error genérico | Cualquier otro error |

### Para Administradores (errorAdmin.php)

| Tipo | Descripción | Uso |
|------|-------------|-----|
| `actualizacion` | Error al actualizar | Fallo en actualización de datos |
| `eliminacion` | Error al eliminar | No se puede eliminar registro |
| `login_admin` | Error de autenticación admin | Login de admin fallido |
| `permisos` | Permisos insuficientes | Falta de privilegios |
| `base_datos` | Error de BD | Problemas de conexión |
| `subida_archivo` | Error al subir archivo | Fallo en upload de imágenes |
| `validacion` | Error de validación | Datos inválidos en formulario |
| `general` | Error genérico | Cualquier otro error |

---

## 📖 Guía de Uso

### Uso Básico

#### 1. Redirección Simple
```php
// Redirigir a error de compra
header("Location: error.php?tipo=compra");
exit;
```

#### 2. Con Mensaje Personalizado
```php
// Error con mensaje específico
$mensaje = "No se pudo procesar tu tarjeta de crédito";
header("Location: error.php?tipo=pago&mensaje=" . urlencode($mensaje));
exit;
```

#### 3. Desde el Área de Admin
```php
// Error en panel de administración
header("Location: errorAdmin.php?tipo=actualizacion");
exit;
```

### Función Helper (Opcional)

Puedes agregar esta función a `clienteFunciones.php`:

```php
/**
 * Redirige a la página de error con mensaje personalizado
 * @param string $tipo Tipo de error
 * @param string $mensaje Mensaje personalizado (opcional)
 */
function redirigirError($tipo = 'general', $mensaje = '') {
    $url = "error.php?tipo=" . urlencode($tipo);
    if (!empty($mensaje)) {
        $url .= "&mensaje=" . urlencode($mensaje);
    }
    header("Location: " . $url);
    exit;
}

// Uso:
// redirigirError('compra', 'Tu carrito está vacío');
// redirigirError('login');
```

---

## 💡 Ejemplos de Implementación

### Ejemplo 1: Zona de Entrega (proceso_pago.php)

**ANTES:**
```php
if (in_array($direccion_entrega_lower, $direcciones_permitidas)) {
    // procesar
} else {
    echo "Lo sentimos, no ofrecemos servicio de entrega en su zona.";
    exit;
}
```

**DESPUÉS:** ✅
```php
if (in_array($direccion_entrega_lower, $direcciones_permitidas)) {
    // procesar
} else {
    header("Location: error.php?tipo=zona_entrega");
    exit;
}
```

### Ejemplo 2: Error en Registro (registro.php)

**ANTES:**
```php
if ($stmt->execute()) {
    // éxito
} else {
    $errors[] = "Error al registrar usuario";
}
```

**DESPUÉS:** ✅
```php
if ($stmt->execute()) {
    // éxito
} else {
    header("Location: error.php?tipo=registro&mensaje=" . urlencode("No se pudo crear tu cuenta. Intenta nuevamente."));
    exit;
}
```

### Ejemplo 3: Validación de Sesión

**NUEVO:** ✅
```php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: error.php?tipo=sesion_expirada");
    exit;
}
```

### Ejemplo 4: Producto No Encontrado (details.php)

**NUEVO:** ✅
```php
$producto = $sql->fetch(PDO::FETCH_ASSOC);
if (!$producto) {
    header("Location: error.php?tipo=producto_no_disponible");
    exit;
}
```

### Ejemplo 5: Error en Admin (actualizarProductos.php)

**NUEVO:** ✅
```php
if ($stmt->execute()) {
    // éxito
} else {
    header("Location: errorAdmin.php?tipo=actualizacion&mensaje=" . urlencode("No se pudo actualizar el producto"));
    exit;
}
```

---

## 🎨 Personalización

### Agregar Nuevo Tipo de Error

Edita `error.php` y agrega en el array `$errores`:

```php
'mi_nuevo_error' => [
    'titulo' => 'Título del Error',
    'icono' => 'fas fa-icono-fontawesome',
    'mensaje' => 'Mensaje principal corto',
    'detalles' => 'Descripción más detallada del error',
    'acciones' => [
        ['texto' => 'Botón Principal', 'url' => 'pagina.php', 'clase' => 'btn-primary'],
        ['texto' => 'Botón Secundario', 'url' => 'otra.php', 'clase' => 'btn-secondary']
    ]
]
```

### Cambiar Colores

Edita `clientes/css/error.css`:

```css
/* Cambiar gradiente de fondo */
body {
    background: linear-gradient(135deg, #TU_COLOR_1 0%, #TU_COLOR_2 100%);
}

/* Cambiar color del icono */
.error-icon {
    color: #TU_COLOR;
}
```

### Cambiar Estilos de Botones

```css
.btn-primary {
    background: linear-gradient(135deg, #TU_COLOR_1 0%, #TU_COLOR_2 100%);
}
```

---

## 🧪 Testing

### URLs de Prueba - Área de Clientes

Prueba cada tipo de error visitando estas URLs:

```
http://localhost/proyecto/clientes/phpClientes/error.php?tipo=compra
http://localhost/proyecto/clientes/phpClientes/error.php?tipo=pago
http://localhost/proyecto/clientes/phpClientes/error.php?tipo=registro
http://localhost/proyecto/clientes/phpClientes/error.php?tipo=login
http://localhost/proyecto/clientes/phpClientes/error.php?tipo=base_datos
http://localhost/proyecto/clientes/phpClientes/error.php?tipo=zona_entrega
http://localhost/proyecto/clientes/phpClientes/error.php?tipo=producto_no_disponible
http://localhost/proyecto/clientes/phpClientes/error.php?tipo=sesion_expirada
http://localhost/proyecto/clientes/phpClientes/error.php?tipo=general
```

### URLs de Prueba - Área de Admin

```
http://localhost/proyecto/admin/phpAdmin/errorAdmin.php?tipo=actualizacion
http://localhost/proyecto/admin/phpAdmin/errorAdmin.php?tipo=eliminacion
http://localhost/proyecto/admin/phpAdmin/errorAdmin.php?tipo=login_admin
http://localhost/proyecto/admin/phpAdmin/errorAdmin.php?tipo=permisos
http://localhost/proyecto/admin/phpAdmin/errorAdmin.php?tipo=validacion
```

### Prueba con Mensaje Personalizado

```
http://localhost/proyecto/clientes/phpClientes/error.php?tipo=compra&mensaje=Este%20es%20un%20mensaje%20de%20prueba
```

---

## 📱 Responsive Design

El sistema es completamente responsive y se adapta a:

- 📱 **Móviles** (< 480px)
- 📱 **Tablets** (480px - 768px)
- 💻 **Desktop** (> 768px)

---

## 🔒 Seguridad

### Buenas Prácticas Implementadas

1. ✅ **Escapado de HTML:** Uso de `htmlspecialchars()` para prevenir XSS
2. ✅ **URL Encoding:** Uso de `urlencode()` para parámetros URL
3. ✅ **No Indexación:** Meta tag `noindex, nofollow` en páginas de error
4. ✅ **Mensajes Genéricos:** No expone detalles técnicos sensibles
5. ✅ **Validación de Tipos:** Validación del parámetro tipo de error

---

## 📊 Ventajas del Sistema

| Ventaja | Descripción |
|---------|-------------|
| 🎯 **Centralizado** | Un solo archivo maneja todos los errores |
| 🔧 **Mantenible** | Fácil agregar o modificar tipos de error |
| 🎨 **Profesional** | Diseño moderno y atractivo |
| 📱 **Responsive** | Funciona en todos los dispositivos |
| 🚀 **Rápido** | Carga ligera y rápida |
| 🔍 **Rastreable** | Código único para cada error |
| 👥 **UX Mejorada** | Mensajes claros y acciones directas |
| 🛡️ **Seguro** | No expone información sensible |

---

## 🎓 Mejores Prácticas

### ✅ HACER

- Usar tipos de error específicos
- Incluir mensajes personalizados cuando sea relevante
- Siempre usar `exit;` después de la redirección
- Codificar mensajes con `urlencode()`
- Registrar errores críticos en logs del servidor

### ❌ NO HACER

- Exponer detalles técnicos al usuario
- Olvidar el `exit;` después de `header()`
- Usar echo para mostrar errores críticos
- Redirecciones sin codificar parámetros
- Ignorar el registro de errores en producción

---

## 🔄 Integración con Código Existente

### Archivos Ya Actualizados

✅ **proceso_pago.php** - Errores de zona de entrega y procesamiento de pedidos

### Archivos Sugeridos para Actualizar

- [ ] **registro.php** - Errores de registro
- [ ] **login.php** - Errores de autenticación
- [ ] **checkout.php** - Errores de carrito vacío
- [ ] **details.php** - Errores de producto no encontrado
- [ ] **Admin: actualizarProductos.php** - Errores de actualización
- [ ] **Admin: loginAdmin.php** - Errores de login admin

---

## 📝 Notas Adicionales

### Logging de Errores (Recomendado)

Para producción, considera agregar registro de errores:

```php
// Antes de redirigir, registrar el error
error_log("Error tipo: $tipo_error - Mensaje: $mensaje - Usuario: " . $_SESSION['user_id']);
header("Location: error.php?tipo=$tipo_error");
exit;
```

### Notificaciones por Email (Opcional)

Para errores críticos:

```php
if ($tipo_error == 'base_datos') {
    // Enviar email al administrador
    mail('admin@jireh.com', 'Error Crítico', $mensaje_error);
}
```

---

## 🎉 Resultado Final

El sistema proporciona:

1. ✅ Páginas de error profesionales y atractivas
2. ✅ Manejo consistente de errores en todo el sitio
3. ✅ Mejor experiencia de usuario
4. ✅ Código más limpio y mantenible
5. ✅ Rastreabilidad de errores
6. ✅ Diseño responsive para todos los dispositivos
7. ✅ Separación de errores de clientes y administradores

---

## 📞 Soporte

Para más información o dudas:
- Revisa: `GUIA_ERRORES.md`
- Consulta: `ejemplos_integracion_errores.php`
- Código fuente: `error.php` y `errorAdmin.php`

---

**Desarrollado para JIREH - Sistema de Comida Rápida** 🍔

*Última actualización: Enero 2026*
