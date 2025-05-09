<?php
require '../config/database.php';

// Crear una instancia de la clase Database
$db = new Database();

// Conectar a la base de datos
$conexion = $db->conectar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>JIREH Foods | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="../css/styles.css" rel="stylesheet" />
    <link rel="icon" type="images/jpeg"  href="../../images/jLeft.png" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart', 'table', 'controls']});
        google.charts.setOnLoadCallback(drawDashboard);

        function drawDashboard() {
            var data = google.visualization.arrayToDataTable([
                ['Cliente', 'Pedidos'],
                <?php 
                // Preparar y ejecutar la consulta
                $sql = "SELECT nombres, registro_pedidos FROM clientes";
                $stmt = $conexion->query($sql);

                // Obtener los resultados y generar los datos para la gráfica
                while ($resultado = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "['" . $resultado['nombres'] . "', " . $resultado['registro_pedidos'] . "],";
                }
                ?>
            ]);

            var dashboard = new google.visualization.Dashboard(document.getElementById('dashboard_div'));

            // Definir el control de filtro
            var filter = new google.visualization.ControlWrapper({
                'controlType': 'CategoryFilter',
                'containerId': 'filter_div',
                'options': {
                    'filterColumnLabel': 'Cliente',
                    'ui': {
                        'allowTyping': false,
                        'allowMultiple': true,
                        'labelStacking': 'vertical'
                    }
                }
            });

            // Definir la tabla
            var table = new google.visualization.ChartWrapper({
                'chartType': 'Table',
                'containerId': 'table_div',
                'options': {
                    'width': '100%',
                    'height': '100%'
                }
            });

            // Definir el gráfico de barras
            var chart = new google.visualization.ChartWrapper({
                'chartType': 'BarChart',
                'containerId': 'chart_div',
                'options': {
                    'title': 'Pedidos por Cliente',
                    'width': '100%',
                    'height': 500,
                    'legend': { position: 'none' },
                    'bars': 'horizontal'
                }
            });

            // Conectar el control al gráfico y la tabla
            dashboard.bind(filter, [table, chart]);
            dashboard.draw(data);
        }
    </script>
    <style>
        .sb-sidenav-menu {
            background-color:#FFE1DE;
        }
        #js a {
            color:#8B4513;
        }
        #js a:hover {
            text-decoration: underline;
        }
        #js a:active {
            background-color: #FFA500;
        }
        #js.sb-nav-link-icon {
            color:black;
        }
    </style>
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark" style="background-color: #8B4513;">
        <a class="navbar-brand ps-3" href="../phpAdmin/inicio.php">JIREH FOODS | Admin</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div id="js" class="nav">
                        <div class="sb-sidenav-menu-heading" style="color:black;">Core</div>
                        <a class="nav-link" href="../phpAdmin/pedidos.php">
                            <div class="sb-nav-link-icon" style="color:#8B4513;"><i class="fas fa-list"></i></div>
                            Pedidos
                        </a>
                        <a class="nav-link" href="../phpAdmin/usuarios.php">
                            <div class="sb-nav-link-icon" style="color:#8B4513;"><i class="fas fa-users"></i></div>
                            Clientes
                        </a>
                        <a class="nav-link" href="../phpAdmin/productos.php">
                            <div class="sb-nav-link-icon" style="color:#8B4513;"><i class="fa-solid fa-burger"></i></div>
                            Productos
                        </a>
                        <a class="nav-link" href="../phpAdmin/buzon.php">
                            <div class="sb-nav-link-icon" style="color:#8B4513;"><i class="fa-solid fa-comment"></i></div>
                            Buzon
                        </a>
                        <div class="sb-sidenav-menu-heading" style="color:black;">Interface</div>
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon" style="color:#8B4513;"><i class="fas fa-columns"></i></div>
                            Reportes
                            <div class="sb-sidenav-collapse-arrow" style="color:#8B4513;"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="graficoCompras.php">Estadisticas de Compradores</a>
                                <a class="nav-link" href="graficoPedidos.php">Estadisticas de Pedidos</a>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="sb-sidenav-footer" style="background-color:#FFE1DE;">
                    <div class="small" style="color:black;">Conectado como: <br> Administrador </div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <div id="dashboard_div" style="width: 100%; height: 500px;">
                <div id="filter_div" style="padding: 10px;"></div>
                <div id="chart_div"></div>
                <div id="table_div"></div>
            </div>
            <?php include '../layoutAdmin/footer.php'; ?>
        </div>
    </div>
</body>
</html>
