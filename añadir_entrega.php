<?php
include("header.php");
include("conexion.php");

$sql = "SELECT * FROM proveedores";
$result = mysqli_query($conexion,$sql);

$sql1 = "SELECT * FROM materias_primas";
$result2 = mysqli_query($conexion,$sql1);
?>

<div id="layoutSidenav_content">
    <main>
        <h1 class="text-center mb-4">Registro de Entregas de Materia Prima</h1>
        <div class="card p-4 form-section">
            <form action="guardar_entrega.php" method="POST">
                <div class="form-group">
                    <label for="id_proveedor">Proveedor:</label>
                    <select class="form-control" id="id_proveedor" name="id_proveedor" required>
                        <?php
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo '<option value="' . htmlspecialchars($row["id_proveedor"]) . '">' . htmlspecialchars($row["nombre"]) . '</option>';
                            }
                        } else {
                            echo '<option value="">No hay proveedores disponibles</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fecha_entrega">Fecha de Entrega:</label>
                    <input type="date" class="form-control" id="fecha_entrega" name="fecha_entrega" required>
                </div>
                <div class="form-group">
                    <label for="numero_factura">Número de Factura:</label>
                    <input type="text" class="form-control" id="numero_factura" name="numero_factura">
                </div>
                
                <label for="detalles">Detalles de Entrega</label>
                <div id="detalles">
                    <div class="detalle-item mb-3">
                        <select class="form-control mb-2 materia-select" name="id_materia_prima[]" required>
                            <option value="">Seleccione materia prima</option>
                            <?php
                            // Opciones iniciales desde PHP
                            mysqli_data_seek($result2, 0);
                            if ($result2->num_rows > 0) {
                                while($row2 = $result2->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row2["id_materia_prima"]) . '">' . htmlspecialchars($row2["nombre"]) . '</option>';
                                }
                            }
                            ?>
                        </select>
                        <input type="number" class="form-control" name="cantidad[]" placeholder="Cantidad" step="0.01" required>
                    </div>
                </div>
                
               <button type="button" class="btn btn-secondary" onclick="addDetail()">Agregar Detalle</button>
               <button type="submit" class="btn btn-primary">Guardar Entrega</button>
                
            </form>
        </div>

        <script>
        let materiasPrimas = [];

        // Cargar materias primas al iniciar la página
        async function cargarMateriasPrimas() {
            try {
                const response = await fetch('get_materias2.php');
                if (!response.ok) throw new Error('Error en la respuesta del servidor');
                
                materiasPrimas = await response.json();
                console.log('Materias primas cargadas:', materiasPrimas);
                
            } catch (error) {
                console.error('Error al cargar materias primas:', error);
                alert('Error al cargar las materias primas: ' + error.message);
            }
        }

        // Función para llenar un select con las materias primas
        function llenarSelect(selectElement) {
            // Limpiar el select
            selectElement.innerHTML = '<option value="">Seleccione materia prima</option>';
            
            // Llenar con las opciones desde el array
            materiasPrimas.forEach(materia => {
                const option = document.createElement('option');
                option.value = materia.id_materia_prima;
                option.textContent = materia.nombre;
                selectElement.appendChild(option);
            });
        }

        // Función para agregar nuevo detalle
        function addDetail() {
            const detallesDiv = document.getElementById('detalles');
            const newDetail = document.createElement('div');
            newDetail.classList.add('detalle-item', 'mb-3');
            
            newDetail.innerHTML = `
                <select class="form-control mb-2 materia-select" name="id_materia_prima[]" required>
                    <option value="">Cargando materias primas...</option>
                </select>
                <input type="number" class="form-control" name="cantidad[]" placeholder="Cantidad" step="0.01" required>
                <button type="button" class="btn btn-danger btn-sm mt-2" onclick="removeDetail(this)">Eliminar</button>
            `;
            
            detallesDiv.appendChild(newDetail);
            
            // Llenar el nuevo select con las materias primas
            const newSelect = newDetail.querySelector('.materia-select');
            llenarSelect(newSelect);
        }

        // Función para eliminar un detalle
        function removeDetail(button) {
            if (document.querySelectorAll('.detalle-item').length > 1) {
                button.closest('.detalle-item').remove();
            } else {
                alert('Debe haber al menos un detalle');
            }
        }

        // Inicializar cuando la página cargue
        document.addEventListener('DOMContentLoaded', function() {
            cargarMateriasPrimas();
        });
        </script>
 



</main>
<?php
include("footer.php");

?>