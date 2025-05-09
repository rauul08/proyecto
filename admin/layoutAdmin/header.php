<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>JIREH Foods | Admin</title>
        <link rel="icon" type="images/jpeg"  href="../images/jLeft.png" />
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
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
            <!-- Navbar Brand-->
            <a class="navbar-brand ps-3" href="../phpAdmin/inicio.php">JIREH FOODS | Admin</a>
            <!-- Sidebar Toggle-->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar Search-->
          
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div id="js" class="nav">
                            <div class="sb-sidenav-menu-heading" style="color:black;" >Core</div>
                            <a class="nav-link"href="pedidos.php">
                                <div class="sb-nav-link-icon" style="color:#8B4513;"><i class="fas fa-list"></i></div>
                                Pedidos
                            </a>
                            <a class="nav-link" href="usuarios.php">
                                <div class="sb-nav-link-icon" style="color:#8B4513;"><i class="fas fa-users"></i></div>
                                Clientes
                            </a>
                            <a class="nav-link" href="productos.php">
                                <div class="sb-nav-link-icon" style="color:#8B4513;"><i class="fa-solid fa-burger"></i></div>
                                Productos
                            </a>
                            <a class="nav-link" href="buzon.php">
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
                                    <a class="nav-link" href="../charts/graficoCompras.php">Estadisticas de Compradores</a>
                                    <a class="nav-link" href="../charts/graficoPedidos.php">Estadisticas de Pedidos</a>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer" style="background-color:#FFE1DE;">
                        <div class="small" style="color:black;">Conectado como: <br>
                        Administrador </div>
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">