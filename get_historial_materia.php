<?php
include("header.php");
include("conexion.php");
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <h1 class="mt-4">Inventario de Materias Primas</h1>
            
            <div class="card mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
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
                                
                                while ($row = $result->fetch_assoc()) {
                                    $clase_stock = ($row['stock_actual'] <= 0) ? 'text-danger' : (($row['stock_actual'] < 10) ? 'text-warning' : 'text-success');
                                    
                                    echo '
                                    <tr>
                                        <td>' . htmlspecialchars($row['nombre']) . '</td>
                                        <td>' . htmlspecialchars($row['unidad_medida']) . '</td>
                                        <td class="' . $clase_stock . ' font-weight-bold">' . number_format($row['stock_actual'], 2) . '</td>
                                        <td>' . ($row['fecha_actualizacion'] ? date('d/m/Y H:i', strtotime($row['fecha_actualizacion'])) : 'Nunca') . '</td>
                                        <td>
                                            <button class="btn btn-info btn-sm" onclick="verHistorial(' . $row['id_materia_prima'] . ')">
                                                <i class="fas fa-history"></i> Ver Historial
                                            </button>
                                        </td>
                                    </tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para el historial -->
        <div class="modal fade" id="modalHistorial" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tituloHistorial">Historial de Movimientos</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="contenidoHistorial">
                        <!-- Aquí se carga el historial -->
                    </div>
                </div>
            </div>
        </div>

        <script>
        function verHistorial(idMateriaPrima) {
            // Mostrar loading
            $('#contenidoHistorial').html('<div class="text-center"><div class="spinner-border" role="status"><span class="sr-only">Cargando...</span></div></div>');
            
            // Cargar el historial via AJAX
            $.ajax({
                url: 'get_historial_materia.php',
                type: 'GET',
                data: { id_materia_prima: idMateriaPrima },
                success: function(response) {
                    $('#contenidoHistorial').html(response);
                    $('#modalHistorial').modal('show');
                },
                error: function() {
                    $('#contenidoHistorial').html('<div class="alert alert-danger">Error al cargar el historial</div>');
                }
            });
        }
        </script>
    </main>
</div>

<?php include("footer.php"); ?>