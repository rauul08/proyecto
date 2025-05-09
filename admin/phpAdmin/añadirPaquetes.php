<?php
require '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $descuento = $_POST['descuento'];

    if (!empty($nombre) && !empty($descripcion) && !empty($precio) && !empty($descuento)) {
        $db = new Database();
        $con = $db->conectar();

        $sql = $con->prepare("INSERT INTO paquetes (nombre, descripcion, precio, descuento, activo) VALUES (?, ?, ?, ?, 1)");
        $sql->execute([$nombre, $descripcion, $precio, $descuento]);

        if ($sql) {
            $mensaje = "Paquete añadido exitosamente";
        } else {
            $mensaje = "Error al añadir el paquete";
        }
    } else {
        $mensaje = "Todos los campos son obligatorios";
    }
}
?>

<?php include_once '../layoutAdmin/header.php'; ?>
<span>¨</span>
<ul class="nav nav-tabs">
  <li class="nav-item">
    <a class="nav-link" href="productos.php">Productos</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="paquetes.php">Paquetes</a>
  </li>
  <li class="nav-item">
    <a class="nav-link active" aria-current="page" href="añadirProductos.php">Añadir Paquete</a>
  </li>
</ul>

<div class="card">
    <div class="card-body">
        <?php if (isset($mensaje)) { ?>
            <div class="alert alert-info"><?php echo $mensaje; ?></div>
        <?php } ?>
        <form method="POST" action="añadirPaquetes.php" autocomplete="off">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del Producto</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" required></textarea>
            </div>
            <div class="mb-3">
                <label for="precio" class="form-label">Precio</label>
                <input type="number" step="0.01" class="form-control" id="precio" name="precio" required>
            </div>
            <div class="mb-3">
                <label for="descuento" class="form-label">Descuento</label>
                <input type="number" step="0.01" class="form-control" id="descuento" name="descuento">
            </div>
            <button type="submit" class="btn btn-success">Registrar</button>
            <button type="reset" class="btn btn-danger">Cancelar</button>
        </form>
    </div>
</div>

<?php include_once '../layoutAdmin/footer.php'; ?>
