<?php
session_start();
if (empty($_SESSION["user"])) {
    echo "<script> alert('Su usuario no está logueado.. ¡Inicie Sesión!'); 
    window.location.href = 'index.php';
</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>U.P.Q.L - Pagina Principal</title>
        <link rel="icon" href="image/logo.png">
        <link href="css/datatables.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
        <script src="js/font-awesome.js"></script>
    </head>
    <body class="sb-nav-fixed" style="background-color:rgb(255, 255, 255);">
        <nav class="sb-topnav navbar navbar-expand navbar-dark" style="background-color: #FF8C42;">
            <!-- Navbar Brand-->
            <a class="navbar-brand ps-3" href="#">
                <img src="image/logo.png" alt="" style="wight: 50px; height: 45px; ">
            </a>
            <!-- Sidebar Toggle-->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar Search-->
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group">
                    
                </div>
            </form>
            <!-- Navbar-->
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end " aria-labelledby="navbarDropdown">
                       
                        <li><a class="dropdown-item text-dark" href="salir.php">Salir</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark text-dark" id="sidenavAccordion" style="background-color: #FF8C42;">
                    <div class="sb-sidenav-menu">
                    <div class="nav" style="color: #000000ff;"> 
                        <div class="sb-sidenav-menu-heading text-dark">Menu Inicio</div>
                            <a class="nav-link text-dark" href="inicio.php">
                                <div class="sb-nav-link-icon text-dark"><i class="fas fa-home"></i></div>
                                Inicio
                            </a>
                          
                            <div class="sb-sidenav-menu-heading text-dark">Principal</div>
                             <a class="nav-link text-dark" href="inventario.php">
                                <div class="sb-nav-link-icon text-dark"><i class="fa fa-industry" aria-hidden="true"></i></div>
                                Almacen
                            </a>
                           
                            <a class="nav-link text-dark" href="entregas.php">
                                <div class="sb-nav-link-icon text-dark"><i class="fa fa-flask" aria-hidden="true"></i></div>
                                Entrega
                            </a>
                            <a class="nav-link text-dark" href="salidas.php">
                                <div class="sb-nav-link-icon text-dark"><i class="fa fa-history" aria-hidden="true"></i></div>
                                Salidas 
                            </a>
                            <a class="nav-link text-dark" href="materias_primas.php">
                                <div class="sb-nav-link-icon text-dark"><i class="fa fa-flask" aria-hidden="true"></i></div>
                                Materia Prima
                            </a>
                            <a class="nav-link text-dark" href="proveedores.php">
                                <div class="sb-nav-link-icon text-dark"><i class="fa fa-truck" aria-hidden="true"></i></div>
                                Proveedores
                                <a class="nav-link text-dark" href="productos.php">
                                <div class="sb-nav-link-icon text-dark"><i class="fa fa-shopping-cart" aria-hidden="true"></i></div>
                                Productos
                            </a>
                            </a>
                            <a class="nav-link text-dark" href="usuarios.php">
                                <div class="sb-nav-link-icon text-dark"><i class="fa fa-users" aria-hidden="true"></i>
                                </div>
                                Usuarios
                            </a>

                            <?php
                                if($_SESSION["tipo"] == "admin"){ ?>
                            <?php
                             }
                             ?>
      
                        </div>
                    </div>
                    
                </nav>
            </div>
                        

        