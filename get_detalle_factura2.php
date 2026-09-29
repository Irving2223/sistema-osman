<?php
include("conexion.php");

if (isset($_GET['numero_factura'])) {
    $numero_factura = mysqli_real_escape_string($conexion, $_GET['numero_factura']);
    
    // Consulta para los datos generales de la factura
    $sql_factura = "
        SELECT 
            e.numero_factura,
            p.nombre as proveedor,
            p.telefono,
            p.correo,
           
            MIN(e.fecha_entrega) as fecha_entrega,
            COUNT(DISTINCT e.id_entrega) as total_entregas,
            COUNT(de.id_detalle_entrega) as total_items,
            SUM(de.cantidad) as cantidad_total
        FROM entregas e
        INNER JOIN proveedores p ON e.id_proveedor = p.id_proveedor
        INNER JOIN detalle_entregas de ON e.id_entrega = de.id_entrega
        WHERE e.numero_factura = '$numero_factura'
        GROUP BY e.numero_factura, p.nombre, p.telefono, p.correo
    ";
    
    $result_factura = mysqli_query($conexion, $sql_factura);
    $factura = $result_factura->fetch_assoc();
    
    // Consulta para los detalles de todas las entregas de esta factura
    $sql_detalles = "
        SELECT 
            e.id_entrega,
            e.fecha_entrega,
            mp.nombre as materia_prima,
            mp.unidad_medida,
            de.cantidad
            
        FROM entregas e
        INNER JOIN detalle_entregas de ON e.id_entrega = de.id_entrega
        INNER JOIN materias_primas mp ON de.id_materia_prima = mp.id_materia_prima
        WHERE e.numero_factura = '$numero_factura'
        ORDER BY e.id_entrega, mp.nombre
    ";
    
    $result_detalles = mysqli_query($conexion, $sql_detalles);
    ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">Información de la Factura</h6>
                </div>
                <div class="card-body">
                    <p><strong>N° Factura:</strong> <?= htmlspecialchars($factura['numero_factura']) ?></p>
                    <p><strong>Proveedor:</strong> <?= htmlspecialchars($factura['proveedor']) ?></p>
                   
                    <p><strong>Fecha de Entrega:</strong> <?= $factura['fecha_entrega'] ?></p>
                    
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Contacto del Proveedor</h6>
                </div>
                <div class="card-body">
                    <p><strong>Teléfono:</strong> <?= htmlspecialchars($factura['telefono']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($factura['correo']) ?></p>
                    
                </div>
            </div>
        </div>
    </div>
    
    <hr>
    
   
    
    <hr>
    
    <h6>Detalles de Materiales</h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th>Materia Prima</th>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php
                $entrega_actual = null;
                $total_general = 0;
                
                while ($detalle = $result_detalles->fetch_assoc()) {
                    
                    
                    // Mostrar fila de agrupación por entrega
                    if ($entrega_actual != $detalle['id_entrega']) {
                        if ($entrega_actual !== null) {
                            echo '<tr class="table-active"><td colspan="6"></td></tr>';
                        }
                        $entrega_actual = $detalle['id_entrega'];
                    }
                    
                    echo '
                    <tr>
                        
                        
                        <td>' . htmlspecialchars($detalle['materia_prima']) . '</td>
                        <td class="text-right">' . number_format($detalle['cantidad'], 2) . '</td>
                        <td>' . htmlspecialchars($detalle['unidad_medida']) . '</td>
                       
                    </tr>';
                }
                ?>
            </tbody>
            
        </table>
    </div>
    <?php
}
?>