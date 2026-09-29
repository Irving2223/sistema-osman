<?php

session_start();
include('conexion.php');

// Verificar si el RIF ya existe
$rif = trim($_POST['rif'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');

// Validar que todos los campos requeridos estén presentes
if (empty($nombre) || empty($rif) || empty($correo) || empty($telefono)) {
    $_SESSION['error'] = "Todos los campos son obligatorios";
    header("Location: añadir_proveedor.php");
    exit();
}

// Verificar si el RIF ya existe
try {
    $stmt = $conexion->prepare("SELECT id_proveedor FROM proveedores WHERE rif = ?");
    $stmt->bind_param("s", $rif);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "El RIF ingresado ya está registrado";
        header("Location: añadir_proveedor.php");
        exit();
    }
    $stmt->close();

    // Si no existe, proceder con la inserción
    $stmt = $conexion->prepare("INSERT INTO proveedores (nombre, rif, correo, telefono) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $rif, $correo, $telefono);

    if ($stmt->execute()) {
        $_SESSION['status'] = "Proveedor Agregado";
        header("Location: proveedores.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: No se pudo guardar el registro";
        header("Location: añadir_proveedor.php");
        exit();
    }
} catch (Exception $e) {
    error_log("Error al guardar proveedor: " . $e->getMessage());
    $_SESSION['error'] = "Error: No se pudo guardar el registro";
    header("Location: añadir_proveedor.php");
    exit();
}
?>