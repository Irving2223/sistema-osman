<?php
include("conexion.php");

if (isset($_GET['id_salida'])) {
    $id_salida = intval($_GET['id_salida']);
    
    $conexion->begin_transaction();
    
    try {
        $sql_salida = "SELECT id_producto, cantidad_producto FROM salidas WHERE id_salida = ?";
        $stmt_salida_info = $conexion->prepare($sql_salida);
        $stmt_salida_info->bind_param("i", $id_salida);
        $stmt_salida_info->execute();
        $salida = $stmt_salida_info->get_result()->fetch_assoc();
        
        if (!$salida) {
            throw new Exception("Salida no encontrada");
        }
        
        $sql_detalles = "SELECT id_materia_prima, cantidad_utilizada FROM detalle_salidas WHERE id_salida = ?";
        $stmt_detalles = $conexion->prepare($sql_detalles);
        $stmt_detalles->bind_param("i", $id_salida);
        $stmt_detalles->execute();
        $detalles = $stmt_detalles->get_result()->fetch_all(MYSQLI_ASSOC);
        
        foreach ($detalles as $detalle) {
            $stmt_inventario = $conexion->prepare("UPDATE inventario SET cantidad_actual = cantidad_actual + ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id_materia_prima = ?");
            $stmt_inventario->bind_param("di", $detalle['cantidad_utilizada'], $detalle['id_materia_prima']);
            
            if (!$stmt_inventario->execute()) {
                throw new Exception("Error al revertir inventario para materia prima ID: " . $detalle['id_materia_prima']);
            }
        }
        
        if ($salida['id_producto'] && $salida['id_producto'] > 0 && floatval($salida['cantidad_producto']) > 0) {
            $stmt_stock_producto = $conexion->prepare("UPDATE productos SET cantidad_stock = GREATEST(cantidad_stock - ?, 0) WHERE id_producto = ?");
            $stmt_stock_producto->bind_param("di", $salida['cantidad_producto'], $salida['id_producto']);
            
            if (!$stmt_stock_producto->execute()) {
                throw new Exception("Error al revertir stock del producto terminado");
            }
        }
        
        $stmt_eliminar_detalles = $conexion->prepare("DELETE FROM detalle_salidas WHERE id_salida = ?");
        $stmt_eliminar_detalles->bind_param("i", $id_salida);
        
        if (!$stmt_eliminar_detalles->execute()) {
            throw new Exception("Error al eliminar detalles de la salida");
        }
        
        $stmt_eliminar_salida = $conexion->prepare("DELETE FROM salidas WHERE id_salida = ?");
        $stmt_eliminar_salida->bind_param("i", $id_salida);
        
        if (!$stmt_eliminar_salida->execute()) {
            throw new Exception("Error al eliminar la salida");
        }
        
        $conexion->commit();
        header("Location: salidas.php?success=1&action=delete");
        exit();
        
    } catch (Exception $e) {
        $conexion->rollback();
        header("Location: salidas.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: salidas.php");
    exit();
}
?>