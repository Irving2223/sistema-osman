<?php
include("conexion.php");

if (isset($_GET['id_producto'])) {
    $id_producto = intval($_GET['id_producto']);
    
    // Obtener información del producto
    $sql_producto = "SELECT * FROM productos WHERE id_producto = ?";
    $stmt_producto = $conexion->prepare($sql_producto);
    $stmt_producto->bind_param("i", $id_producto);
    $stmt_producto->execute();
    $producto = $stmt_producto->get_result()->fetch_assoc();
    
    if (!$producto) {
        echo '<div class="alert alert-danger">Producto no encontrado</div>';
        exit();
    }
    
    // Obtener receta del producto
    $sql_receta = "
        SELECT r.*, mp.nombre as materia_prima, mp.unidad_medida,
               COALESCE(i.cantidad_actual, 0) as stock_actual
        FROM recetas r
        INNER JOIN materias_primas mp ON r.id_materia_prima = mp.id_materia_prima
        LEFT JOIN inventario i ON mp.id_materia_prima = i.id_materia_prima
        WHERE r.id_producto = ?
        ORDER BY mp.nombre
    ";
    
    $stmt_receta = $conexion->prepare($sql_receta);
    $stmt_receta->bind_param("i", $id_producto);
    $stmt_receta->execute();
    $receta = $stmt_receta->get_result()->fetch_all(MYSQLI_ASSOC);
    ?>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card bg-light mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Información del Producto</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Producto:</strong> <?= htmlspecialchars($producto['nombre']) ?></p>
                            <p><strong>Unidad:</strong> <?= htmlspecialchars($producto['unidad_medida']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <?php if ($producto['precio_venta']): ?>
                                <p><strong>Precio:</strong> $<?= number_format($producto['precio_venta'], 2) ?></p>
                            <?php endif; ?>
                            <p><strong>Materiales en Receta:</strong> <?= count($receta) ?></p>
                        </div>
                        <div class="col-md-4">
                            <?php if (!empty($producto['descripcion'])): ?>
                                <p><strong>Descripción:</strong> <?= htmlspecialchars($producto['descripcion']) ?></p>
                            <?php endif; ?>
                        </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (count($receta) > 0): ?>
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-list-ul mr-2"></i>Receta - Materiales Necesarios</h6>
                <span class="badge badge-light"><?= count($receta) ?> materiales</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th class="align-middle" style="width: 25%;">Materia Prima</th>
                                <th class="text-center align-middle" style="width: 15%;">Cantidad Necesaria</th>
                                <th class="text-center align-middle" style="width: 10%;">Unidad</th>
                                <th class="text-center align-middle" style="width: 15%;">Stock Disponible</th>
                                <th class="align-middle" style="width: 25%;">Instrucciones</th>
                                <th class="text-center align-middle" style="width: 10%;">Estado Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_materiales = 0;
                            $materiales_suficientes = 0;
                            $materiales_insuficientes = 0;
                            
                            foreach ($receta as $item): 
                                $total_materiales++;
                                $suficiente = $item['stock_actual'] >= $item['cantidad_necesaria'];
                                
                                if ($suficiente) {
                                    $materiales_suficientes++;
                                } else {
                                    $materiales_insuficientes++;
                                }
                            ?>
                                <tr class="<?= $suficiente ? '' : 'table-warning' ?> align-middle">
                                    <td class="font-weight-bold">
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-<?= $suficiente ? 'success' : 'danger' ?> mr-2">
                                                <i class="fas fa-<?= $suficiente ? 'check-circle' : 'exclamation-circle' ?>"></i>
                                            </span>
                                            <?= htmlspecialchars($item['materia_prima']) ?>
                                        </div>
                                    </td>
                                    <td class="text-center font-weight-bold">
                                        <span class="badge badge-light text-dark" style="font-size: 0.9em;">
                                            <?= number_format($item['cantidad_necesaria'], 4) ?>
                                        </span>
                                    </td>
                                    <td class="text-center text-uppercase text-muted font-weight-bold" style="font-size: 0.8em;">
                                        <?= htmlspecialchars($item['unidad_medida']) ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-column">
                                            <span class="font-weight-bold"><?= number_format($item['stock_actual'], 2) ?></span>
                                            <?php if (!$suficiente): ?>
                                                <small class="text-danger">
                                                    Faltan: <?= number_format($item['cantidad_necesaria'] - $item['stock_actual'], 2) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($item['instrucciones'])): ?>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-info-circle text-primary mr-2"></i>
                                                <small><?= htmlspecialchars($item['instrucciones']) ?></small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted font-italic">Sin instrucciones</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($suficiente): ?>
                                            <span class="badge badge-success badge-pill px-3 py-2">
                                                <i class="fas fa-check-circle mr-1"></i> OK
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger badge-pill px-3 py-2">
                                                <i class="fas fa-exclamation-triangle mr-1"></i> FALTA
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <td colspan="6" class="p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-muted">Total materiales:</span>
                                            <span class="badge badge-secondary ml-1"><?= $total_materiales ?></span>
                                        </div>
                                        <div>
                                            <span class="text-success">
                                                <i class="fas fa-check-circle"></i> <?= $materiales_suficientes ?> suficientes
                                            </span>
                                            <?php if ($materiales_insuficientes > 0): ?>
                                                <span class="text-danger ml-3">
                                                    <i class="fas fa-exclamation-circle"></i> <?= $materiales_insuficientes ?> faltantes
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Información adicional -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white py-2">
                                <h6 class="mb-0"><i class="fas fa-calculator mr-2"></i>Cálculo de Producción</h6>
                            </div>
                            <div class="card-body p-3">
                                <p class="mb-2">Para producir <span class="badge badge-primary">1 <?= $producto['unidad_medida'] ?></span> de <strong><?= htmlspecialchars($producto['nombre']) ?></strong> se necesitan:</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tbody>
                                            <?php foreach ($receta as $item): ?>
                                                <tr>
                                                    <td class="border-0 py-1" style="width: 40px;">
                                                        <span class="badge badge-light text-dark"><?= number_format($item['cantidad_necesaria'], 2) ?> <?= $item['unidad_medida'] ?></span>
                                                    </td>
                                                    <td class="border-0 py-1">
                                                        <small class="text-muted"><?= htmlspecialchars($item['materia_prima']) ?></small>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?php if ($materiales_insuficientes > 0): ?>
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark py-2">
                                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle mr-2"></i>Alertas de Stock</h6>
                                </div>
                                <div class="card-body p-3">
                                    <p class="mb-2">No hay stock suficiente para producir este producto. Materiales faltantes:</p>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($receta as $item): ?>
                                            <?php if ($item['stock_actual'] < $item['cantidad_necesaria']): 
                                                $faltante = $item['cantidad_necesaria'] - $item['stock_actual'];
                                                $porcentaje = ($item['stock_actual'] / $item['cantidad_necesaria']) * 100;
                                            ?>
                                                <div class="list-group-item list-group-item-action p-2 border-left-0 border-right-0">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong><?= htmlspecialchars($item['materia_prima']) ?></strong>
                                                            <div class="progress mt-1" style="height: 5px;">
                                                                <div class="progress-bar bg-danger" role="progressbar" 
                                                                     style="width: <?= $porcentaje > 100 ? 100 : $porcentaje ?>%;" 
                                                                     aria-valuenow="<?= $porcentaje ?>" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="text-right">
                                                            <span class="badge badge-danger">
                                                                Faltan: <?= number_format($faltante, 2) ?> <?= $item['unidad_medida'] ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card border-success">
                                <div class="card-header bg-success text-white py-2">
                                    <h6 class="mb-0"><i class="fas fa-check-circle mr-2"></i>Stock Disponible</h6>
                                </div>
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="mb-3">¡Stock suficiente!</h5>
                                    <p class="mb-4">Puedes proceder con la producción de <strong><?= htmlspecialchars($producto['nombre']) ?></strong>.</p>
                                    <button class="btn btn-success btn-lg px-4" onclick="producirDesdeModal(<?= $id_producto ?>)">
                                        <i class="fas fa-cogs mr-2"></i> Iniciar Producción
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        function producirDesdeModal(idProducto) {
            $('#modalReceta').modal('hide');
            setTimeout(function() {
                window.location.href = 'registro_salidas.php?id_producto=' + idProducto;
            }, 500);
        }
        </script>
        
    <?php else: ?>
        <div class="alert alert-warning">
            <h6><i class="fas fa-exclamation-triangle"></i> Receta No Configurada</h6>
            <p class="mb-0">Este producto no tiene una receta registrada. Debe configurar los materiales necesarios para poder producirlo.</p>
            <a href="editar_producto.php?id_producto=<?= $id_producto ?>" class="btn btn-primary btn-sm mt-2">
                <i class="fas fa-edit"></i> Configurar Receta
            </a>
        </div>
    <?php endif; ?>
    
    <?php
} else {
    echo '<div class="alert alert-danger">No se especificó ID de producto</div>';
}

$conexion->close();
?>