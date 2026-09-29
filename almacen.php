<?php
include("header.php");
include("conexion.php");

// Obtener lista de materias primas para el filtro
$sql_materias = "SELECT * FROM materias_primas ORDER BY nombre";
$result_materias = mysqli_query($conexion, $sql_materias);

// Obtener la materia prima seleccionada (si hay)
$materia_seleccionada = isset($_GET['id_materia_prima']) ? intval($_GET['id_materia_prima']) : 0;
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <h1 class="mt-4">Movimientos de Inventario</h1>
            
            <!-- Filtro por materia prima -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter mr-1"></i>
                    Filtros de Búsqueda
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="form-inline">
                        <div class="form-group mr-3">
                            <label for="id_materia_prima" class="mr-2">Materia Prima:</label>
                            <select class="form-control" id="id_materia_prima" name="id_materia_prima" onchange="this.form.submit()">
                                <option value="0">Todas las materias primas</option>
                                <?php
                                if ($result_materias->num_rows > 0) {
                                    while($materia = $result_materias->fetch_assoc()) {
                                        $selected = ($materia_seleccionada == $materia['id_materia_prima']) ? 'selected' : '';
                                        echo '<option value="' . $materia['id_materia_prima'] . '" ' . $selected . '>' 
                                             . htmlspecialchars($materia['nombre']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <?php if ($materia_seleccionada > 0): ?>
                            <a href="movimientos_inventario.php" class="btn btn-secondary ml-2">Ver Todas</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Resumen del inventario actual -->
            <div class="row mb-4">
                <?php
                // Consulta para el resumen de inventario
                $sql_resumen = "SELECT mp.id_materia_prima, mp.nombre, mp.unidad_medida, 
                                       COALESCE(i.cantidad_actual, 0) as stock_actual,
                                       i.fecha_actualizacion
                                FROM materias_primas mp
                                LEFT JOIN inventario i ON mp.id_materia_prima = i.id_materia_prima";
                
                if ($materia_seleccionada > 0) {
                    $sql_resumen .= " WHERE mp.id_materia_prima = $materia_seleccionada";
                }
                
                $sql_resumen .= " ORDER BY mp.nombre";
                
                $result_resumen = mysqli_query($conexion, $sql_resumen);
                
                while ($materia = $result_resumen->fetch_assoc()) {
                    $clase_stock = ($materia['stock_actual'] <= 0) ? 'bg-danger' : (($materia['stock_actual'] < 10) ? 'bg-warning' : 'bg-success');
                    echo '
                    <div class="col-xl-3 col-md-6">
                        <div class="card ' . $clase_stock . ' text-white mb-4">
                            <div class="card-body">
                                <h6 class="card-title">' . htmlspecialchars($materia['nombre']) . '</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="mb-0">' . number_format($materia['stock_actual'], 2) . '</h4>
                                        <small>' . $materia['unidad_medida'] . '</small>
                                    </div>
                                    <i class="fas fa-boxes fa-2x"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <small>Última actualización: ' . ($materia['fecha_actualizacion'] ? date('d/m/Y H:i', strtotime($materia['fecha_actualizacion'])) : 'Nunca') . '</small>
                            </div>
                        </div>
                    </div>';
                }
                ?>
            </div>

            <!-- Tabla de movimientos -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table mr-1"></i>
                    Historial de Movimientos
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Materia Prima</th>
                                    <th>Documento</th>
                                    <th>Cantidad</th>
                                    <th>Stock Posterior</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Consulta para unir entradas y salidas en un solo historial
                                $sql_movimientos = "
                                    -- ENTRADAS (desde entregas)
                                    SELECT 
                                        e.fecha_entrega as fecha,
                                        'ENTRADA' as tipo,
                                        mp.nombre as materia_prima,
                                        mp.unidad_medida,
                                        CONCAT('Entrega #', e.id_entrega) as documento,
                                        de.cantidad as cantidad,
                                        NULL as stock_posterior,  -- Esto lo calcularemos después
                                        e.id_entrega as id_referencia,
                                        'entrega' as tabla_origen
                                    FROM detalle_entregas de
                                    INNER JOIN entregas e ON de.id_entrega = e.id_entrega
                                    INNER JOIN materias_primas mp ON de.id_materia_prima = mp.id_materia_prima
                                    
                                    UNION ALL
                                    
                                    -- SALIDAS (desde salidas)
                                    SELECT 
                                        s.fecha_salida as fecha,
                                        'SALIDA' as tipo,
                                        mp.nombre as materia_prima,
                                        mp.unidad_medida,
                                        CONCAT('Salida #', s.id_salida, ' - ', s.producto) as documento,
                                        ds.cantidad as cantidad,
                                        NULL as stock_posterior,
                                        s.id_salida as id_referencia,
                                        'salida' as tabla_origen
                                    FROM detalle_salidas ds
                                    INNER JOIN salidas s ON ds.id_salida = s.id_salida
                                    INNER JOIN materias_primas mp ON ds.id_materia_prima = mp.id_materia_prima
                                    
                                    ORDER BY fecha DESC, id_referencia DESC
                                ";
                                
                                $result_movimientos = mysqli_query($conexion, $sql_movimientos);
                                
                                if ($result_movimientos && $result_movimientos->num_rows > 0) {
                                    // Primero, necesitamos calcular el stock posterior para cada movimiento
                                    $movimientos = [];
                                    $stock_actual_por_materia = [];
                                    
                                    // Obtener stock actual por materia prima
                                    $sql_stock_actual = "SELECT id_materia_prima, cantidad_actual FROM inventario";
                                    $result_stock = mysqli_query($conexion, $sql_stock_actual);
                                    while ($stock = $result_stock->fetch_assoc()) {
                                        $stock_actual_por_materia[$stock['id_materia_prima']] = $stock['cantidad_actual'];
                                    }
                                    
                                    // Recolectar todos los movimientos
                                    while ($movimiento = $result_movimientos->fetch_assoc()) {
                                        $movimientos[] = $movimiento;
                                    }
                                    
                                    // Calcular stock posterior (empezando desde el más reciente)
                                    for ($i = count($movimientos) - 1; $i >= 0; $i--) {
                                        // Para simplificar, mostramos el stock actual como referencia
                                        // En una implementación real, necesitarías un campo de stock acumulado en la BD
                                        $movimientos[$i]['stock_posterior'] = 'N/A'; // Esto sería calculado
                                    }
                                    
                                    // Mostrar movimientos
                                    foreach ($movimientos as $mov) {
                                        $badge_class = ($mov['tipo'] == 'ENTRADA') ? 'badge-success' : 'badge-danger';
                                        $icon = ($mov['tipo'] == 'ENTRADA') ? 'fa-arrow-down' : 'fa-arrow-up';
                                        
                                        echo '
                                        <tr>
                                            <td>' . $mov['fecha'] . '</td>
                                            <td><span class="badge ' . $badge_class . '"><i class="fas ' . $icon . '"></i> ' . $mov['tipo'] . '</span></td>
                                            <td>' . htmlspecialchars($mov['materia_prima']) . '</td>
                                            <td>' . htmlspecialchars($mov['documento']) . '</td>
                                            <td class="text-right ' . (($mov['tipo'] == 'SALIDA') ? 'text-danger' : 'text-success') . '">
                                                ' . (($mov['tipo'] == 'SALIDA') ? '-' : '+') . number_format($mov['cantidad'], 2) . '
                                            </td>
                                            <td class="text-right">' . $mov['stock_posterior'] . '</td>
                                            <td class="text-center">
                                                <button class="btn btn-info btn-sm" onclick="verDetalleMovimiento(\'' . $mov['tabla_origen'] . '\', ' . $mov['id_referencia'] . ')">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </td>
                                        </tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para ver detalles del movimiento -->
        <div class="modal fade" id="modalDetalleMovimiento" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tituloMovimiento">Detalles del Movimiento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="detalleMovimientoContent">
                        <!-- Contenido cargado por AJAX -->
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
        
        <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                "order": [[0, 'desc']], // Ordenar por fecha descendente
                "columnDefs": [
                    { "orderable": false, "targets": [6] } // Deshabilitar ordenamiento en columna Acciones
                ]
            });
        });

        function verDetalleMovimiento(tipo, id) {
            let titulo = '';
            let url = '';
            
            if (tipo === 'entrega') {
                titulo = 'Detalles de Entrega #' + id;
                url = 'get_detalle_entrega.php?id_entrega=' + id;
            } else if (tipo === 'salida') {
                titulo = 'Detalles de Salida #' + id;
                url = 'get_detalle_salida.php?id_salida=' + id;
            }
            
            $('#tituloMovimiento').text(titulo);
            
            $.ajax({
                url: url,
                type: 'GET',
                beforeSend: function() {
                    $('#detalleMovimientoContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="sr-only">Cargando...</span></div></div>');
                },
                success: function(response) {
                    $('#detalleMovimientoContent').html(response);
                    $('#modalDetalleMovimiento').modal('show');
                },
                error: function() {
                    $('#detalleMovimientoContent').html('<div class="alert alert-danger">Error al cargar los detalles</div>');
                }
            });
        }
        </script>
    </main>
    

<?php include("footer.php"); ?>