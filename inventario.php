<?php
include("header.php");
include("conexion.php");

$id_materia_prima = isset($_GET['id_materia_prima']) ? intval($_GET['id_materia_prima']) : 0;
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <h1 class="mt-4">Inventario de Materias Primas</h1>
            
            <?php if ($id_materia_prima == 0): ?>
                <!-- VISTA PRINCIPAL: Lista de todas las materias primas -->
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-boxes mr-1"></i>
                                Lista de Materias Primas
                            </div>
                            <a href="generar_pdf_inventario.php" class="btn btn-danger btn-sm" target="_blank">
                                <i class="fas fa-file-pdf"></i> Generar PDF
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tablaInventario" width="100%" cellspacing="0">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Materia Prima</th>
                                        <th>Unidad</th>
                                        <th>Stock Actual</th>
                                        <th>Última Actualización</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "
                                        SELECT 
                                            mp.id_materia_prima,
                                            mp.nombre,
                                            mp.unidad_medida,
                                            COALESCE(i.cantidad_actual, 0) as stock_actual,
                                            i.fecha_actualizacion
                                        FROM materias_primas mp
                                        LEFT JOIN inventario i ON mp.id_materia_prima = i.id_materia_prima
                                        ORDER BY mp.nombre
                                    ";
                                    
                                    $result = mysqli_query($conexion, $sql);
                                    
                                    while ($row = $result->fetch_assoc()):
                                        $clase_stock = ($row['stock_actual'] <= 0) ? 'text-danger' : (($row['stock_actual'] < 10) ? 'text-warning' : 'text-success');
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['nombre']) ?></td>
                                            <td><?= htmlspecialchars($row['unidad_medida']) ?></td>
                                            <td class="<?= $clase_stock ?> font-weight-bold"><?= number_format($row['stock_actual'], 2) ?></td>
                                            <td><?= ($row['fecha_actualizacion'] ? date('d/m/Y H:i', strtotime($row['fecha_actualizacion'])) : 'Nunca') ?></td>
                                            <td>
                                                <a href="inventario.php?id_materia_prima=<?= $row['id_materia_prima'] ?>" 
                                                   class="btn btn-info btn-sm">
                                                   <i class="fas fa-history"></i> Ver Historial
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- VISTA DE HISTORIAL ESPECÍFICO -->
                <?php
                // Obtener información de la materia prima
                $sql_materia = "SELECT * FROM materias_primas WHERE id_materia_prima = ?";
                $stmt = $conexion->prepare($sql_materia);
                $stmt->bind_param("i", $id_materia_prima);
                $stmt->execute();
                $materia = $stmt->get_result()->fetch_assoc();

                // Obtener stock actual
                $sql_stock = "SELECT cantidad_actual FROM inventario WHERE id_materia_prima = ?";
                $stmt = $conexion->prepare($sql_stock);
                $stmt->bind_param("i", $id_materia_prima);
                $stmt->execute();
                $stock_actual = $stmt->get_result()->fetch_assoc();
                $stock_actual = $stock_actual ? $stock_actual['cantidad_actual'] : 0;

                // Obtener ENTRADAS (entregas)
                $sql_entradas = "
                    SELECT 
                        e.id_entrega,
                        e.fecha_entrega as fecha,
                        'ENTRADA' as tipo,
                        CONCAT('Entrega #', e.id_entrega, ' - ', p.nombre) as referencia,
                        de.cantidad as cantidad,
                        e.fecha_entrega
                    FROM detalle_entregas de
                    INNER JOIN entregas e ON de.id_entrega = e.id_entrega
                    INNER JOIN proveedores p ON e.id_proveedor = p.id_proveedor
                    WHERE de.id_materia_prima = ?
                    ORDER BY e.fecha_entrega DESC
                ";
                
                $stmt = $conexion->prepare($sql_entradas);
                $stmt->bind_param("i", $id_materia_prima);
                $stmt->execute();
                $entradas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

                // Obtener SALIDAS (producción)
                $sql_salidas = "
                    SELECT 
                        s.id_salida,
                        s.fecha_salida as fecha,
                        'SALIDA' as tipo,
                        CONCAT('Salida #', s.id_salida, ' - ', s.producto) as referencia,
                        ds.cantidad_utilizada as cantidad,
                        s.fecha_salida
                    FROM detalle_salidas ds
                    INNER JOIN salidas s ON ds.id_salida = s.id_salida
                    WHERE ds.id_materia_prima = ?
                    ORDER BY s.fecha_salida DESC
                ";
                
                $stmt = $conexion->prepare($sql_salidas);
                $stmt->bind_param("i", $id_materia_prima);
                $stmt->execute();
                $salidas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

                // Combinar y ordenar movimientos
                $movimientos = array_merge($entradas, $salidas);
                usort($movimientos, function($a, $b) {
                    return strtotime($b['fecha']) - strtotime($a['fecha']);
                });

                // Calcular stock acumulado
                $stock_acumulado = $stock_actual;
                $movimientos_con_stock = [];
                
                foreach ($movimientos as $mov) {
                    if ($mov['tipo'] == 'ENTRADA') {
                        $stock_anterior = $stock_acumulado - $mov['cantidad'];
                    } else {
                        $stock_anterior = $stock_acumulado + $mov['cantidad'];
                    }
                    
                    $movimientos_con_stock[] = [
                        'fecha' => $mov['fecha'],
                        'tipo' => $mov['tipo'],
                        'referencia' => $mov['referencia'],
                        'cantidad' => $mov['cantidad'],
                        'stock_anterior' => $stock_anterior,
                        'stock_posterior' => $stock_acumulado,
                        'id_referencia' => $mov['tipo'] == 'ENTRADA' ? $mov['id_entrega'] : $mov['id_salida'],
                        'tabla_origen' => $mov['tipo'] == 'ENTRADA' ? 'entrega' : 'salida'
                    ];
                    
                    $stock_acumulado = $stock_anterior;
                }

                $movimientos_con_stock = array_reverse($movimientos_con_stock);
                ?>

                <!-- Botón para volver -->
                <div class="mb-3">
                    <a href="inventario.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Inventario
                    </a>
                </div>

                <!-- Resumen -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history"></i> 
                            Historial de: <?= htmlspecialchars($materia['nombre']) ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="alert alert-info">
                                    <h6>Stock Actual</h6>
                                    <h3 class="mb-0"><?= number_format($stock_actual, 2) ?></h3>
                                    <small><?= $materia['unidad_medida'] ?></small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="alert alert-success">
                                    <h6>Total Entradas</h6>
                                    <h3 class="mb-0"><?= count($entradas) ?></h3>
                                    <small>Registros</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="alert alert-warning">
                                    <h6>Total Salidas</h6>
                                    <h3 class="mb-0"><?= count($salidas) ?></h3>
                                    <small>Registros</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="alert alert-secondary">
                                    <h6>Movimientos</h6>
                                    <h3 class="mb-0"><?= count($movimientos) ?></h3>
                                    <small>Totales</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de historial -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Detalle de Movimientos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tablaHistorial" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Referencia</th>
                                        <th>Cantidad</th>
                                        <th>Stock Anterior</th>
                                        <th>Stock Posterior</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($movimientos_con_stock) > 0): ?>
                                        <?php foreach ($movimientos_con_stock as $mov): ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($mov['fecha'])) ?></td>
                                                <td>
                                                    <span class="badge <?= $mov['tipo'] == 'ENTRADA' ? 'badge-success' : 'badge-danger' ?> text-dark">
                                                        <?= $mov['tipo'] ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($mov['referencia']) ?></td>
                                                <td class="<?= $mov['tipo'] == 'ENTRADA' ? 'text-success font-weight-bold' : 'text-danger font-weight-bold' ?>">
                                                    <?= $mov['tipo'] == 'ENTRADA' ? '+' : '-' ?><?= number_format($mov['cantidad'], 2) ?>
                                                </td>
                                                <td class="text-right"><?= number_format($mov['stock_anterior'], 2) ?></td>
                                                <td class="text-right font-weight-bold"><?= number_format($mov['stock_posterior'], 2) ?></td>
                                                <td class="text-center">
                                                    <button class="btn btn-info btn-sm" onclick="verDetalleMovimiento('<?= $mov['tabla_origen'] ?>', <?= $mov['id_referencia'] ?>)">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </td>
                                            </tr>
