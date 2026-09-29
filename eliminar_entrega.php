<?php
include("conexion.php");

if (isset($_GET['id_entrega'])) {
    $id_entrega = intval($_GET['id_entrega']);
    
    $conexion->begin_transaction();
    
    try {
        // 1. Obtener detalles para revertir inventario
        $sql_detalles = "SELECT id_materia_prima, cantidad FROM detalle_entregas WHERE id_entrega = ?";
        $stmt_detalles = $conexion->prepare($sql_detalles);
        $stmt_detalles->bind_param("i", $id_entrega);
        $stmt_detalles->execute();
        $detalles = $stmt_detalles->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // 2. Revertir inventario
        foreach ($detalles as $detalle) {
            $stmt_inventario = $conexion->prepare("UPDATE inventario SET cantidad_actual = cantidad_actual - ? WHERE id_materia_prima = ?");
            $stmt_inventario->bind_param("di", $detalle['cantidad'], $detalle['id_materia_prima']);
            $stmt_inventario->execute();
        }
        
        // 3. Eliminar detalles
        $stmt_eliminar_detalles = $conexion->prepare("DELETE FROM detalle_entregas WHERE id_entrega = ?");
        $stmt_eliminar_detalles->bind_param("i", $id_entrega);
        $stmt_eliminar_detalles->execute();
        
        // 4. Eliminar entrega
        $stmt_eliminar_entrega = $conexion->prepare("DELETE FROM entregas WHERE id_entrega = ?");
        $stmt_eliminar_entrega->bind_param("i", $id_entrega);
        $stmt_eliminar_entrega->execute();
        
        $conexion->commit();
        header("Location: entregas.php?success=1&action=delete");
        exit();
        
    } catch (Exception $e) {
        $conexion->rollback();
        header("Location: entregas.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: entregas.php");
    exit();
}
?>