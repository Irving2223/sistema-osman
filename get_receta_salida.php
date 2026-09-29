<?php
include("conexion.php");

if (isset($_GET['id_producto'])) {
    $id_producto = intval($_GET['id_producto']);
    
    $sql_producto = "SELECT * FROM productos WHERE id_producto = ?";
    $stmt_producto = $conexion->prepare($sql_producto);
    $stmt_producto->bind_param("i", $id_producto);
    $stmt_producto->execute();
    $producto = $stmt_producto->get_result()->fetch_assoc();
    
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
    
    if ($producto) {
        echo "
        <div class='alert alert-light border mb-3'>
            <strong>Stock actual de {$producto['nombre']}:</strong> 
            <span class='badge badge-primary badge-lg'>" . number_format($producto['cantidad_stock'], 2) . " {$producto['unidad_medida']}</span>
        </div>";
    }
    
    if (count($receta) > 0) {
        ?>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr>
                        <th>Materia Prima</th>
                        <th>Cantidad por Unidad</th>
                        <th>Cantidad a Utilizar</th>
                        <th>Unidad</th>
                        <th>Stock Disponible</th>
                        <th>Instrucciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($receta as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['materia_prima']) ?></td>
                        <td class="text-right"><?= number_format($item['cantidad_necesaria'], 2) ?></td>
                        <td class="text-right">
                            <strong class="cantidad-receta" data-cantidad-base="<?= $item['cantidad_necesaria'] ?>">
                                <?= number_format($item['cantidad_necesaria'], 2) ?>
                            </strong>
                        </td>
                        <td><?= htmlspecialchars($item['unidad_medida']) ?></td>
                        <td>
                            <span class="badge badge-<?= $item['stock_actual'] >= $item['cantidad_necesaria'] ? 'success' : 'danger' ?> stock-info"
                                  data-stock="<?= $item['stock_actual'] ?>"
                                  data-materia-nombre="<?= htmlspecialchars($item['materia_prima']) ?>">
                                <?= number_format($item['stock_actual'], 2) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($item['instrucciones']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    } else {
        echo '<div class="alert alert-warning">Este producto no tiene receta registrada. Use la sección de materiales adicionales para registrar el consumo.</div>';
    }
} else {
    echo '<div class="alert alert-danger">No se especificó producto.</div>';
}
?>