<?php
include("header.php");
include("conexion.php");
?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <h3 class="mt-4">Productos y Recetas</h3>
            
            <!-- Mostrar mensajes -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>¡Éxito!</strong> 
                    <?php 
                    if (isset($_GET['action'])) {
                        if ($_GET['action'] == 'edit_receta') {
                            $items = isset($_GET['items']) ? intval($_GET['items']) : 0;
                            echo "Receta actualizada correctamente. $items materiales actualizados.";
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

            <!-- Botón para nuevo producto -->
            <div class="mb-3">
                
                <a href="registro_producto.php"><button type="button" class="btn btn-dark"><i class="fas fa-plus"></i>Nuevo producto</button></a>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table mr-1"></i>
                    Lista de Productos
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Producto</th>
                                    <th>Descripción</th>
                                    <th>Unidad</th>
                                    <th>Precio</th>
                                    <th>Stock Actual</th>
                                    <th>Materiales en Receta</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "
                                    SELECT 
                                        p.id_producto,
                                        p.nombre,
                                        p.descripcion,
                                        p.unidad_medida,
                                        p.precio_venta,
                                        p.cantidad_stock,
                                        COUNT(r.id_receta) as total_materiales
                                    FROM productos p
                                    LEFT JOIN recetas r ON p.id_producto = r.id_producto
                                    WHERE p.activo = 1
                                    GROUP BY p.id_producto, p.nombre, p.descripcion, p.unidad_medida, p.precio_venta, p.cantidad_stock
                                    ORDER BY p.nombre
                                ";
                                
                                $result = mysqli_query($conexion, $sql);
                                
                                if ($result && $result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        $precio = $row['precio_venta'] ? '$' . number_format($row['precio_venta'], 2) : 'No definido';
                                        $clase_stock = ($row['cantidad_stock'] > 0) ? 'text-success' : 'text-danger';
                                        
                                        // CORREGIDO: Usar concatenación correcta para el enlace
                                        echo '
                                        <tr>
                                            <td><strong>' . htmlspecialchars($row['nombre']) . '</strong></td>
                                            <td>' . ($row['descripcion'] ? htmlspecialchars(substr($row['descripcion'], 0, 50)) . '...' : '-') . '</td>
                                            <td>' . htmlspecialchars($row['unidad_medida']) . '</td>
                                            <td>' . $precio . '</td>
                                            <td class="text-center"><span class="' . $clase_stock . ' font-weight-bold">' . number_format($row['cantidad_stock'], 2) . '</span></td>
                                            <td class="text-center"><span>' . $row['total_materiales'] . '</span></td>
                                            <td class="text-center">
                                                <button onclick="verReceta(' . $row['id_producto'] . ')" 
                                                        class="btn btn-info btn-sm" title="Ver Receta">
                                                    <i class="fas fa-list"></i>
                                                </button>
                                                <a href="editar_receta.php?id_producto=' . $row['id_producto'] . '" 
                                                   class="btn btn-warning btn-sm" title="Editar Receta">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button onclick="producirProducto(' . $row['id_producto'] . ')" 
                                                        class="btn btn-success btn-sm" title="Producir">
                                                    <i class="fas fa-cogs"></i>
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

        <!-- Modal para ver receta -->
        <div class="modal fade" id="modalReceta" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Receta: <span id="tituloReceta"></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="contenidoReceta">
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
                "order": [[0, 'asc']],
                "columnDefs": [
                    { "orderable": false, "targets": [6] }
                ]
            });
        });

        function verReceta(idProducto) {
            $('#tituloReceta').text('Cargando...');
            
            $.ajax({
                url: 'get_receta_producto.php',
                type: 'GET',
                data: { id_producto: idProducto },
                beforeSend: function() {
                    $('#contenidoReceta').html(`
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Cargando receta...</span>
                            </div>
                            <p class="mt-2">Cargando receta del producto...</p>
                        </div>
                    `);
                },
                success: function(response) {
                    $('#contenidoReceta').html(response);
                    
                    // Actualizar el título con el nombre del producto
                    const productoNombre = response.match(/Producto:.*?<span class="text-primary">(.*?)<\/span>/);
                    if (productoNombre && productoNombre[1]) {
                        $('#tituloReceta').text('Receta: ' + productoNombre[1]);
                    } else {
                        $('#tituloReceta').text('Receta del Producto');
                    }
                    
                    $('#modalReceta').modal('show');
                },
                error: function() {
                    $('#contenidoReceta').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            Error al cargar la receta del producto
                        </div>
                    `);
                    $('#tituloReceta').text('Error');
                }
            });
        }

        function producirProducto(idProducto) {
            if (confirm('¿Desea producir este producto? Se verificará el stock disponible de materiales.')) {
                window.location.href = 'registro_salidas.php?id_producto=' + idProducto;
            }
        }
        </script>
    </main>


<?php include("footer.php"); ?>