# 🔐 Guía de Implementación: Multisesiones en Proyecto Gerardo

## 📋 Resumen General

Se ha implementado un sistema completo de **multisesiones simultáneas** que permite a cada usuario tener múltiples sesiones activas en diferentes dispositivos/navegadores, con capacidad de:

- ✅ Rastrear todas las sesiones activas
- ✅ Cerrar sesiones remotamente
- ✅ Ver información del dispositivo y IP
- ✅ Admin puede gestionar sesiones de usuarios
- ✅ Límites configurables por rol
- ✅ Timeout automático por inactividad
- ✅ Auditoría completa de eventos

---

## 🚀 Instalación (Paso a Paso)

### 1️⃣ Ejecutar Script SQL

**Ubicación:** `config_sql/01_multi_sessions_schema.sql`

Abre tu phpMyAdmin o cliente MySQL y ejecuta el contenido del archivo para crear:

- Tabla `user_sessions` (sesiones activas)
- Tabla `user_sessions_audit` (historial)
- Tabla `user_session_limits` (configuración por rol)
- Procedimiento almacenado para limpieza

```bash
# Desde línea de comandos (opcional)
mysql -u root -p tienda_online < config_sql/01_multi_sessions_schema.sql
```

**Verifica:** En phpMyAdmin deberías ver 3 tablas nuevas en `tienda_online`.

---

### 2️⃣ Archivos Generados

Todos estos archivos ya están creados:

| Archivo                          | Ubicación               | Propósito                  |
| -------------------------------- | ----------------------- | -------------------------- |
| `SessionManager.php`             | `shared/`               | Clase principal de gestión |
| `AuthService.php`                | `shared/`               | (Actualizado) Integración  |
| `sessionManagementAjax.php`      | `clientes/clases/`      | API AJAX para clientes     |
| `adminSessionManagementAjax.php` | `admin/clases/`         | API AJAX para admin        |
| `mis_sesiones.php`               | `clientes/phpClientes/` | Panel de sesiones usuario  |
| `admin_sesiones.php`             | `admin/phpAdmin/`       | Panel de sesiones admin    |

---

### 3️⃣ Integración en Login Existente

En tus archivos de login (cliente y admin), **después de autenticar**, agrega:

#### Para clientes (ej: `login.php`):

```php
<?php
require '../config/config.php';
require_once '../../shared/AuthService.php';

// ... código de autenticación ...

if (autenticacionExitosa) {
    // Autenticación normal
    $_SESSION['auth_user_id'] = $userId;
    $_SESSION['auth_role'] = 'customer';

    // ✨ AGREGAR: Registrar sesión en gestor
    $db = (new Database())->conectar();
    $sessionResult = registerUserSession($userId, $db, true);

    if ($sessionResult['ok']) {
        // Sesión registrada, redirigir
        header('Location: index.php');
    } else {
        // Error - manejar
        echo "Error registrando sesión: " . $sessionResult['error'];
    }
}
?>
```

#### Para admin (ej: `loginAdmin.php`):

```php
<?php
require '../config/database.php';
require_once '../../shared/AuthService.php';

// ... autenticación ...

if (autenticacionExitosa) {
    $_SESSION['auth_user_id'] = $adminId;
    $_SESSION['auth_role'] = 'admin';

    // ✨ AGREGAR: Registrar sesión
    $db = (new Database())->conectar();
    $sessionResult = registerUserSession($adminId, $db, true);

    if ($sessionResult['ok']) {
        header('Location: inicio.php');
    }
}
?>
```

---

### 4️⃣ Integración en Logout

En logout, cierra la sesión correctamente:

#### Cliente (`logout.php`):

```php
<?php
require '../config/config.php';
require_once '../../shared/AuthService.php';

$db = (new Database())->conectar();
logoutCurrentSession($db, 'user_logout');

header('Location: index.php');
?>
```

#### Admin (`logout.php`):

