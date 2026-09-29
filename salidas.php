<?php
include("header.php");
include("conexion.php");
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <h3 class="mt-4">Salidas Registradas</h3>
            
            <!-- Mostrar mensajes -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>¡Éxito!</strong> 
                    <?php 
                    if (isset($_GET['action'])) {
                        if ($_GET['action'] == 'delete') {
                            echo 'Salida eliminada correctamente.';
                        }
                    } elseif (isset($_GET['materiales'])) {
                        $materiales = intval($_GET['materiales']);
                        $cantidad = isset($_GET['cantidad']) ? floatval($_GET['cantidad']) : 0;
                        echo "Producción registrada correctamente. ";
                        echo "Materiales procesados: $materiales";
                        if ($cantidad > 0) {
                            echo " - Cantidad producida: $cantidad";
                        }
                    } else {
                        echo 'Operación realizada correctamente.';
                    }
                    ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> <?= htmlspecialchars($_GET['error']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['warning'])): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Advertencia:</strong> <?= htmlspecialchars($_GET['warning']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Botón para nueva salida -->
            <div class="mb-3">
               
                    <a href="registro_salidas.php"><button type="button" class="btn btn-dark"><i class="fas fa-plus"></i>Añadir</button></a>
                   
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table mr-1"></i>
                    Historial de Salidas
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="datatablesSimple" width="100%" cellspacing="0">
                            <thead class="thead-dark">
                                <tr>
                                   
                                    <th>Producto</th>
                                    <th>Fecha Salida</th>
                                    <th>Cantidad Producida</th>
                                    
                                    <th>Cantidad Total Utilizada</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "
                                    SELECT 
                                        s.id_salida,
                                        s.producto,
                                        s.fecha_salida,
                                        s.cantidad_producto,
                                        COUNT(ds.id_detalle_salida) as total_materiales,
                                        SUM(ds.cantidad_utilizada) as cantidad_total_utilizada
                                    FROM salidas s
                                    LEFT JOIN detalle_salidas ds ON s.id_salida = ds.id_salida
                                    GROUP BY s.id_salida, s.producto, s.fecha_salida, s.cantidad_producto
                                    ORDER BY s.fecha_salida DESC, s.id_salida DESC
                                ";
                                
                                $result = mysqli_query($conexion, $sql);
                                
                                if ($result && $result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo '
                                        <tr>
                                            
                                            <td>' . htmlspecialchars($row['producto']) . '</td>
                                            <td>' . $row['fecha_salida'] . '</td>
                                            <td class="text-center">' . ($row['cantidad_producto'] ? number_format($row['cantidad_producto'], 2) : '-') . '</td>
                                          
                                            <td class="text-right"><strong>' . number_format($row['cantidad_total_utilizada'], 2) . '</strong></td>
                                            <td class="text-center">
                                                <button onclick="verDetalleSalida(' . $row['id_salida'] . ')" 
                                                        class="btn btn-info btn-sm" title="Ver Detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button onclick="confirmarEliminacion(' . $row['id_salida'] . ')" 
                                                        class="btn btn-danger btn-sm" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
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

        <!-- Modal para detalles de salida -->
        <div class="modal fade" id="modalDetalleSalida" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles de Salida: <span id="tituloSalida"></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="detalleSalidaContent">
                        <!-- Contenido cargado por AJAX -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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
                "order": [[2, 'desc']],
                "columnDefs": [
                    { "orderable": false, "targets": [6] }
                ]
            });
        });

        function confirmarEliminacion(idSalida) {
            if (confirm('¿Estás seguro de que quieres eliminar esta salida?\n\nEsta acción revertirá el inventario y eliminará todos los materiales asociados.\n\nEsta acción no se puede deshacer.')) {
                window.location.href = 'eliminar_salida.php?id_salida=' + idSalida;
            }
        }

        function verDetalleSalida(idSalida) {
            $('#tituloSalida').text('Salida #' + idSalida);
            
            $.ajax({
                url: 'get_detalle_salida.php',
                type: 'GET',
                data: { id_salida: idSalida },
                beforeSend: function() {
                    $('#detalleSalidaContent').html(`
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando detalles...</p>
                        </div>
                    `);
                },
                success: function(response) {
                    $('#detalleSalidaContent').html(response);
                    $('#modalDetalleSalida').modal('show');
                },
                error: function() {
                    $('#detalleSalidaContent').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            Error al cargar los detalles de la salida
                        </div>
                    `);
                    $('#modalDetalleSalida').modal('show');
                }
            });
        }

        // Auto-cerrar alertas después de 5 segundos
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        </script>
    </main>


<?php include("footer.php"); ?>