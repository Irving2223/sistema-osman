<?php
include("conexion.php");

if (isset($_GET['id_entrega'])) {
    $id_entrega = filter_var($_GET['id_entrega'], FILTER_VALIDATE_INT);
    
    if ($id_entrega) {
        // Consulta para los datos generales de la entrega
        $sql_entrega = "
            SELECT e.*, p.nombre as proveedor, p.telefono, p.correo
            FROM entregas e 
            INNER JOIN proveedores p ON e.id_proveedor = p.id_proveedor 
            WHERE e.id_entrega = ?
        ";
        
        $stmt_entrega = $conexion->prepare($sql_entrega);
        $stmt_entrega->bind_param("i", $id_entrega);
        $stmt_entrega->execute();
        $result_entrega = $stmt_entrega->get_result();
        $entrega = $result_entrega->fetch_assoc();
        
        // Consulta para los detalles de la entrega
        $sql_detalles = "
            SELECT de.*, mp.nombre as materia_prima, mp.unidad_medida
            FROM detalle_entregas de
            INNER JOIN materias_primas mp ON de.id_materia_prima = mp.id_materia_prima
            WHERE de.id_entrega = ?
        ";
        
        $stmt_detalles = $conexion->prepare($sql_detalles);
        $stmt_detalles->bind_param("i", $id_entrega);
        $stmt_detalles->execute();
        $result_detalles = $stmt_detalles->get_result();
        ?>
        
        <div class="row">
            <div class="col-md-6">
                <h6>Información de la Entrega</h6>
              
                <p><strong>Proveedor:</strong> <?= htmlspecialchars($entrega['proveedor']) ?></p>
                <p><strong>Fecha:</strong> <?= $entrega['fecha_entrega'] ?></p>
                <p><strong>N° Factura:</strong> <?= htmlspecialchars($entrega['numero_factura']) ?></p>
            </div>
            <div class="col-md-6">
                <h6>Contacto Proveedor</h6>
                <p><strong>Teléfono:</strong> <?= htmlspecialchars($entrega['telefono']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($entrega['correo']) ?></p>
            </div>
        </div>
        
        <hr>
        
        <h6>Materiales Entregados</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Materia Prima</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_cantidad = 0;
                    while ($detalle = $result_detalles->fetch_assoc()) {
                        $total_cantidad += $detalle['cantidad'];
                        echo '
                        <tr>
                            <td>' . htmlspecialchars($detalle['materia_prima']) . '</td>
                            <td>' . $detalle['cantidad'] . '</td>
                            <td>' . htmlspecialchars($detalle['unidad_medida']) . '</td>
                        </tr>';
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr class="table-info">
                        <td><strong>Total</strong></td>
                        <td><strong><?= number_format($total_cantidad, 2) ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php
    }
}
?>