```php
<?php
require '../config/database.php';
require_once '../../shared/AuthService.php';

$db = (new Database())->conectar();
logoutCurrentSession($db, 'admin_logout');

header('Location: loginAdmin.php');
?>
```

---

### 5️⃣ Actualizar Actividad (IMPORTANTE)

Para que el timeout automático funcione, **actualiza la actividad en cada request autenticado**.

Crea un archivo `session_activity.php` en `shared/`:

```php
<?php
// shared/session_activity.php
require_once __DIR__ . '/AuthService.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Si hay sesión activa, actualizar actividad
if (!empty($_SESSION['auth_user_id'])) {
    try {
        $dbClass = class_exists('Database') ? 'Database' : null;
        if ($dbClass) {
            $db = (new $dbClass())->conectar();
            updateSessionActivity($db);
        }
    } catch (Throwable $e) {
        // Silenciar error si la BD no está disponible
    }
}
?>
```

Luego, en **TODAS tus páginas protegidas**, agrega **al inicio**:

```php
<?php
require '../config/config.php';
require_once '../../shared/session_activity.php'; // ← AGREGAR ESTO
require_once '../../shared/AuthGuards.php';

requireRoutePermission(...);
// resto del código
?>
```

---

## 📊 Estructura de Datos

### Tabla `user_sessions`

```sql
id              -- UUID único para la sesión
auth_user_id    -- ID del usuario
session_id      -- PHP session_id()
ip_address      -- IP del cliente
user_agent      -- Navegador/SO
device_info     -- "Chrome / Windows" (parseado)
is_active       -- 1 = activa, 0 = cerrada
created_at      -- Cuándo se creó
last_activity   -- Última vez que se vio actividad
expires_at      -- Cuándo expira por timeout
closed_at       -- Cuándo se cerró manualmente
```

### Tabla `user_session_limits`

```sql
role                      -- 'admin' o 'customer'
max_concurrent_sessions   -- Máximo simultáneo (por defecto 5)
session_timeout_minutes   -- Timeout por inactividad (por defecto 1440 = 24h)
```

---

## 🎯 API Reference

### SessionManager (Clase Principal)

```php
$manager = new SessionManager($db);

// 1. Crear sesión con límite automático
$manager->createSessionWithLimit($userId, $sessionId, $ip, $userAgent);

// 2. Obtener sesiones activas
$sessions = $manager->getActiveSessions($userId);

// 3. Cerrar sesión
$manager->closeSession($sessionId, 'logout');

// 4. Cerrar sesión remota (desde otro dispositivo)
$manager->closeRemoteSession($userId, $sessionIdToClose, $currentSessionId);

// 5. Cerrar todas excepto la actual
$manager->closeAllOtherSessions($userId, $currentSessionId);

// 6. Validar sesión
if ($manager->isSessionValid($sessionId)) { ... }

// 7. Limpiar expiradas
$manager->cleanupExpiredSessions();
```

### AuthService (Funciones Ayuda)

```php
// Registrar nueva sesión
registerUserSession($userId, $db, $enforceLimit = true);

// Actualizar último acceso
updateSessionActivity($db);

// Logout actual
logoutCurrentSession($db, $reason);

// Obtener IP cliente
getClientIpAddress();

// Obtener manager
getSessionManager($db);
```

---

## 🔗 Endpoints AJAX

### Cliente: `sessionManagementAjax.php`

```javascript
// Listar sesiones
POST action=list_sessions
Response: {ok, data: {sessions: [], session_count}}

// Cerrar sesión remota
POST action=close_session&session_id=XXX
Response: {ok, message}

// Cerrar todas excepto la actual
POST action=close_all_other
Response: {ok, data: {closed_count}}
```

### Admin: `adminSessionManagementAjax.php`

```javascript
// Listar sesiones de usuario
POST action=list_user_sessions&user_id=XXX
Response: {ok, data: {sessions}}

// Cerrar sesión
POST action=close_user_session&session_id=XXX&reason=...
Response: {ok}

// Limpiar expiradas
POST action=cleanup_expired
Response: {ok, data: {cleaned_count}}
```

