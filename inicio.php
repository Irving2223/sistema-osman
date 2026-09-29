<?php
include("header.php");
include("conexion.php");

// Obtener estadísticas para el dashboard
$sql_estadisticas = "
    -- Total materias primas
    SELECT COUNT(*) as total_materias FROM materias_primas;
    
    -- Materias primas con stock bajo (menos de 10)
    SELECT COUNT(*) as stock_bajo FROM inventario WHERE cantidad_actual < 10;
    
    -- Total de entregas este mes
    SELECT COUNT(*) as entregas_mes FROM entregas WHERE MONTH(fecha_entrega) = MONTH(CURRENT_DATE()) AND YEAR(fecha_entrega) = YEAR(CURRENT_DATE());
    
    -- Total de salidas este mes
    SELECT COUNT(*) as salidas_mes FROM salidas WHERE MONTH(fecha_salida) = MONTH(CURRENT_DATE()) AND YEAR(fecha_salida) = YEAR(CURRENT_DATE());
    
    -- Productos terminados con stock
    SELECT COUNT(*) as productos_con_stock FROM productos WHERE activo = 1 AND cantidad_stock > 0;
    
    -- Últimas 5 entregas
    SELECT e.id_entrega, e.fecha_entrega, p.nombre as proveedor, e.numero_factura 
    FROM entregas e 
    INNER JOIN proveedores p ON e.id_proveedor = p.id_proveedor 
    ORDER BY e.fecha_entrega DESC 
    LIMIT 5;
    
    -- Últimas 5 salidas
    SELECT id_salida, producto, fecha_salida 
    FROM salidas 
    ORDER BY fecha_salida DESC 
    LIMIT 5;
    
    -- Materias primas con stock más bajo
    SELECT mp.nombre, i.cantidad_actual, mp.unidad_medida 
    FROM inventario i 
    INNER JOIN materias_primas mp ON i.id_materia_prima = mp.id_materia_prima 
    ORDER BY i.cantidad_actual ASC 
    LIMIT 5;
";

