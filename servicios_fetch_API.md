# Documentación Técnica - Peticiones AJAX del Panel de Administración

## Índice
1. [Resumen General](#resumen-general)
2. [Endpoints de Clientes](#1-endpoint-actualizarclientesphp)
3. [Endpoints de Productos](#2-endpoint-actualizarproductosphp)
4. [Endpoints de Paquetes](#3-endpoint-actualizarpaquetesphp)
5. [Endpoints de Pedidos](#4-endpoint-procesarpedidosphp)
6. [Endpoint del Buzón](#5-endpoint-buzonclientesphp)
7. [Diagrama de Flujo](#diagrama-de-flujo)
8. [Consideraciones de Seguridad](#consideraciones-de-seguridad)

---

## Resumen General
2
El sistema de administración utiliza peticiones AJAX con JavaScript Vanilla para realizar operaciones CRUD sin recargar la página completa. Todos los endpoints se encuentran en la carpeta `admin/clases/` y son consumidos desde los archivos PHP en `admin/phpAdmin/`.

### Tecnologías Utilizadas
- **Frontend**: JavaScript Vanilla (Fetch API y XMLHttpRequest)
- **Backend**: PHP con PDO para conexiones a base de datos
- **Formato de Intercambio**: JSON (excepto `buzonClientes.php`)
- **Framework CSS**: Bootstrap 5 (modales para confirmaciones)

---

## 1. Endpoint: `actualizarClientes.php`

**Ubicación**: `admin/clases/actualizarClientes.php`  
**Consumido desde**: `admin/phpAdmin/usuarios.php`

### Descripción General
Gestiona todas las operaciones relacionadas con clientes: dar de baja, modificar datos y reactivar cuentas.

---

### 1.1 Acción: `eliminar` (Dar de Baja)

**Función en el sistema**: Desactiva un cliente estableciendo su estatus a 0 y registrando la fecha de baja.

#### Request
```http
POST /admin/clases/actualizarClientes.php
Content-Type: application/x-www-form-urlencoded
```

| Parámetro | Tipo   | Requerido | Descripción                    |
|-----------|--------|-----------|--------------------------------|
| `action`  | string | Sí        | Valor fijo: `"eliminar"`       |
| `id`      | int    | Sí        | ID del cliente a dar de baja   |

#### Ejemplo de Request (JavaScript)
```javascript
fetch('../clases/actualizarClientes.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: 'action=eliminar&id=' + clienteIdEliminar
})
```

#### Response (JSON)
```json
{
    "ok": true
}
```

| Campo | Tipo    | Descripción                                      |
|-------|---------|--------------------------------------------------|
| `ok`  | boolean | `true` si la operación fue exitosa, `false` si falló |

#### Operación SQL
```sql
UPDATE clientes SET estatus = 0, fecha_baja = NOW() WHERE id = ?
```

---

### 1.2 Acción: `modificar` (Editar Cliente)

**Función en el sistema**: Actualiza los datos de un cliente existente. Valida que el email no esté duplicado.

#### Request
```http
POST /admin/clases/actualizarClientes.php
Content-Type: application/x-www-form-urlencoded
```

| Parámetro   | Tipo   | Requerido | Descripción                    |
|-------------|--------|-----------|--------------------------------|
| `action`    | string | Sí        | Valor fijo: `"modificar"`      |
| `id`        | int    | Sí        | ID del cliente a modificar     |
| `nombres`   | string | Sí        | Nombres del cliente            |
| `apellidos` | string | Sí        | Apellidos del cliente          |
| `email`     | string | Sí        | Correo electrónico (único)     |
| `telefono`  | string | Sí        | Número de teléfono             |
| `direccion` | string | Sí        | Dirección del cliente          |

#### Ejemplo de Request (JavaScript)
```javascript
var formData = new FormData(formEditar);
formData.append('action', 'modificar');

fetch('../clases/actualizarClientes.php', {
    method: 'POST',
    body: new URLSearchParams(formData)
})
```

#### Response (JSON)
```json
{
    "ok": true
}
```

| Campo | Tipo    | Descripción                                                     |
|-------|---------|-----------------------------------------------------------------|
| `ok`  | boolean | `true` si se actualizó, `false` si el email ya existe o falló |

#### Operación SQL
```sql
UPDATE clientes 
SET nombres = ?, apellidos = ?, email = ?, telefono = ?, direccion = ?, fecha_modifica = NOW() 
WHERE id = ?
```

#### Validación Adicional
Se verifica que el email no esté en uso por otro cliente:
```sql
SELECT id FROM clientes WHERE email = ? AND id != ?
```

---

### 1.3 Acción: `alta` (Reactivar Cliente)

**Función en el sistema**: Reactiva un cliente previamente dado de baja, estableciendo su estatus a 1.

#### Request
```http
POST /admin/clases/actualizarClientes.php
Content-Type: application/x-www-form-urlencoded
```

| Parámetro | Tipo   | Requerido | Descripción                    |
|-----------|--------|-----------|--------------------------------|
| `action`  | string | Sí        | Valor fijo: `"alta"`           |
| `id`      | int    | Sí        | ID del cliente a reactivar     |

#### Ejemplo de Request (JavaScript)
```javascript
fetch('../clases/actualizarClientes.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: 'action=alta&id=' + clienteIdGuardar
})
```

#### Response (JSON)
```json
{
    "ok": true
}
```

#### Operación SQL
```sql
UPDATE clientes SET estatus = 1, fecha_modifica = NOW() WHERE id = ?
```

---

## 2. Endpoint: `actualizarProductos.php`

**Ubicación**: `admin/clases/actualizarProductos.php`  
**Consumido desde**: `admin/phpAdmin/productos.php`

### Descripción General
Gestiona las operaciones de productos: editar información y desactivar productos de la tienda.

---

### 2.1 Acción: `eliminar` (Desactivar Producto)

**Función en el sistema**: Desactiva un producto estableciendo el campo `activo` a 0 (soft delete).

#### Request
```http
POST /admin/clases/actualizarProductos.php
Content-Type: application/x-www-form-urlencoded
```

| Parámetro | Tipo   | Requerido | Descripción                    |
|-----------|--------|-----------|--------------------------------|
| `action`  | string | Sí        | Valor fijo: `"eliminar"`       |
| `id`      | int    | Sí        | ID del producto a desactivar   |

#### Ejemplo de Request (JavaScript)
```javascript
fetch('../clases/actualizarProductos.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: new URLSearchParams({
        action: 'eliminar',
        id: productoIdQuitar
    })
})
```

#### Response (JSON)
```json
{
    "ok": true
}
```

| Campo   | Tipo    | Descripción                                |
|---------|---------|-------------------------------------------|
| `ok`    | boolean | `true` si la operación fue exitosa        |
| `error` | string  | Mensaje de error (solo si hay excepción)  |

#### Operación SQL
```sql
UPDATE productos SET activo = 0 WHERE id = ?
```

---

### 2.2 Acción: `editar` (Modificar Producto)

**Función en el sistema**: Actualiza el nombre, descripción y precio de un producto existente.

#### Request
```http
POST /admin/clases/actualizarProductos.php
Content-Type: application/x-www-form-urlencoded
```

| Parámetro     | Tipo   | Requerido | Descripción                    |
|---------------|--------|-----------|--------------------------------|
| `action`      | string | Sí        | Valor fijo: `"editar"`         |
| `id`          | int    | Sí        | ID del producto a editar       |
| `nombre`      | string | Sí        | Nombre del producto            |
| `descripcion` | string | Sí        | Descripción del producto       |
| `precio`      | float  | Sí        | Precio del producto            |

#### Ejemplo de Request (JavaScript)
```javascript
var formData = new FormData(formEditar);
formData.append('id', productoIdEditar);
formData.append('action', 'editar');

fetch('../clases/actualizarProductos.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: new URLSearchParams(formData)
})
```

#### Response (JSON)
```json
{
    "ok": true
}
```

**Response con error de datos faltantes:**
```json
{
    "ok": false,
    "error": "Faltan datos"
}
```

#### Operación SQL
```sql
UPDATE productos SET nombre = ?, descripcion = ?, precio = ? WHERE id = ?
```

---

## 3. Endpoint: `actualizarPaquetes.php`

**Ubicación**: `admin/clases/actualizarPaquetes.php`  
**Consumido desde**: `admin/phpAdmin/paquetes.php`

### Descripción General
Gestiona las operaciones de paquetes promocionales: editar información y desactivar paquetes.

---

### 3.1 Acción: `eliminar` (Desactivar Paquete)

**Función en el sistema**: Desactiva un paquete estableciendo el campo `activo` a 0.

#### Request
```http
POST /admin/clases/actualizarPaquetes.php
Content-Type: application/x-www-form-urlencoded
```

| Parámetro | Tipo   | Requerido | Descripción                    |
|-----------|--------|-----------|--------------------------------|
| `action`  | string | Sí        | Valor fijo: `"eliminar"`       |
| `id`      | int    | Sí        | ID del paquete a desactivar    |

#### Ejemplo de Request (JavaScript)
```javascript
fetch('../clases/actualizarPaquetes.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: 'action=eliminar&id=' + paqueteIdEliminar
})
```

#### Response (JSON)
```json
{
    "ok": true
}
```

#### Operación SQL
```sql
UPDATE paquetes SET activo = 0 WHERE id = ?
```

---

### 3.2 Acción: `editar` (Modificar Paquete)

**Función en el sistema**: Actualiza el nombre, descripción, precio y descuento de un paquete.

#### Request
```http
POST /admin/clases/actualizarPaquetes.php
Content-Type: application/x-www-form-urlencoded
```

| Parámetro     | Tipo   | Requerido | Descripción                    |
|---------------|--------|-----------|--------------------------------|
| `action`      | string | Sí        | Valor fijo: `"editar"`         |
| `id`          | int    | Sí        | ID del paquete a editar        |
| `nombre`      | string | Sí        | Nombre del paquete             |
| `descripcion` | string | Sí        | Descripción del paquete        |
| `precio`      | float  | Sí        | Precio del paquete             |
| `descuento`   | float  | Sí        | Porcentaje de descuento        |

#### Ejemplo de Request (JavaScript)
```javascript
var formData = new FormData(formEditar);
formData.append('id', paqueteIdEditar);
formData.append('action', 'editar');

fetch('../clases/actualizarPaquetes.php', {
    method: 'POST',
    body: new URLSearchParams(formData)
})
```

#### Response (JSON)
```json
{
    "ok": true
}
```

**Response sin acción especificada:**
```json
{
    "ok": false,
    "error": "No action specified"
}
```

#### Operación SQL
```sql
UPDATE paquetes SET nombre = ?, descripcion = ?, precio = ?, descuento = ? WHERE id = ?
```

---

## 4. Endpoint: `procesarPedidos.php`

**Ubicación**: `admin/clases/procesarPedidos.php`  
**Consumido desde**: 
- `admin/phpAdmin/pedidos.php` (proceso=1 → proceso=2)
- `admin/phpAdmin/pedidosPendientes.php` (proceso=2 → proceso=3)

### Descripción General
Gestiona el flujo de estados de los pedidos en el sistema:
- **proceso = 1**: Pedido nuevo (pendiente de tomar)
- **proceso = 2**: Pedido en proceso
- **proceso = 3**: Pedido finalizado

---

### Cambiar Estado del Pedido

**Función en el sistema**: Actualiza el estado de un pedido y registra la fecha correspondiente.

#### Request
```http
POST /admin/clases/procesarPedidos.php
Content-Type: application/x-www-form-urlencoded
```

| Parámetro | Tipo | Requerido | Descripción                                      |
|-----------|------|-----------|--------------------------------------------------|
| `id`      | int  | Sí        | ID del pedido                                    |
| `proceso` | int  | Sí        | Nuevo estado: `2` (en proceso) o `3` (finalizado)|

#### Ejemplo de Request - Tomar Pedido (XMLHttpRequest)
```javascript
var xhr = new XMLHttpRequest();
xhr.open('POST', '../clases/procesarPedidos.php', true);
xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
        console.log(xhr.responseText);
        location.reload();
    }
};
xhr.send('id=' + pedidoId + '&proceso=2');
```

#### Ejemplo de Request - Finalizar Pedido (XMLHttpRequest)
```javascript
xhr.send('id=' + pedidoId + '&proceso=3');
```

#### Response (Texto Plano)
**Éxito:**
```
proceso del pedido actualizado correctamente.
```

**Error:**
```
Error al actualizar el proceso del pedido.
```

> **Nota**: Este endpoint retorna texto plano, no JSON.

#### Operaciones SQL Según el Estado

**Para proceso = 2 (En Proceso):**
```sql
UPDATE pedidos SET proceso = 2, fecha_procesa = NOW() WHERE id = ?
```

**Para proceso = 3 (Finalizado):**
```sql
UPDATE pedidos SET proceso = 3, fecha_finaliza = NOW() WHERE id = ?
```

---

## 5. Endpoint: `buzonClientes.php`

**Ubicación**: `admin/clases/buzonClientes.php`  
**Consumido desde**: Formulario en `clientes/html/conócenos.html`

### Descripción General
Procesa el formulario de contacto de los clientes. **No utiliza AJAX**, sino un envío de formulario tradicional con redirección.

#### Request
```http
POST /admin/clases/buzonClientes.php
Content-Type: application/x-www-form-urlencoded
```

| Parámetro    | Tipo   | Requerido | Descripción                |
|--------------|--------|-----------|----------------------------|
| `nombre`     | string | Sí        | Nombre del remitente       |
| `email`      | string | Sí        | Correo electrónico         |
| `usuario`    | string | No        | Nombre de usuario (opcional)|
| `asunto`     | string | No        | Asunto del mensaje         |
| `comentario` | string | Sí        | Contenido del mensaje      |

#### Response
- **Éxito**: Redirección a `../../clientes/html/conócenos.html`
- **Error**: Muestra mensaje `"Error al enviar el comentario"`

#### Operación SQL
```sql
INSERT INTO comentarios (nombre, email, usuario, asunto, comentario) 
VALUES (:nombre, :email, :usuario, :asunto, :comentario)
```

---

## Diagrama de Flujo

```
┌─────────────────────────────────────────────────────────────────────┐
│                     PANEL DE ADMINISTRACIÓN                         │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌─────────────┐    AJAX/Fetch    ┌──────────────────────────────┐ │
│  │ usuarios.php│ ───────────────► │ actualizarClientes.php       │ │
│  └─────────────┘                  │  ├─ eliminar (baja)          │ │
│                                   │  ├─ modificar (editar)       │ │
│                                   │  └─ alta (reactivar)         │ │
│                                   └──────────────────────────────┘ │
│                                                                     │
│  ┌──────────────┐   AJAX/Fetch    ┌──────────────────────────────┐ │
│  │ productos.php│ ──────────────► │ actualizarProductos.php      │ │
│  └──────────────┘                 │  ├─ eliminar (desactivar)    │ │
│                                   │  └─ editar (modificar)       │ │
│                                   └──────────────────────────────┘ │
│                                                                     │
│  ┌─────────────┐    AJAX/Fetch    ┌──────────────────────────────┐ │
│  │ paquetes.php│ ────────────────►│ actualizarPaquetes.php       │ │
│  └─────────────┘                  │  ├─ eliminar (desactivar)    │ │
│                                   │  └─ editar (modificar)       │ │
│                                   └──────────────────────────────┘ │
│                                                                     │
│  ┌─────────────────────┐   XHR    ┌──────────────────────────────┐ │
│  │ pedidos.php         │ ────────►│ procesarPedidos.php          │ │
│  │ pedidosPendientes.php│         │  └─ cambiar estado (proceso) │ │
│  └─────────────────────┘          └──────────────────────────────┘ │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Tabla Resumen de Endpoints

| Endpoint                   | Acciones Disponibles         | Método | Formato Response |
|----------------------------|------------------------------|--------|------------------|
| `actualizarClientes.php`   | eliminar, modificar, alta    | POST   | JSON             |
| `actualizarProductos.php`  | eliminar, editar             | POST   | JSON             |
| `actualizarPaquetes.php`   | eliminar, editar             | POST   | JSON             |
| `procesarPedidos.php`      | actualizar proceso           | POST   | Texto Plano      |
| `buzonClientes.php`        | insertar comentario          | POST   | Redirección      |

---

## Consideraciones de Seguridad

### Vulnerabilidades Identificadas

1. **Sin autenticación en endpoints**: Los endpoints no verifican si el usuario tiene una sesión activa de administrador.

2. **Sin protección CSRF**: No se utilizan tokens CSRF para validar el origen de las peticiones.

3. **SQL Injection**: Los endpoints utilizan prepared statements, lo cual es correcto y seguro.

4. **XSS potencial**: Los datos se muestran directamente en las tablas sin sanitización con `htmlspecialchars()`.

### Recomendaciones

```php
// Ejemplo de verificación de sesión (añadir al inicio de cada endpoint)
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

// Ejemplo de token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['ok' => false, 'error' => 'Token inválido']);
    exit;
}
```

---

## Patrones de Manejo de Respuestas en JavaScript

### Patrón 1: Fetch API con JSON
```javascript
fetch('../clases/endpoint.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=accion&id=' + id
})
.then(response => response.json())
.then(data => {
    if (data.ok) {
        window.location.reload();
    } else {
        alert('Error en la operación');
    }
})
.catch(error => console.error('Error:', error));
```

### Patrón 2: XMLHttpRequest (usado en pedidos)
```javascript
var xhr = new XMLHttpRequest();
xhr.open('POST', '../clases/endpoint.php', true);
xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
        console.log(xhr.responseText);
        location.reload();
    }
};
xhr.send('id=' + id + '&param=valor');
```

---

## Estados de Pedidos

| Valor `proceso` | Estado        | Página de Visualización      |
|-----------------|---------------|------------------------------|
| 1               | Nuevo         | `pedidos.php`                |
| 2               | En Proceso    | `pedidosPendientes.php`      |
| 3               | Finalizado    | `pedidosFinalizados.php`     |

---

