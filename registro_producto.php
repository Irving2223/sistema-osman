<?php
include("header.php");
include("conexion.php");

// Obtener materias primas para las recetas
$sql_materias = "SELECT * FROM materias_primas  ORDER BY nombre";
$result_materias = mysqli_query($conexion, $sql_materias);
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <h1 class="mt-4">Registro de Producto y Receta</h1>
            
            <!-- Mostrar mensajes -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>¡Éxito!</strong> Producto y receta registrados correctamente.
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
                    <form action="guardar_producto_receta.php" method="POST">
                        <!-- Información del Producto -->
                        <h4 class="mb-3">Información del Producto</h4>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="nombre">Nombre del Producto:</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       placeholder="Ej: Jabón Líquido, Cloro Activo, etc." required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="unidad_medida">Unidad de Medida:</label>
                                <select class="form-control" id="unidad_medida" name="unidad_medida" required>
                                    <option value="unidad">Unidad</option>
                                    <option value="litro">Litro</option>
                                    <option value="galon">Galón</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="precio_venta">Precio de Venta:</label>
                                <input type="number" class="form-control" id="precio_venta" name="precio_venta" 
                                       step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="descripcion">Descripción:</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" 
                                      rows="3" placeholder="Descripción del producto..."></textarea>
                        </div>
                        
                        <hr>
                        
                        <!-- Receta - Materias Primas Necesarias -->
                        <h4 class="mb-3">Receta - Materias Primas Necesarias</h4>
                        <p class="text-muted">Agregue las materias primas necesarias para producir este producto:</p>
                        
                        <div id="receta_detalles">
                            <div class="receta-item mb-3 border p-3 rounded">
                                <div class="form-row">
                                    <div class="form-group col-md-5">
                                        <label>Materia Prima:</label>
                                        <select class="form-control materia-prima-select" name="id_materia_prima[]" 
                                                onchange="actualizarUnidadReceta(this)" required>
                                            <option value="">Seleccione materia prima</option>
                                            <?php
                                            if ($result_materias->num_rows > 0) {
                                                while($materia = $result_materias->fetch_assoc()) {
                                                    echo '<option value="' . $materia['id_materia_prima'] . '" 
                                                            data-unidad="' . htmlspecialchars($materia['unidad_medida']) . '">
                                                            ' . htmlspecialchars($materia['nombre']) . '
                                                          </option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-5">
                                        <label>Cantidad Necesaria:</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="cantidad_necesaria[]" 
                                                   step="any" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text unidad-receta-text">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label style="visibility: hidden;">Eliminar</label>
                                        <button type="button" class="btn btn-outline-danger btn-block" 
                                                onclick="eliminarRecetaItem(this)" disabled>
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Instrucciones (opcional):</label>
                                    <input type="text" class="form-control" name="instrucciones[]" 
                                           placeholder="Instrucciones específicas para esta materia prima">
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-outline-secondary mb-3" onclick="agregarRecetaItem()">
                            <i class="fas fa-plus"></i> Agregar Otra Materia Prima
                        </button>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Guardar Producto y Receta
                            </button>
                            <a href="productos.php" class="btn btn-secondary btn-lg">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        // Función para actualizar la unidad de medida en la receta
        function actualizarUnidadReceta(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const unidad = selectedOption.getAttribute('data-unidad') || '';
            
            // Actualizar unidad en el input group
            const unidadSpan = selectElement.closest('.form-row').querySelector('.unidad-receta-text');
            unidadSpan.textContent = unidad;
        }

        // Función para agregar nuevo item a la receta
        function agregarRecetaItem() {
            const recetaDiv = document.getElementById('receta_detalles');
            const newItem = document.createElement('div');
            newItem.classList.add('receta-item', 'mb-3', 'border', 'p-3', 'rounded');
            
            newItem.innerHTML = `
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label>Materia Prima:</label>
                        <select class="form-control materia-prima-select" name="id_materia_prima[]" 
                                onchange="actualizarUnidadReceta(this)" required>
                            <option value="">Seleccione materia prima</option>
                            <?php
                            if ($result_materias->num_rows > 0) {
                                mysqli_data_seek($result_materias, 0);
                                while($materia = $result_materias->fetch_assoc()) {
                                    echo '<option value="' . $materia['id_materia_prima'] . '" 
                                            data-unidad="' . htmlspecialchars($materia['unidad_medida']) . '">
                                            ' . htmlspecialchars($materia['nombre']) . '
                                          </option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-5">
                        <label>Cantidad Necesaria:</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="cantidad_necesaria[]" 
                                   step="0.01" min="0.01" required>
                            <div class="input-group-append">
                                <span class="input-group-text unidad-receta-text">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-2">
                        <label style="visibility: hidden;">Eliminar</label>
                        <button type="button" class="btn btn-outline-danger btn-block" 
                                onclick="eliminarRecetaItem(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Instrucciones (opcional):</label>
                    <input type="text" class="form-control" name="instrucciones[]" 
                           placeholder="Instrucciones específicas para esta materia prima">
                </div>
            `;
            
            recetaDiv.appendChild(newItem);
            
            // Habilitar eliminar en el primer item si ahora hay más de uno
            const todosLosItems = document.querySelectorAll('.receta-item');
            if (todosLosItems.length > 1) {
                const primerBoton = todosLosItems[0].querySelector('button[onclick="eliminarRecetaItem(this)"]');
                primerBoton.disabled = false;
            }
        }

        // Función para eliminar un item de la receta
        function eliminarRecetaItem(button) {
            const items = document.querySelectorAll('.receta-item');
            if (items.length > 1) {
                button.closest('.receta-item').remove();
                
                // Si queda solo un item, deshabilitar su botón eliminar
                if (items.length === 2) {
                    const primerBoton = document.querySelector('.receta-item button[onclick="eliminarRecetaItem(this)"]');
                    primerBoton.disabled = true;
                }
            }
        }

        // Inicializar cuando la página cargue
        document.addEventListener('DOMContentLoaded', function() {
            const primerSelect = document.querySelector('.materia-prima-select');
            if (primerSelect) {
                actualizarUnidadReceta(primerSelect);
            }
        });
        </script>
    </main>

<?php include("footer.php"); ?>