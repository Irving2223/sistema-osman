<?php 

include('header.php'); 
include('conexion.php'); 

$id_proveedor = $_GET['id_proveedor'];


$sql = "select * from proveedores where id_proveedor = '$id_proveedor'";


$result = mysqli_query($conexion, $sql);

$row = mysqli_fetch_assoc($result);


?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h3 class="mt-4">Editar Proveedor</h3>
            <form class="row g-3 needs-validation" method="post" action="update_proveedor.php" id="#myform">
                <div class="col-md-6">
                    <label for="nombre_proveedor" class="form-label">Nombre del Proveedor</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required value="<?php echo htmlspecialchars($row['nombre']); ?>">
                    <div class="invalid-feedback">Por favor, ingresa el nombre del proveedor.</div>

                </div>
                <div class="col-md-6">
                    <label for="contacto" class="form-label">Rif </label>
                    <input type="text" class="form-control" id="rif" name="rif" required value="<?php echo htmlspecialchars($row['rif']); ?>">
                    <div class="invalid-feedback">Datos Incorrecto</div>

                    <?php if (!empty($message)): ?>
                    <div style="color: red;"><?php echo $message; ?></div>
                 <?php endif; ?>
                
                </div>
                <div class="col-md-6">
                    <label for="contacto" class="form-label">Correo</label>
                    <input type="text" class="form-control" id="correo" name="correo" required autofocus value="<?php echo htmlspecialchars($row['correo']); ?>">
                    <div class="invalid-feedback">Datos Incorrecto</div>

                </div>
                <div class="col-md-6">
                    <label for="direccion" class="form-label">Telefono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" required value="<?php echo htmlspecialchars($row['telefono']); ?>">
                    <div class="invalid-feedback">Datos Incorrecto</div>
                </div>
                
                <div class="col-12 text-center mt-4">
                    <input type="hidden" name="id_proveedor" value="<?php echo $row['id_proveedor']; ?>">
                    <button type="submit" class="btn btn-primary">Editar Proveedor</button>
                     <button  class="btn btn-secondary"><a href="proveedores.php">Volver</a></button>
                </div>
            </form>
        </div>
    </main>
    <script type="text/javascript">
$(document).ready(function() {
    $('#myform').on('submit', function(event) {
        event.preventDefault(); // Evita que el formulario se envíe de inmediato

        // Limpia mensajes de error
        $('#errorNombre').text('');
       
        $('#errorEmail').text('');

        let esValido = true;

        // Validar nombre
        const nombre = $('#nombre').val().trim();
        if (nombre === '') {
            $('#errorNombre').text('El nombre es obligatorio.');
            esValido = false;
        }
        // Validar cédula
      
     

        // Validar correo electrónico
        const email = $('#correo').val().trim();
        const regexEmail = /^[^s@]+@[^s@]+.[^s@]+$/; // Expresión regular para validar correo
        if (email === '') {
            $('#errorEmail').text('El correo electrónico es obligatorio.');
            esValido = false;
        } else if (!regexEmail.test(email)) {
            $('#errorEmail').text('Introduce un correo electrónico válido.');
            esValido = false;
        }

        // Si es válido, confirmar envío
        if (esValido) {
            const confirmarEnvio = confirm('¿Estás seguro de que deseas enviar el formulario?');
            if (confirmarEnvio) {
                this.submit();
            } else {
                // Si el usuario cancela, no hacemos nada y dejamos los campos llenos
                return; // Salimos de la función sin hacer nada
            }
        }
    });
});
</script>

<?php
include("footer.php");
?>
