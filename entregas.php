<?php
include("header.php");
include("conexion.php");

?>
<div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h3 class="mt-4">Listado de Entregas</h3>
                        <a href="añadir_entrega.php"><button type="button" class="btn btn-dark"><i class="fas fa-plus"></i>Añadir</button></a>
                        <?php
// Mostrar mensajes de éxito o error
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $detalles = isset($_GET['detalles']) ? $_GET['detalles'] : 0;
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>¡Éxito!</strong> Entrega guardada correctamente. ' . $detalles . ' detalles registrados.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
          </div>';
}

if (isset($_GET['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error:</strong> ' . htmlspecialchars($_GET['error']) . '
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
          </div>';
}
?>
                       
                        
                        <div class="card mb-4">
                        <br>
                            
                            <div class="card-body">
                                 <table class="table table-bordered table-striped" id="datatablesSimple">
                            <thead class="thead-dark">
                                <tr>
                                    <th>N° Factura</th>
                                    <th>Proveedor</th>
                                    <th>Fecha Entrega</th>
                                    <th>Cantidad Total</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "
                                    SELECT
                                        e.id_entrega, 
                                        e.numero_factura,
                                        p.nombre as proveedor,
                                        e.fecha_entrega,
                                        COUNT(DISTINCT e.id_entrega) as total_entregas,
                                        COUNT(de.id_detalle_entrega) as total_items,
                                        SUM(de.cantidad) as cantidad_total,
                                        GROUP_CONCAT(DISTINCT e.id_entrega) as ids_entregas
                                    FROM entregas e
                                    INNER JOIN proveedores p ON e.id_proveedor = p.id_proveedor
                                    INNER JOIN detalle_entregas de ON e.id_entrega = de.id_entrega
                                    WHERE e.numero_factura IS NOT NULL AND e.numero_factura != ''
                                    GROUP BY e.numero_factura, p.nombre, e.fecha_entrega
                                    ORDER BY e.fecha_entrega DESC
                                ";
                                
                                $result = mysqli_query($conexion, $sql);
                                
                                if ($result && $result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo '
                                        <tr>
                                            <td><strong>' . htmlspecialchars($row['numero_factura']) . '</strong></td>
                                            <td>' . htmlspecialchars($row['proveedor']) . '</td>
                                            <td>' . htmlspecialchars($row['fecha_entrega']) . '</td>
                                            
                                            <td class="text-right"><strong>' . number_format($row['cantidad_total'], 2) . '</strong></td>
                                            <td class="text-center">
                                                <button class="btn btn-info btn-sm" onclick="verDetalleFactura(\'' . $row['numero_factura'] . '\')">
                                                    <i class="fas fa-eye"></i> 
                                                </button>
                                                 
                                                <button onclick="confirmarEliminacion(' . $row['id_entrega'] . ')" 
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
                    <!-- Modal para ver detalles de factura -->
        <div class="modal fade" id="modalDetalleFactura" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles de Factura: <span id="tituloFactura"></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="detalleFacturaContent">
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
                "order": [[2, 'desc']] // Ordenar por fecha descendente
            });
            
            $('#dataTable2').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                "order": [[2, 'desc']]
            });
        });

        function verDetalleFactura(numeroFactura) {
            $('#tituloFactura').text(numeroFactura);
            
            $.ajax({
                url: 'get_detalle_factura2.php',
                type: 'GET',
                data: { numero_factura: numeroFactura },
                beforeSend: function() {
                    $('#detalleFacturaContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="sr-only">Cargando...</span></div></div>');
                },
                success: function(response) {
                    $('#detalleFacturaContent').html(response);
                    $('#modalDetalleFactura').modal('show');
                },
                error: function() {
                    alert('Error al cargar los detalles de la factura');
                }
            });
        }


function confirmarEliminacion(idEntrega) {
    if (confirm('¿Estás seguro de que quieres eliminar esta entrega? Esta acción no se puede deshacer.')) {
        window.location.href = 'eliminar_entrega.php?id_entrega=' + idEntrega;
    }
}


        
        </script>
    </main>





<?php
include("footer.php");

?>