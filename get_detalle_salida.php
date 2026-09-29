<?php
include("conexion.php");

if (isset($_GET['id_salida'])) {
    $id_salida = intval($_GET['id_salida']);
    
    // Obtener información de la salida
    $sql_salida = "SELECT * FROM salidas WHERE id_salida = ?";
    $stmt_salida = $conexion->prepare($sql_salida);
    $stmt_salida->bind_param("i", $id_salida);
    $stmt_salida->execute();
    $salida = $stmt_salida->get_result()->fetch_assoc();
    
    // Obtener detalles de la salida
    $sql_detalles = "
        SELECT ds.*, mp.nombre as materia_prima, mp.unidad_medida 
        FROM detalle_salidas ds 
        INNER JOIN materias_primas mp ON ds.id_materia_prima = mp.id_materia_prima 
        WHERE ds.id_salida = ?
    ";
    
    $stmt_detalles = $conexion->prepare($sql_detalles);
    $stmt_detalles->bind_param("i", $id_salida);
    $stmt_detalles->execute();
    $detalles = $stmt_detalles->get_result()->fetch_all(MYSQLI_ASSOC);
    ?>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card bg-light mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Información de la Salida</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>ID Salida:</strong> #<?= $salida['id_salida'] ?></p>
                            <p><strong>Producto:</strong> <?= htmlspecialchars($salida['producto']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Fecha de Salida:</strong> <?= $salida['fecha_salida'] ?></p>
                            <?php if ($salida['cantidad_producto']): ?>
                                <p><strong>Cantidad Producida:</strong> <?= number_format($salida['cantidad_producto'], 2) ?> unidades</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <?php if (!empty($salida['observaciones'])): ?>
                                <p><strong>Observaciones:</strong> <?= htmlspecialchars($salida['observaciones']) ?></p>
                            <?php endif; ?>
                            <p><strong>Materiales Utilizados:</strong> <?= count($detalles) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-warning text-white">
            <h6 class="mb-0"><i class="fas fa-boxes"></i> Materiales Utilizados</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Materia Prima</th>
                            <th>Cantidad Utilizada</th>
                            <th>Unidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($detalles) > 0): ?>
                            <?php 
                            $total_cantidad = 0;
                            foreach ($detalles as $detalle): 
                                $total_cantidad += $detalle['cantidad_utilizada'];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($detalle['materia_prima']) ?></td>
                                    <td class="text-right text-danger font-weight-bold"><?= number_format($detalle['cantidad_utilizada'], 2) ?></td>
                                    <td><?= htmlspecialchars($detalle['unidad_medida']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-danger">
                                <td><strong>Total Utilizado</strong></td>
                                <td class="text-right"><strong><?= number_format($total_cantidad, 2) ?></strong></td>
                                <td></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">No hay materiales registrados</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
} else {
    echo '<div class="alert alert-danger">No se especificó ID de salida</div>';
}
?>