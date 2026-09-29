<?php
include("header.php");
include("conexion.php");


$message = isset($_GET['message']) ? $_GET['message'] : '';
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h3 class="mt-4">Registrar Materia</h3>
            <form class="row g-3 needs-validation" method="post" action="guardar_materia_prima.php" id="#myform">
                <div class="col-md-6">
                    <label for="nombre_proveedor" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                    <div class="invalid-feedback">Por favor, ingresa el nombre del proveedor.</div>

                </div>
                <div class="col-md-6">
                    <label for="contacto" class="form-label">Unidad </label>
                    <select name="unidad" id="unidad" class="form-control">
                    <option value="0">Seleccione</option>
                    <option value="L">L</option>
                    <option value="ml">ml</option>
                    <option value="Kg">Kg</option>
                    <option value="gr">gr</option>

                    </select>
                    <div class="invalid-feedback">Datos Incorrecto</div>

                   
                
                </div>
                <div class="col-md-6">
                    <label for="contacto" class="form-label">Descripcion</label>
                    <input type="text" class="form-control" id="descripcion" name="descripcion" required autofocus>
                    <div class="invalid-feedback">Datos Incorrecto</div>
                </div>
                
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-primary">Guardar Materia</button>
                    <button type="reset" class="btn btn-secondary">Limpiar Informacion</button>
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
