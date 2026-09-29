<?php
include("header.php");
include("conexion.php");



?>
<div class="container mt-5">
    <h2>Registrar Entrega</h2>
    <form action="guardar_entrega.php" method="POST">
        <div class="form-group">
            <label for="id_proveedor">Proveedor:</label>
            <select class="form-control" id="id_proveedor" name="id_proveedor" required>
                <!-- Aquí deberías llenar los proveedores desde la base de datos -->
                <option value="">Seleccione un proveedor</option>
                <!-- Ejemplo de opciones -->
                <option value="1">Proveedor 1</option>
                <option value="2">Proveedor 2</option>
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

        <h4>Detalles de Entrega</h4>
        <div id="detalles">
            <div class="detalle-item mb-3">
                <select class="form-control mb-2" name="id_materia_prima[]" required>
                    <!-- Aquí deberías llenar las materias primas desde la base de datos -->
                    <option value="">Seleccione materia prima</option>
                    <option value="1">Materia Prima 1</option>
                    <option value="2">Materia Prima 2</option>
                </select>
                <input type="number" class="form-control" name="cantidad[]" placeholder="Cantidad" required>
            </div>
        </div>
        
        <button type="button" class="btn btn-secondary mb-3" onclick="addDetail()">Agregar Detalle</button>

        <button type="submit" class="btn btn-primary">Guardar Entrega</button>
    </form>
</div>

<script>
function addDetail() {
    const detallesDiv = document.getElementById('detalles');
    const newDetail = document.createElement('div');
    newDetail.classList.add('detalle-item', 'mb-3');
    newDetail.innerHTML = 
        <select class="form-control mb-2" name="id_materia_prima[]" required>
            <option value="">Seleccione materia prima</option>
            <option value="1">Materia Prima 1</option>
            <option value="2">Materia Prima 2</option>
        </select>
        <input type="number" class="form-control" name="cantidad[]" placeholder="Cantidad" required>
    ;
    detallesDiv.appendChild(newDetail);
}
</script>
</main>
<?php
include("footer.php");

?>