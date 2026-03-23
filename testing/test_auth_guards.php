<?php
declare(strict_types=1);

require_once __DIR__ . '/../shared/AuthGuards.php';
require_once __DIR__ . '/../shared/AuthService.php';

function assertTrue(bool $cond, string $message): void
{
    if (!$cond) {
        throw new RuntimeException("FALLÓ: $message");
    }
}

function assertSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException("FALLÓ: $message (esperado: " . var_export($expected, true) . ", actual: " . var_export($actual, true) . ")");
    }
}

function runRoutePermissionProcess(string $route, array $sessionVars = []): array
{
    $tmp = tempnam(sys_get_temp_dir(), 'authg_');
    $sessionCode = "session_start();\nclearLegacyAuthSession();\n";
    foreach ($sessionVars as $k => $v) {
        $sessionCode .= "
        \\$_SESSION['" . addslashes($k) . "'] = " . var_export($v, true) . ";\n";
    }

    $script = "<?php\n";
    $script .= "require_once '" . addslashes(__DIR__ . '/../shared/AuthGuards.php') . "';\n";
    $script .= "require_once '" . addslashes(__DIR__ . '/../shared/AuthService.php') . "';\n";
    $script .= $sessionCode;
    $script .= "\nrequireRoutePermission('" . addslashes($route) . "', ['redirect' => 'login.php', 'response_mode' => 'json']);\n";
    $script .= "echo 'ACCESS_GRANTED';\n";

    file_put_contents($tmp, $script);

    exec("php " . escapeshellarg($tmp), $outputLines, $exitCode);
    unlink($tmp);

    return [$exitCode, implode("\n", $outputLines)];
}

try {
    // 1) Roles y estado de sesión
    session_start();
    clearLegacyAuthSession();
    assertSame(null, currentAuthRole(), 'Sin sesión debe no tener rol');

    \\$_SESSION['auth_role'] = 'customer';
    assertSame('customer', currentAuthRole(), 'auth_role customer');
    assertTrue(isCustomerAuthenticated(), 'isCustomerAuthenticated()');
    assertTrue(!isAdminAuthenticated(), 'isAdminAuthenticated() con customer');

    clearLegacyAuthSession();
    \\$_SESSION['user_type'] = 'admin';
    assertSame('admin', currentAuthRole(), 'user_type admin se normaliza');

    clearLegacyAuthSession();

    // 2) Matriz de rutas
    $matrix = getRoutePermissionMatrix();
    assertTrue(isset($matrix['clientes/phpClientes/perfil.php']), 'Perfil existe en la matriz');
    assertTrue(in_array('customer', $matrix['clientes/phpClientes/perfil.php'], true), 'Perfil para customer');
    assertTrue(isset($matrix['admin/phpAdmin/inicio.php']), 'Inicio admin existe en la matriz');

    // 3) Comportamiento de requireRoutePermission en varios escenarios (subproceso porque requiere exit/redirect)
    [$code, $output] = runRoutePermissionProcess('clientes/phpClientes/index.php');
    assertSame(0, $code, 'Ruta pública debería permitirse');
    assertTrue(strpos($output, 'ACCESS_GRANTED') !== false, 'Ruta pública returns ACCESS_GRANTED');

    [$code, $output] = runRoutePermissionProcess('clientes/phpClientes/perfil.php');
    assertSame(0, $code, 'Ruta perfil para guest retorna 401/json');
    assertTrue(strpos($output, '"ok":false') !== false, 'Ruta perfil no autorizada produce JSON');

    [$code, $output] = runRoutePermissionProcess('clientes/phpClientes/perfil.php', ['auth_role' => 'customer']);
    assertSame(0, $code, 'Ruta perfil para customer permitida');
    assertTrue(strpos($output, 'ACCESS_GRANTED') !== false, 'Perfil customer ACCESS_GRANTED');

    [$code, $output] = runRoutePermissionProcess('admin/phpAdmin/inicio.php', ['auth_role' => 'admin']);
    assertSame(0, $code, 'Ruta admin para admin permitida');
    assertTrue(strpos($output, 'ACCESS_GRANTED') !== false, 'Admin ACCESS_GRANTED');

    echo "OK: Todos los tests pasaron.\n";
    exit(0);
} catch (Throwable $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
