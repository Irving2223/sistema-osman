<?php
include("header.php");
include("conexion.php");

// Obtener productos para el select
$sql_productos = "SELECT * FROM productos WHERE activo = 1 ORDER BY nombre";
$result_productos = mysqli_query($conexion, $sql_productos);

// Obtener materias primas (por si necesita agregar materiales extra)
$sql_materias = "SELECT * FROM materias_primas ORDER BY nombre";
$result_materias = mysqli_query($conexion, $sql_materias);
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <h1 class="mt-4">Registro de Salida de Materia Prima</h1>
            
            <!-- Mostrar mensajes -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>¡Éxito!</strong> Salida registrada correctamente.
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

            <div class="card mb-4">
                <div class="card-body">
                    <form action="guardar_salida_receta.php" method="POST" id="formSalida">
                        <!-- Información básica de la salida -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="id_producto">Producto a Elaborar:</label>
                                <select class="form-control" id="id_producto" name="id_producto" onchange="cargarReceta()" required>
                                    <option value="">Seleccione un producto</option>
                                    <?php
                                    $id_producto_seleccionado = isset($_GET['id_producto']) ? intval($_GET['id_producto']) : 0;
                                    if ($result_productos->num_rows > 0) {
                                        while($row = $result_productos->fetch_assoc()) {
                                            $selected = ($row['id_producto'] == $id_producto_seleccionado) ? ' selected' : '';
                                            echo '<option value="' . $row['id_producto'] . '"' . $selected . '>' . htmlspecialchars($row['nombre']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="fecha_salida">Fecha de Salida:</label>
                                <input type="date" class="form-control" id="fecha_salida" name="fecha_salida" 
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="cantidad_producto">Cantidad a Producir:</label>
                                <input type="number" class="form-control" id="cantidad_producto" 
                                       name="cantidad_producto" step="0.01" min="0.01" 
                                       onchange="actualizarCantidadesReceta()" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="observaciones">Observaciones:</label>
                                <input type="text" class="form-control" id="observaciones" name="observaciones" 
                                       placeholder="Notas adicionales (opcional)">
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Receta cargada automáticamente -->
                        <h5>Materiales de la Receta <span id="cargandoReceta" class="badge badge-info" style="display:none">Cargando...</span></h5>
                        <div id="infoReceta" class="alert alert-info" style="display:none">
                            <i class="fas fa-info-circle"></i> 
                            <span id="textoInfoReceta"></span>
                        </div>
                        
                        <div id="detalles_receta">
                            <!-- Aquí se cargarán automáticamente los materiales de la receta -->
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Seleccione un producto para cargar su receta
                            </div>
                        </div>
                        
                        <!-- Sección para materiales adicionales (opcional) -->
                        <div class="mt-4">
                            <h6>Materiales Adicionales (Opcional)</h6>
                            <p class="text-muted">Agregue materiales que no están en la receta pero se utilizaron:</p>
                            <div id="detalles_adicionales">
                                <!-- Los materiales adicionales se agregarán aquí -->
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="agregarMaterialAdicional()">
                                <i class="fas fa-plus"></i> Agregar Material Extra
                            </button>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="btnGuardar" disabled>
                                <i class="fas fa-save"></i> Registrar Salida y Producir
                            </button>
                            <a href="salidas.php" class="btn btn-secondary btn-lg">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        // Función para cargar la receta del producto seleccionado
        function cargarReceta() {
            const idProducto = document.getElementById('id_producto').value;
            const detallesDiv = document.getElementById('detalles_receta');
            const btnGuardar = document.getElementById('btnGuardar');
            const infoReceta = document.getElementById('infoReceta');
            const textoInfoReceta = document.getElementById('textoInfoReceta');
            const cargandoReceta = document.getElementById('cargandoReceta');
            
            if (!idProducto) {
                detallesDiv.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Seleccione un producto para cargar su receta
                    </div>
                `;
                btnGuardar.disabled = true;
                infoReceta.style.display = 'none';
                return;
            }
            
            // Mostrar loading
            cargandoReceta.style.display = 'inline';
            detallesDiv.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando receta...</span>
                    </div>
                    <p class="mt-2">Cargando receta del producto...</p>
                </div>
            `;
            
            // Cargar receta via AJAX
            $.ajax({
                url: 'get_receta_salida.php',
                type: 'GET',
                data: { id_producto: idProducto },
                success: function(response) {
                    detallesDiv.innerHTML = response;
                    btnGuardar.disabled = false;
                    infoReceta.style.display = 'block';
                    
                    // Obtener nombre del producto seleccionado
                    const selectProducto = document.getElementById('id_producto');
                    const productoNombre = selectProducto.options[selectProducto.selectedIndex].text;
                    textoInfoReceta.textContent = `Receta cargada para: ${productoNombre}. Ajuste las cantidades si es necesario.`;
                    
                    // Actualizar cantidades basado en la cantidad a producir
                    actualizarCantidadesReceta();
                    
                    cargandoReceta.style.display = 'none';
                },
                error: function() {
                    detallesDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            Error al cargar la receta del producto
                        </div>
                    `;
                    btnGuardar.disabled = true;
                    infoReceta.style.display = 'none';
                    cargandoReceta.style.display = 'none';
                }
            });
        }

        // Función para actualizar cantidades basado en la cantidad a producir
        function actualizarCantidadesReceta() {
            const cantidadProducto = parseFloat(document.getElementById('cantidad_producto').value) || 1;
            const spansCantidad = document.querySelectorAll('.cantidad-receta');
            
            spansCantidad.forEach(span => {
                const cantidadBase = parseFloat(span.getAttribute('data-cantidad-base')) || 0;
                const nuevaCantidad = cantidadBase * cantidadProducto;
                span.textContent = nuevaCantidad.toFixed(2);
                
                verificarStock(span);
            });
        }

        // Función para verificar stock disponible
        function verificarStock(spanCantidad) {
            const row = spanCantidad.closest('tr');
            const stockSpan = row.querySelector('.stock-info');
            if (!stockSpan) return;
            
            const stockDisponible = parseFloat(stockSpan.getAttribute('data-stock')) || 0;
            const cantidadRequerida = parseFloat(spanCantidad.textContent) || 0;
            
            if (cantidadRequerida > stockDisponible) {
                stockSpan.className = 'badge badge-danger stock-info';
                spanCantidad.className = 'cantidad-receta text-danger';
            } else {
                stockSpan.className = 'badge badge-success stock-info';
                spanCantidad.className = 'cantidad-receta text-success';
            }
        }

        // Función para agregar material adicional
        function agregarMaterialAdicional() {
            const detallesDiv = document.getElementById('detalles_adicionales');
            const newItem = document.createElement('div');
            newItem.classList.add('material-adicional-item', 'mb-3', 'border', 'p-3', 'rounded');
            
            newItem.innerHTML = `
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label>Materia Prima:</label>
                        <select class="form-control" name="id_materia_prima_extra[]" required>
                            <option value="">Seleccione materia prima</option>
                            <?php
                            if ($result_materias->num_rows > 0) {
                                mysqli_data_seek($result_materias, 0);
                                while($materia = $result_materias->fetch_assoc()) {
                                    echo '<option value="' . $materia['id_materia_prima'] . '">' . htmlspecialchars($materia['nombre']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-5">
                        <label>Cantidad Utilizada:</label>
                        <input type="number" class="form-control" name="cantidad_utilizada_extra[]" step="0.01" min="0.01" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label style="visibility: hidden;">Eliminar</label>
                        <button type="button" class="btn btn-outline-danger btn-block" onclick="eliminarMaterialAdicional(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            
            detallesDiv.appendChild(newItem);
        }

        // Función para eliminar material adicional
        function eliminarMaterialAdicional(button) {
            button.closest('.material-adicional-item').remove();
        }

        // Validar formulario antes de enviar
        document.getElementById('formSalida').addEventListener('submit', function(e) {
            const spansInvalidos = document.querySelectorAll('.cantidad-receta.text-danger');
            if (spansInvalidos.length > 0) {
                e.preventDefault();
                alert('Hay materiales con stock insuficiente. La producción no puede registrarse.');
            }
        });

        // Cargar receta automáticamente si se llegó con producto preseleccionado
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            const idProductoURL = params.get('id_producto');
            if (idProductoURL) {
                const selectProducto = document.getElementById('id_producto');
                if (selectProducto) {
                    selectProducto.value = idProductoURL;
                    cargarReceta();
                }
            }
        });
        </script>
    </main>


<?php include("footer.php"); ?>