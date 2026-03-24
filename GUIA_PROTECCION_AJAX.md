# Guía de Protección de Endpoints AJAX

## 📋 Resumen de Mejoras

Se han agregado nuevas funciones en `shared/AuthGuards.php` para proteger endpoints AJAX de forma consistente y segura.

---

## 🔐 Nuevas Funciones Disponibles

### 1. Validación de Solicitud AJAX

#### `isAjaxRequest(): bool`

Devuelve `true` si la solicitud tiene el header `X-Requested-With: XMLHttpRequest`.

```php
if (isAjaxRequest()) {
    // Es una solicitud AJAX válida
}
```

#### `requireAjaxRequest(): void`

Valida que sea una solicitud AJAX. Si no lo es, envía error JSON 400 y termina.

```php
requireAjaxRequest(); // Si no es AJAX, envía error y sale
```

---

### 2. Validación de Autenticación para AJAX

#### `requireCustomerAjax(string $customMessage = '...'): void`

Valida que el cliente esté autenticado. Envía JSON 401 si no lo está.

```php
<?php
require_once '../../shared/AuthGuards.php';
require_once '../config/database.php';

// Valida que sea AJAX y que cliente esté autenticado
requireCustomerAjaxAuth();

// Resto del código
```

#### `requireAdminAjax(string $customMessage = '...'): void`

Valida que sea administrador. Envía JSON 401 si no lo es.

```php
<?php
require_once '../../shared/AuthGuards.php';

// Valida que sea AJAX y admin
requireAdminAjaxAuth();

// Resto del código
```

---

### 3. Funciones Combinadas (Recomendadas)

#### `requireCustomerAjaxAuth(string $customMessage = '...'): void`

**RECOMENDADO** - Valida AJAX + autenticación de cliente en una línea.

```php
<?php
require_once '../../shared/AuthGuards.php';
require_once '../config/database.php';

requireCustomerAjaxAuth('Cliente no autenticado');

// Resto del código - está garantizado ser AJAX y autenticado
```

#### `requireAdminAjaxAuth(string $customMessage = '...'): void`

**RECOMENDADO** - Valida AJAX + autenticación de admin en una línea.

```php
<?php
require_once '../../shared/AuthGuards.php';

requireAdminAjaxAuth('Acceso de administrador requerido');

// Resto del código - está garantizado ser AJAX y admin
```

---

### 4. Funciones de Respuesta JSON

#### `sendJsonError(string $error, string $code = 'ERROR', int $httpCode = 400): void`

Envía respuesta JSON de error y termina.

```php
if (!$usuarioValido) {
    sendJsonError('Usuario no válido', 'INVALID_USER', 400);
}
```

**Respuesta enviada:**

```json
{
  "ok": false,
  "error": "Usuario no válido",
  "code": "INVALID_USER"
}
```

#### `sendJsonSuccess($data = null, int $httpCode = 200): void`

Envía respuesta JSON exitosa y termina.

```php
$resultado = ['id' => 123, 'nombre' => 'Juan'];
sendJsonSuccess($resultado);
```

**Respuesta enviada:**

```json
{
  "ok": true,
  "data": {
    "id": 123,
    "nombre": "Juan"
  }
}
```

---

### 5. Funciones de Información del Usuario

#### `getCurrentAuthUserId(): ?int`

Devuelve el ID del usuario autenticado o `null`.

```php
$userId = getCurrentAuthUserId();
if ($userId === null) {
    sendJsonError('No autenticado', 'UNAUTHORIZED', 401);
}
```

#### `getCurrentAuthUserName(): ?string`

Devuelve el nombre del usuario autenticado o `null`.

```php
$userName = getCurrentAuthUserName();
echo "Conectado como: " . htmlspecialchars($userName);
```

---

## 📝 Ejemplos de Implementación

### Ejemplo 1: Endpoint AJAX de Clientes (Público)

**Archivo:** `clientes/clases/clienteAjax.php`

```php
<?php
require_once '../config/database.php';
require_once '../config/config.php';
require_once 'clienteFunciones.php';
require_once '../../shared/AuthGuards.php';

// Valida que sea AJAX
requireAjaxRequest();

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_POST['action'])) {
    sendJsonError('Acción no especificada', 'MISSING_ACTION', 400);
}

$action = trim($_POST['action']);
$db = new Database();
$con = $db->conectar();

try {
    if ($action === 'existeUsuario' && isset($_POST['usuario'])) {
        $usuario = trim($_POST['usuario']);

        if (strlen($usuario) < 3) {
            sendJsonError('Usuario muy corto', 'INVALID_INPUT', 400);
        }

        $existe = usuarioExiste($usuario, $con);
        sendJsonSuccess(['existe' => (bool) $existe]);

    } else {
        sendJsonError('Acción no reconocida', 'INVALID_ACTION', 400);
    }
} catch (Exception $e) {
    error_log('Error en clienteAjax: ' . $e->getMessage());
    sendJsonError('Error en servidor', 'SERVER_ERROR', 500);
}
?>
```

---

### Ejemplo 2: Endpoint AJAX de Admin (Protegido)

**Archivo:** `admin/clases/actualizarClientes.php` (mejora)

