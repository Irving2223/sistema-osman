<?php
include("header.php");
include("conexion.php");


$message = isset($_GET['message']) ? $_GET['message'] : '';
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h3 class="mt-4">Añadir Usuario</h3>
            <form class="row g-3 needs-validation" method="post" action="guardar_usuario.php" id="#myform">
                <div class="col-md-6">
                    <label for="id_usuario" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required pattern="[A-Za-z\s]+" title="Solo se permiten letras.">
                    <div class="invalid-feedback">Por favor, ingresa el nombre del proveedor.</div>

                </div>
                <div class="col-md-6">
                    <label for="contacto" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" maxlength="11" required>
                    <div class="invalid-feedback">Datos Incorrecto</div>

                    <?php if (!empty($message)): ?>
                    <div style="color: red;"><?php echo $message; ?></div>
                 <?php endif; ?>
                
                </div>
                <div class="col-md-6">
                    <label for="contacto" class="form-label">Clave</label>
                    <input type="password" class="form-control" id="clave" name="clave" required autofocus>
                    <div class="invalid-feedback">Datos Incorrecto</div>

                </div>
                <div class="col-md-6">
                    <label for="contacto" class="form-label">Pregunta</label>
                    <input type="text" class="form-control" id="pregunta" name="pregunta" maxlength="30" required>
                    <div class="invalid-feedback">Datos Incorrecto</div>
                </div>
                <div class="col-md-6">
                    <label for="contacto" class="form-label">Respuesta</label>
                    <input type="text" class="form-control" id="respuesta" name="respuesta" maxlength="30" required>
                    <div class="invalid-feedback">Datos Incorrecto</div>
                </div>
                <div class="col-md-6">
                    <label for="direccion" class="form-label">Tipo</label>
                    <select name="tipo" class="form-select" id="id_usuario" required>
                     <option value="">Seleccione</option>
                    <option value="admin">Administrador</option>
                    <option value="usuario">Usuario</option>
                
                </select>
                </div>
                
                
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
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
