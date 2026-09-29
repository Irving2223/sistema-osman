<?php
header('Content-Type: application/json');
include('conexion.php');

if (!isset($_GET['rif'])) {
    echo json_encode(['existe' => false]);
    exit();
}

$rif = trim($_GET['rif']);
$existe = false;

try {
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM proveedores WHERE rif = ?");
    $stmt->bind_param("s", $rif);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $existe = ($row['total'] > 0);
} catch (Exception $e) {
    error_log("Error al verificar RIF: " . $e->getMessage());
}

echo json_encode(['existe' => $existe]);

if (isset($stmt)) {
    $stmt->close();
}
$conexion->close();
?>
