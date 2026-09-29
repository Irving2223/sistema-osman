<?php
include("header.php");
include("conexion.php");

$id_entrega = isset($_GET['id_entrega']) ? intval($_GET['id_entrega']) : 0;

if ($id_entrega == 0) {
    header("Location: entregas.php");
    exit();
}

// Obtener datos de la entrega
$sql_entrega = "SELECT * FROM entregas WHERE id_entrega = ?";
$stmt_entrega = $conexion->prepare($sql_entrega);
$stmt_entrega->bind_param("i", $id_entrega);
$stmt_entrega->execute();
$entrega = $stmt_entrega->get_result()->fetch_assoc();

if (!$entrega) {
    die("Entrega no encontrada");
}

// Obtener detalles de la entrega
$sql_detalles = "
    SELECT de.*, mp.nombre as materia_prima, mp.unidad_medida 
    FROM detalle_entregas de 
    INNER JOIN materias_primas mp ON de.id_materia_prima = mp.id_materia_prima 
    WHERE de.id_entrega = ?";
$stmt_detalles = $conexion->prepare($sql_detalles);
$stmt_detalles->bind_param("i", $id_entrega);
$stmt_detalles->execute();
$detalles = $stmt_detalles->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener proveedores para el select
$sql_proveedores = "SELECT * FROM proveedores";
$result_proveedores = mysqli_query($conexion, $sql_proveedores);

// Obtener materias primas para el select
$sql_materias = "SELECT * FROM materias_primas";
$result_materias = mysqli_query($conexion, $sql_materias);
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <h1 class="mt-4">Editar Entrega #<?= $entrega['id_entrega'] ?></h1>
            
            <div class="card mb-4">
                <div class="card-body">
                    <form action="procesar_editar_entrega.php" method="POST">
                        <input type="hidden" name="id_entrega" value="<?= $entrega['id_entrega'] ?>">
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="id_proveedor">Proveedor:</label>
                                <select class="form-control" id="id_proveedor" name="id_proveedor" required>
                                    <?php while($proveedor = $result_proveedores->fetch_assoc()): ?>
                                        <option value="<?= $proveedor['id_proveedor'] ?>" 
                                            <?= $proveedor['id_proveedor'] == $entrega['id_proveedor'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($proveedor['nombre']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="fecha_entrega">Fecha de Entrega:</label>
                                <input type="date" class="form-control" id="fecha_entrega" name="fecha_entrega" 
                                       value="<?= $entrega['fecha_entrega'] ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="numero_factura">Número de Factura:</label>
                            <input type="text" class="form-control" id="numero_factura" name="numero_factura" 
                                   value="<?= htmlspecialchars($entrega['numero_factura']) ?>">
                        </div>
                        
                        <h4>Detalles de Entrega</h4>
                        <div id="detalles">
                            <?php foreach ($detalles as $index => $detalle): ?>
                                <div class="detalle-item mb-3 border p-3">
                                    <input type="hidden" name="id_detalle[]" value="<?= $detalle['id_detalle_entrega'] ?>">
                                    <div class="form-row">
                                        <div class="form-group col-md-5">
                                            <label>Materia Prima:</label>
                                            <select class="form-control" name="id_materia_prima[]" required>
                                                <?php 
                                                mysqli_data_seek($result_materias, 0);
                                                while($materia = $result_materias->fetch_assoc()): 
                                                ?>
                                                    <option value="<?= $materia['id_materia_prima'] ?>" 
                                                        <?= $materia['id_materia_prima'] == $detalle['id_materia_prima'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($materia['nombre']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label>Cantidad:</label>
                                            <input type="number" class="form-control" name="cantidad[]" 
                                                   value="<?= $detalle['cantidad'] ?>" step="0.01" required>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label style="visibility: hidden;">Eliminar</label>
                                            <button type="button" class="btn btn-danger btn-block" onclick="eliminarDetalle(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button type="button" class="btn btn-secondary mb-3" onclick="agregarDetalle()">
                            <i class="fas fa-plus"></i> Agregar Detalle
                        </button>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar Entrega
                            </button>
                            <a href="entregas.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        function agregarDetalle() {
            const detallesDiv = document.getElementById('detalles');
            const newDetail = document.createElement('div');
            newDetail.classList.add('detalle-item', 'mb-3', 'border', 'p-3');
            
            newDetail.innerHTML = `
                <input type="hidden" name="id_detalle[]" value="0">
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label>Materia Prima:</label>
                        <select class="form-control" name="id_materia_prima[]" required>
                            <?php 
                            mysqli_data_seek($result_materias, 0);
                            while($materia = $result_materias->fetch_assoc()): 
                            ?>
                                <option value="<?= $materia['id_materia_prima'] ?>">
                                    <?= htmlspecialchars($materia['nombre']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-5">
                        <label>Cantidad:</label>
                        <input type="number" class="form-control" name="cantidad[]" step="0.01" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label style="visibility: hidden;">Eliminar</label>
                        <button type="button" class="btn btn-danger btn-block" onclick="eliminarDetalle(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            
            detallesDiv.appendChild(newDetail);
        }

        function eliminarDetalle(button) {
            if (document.querySelectorAll('.detalle-item').length > 1) {
                button.closest('.detalle-item').remove();
            } else {
                alert('Debe haber al menos un detalle');
            }
        }
        </script>
    </main>


<?php include("footer.php"); ?>