```php
<?php
require '../config/database.php';
require_once '../../shared/AuthGuards.php';

// Valida AJAX + autenticación de admin
requireAdminAjaxAuth('Acceso de administrador requerido');

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_POST['action'])) {
    sendJsonError('Acción no especificada', 'MISSING_ACTION', 400);
}

$action = (string)$_POST['action'];
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

try {
    $db = new Database();
    $con = $db->conectar();

    if ($action === 'eliminar') {
        if ($id <= 0) {
            sendJsonError('ID inválido', 'INVALID_ID', 400);
        }
        $ok = eliminar($id, $con);
        if ($ok) {
            sendJsonSuccess(['message' => 'Cliente eliminado']);
        } else {
            sendJsonError('No se pudo eliminar', 'DELETE_FAILED', 500);
        }
    } else {
        sendJsonError('Acción no reconocida', 'INVALID_ACTION', 400);
    }
} catch (Exception $e) {
    error_log('Error en actualizarClientes: ' . $e->getMessage());
    sendJsonError('Error en servidor', 'SERVER_ERROR', 500);
}

function eliminar($id, $con) {
    // Lógica de eliminación...
    return true;
}
?>
```

---

### Ejemplo 3: Endpoint AJAX para Cliente Autenticado

```php
<?php
require '../config/database.php';
require '../config/config.php';
require_once '../../shared/AuthGuards.php';

// Valida AJAX + autenticación de cliente
requireCustomerAjaxAuth('Debe iniciar sesión');

header('Content-Type: application/json; charset=UTF-8');

$userId = getCurrentAuthUserId();
$action = $_POST['action'] ?? '';

try {
    if ($action === 'obtenerPedidos') {
        $db = new Database();
        $con = $db->conectar();

        $sql = $con->prepare("SELECT * FROM pedidos WHERE cliente_id = ? ORDER BY fecha DESC");
        $sql->execute([$userId]);
        $pedidos = $sql->fetchAll(PDO::FETCH_ASSOC);

        sendJsonSuccess(['pedidos' => $pedidos]);
    } else {
        sendJsonError('Acción no válida', 'INVALID_ACTION', 400);
    }
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    sendJsonError('Error en servidor', 'SERVER_ERROR', 500);
}
?>
```

---

## 🔄 Refactorización de Endpoints Existentes

### De Viejo a Nuevo

**Antes:**

```php
<?php
requireAdminAuth([
    'response_mode' => 'json',
    'redirect' => '../phpAdmin/loginAdmin.php'
]);

header('Content-Type: application/json');
$datos = ['ok' => false];

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    // ...
}

echo json_encode($datos);
?>
```

**Después:**

```php
<?php
requireAdminAjaxAuth();

if (!isset($_POST['action'])) {
    sendJsonError('Acción no especificada', 'MISSING_ACTION', 400);
}

$action = $_POST['action'];
try {
    // Lógica...
    sendJsonSuccess(['ok' => true]);
} catch (Exception $e) {
    sendJsonError('Error: ' . $e->getMessage(), 'ERROR', 500);
}
?>
```

---

## 📊 Códigos de Error JSON Estándar

| Código            | HTTP | Descripción                        |
| ----------------- | ---- | ---------------------------------- |
| `UNAUTHORIZED`    | 401  | Cliente/Usuario no autenticado     |
| `ADMIN_REQUIRED`  | 401  | Requiere permisos de administrador |
| `INVALID_REQUEST` | 400  | Solicitud AJAX inválida            |
| `MISSING_ACTION`  | 400  | Acción no especificada             |
| `INVALID_ACTION`  | 400  | Acción no reconocida               |
| `INVALID_INPUT`   | 400  | Parámetros de entrada inválidos    |
| `INVALID_ID`      | 400  | ID inválido                        |
| `INVALID_EMAIL`   | 400  | Email inválido                     |
| `SERVER_ERROR`    | 500  | Error en el servidor               |
| `DATABASE_ERROR`  | 500  | Error de base de datos             |

---

## 🧾 Matriz de Permisos de Rutas

Esta matriz está definida en `shared/AuthGuards.php` mediante la función `getRoutePermissionMatrix()`.

- `guest`: acceso público sin login.
- `customer`: solo usuarios clientes autenticados.
- `admin`: solo administradores autenticados.

Ruta ejemplo:

- `clientes/phpClientes/index.php` => `guest, customer, admin`
- `clientes/phpClientes/perfil.php` => `customer`
- `admin/phpAdmin/inicio.php` => `admin`
- `admin/clases/actualizarClientes.php` => `admin`

### Uso recomendado

```php
requireRoutePermission('clientes/phpClientes/perfil.php', ['redirect' => 'login.php']);
```

---

---

## 🎯 Checklist de Implementación

Para cada endpoint AJAX, verifica:

- [ ] ¿Usa `requireAjaxRequest()` o derivadas?
- [ ] ¿Valida autenticación con `requireCustomerAjaxAuth()` o `requireAdminAjaxAuth()`?
- [ ] ¿Valida parámetros de entrada?
- [ ] ¿Usa `sendJsonSuccess()` para éxito?
- [ ] ¿Usa `sendJsonError()` para errores?
- [ ] ¿Tiene manejo de excepciones try/catch?
- [ ] ¿Registra errores en logs?
- [ ] ¿Los códigos HTTP son correctos (200, 400, 401, 500)?

---

## 🔍 Testing de Endpoints

### Test 1: Verificar que es solo AJAX

**Request inválido (sin AJAX header):**

```bash
curl -X POST http://localhost/proyecto/admin/clases/endpoint.php \
  -d "action=test"
```

**Respuesta esperada:**

```json
{
  "ok": false,
  "error": "Solicitud AJAX inválida",
  "code": "INVALID_REQUEST"
}
```

### Test 2: Verificar autenticación

**Request AJAX sin sesión:**

```bash
curl -X POST http://localhost/proyecto/admin/clases/endpoint.php \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "action=test"
```

**Respuesta esperada:**

```json
{
  "ok": false,
  "error": "Acceso de administrador requerido",
  "code": "ADMIN_REQUIRED"
}
```

---

## 📚 Documentación Completa

Ver `shared/AuthGuards.php` para la documentación inline de todas las funciones.
