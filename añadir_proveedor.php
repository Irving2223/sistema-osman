<?php
include("header.php");
include("conexion.php");

$message = isset($_GET['message']) ? $_GET['message'] : '';
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h3 class="mt-4">Registrar Nuevo Proveedor</h3>
            <form class="row g-3 needs-validation" method="post" action="guardar_proveedor.php" id="formProveedor" novalidate>
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre del Proveedor</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required pattern="[A-Za-z\s]+" title="Solo se permiten letras.">
                    <div class="invalid-feedback">Por favor, ingresa el nombre del proveedor.</div>
                </div>
                <div class="col-md-6">
                    <label for="rif" class="form-label">RIF</label>
                    <input type="text" class="form-control" id="rif" name="rif" required>
                    <div class="invalid-feedback" id="rif-feedback">Por favor ingresa un RIF válido</div>
                    <div class="valid-feedback">RIF disponible</div>
                    <?php if (!empty($message)): ?>
                        <div class="text-danger mt-2"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="correo" class="form-label">Correo</label>
                    <input type="email" class="form-control" id="correo" name="correo" required>
                    <div class="invalid-feedback">Por favor ingresa un correo válido</div>
                </div>
                <div class="col-md-6">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" maxlength="11" required pattern="\d+" title="Solo se permiten números.">
                    <div class="invalid-feedback">Por favor ingresa un número de teléfono válido</div>
                </div>
                
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-primary">Guardar Proveedor</button>
                    <button type="reset" class="btn btn-secondary">Limpiar Información</button>
                </div>
            </form>
        </div>
    </main>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const rifInput = document.getElementById('rif');
        const form = document.getElementById('formProveedor');
        let rifExiste = false;

        // Función para verificar si el RIF ya existe
        async function verificarRif(rif) {
            if (!rif.trim()) return false;
            try {
                const response = await fetch(`verificar_rif.php?rif=${encodeURIComponent(rif)}`);
                const data = await response.json();
                return data.existe;
            } catch (error) {
                console.error('Error al verificar el RIF:', error);
                return false;
            }
        }

        // Validar RIF al perder el foco
        rifInput.addEventListener('blur', async function() {
            if (this.value.trim() !== '') {
                rifExiste = await verificarRif(this.value);
                const feedback = this.nextElementSibling;
                const validFeedback = this.nextElementSibling.nextElementSibling;
                
                if (rifExiste) {
                    this.setCustomValidity('Este RIF ya está registrado');
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                    feedback.style.display = 'block';
                    validFeedback.style.display = 'none';
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                    feedback.style.display = 'none';
                    validFeedback.style.display = 'block';
                }
                this.reportValidity();
            }
        });

        // Validar antes de enviar el formulario
        form.addEventListener('submit', async function(e) {
            if (rifInput.value.trim() !== '') {
                rifExiste = await verificarRif(rifInput.value);
                if (rifExiste) {
                    e.preventDefault();
                    rifInput.setCustomValidity('Este RIF ya está registrado');
                    rifInput.classList.add('is-invalid');
                    rifInput.reportValidity();
                }
            }
        });
    });
    </script>

<?php
include("footer.php");
?>
