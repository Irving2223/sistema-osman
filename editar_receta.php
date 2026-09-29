<?php
include("header.php");
include("conexion.php");

$id_producto = isset($_GET['id_producto']) ? intval($_GET['id_producto']) : 0;

if ($id_producto == 0) {
    header("Location: productos.php");
    exit();
}

// Obtener información del producto
$sql_producto = "SELECT * FROM productos WHERE id_producto = ?";
$stmt_producto = $conexion->prepare($sql_producto);
$stmt_producto->bind_param("i", $id_producto);
$stmt_producto->execute();
$producto = $stmt_producto->get_result()->fetch_assoc();

if (!$producto) {
    die("Producto no encontrado");
}

// Obtener receta actual del producto
$sql_receta = "
    SELECT r.*, mp.nombre as materia_prima, mp.unidad_medida 
    FROM recetas r 
    INNER JOIN materias_primas mp ON r.id_materia_prima = mp.id_materia_prima 
    WHERE r.id_producto = ?
    ORDER BY mp.nombre
";
$stmt_receta = $conexion->prepare($sql_receta);
$stmt_receta->bind_param("i", $id_producto);
$stmt_receta->execute();
$receta_actual = $stmt_receta->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener todas las materias primas disponibles
$sql_materias = "SELECT * FROM materias_primas  ORDER BY nombre";
$result_materias = mysqli_query($conexion, $sql_materias);
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <h1 class="mt-4">Editar Receta: <?= htmlspecialchars($producto['nombre']) ?></h1>
            
            <!-- Mostrar mensajes -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>¡Éxito!</strong> Receta actualizada correctamente.
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
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-utensils"></i> 
                        Editar Receta del Producto
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Información del Producto -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Información del Producto</h6>
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($producto['nombre']) ?></p>
                            <p><strong>Unidad:</strong> <?= htmlspecialchars($producto['unidad_medida']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <?php if ($producto['precio_venta']): ?>
                                <p><strong>Precio:</strong> $<?= number_format($producto['precio_venta'], 2) ?></p>
                            <?php endif; ?>
                            <?php if ($producto['descripcion']): ?>
                                <p><strong>Descripción:</strong> <?= htmlspecialchars($producto['descripcion']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <form action="procesar_editar_receta.php" method="POST">
                        <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
                        
                        <h6 class="mb-3">Materiales de la Receta</h6>
                        <p class="text-muted">Modifique las cantidades o agregue/elimine materiales según sea necesario:</p>
                        
                        <div id="receta_detalles">
                            <?php if (count($receta_actual) > 0): ?>
                                <?php foreach ($receta_actual as $index => $item): ?>
                                    <div class="receta-item mb-3 border p-3 rounded bg-light">
                                        <input type="hidden" name="id_receta[]" value="<?= $item['id_receta'] ?>">
                                        <div class="form-row">
                                            <div class="form-group col-md-5">
                                                <label>Materia Prima:</label>
                                                <select class="form-control materia-prima-select" name="id_materia_prima[]" 
                                                        onchange="actualizarUnidadReceta(this)" required>
                                                    <option value="">Seleccione materia prima</option>
                                                    <?php
                                                    mysqli_data_seek($result_materias, 0);
                                                    while($materia = $result_materias->fetch_assoc()): 
                                                    ?>
                                                        <option value="<?= $materia['id_materia_prima'] ?>" 
                                                            data-unidad="<?= htmlspecialchars($materia['unidad_medida']) ?>"
                                                            <?= $materia['id_materia_prima'] == $item['id_materia_prima'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($materia['nombre']) ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label>Cantidad Necesaria:</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="cantidad_necesaria[]" 
                                                           value="<?= $item['cantidad_necesaria'] ?>" step="any" required>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text unidad-receta-text"><?= htmlspecialchars($item['unidad_medida']) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label style="visibility: hidden;">Eliminar</label>
                                                <button type="button" class="btn btn-outline-danger btn-block" 
                                                        onclick="eliminarRecetaItem(this)">
                                                    <i class="fas fa-times"></i> Eliminar
                                                </button>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Instrucciones (opcional):</label>
                                            <input type="text" class="form-control" name="instrucciones[]" 
                                                   value="<?= htmlspecialchars($item['instrucciones']) ?>" 
                                                   placeholder="Instrucciones específicas para esta materia prima">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    Este producto no tiene receta configurada. Agregue los materiales necesarios.
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <button type="button" class="btn btn-outline-primary mb-3" onclick="agregarRecetaItem()">
                            <i class="fas fa-plus"></i> Agregar Material
                        </button>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save"></i> Actualizar Receta
                            </button>
                            <a href="productos.php" class="btn btn-secondary btn-lg">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        // Función para actualizar la unidad de medida
        function actualizarUnidadReceta(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const unidad = selectedOption.getAttribute('data-unidad') || '';
            
            const unidadSpan = selectElement.closest('.form-row').querySelector('.unidad-receta-text');
            unidadSpan.textContent = unidad;
        }

        // Función para agregar nuevo item a la receta
        function agregarRecetaItem() {
            const recetaDiv = document.getElementById('receta_detalles');
            const newItem = document.createElement('div');
            newItem.classList.add('receta-item', 'mb-3', 'border', 'p-3', 'rounded', 'bg-light');
            
            newItem.innerHTML = `
                <input type="hidden" name="id_receta[]" value="0">
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label>Materia Prima:</label>
                        <select class="form-control materia-prima-select" name="id_materia_prima[]" 
                                onchange="actualizarUnidadReceta(this)" required>
                            <option value="">Seleccione materia prima</option>
                            <?php
                            mysqli_data_seek($result_materias, 0);
                            while($materia = $result_materias->fetch_assoc()): 
                            ?>
                                <option value="<?= $materia['id_materia_prima'] ?>" 
                                    data-unidad="<?= htmlspecialchars($materia['unidad_medida']) ?>">
                                    <?= htmlspecialchars($materia['nombre']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Cantidad Necesaria:</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="cantidad_necesaria[]" 
                                   step="any" required>
                            <div class="input-group-append">
                                <span class="input-group-text unidad-receta-text">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-3">
                        <label style="visibility: hidden;">Eliminar</label>
                        <button type="button" class="btn btn-outline-danger btn-block" 
                                onclick="eliminarRecetaItem(this)">
                            <i class="fas fa-times"></i> Eliminar
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
        }

        // Función para eliminar un item de la receta
        function eliminarRecetaItem(button) {
            if (confirm('¿Está seguro de eliminar este material de la receta?')) {
                button.closest('.receta-item').remove();
            }
        }

        // Inicializar unidades de medida
        document.addEventListener('DOMContentLoaded', function() {
            const selects = document.querySelectorAll('.materia-prima-select');
            selects.forEach(select => {
                if (select.value) {
                    actualizarUnidadReceta(select);
                }
            });
        });
        </script>
    </main>


<?php include("footer.php"); ?>