<?php
include('conexion.php');
session_start();
$id_usuario = intval($_POST['id_usuario']);
$nombre = trim(htmlspecialchars($_POST['nombre']));
$usuario = trim(htmlspecialchars($_POST['usuario']));
$pregunta = trim(htmlspecialchars($_POST['pregunta']));
$respuesta = trim(htmlspecialchars($_POST['respuesta']));
$tipo = $_POST['tipo'];

// Validar que el tipo sea válido
if (!in_array($tipo, ['admin', 'usuario'])) {
    $_SESSION['error'] = "Tipo de usuario no válido";
    header("Location: editar_usuario.php?id_usuario=" . $id_usuario);
    exit();
}

try {
    // Verificar si el nuevo nombre de usuario ya existe (excluyendo el usuario actual)
    $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario = ? AND id_usuario != ?");
    $stmt->bind_param("si", $usuario, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        throw new Exception("El nombre de usuario ya está en uso");
    }

    // Actualizar los datos del usuario
    if (!empty($_POST['clave'])) {
        // Si se proporcionó una nueva contraseña, actualizarla
        if (strlen($_POST['clave']) < 6) {
            throw new Exception("La contraseña debe tener al menos 6 caracteres");
        }
        $clave = md5($_POST['clave']);
        $sql = "UPDATE usuarios SET nombre = ?, usuario = ?, clave = ?, pregunta = ?, respuesta = ?, tipo = ? WHERE id_usuario = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssssi", $nombre, $usuario, $clave, $pregunta, $respuesta, $tipo, $id_usuario);
    } else {
        // Si no se proporcionó una nueva contraseña, no actualizarla
        $sql = "UPDATE usuarios SET nombre = ?, usuario = ?, pregunta = ?, respuesta = ?, tipo = ? WHERE id_usuario = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssi", $nombre, $usuario, $pregunta, $respuesta, $tipo, $id_usuario);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Usuario actualizado correctamente";
        header("Location: usuarios.php");
        exit();
    } else {
        throw new Exception("Error al actualizar el usuario: " . $conexion->error);
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: editar_usuario.php?id_usuario=" . $id_usuario);
    exit();
}
?>
