<?php
include('conexion.php');
session_start();

// Verificar si se proporcionó un ID de usuario
if (!isset($_GET['id_usuario'])) {
    $_SESSION['error'] = "ID de usuario no proporcionado";
    header("Location: usuarios.php");
    exit();
}

$id_usuario = intval($_GET['id_usuario']);

try {
    // Verificar si el usuario existe
    $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("El usuario no existe");
    }

    // Eliminar el usuario
    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Usuario eliminado correctamente";
    } else {
        throw new Exception("Error al eliminar el usuario");
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header("Location: usuarios.php");
exit();
?>
