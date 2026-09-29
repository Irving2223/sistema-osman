<?php
include("header.php");
include("conexion.php");



$sql1 = "SELECT * FROM materias_primas";
$result2 = mysqli_query($conexion,$sql1);
?>

<div id="layoutSidenav_content">
    <main>
        <h1 class="text-center mb-4">Registro de salida de Materia Prima</h1>
        <div class="card p-4 form-section">
            <form action="guardar_salida.php" method="POST">
                <div class="form-group">
                <div class="form-group">
                <label for="id_proveedor">Producto:</label>
                <select class="form-control" id="producto" name="producto" required>
                <option value="0">Seleciona</option>
                <option value="desisfentante">Desisfentante</option>
                <option value="cloro_activo">Cloro Activo</option>
                <option value="cloro_jabonoso">Cloro Jabonoso</option>
                <option value="jabon_liquido">Jabon Liquido</option>
                <option value="brisol">Brisol</option>
                <option value="limpia_ceramica">Limpia Ceramica</option>
                <option value="limpia_cocina">Limpia Cocina</option>
                <option value="cera_blanca">Cera Blanca</option>
                <option value="cera_roja">Cera roja</option>
                <option value="suavizante">Suavizante</option>
                </select>
            </div>
                </div>
                <div class="form-group">
                    <label for="fecha_entrega">Fecha de salida:</label>
                    <input type="date" class="form-control" id="fecha_entrega" name="fecha_salida" required>
                </div>
                
                <h4>Materiales utilizados</h4>
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
        <button type="submit" class="btn btn-primary">Guardar salida</button>
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