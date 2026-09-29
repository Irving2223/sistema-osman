<?php
include("header.php");
include("conexion.php");

$sql1 = "SELECT * FROM materias_primas";
$result2 = mysqli_query($conexion,$sql1);

?>
<div id="layoutSidenav_content">
    <main>
        <h1 class="text-center mb-4">Registro de Productos Terminados</h1>
        <div class="card p-4 form-section">
        <form action="guardar_entrega.php" method="POST">
            <div class="form-group">
                <label for="id_proveedor">Producto:</label>
                <select class="form-control" id="id_proveedor" name="id_proveedor" required>
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
            <div class="form-group">
                <label for="fecha_entrega">Fecha de Entrega:</label>
                <input type="date" class="form-control" id="fecha_entrega" name="fecha_entrega" required>
            </div>
           
             <label for="fecha_entrega">Materia Prima Utilizado:</label>
        <div id="detalles">
            <div class="detalle-item mb-3">
                <select class="form-control mb-2" name="id_materia_prima[]" required>
                    <!-- Aquí deberías llenar las materias primas desde la base de datos -->
                    <option value="">Seleccione materia prima</option>
                    <?php
            if ($result2->num_rows > 0) {
                // Iterar sobre los resultados y crear las opciones
                while($row2 = $result2->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($row2["id_materia_prima"]) . '">' . htmlspecialchars($row2["nombre"] ) . '</option>';
                }
            } else {
                echo '<option value="">No hay docentes disponibles</option>';
            }
            ?>
                </select>
                <input type="number" class="form-control" name="cantidad[]" placeholder="Cantidad" required>
            </div>
        </div>
        <button type="button" class="btn btn-secondary" onclick="addDetail()">Agregar Detalle</button>
        <button type="submit" class="btn btn-primary">Guardar Entrega</button>
        </form>
    </div>

    <script>
    let materiasPrimas = [];

// Función para cargar las materias primas desde la base de datos
async function cargarMateriasPrimas() {
    try {
        const response = await fetch('get_materias.php');
        materiasPrimas = await response.json();
        
        // Llenar el select inicial
        llenarSelectMateriaPrima(document.querySelector('.materia-prima-select'));
        
    } catch (error) {
        console.error('Error al cargar materias primas:', error);
    }
}

// Función para llenar un select con las materias primas
function llenarSelectMateriaPrima(selectElement) {
    // Limpiar el select
    selectElement.innerHTML = '<option value="">Seleccione materia prima</option>';
    
    // Llenar con las opciones
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
        <select class="form-control mb-2 materia-prima-select" name="id_materia_prima[]" required>
            <option value="">Seleccione materia prima</option>
        </select>
        <input type="number" class="form-control" name="cantidad[]" placeholder="Cantidad" required>
        <button type="button" class="btn btn-danger btn-sm mt-2" onclick="removeDetail(this)">Eliminar</button>
    `;
    
    detallesDiv.appendChild(newDetail);
    
    // Llenar el nuevo select con las materias primas
    const newSelect = newDetail.querySelector('.materia-prima-select');
    llenarSelectMateriaPrima(newSelect);
}

// Función para eliminar un detalle
function removeDetail(button) {
    button.closest('.detalle-item').remove();
}

// Cargar las materias primas cuando la página esté lista
document.addEventListener('DOMContentLoaded', function() {
    cargarMateriasPrimas();
});



    </script>





</main>
<?php
include("footer.php");

?>