<?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Modal para detalles -->
        <div class="modal fade" id="modalDetalleMovimiento" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tituloMovimiento">Detalles del Movimiento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="detalleMovimientoContent"></div>
                </div>
            </div>
        </div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
        
        <script>
        $(document).ready(function() {
            // Inicializar DataTable para la tabla de inventario principal
            if ($.fn.DataTable.isDataTable('#tablaInventario')) {
                $('#tablaInventario').DataTable().destroy();
            }
            
            $('#tablaInventario').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                "order": [[0, "asc"]],
                "columnDefs": [
                    { "orderable": true, "targets": [0, 1, 2, 3] },
                    { "orderable": false, "targets": [4] } // Columna de acciones
                ]
            });
            
            // Inicializar DataTable para la tabla de historial (solo si estamos en la vista de detalle)
            <?php if ($id_materia_prima > 0): ?>
            if ($.fn.DataTable.isDataTable('#tablaHistorial')) {
                $('#tablaHistorial').DataTable().destroy();
            }
            
            // Esperar a que el DOM esté completamente cargado
            $(window).on('load', function() {
var table = $('#tablaHistorial').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
                        "emptyTable": "No hay movimientos registrados para esta materia prima"
                    },
                    "order": [[0, "desc"]],
                    "columnDefs": [
                        { "type": "date", "targets": 0 },
                        { "orderable": true, "targets": [0, 1, 2, 3, 4, 5] },
                        { "orderable": false, "targets": 6 }, // Columna de acciones
                        { "className": "dt-center", "targets": [0, 1, 3, 4, 5, 6] },
                        { "className": "dt-left", "targets": 2 }
                    ],
                    "pageLength": 25,
                    "responsive": true,
                    "autoWidth": false,
                    "destroy": true,
                    "initComplete": function() {
                        console.log('DataTable inicializada correctamente con ' + this.fnSettings().aoColumns.length + ' columnas');
                    }
                });
                
                // Forzar un redibujado de la tabla
                table.columns.adjust().draw();
            });
            <?php endif; ?>
        });

        function verDetalleMovimiento(tipo, id) {
            let url = tipo === 'entrega' ? 'get_detalle_entrega.php?id_entrega=' + id : 'get_detalle_salida.php?id_salida=' + id;
            
            $('#detalleMovimientoContent').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
            
            $.get(url, function(data) {
                $('#detalleMovimientoContent').html(data);
                $('#modalDetalleMovimiento').modal('show');
            }).fail(function() {
                $('#detalleMovimientoContent').html('<div class="alert alert-danger">Error al cargar los detalles</div>');
            });
        }
        </script>
    </main>


<?php include("footer.php"); ?>