// Ejecutar consultas múltiples
if (mysqli_multi_query($conexion, $sql_estadisticas)) {
    do {
        if ($result = mysqli_store_result($conexion)) {
            if (!isset($total_materias)) {
                $total_materias = mysqli_fetch_assoc($result)['total_materias'];
            } else if (!isset($stock_bajo)) {
                $stock_bajo = mysqli_fetch_assoc($result)['stock_bajo'];
            } else if (!isset($entregas_mes)) {
                $entregas_mes = mysqli_fetch_assoc($result)['entregas_mes'];
            } else if (!isset($salidas_mes)) {
                $salidas_mes = mysqli_fetch_assoc($result)['salidas_mes'];
            } else if (!isset($productos_con_stock)) {
                $productos_con_stock = mysqli_fetch_assoc($result)['productos_con_stock'];
            } else if (!isset($ultimas_entregas)) {
                $ultimas_entregas = mysqli_fetch_all($result, MYSQLI_ASSOC);
            } else if (!isset($ultimas_salidas)) {
                $ultimas_salidas = mysqli_fetch_all($result, MYSQLI_ASSOC);
            } else if (!isset($stock_bajo_lista)) {
                $stock_bajo_lista = mysqli_fetch_all($result, MYSQLI_ASSOC);
            }
            mysqli_free_result($result);
        }
    } while (mysqli_next_result($conexion));
}
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <h1 class="mt-4">Pagina de Inicio</h1>
            
            <!-- Tarjetas de Métricas Principales -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">
                                        Total Materias Primas
                                    </div>
                                    <div class="h5 mb-0"><?= $total_materias ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-boxes fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="inventario.php">
                                Ver Inventario
                            </a>
                            <div class="small text-white">
                                <i class="fas fa-angle-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">
                                        Stock Bajo
                                    </div>
                                    <div class="h5 mb-0"><?= $stock_bajo ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="inventario.php">
                                Revisar Stock
                            </a>
                            <div class="small text-white">
                                <i class="fas fa-angle-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">
                                        Entregas Este Mes
                                    </div>
                                    <div class="h5 mb-0"><?= $entregas_mes ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-truck-loading fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="entregas.php">
                                Ver Entregas
                            </a>
                            <div class="small text-white">
                                <i class="fas fa-angle-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-info text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">
                                        Salidas Este Mes
                                    </div>
                                    <div class="h5 mb-0"><?= $salidas_mes ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-shipping-fast fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="salidas.php">
                                Ver Salidas
                            </a>
                            <div class="small text-white">
                                <i class="fas fa-angle-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de stock de productos terminados -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-dark text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">
                                        Productos Terminados en Stock
                                    </div>
                                    <div class="h5 mb-0"><?= $productos_con_stock ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-box-open fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="productos.php">
                                Ver Productos
                            </a>
                            <div class="small text-white">
                                <i class="fas fa-angle-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segunda fila de contenido -->
            <div class="row">
                <!-- Últimas Entregas -->
                <div class="col-xl-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-truck mr-1"></i>
                            Últimas Entregas
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Fecha</th>
                                            <th>Proveedor</th>
                                            <th>Factura</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ultimas_entregas as $entrega): ?>
                                        <tr>
                                            <td>#<?= $entrega['id_entrega'] ?></td>
                                            <td><?= date('d/m/Y', strtotime($entrega['fecha_entrega'])) ?></td>
                                            <td><?= htmlspecialchars($entrega['proveedor']) ?></td>
                                            <td><?= htmlspecialchars($entrega['numero_factura']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer small text-muted">
                            <a href="entregas.php">Ver todas las entregas</a>
                        </div>
                    </div>
                </div>

                <!-- Últimas Salidas -->
                <div class="col-xl-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-shipping-fast mr-1"></i>
                            Últimas Salidas
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Fecha</th>
                                            <th>Producto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ultimas_salidas as $salida): ?>
                                        <tr>
                                            <td>#<?= $salida['id_salida'] ?></td>
                                            <td><?= date('d/m/Y', strtotime($salida['fecha_salida'])) ?></td>
                                            <td><?= htmlspecialchars($salida['producto']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer small text-muted">
                            <a href="salidas.php">Ver todas las salidas</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tercera fila: Alertas de Stock -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Alertas de Stock Bajo
                        </div>
                        <div class="card-body">
                            <?php if (count($stock_bajo_lista) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Materia Prima</th>
                                                <th>Stock Actual</th>
                                                <th>Unidad</th>
                                                <th>Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stock_bajo_lista as $materia): ?>
                                            <tr class="<?= $materia['cantidad_actual'] <= 0 ? 'table-danger' : 'table-warning' ?>">
                                                <td><?= htmlspecialchars($materia['nombre']) ?></td>
                                                <td class="font-weight-bold"><?= number_format($materia['cantidad_actual'], 2) ?></td>
                                                <td><?= $materia['unidad_medida'] ?></td>
                                                <td>
                                                    <a href="añadir_entrega.php" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-plus"></i> Solicitar
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> 
                                    ¡Excelente! No hay materiales con stock bajo.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-rocket mr-1"></i>
                            Acciones Rápidas
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3 mb-3">
                                    <a href="añadir_entrega.php" class="btn btn-success btn-lg btn-block">
                                        <i class="fas fa-truck-loading fa-2x mb-2"></i><br>
                                        Nueva Entrega
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="registro_salidas.php" class="btn btn-warning btn-lg btn-block">
                                        <i class="fa fa-history" aria-hidden="true"></i><br>
                                        Nueva Salida
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="inventario.php" class="btn btn-info btn-lg btn-block">
                                        <i class="fas fa-boxes fa-2x mb-2"></i><br>
                                        Ver Inventario
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="generar_pdf_inventario.php" class="btn btn-primary btn-lg btn-block" target="_blank">
                                        <i class="fas fa-chart-bar fa-2x mb-2"></i><br>
                                        Ver Reportes
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>


<?php include("footer.php"); ?>