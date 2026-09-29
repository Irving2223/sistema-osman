<?php
include('header.php');
include('conexion.php');
session_start();

// Verificar si se proporcionó un ID de usuario
if (!isset($_GET['id_usuario'])) {
    $_SESSION['error'] = "ID de usuario no proporcionado";
    header("Location: usuarios.php");
    exit();
}

$id_usuario = intval($_GET['id_usuario']);

// Obtener los datos del usuario
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    $_SESSION['error'] = "Usuario no encontrado";
    header("Location: usuarios.php");
    exit();
}
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h3 class="mt-4">Editar Usuario</h3>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <form class="row g-3 needs-validation" method="post" action="actualizar_usuario.php" novalidate>
                <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
                
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                           value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                    <div class="invalid-feedback">Por favor ingrese un nombre</div>
                </div>
                
                <div class="col-md-6">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" 
                           value="<?php echo htmlspecialchars($usuario['usuario']); ?>" required>
                    <div class="invalid-feedback">Por favor ingrese un nombre de usuario</div>
                </div>
                
                <div class="col-md-6">
                    <label for="clave" class="form-label">Nueva Contraseña (dejar en blanco para no cambiar)</label>
                    <input type="password" class="form-control" id="clave" name="clave">
                    <div class="form-text">Mínimo 6 caracteres</div>
                </div>
                
                <div class="col-md-6">
                    <label for="pregunta" class="form-label">Pregunta de Seguridad</label>
                    <input type="text" class="form-control" id="pregunta" name="pregunta" 
                           value="<?php echo htmlspecialchars($usuario['pregunta']); ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label for="respuesta" class="form-label">Respuesta</label>
                    <input type="text" class="form-control" id="respuesta" name="respuesta" 
                           value="<?php echo htmlspecialchars($usuario['respuesta']); ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label for="tipo" class="form-label">Tipo de Usuario</label>
                    <select class="form-select" id="tipo" name="tipo" required>
                        <option value="admin" <?php echo ($usuario['tipo'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                        <option value="usuario" <?php echo ($usuario['tipo'] == 'usuario') ? 'selected' : ''; ?>>Usuario</option>
                    </select>
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

    <script>
    // Validación del formulario
    (function () {
        'use strict'
        
        var forms = document.querySelectorAll('.needs-validation')
        
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    
                    form.classList.add('was-validated')
                }, false)
            })
    })()
    </script>

<?php include('footer.php'); ?>