---

## 🌐 URLs de Acceso

### Cliente

- **Panel de sesiones:** `clientes/phpClientes/mis_sesiones.php`
- **API:** `clientes/clases/sessionManagementAjax.php`

### Admin

- **Panel de sesiones:** `admin/phpAdmin/admin_sesiones.php`
- **API:** `admin/clases/adminSessionManagementAjax.php`

---

## ⚙️ Configuración

### Cambiar límites por rol

En phpMyAdmin, actualiza `user_session_limits`:

```sql
-- Admins: solo 2 sesiones, timeout 2h
UPDATE user_session_limits
SET max_concurrent_sessions = 2, session_timeout_minutes = 120
WHERE role = 'admin';

-- Clientes: 5 sesiones, timeout 30 días
UPDATE user_session_limits
SET max_concurrent_sessions = 5, session_timeout_minutes = 43200
WHERE role = 'customer';
```

---

## 🧹 Limpieza Automática

### Opción 1: Cronjob (Recomendado)

```bash
# Ejecutar cada hora para limpiar sesiones expiradas
0 * * * * curl -X POST http://localhost/proyecto/admin/clases/adminSessionManagementAjax.php \
  -d "action=cleanup_expired" \
  -H "X-Requested-With: XMLHttpRequest"
```

### Opción 2: Manual (Desde Admin Panel)

Click en "Limpiar Sesiones Expiradas" → `admin_sesiones.php`

---

## 🔒 Seguridad

### ✅ Implementado:

- ✓ Validación de propiedad (usuario solo ve sus sesiones)
- ✓ Regeneración de session_id al login
- ✓ Cierre de sesión al tiemout
- ✓ Auditoría completa en `user_sessions_audit`
- ✓ Validación de AJAX header
- ✓ IP tracking para detección de anomalías (futuro)

### 📝 Auditoría

Las siguientes acciones se registran:

- `login` - Inicio de sesión
- `logout` - Cierre normal
- `expired` - Expirada por timeout
- `limit_exceeded` - Cerrada por límite
- `closed_remotely` - Cerrada desde otro dispositivo
- `closed_by_admin` - Cerrada por administrador

---

## 🧪 Test Rápido

1. **Login como cliente** → Se crea sesión en BD
2. **Abre otra pestaña** → Login again → Se crea sesión #2
3. **Ve a `mis_sesiones.php`** → Deberías ver 2 sesiones
4. **Cierra una → Pulse botón** → Se cierra esa sesión
5. **Check BD** → `user_sessions_audit` tiene registro

---

## 🐛 Troubleshooting

### "SessionManager no encontrado"

- ✓ Verifica que `shared/SessionManager.php` existe
- ✓ En `AuthService.php` revisa que tiene `require_once SessionManager`

### "Tabla no existe"

- ✓ Ejecuta script SQL: `config_sql/01_multi_sessions_schema.sql`
- ✓ Verifica en phpMyAdmin: `tienda_online` → Tablas

### "Sesión no se registra"

- ✓ Asegúrate de llamar `registerUserSession()` **después** de `$_SESSION['auth_user_id'] =`
- ✓ Verifica que `$db` es válido

### "setTimeout no funciona"

- ✓ Agrega `require_once '../../shared/session_activity.php'` en páginas protegidas
- ✓ O llama manualmente `updateSessionActivity($db)` en puntos clave

---

## 📚 Próximas Mejoras (Opcional)

- [ ] Geolocalización de IP
- [ ] Detección de sesiones sospechosas
- [ ] Notificación de nuevo login
- [ ] Confirmación 2FA para sesión nueva
- [ ] Historial detallado por usuario

---

## 📞 Soporte

Si encuentras problemas:

1. Revisa logs en navegador (DevTools → Console)
2. Revisa errores PHP: `error_log`
3. Verifica tabla `user_sessions_audit` para eventos
4. Verifica conectividad BD y permisos SQL

¡Listo para implementar! 🚀
