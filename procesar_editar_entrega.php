<?php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_entrega = intval($_POST['id_entrega']);
    
    $conexion->begin_transaction();
    
    try {
        // 1. Actualizar datos principales de la entrega
        $id_proveedor = intval($_POST['id_proveedor']);
        $fecha_entrega = $_POST['fecha_entrega'];
        $numero_factura = trim($_POST['numero_factura']);
        
        $stmt_entrega = $conexion->prepare("UPDATE entregas SET id_proveedor = ?, fecha_entrega = ?, numero_factura = ? WHERE id_entrega = ?");
        $stmt_entrega->bind_param("issi", $id_proveedor, $fecha_entrega, $numero_factura, $id_entrega);
        
        if (!$stmt_entrega->execute()) {
            throw new Exception("Error al actualizar la entrega: " . $stmt_entrega->error);
        }
        
        // 2. Procesar detalles - CORREGIDO
        $id_detalles = $_POST['id_detalle'] ?? [];
        $id_materias_primas = $_POST['id_materia_prima'] ?? [];
        $cantidades = $_POST['cantidad'] ?? [];
        
        // Obtener detalles actuales ANTES de cualquier cambio
        $sql_detalles_actuales = "SELECT id_detalle_entrega, id_materia_prima, cantidad FROM detalle_entregas WHERE id_entrega = ?";
        $stmt_actual = $conexion->prepare($sql_detalles_actuales);
        $stmt_actual->bind_param("i", $id_entrega);
        $stmt_actual->execute();
        $detalles_actuales = $stmt_actual->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Crear array de detalles actuales para fácil acceso
        $detalles_por_materia = [];
        foreach ($detalles_actuales as $detalle) {
            $detalles_por_materia[$detalle['id_materia_prima']] = $detalle['cantidad'];
        }
        
        // Preparar statements
        $stmt_eliminar_detalle = $conexion->prepare("DELETE FROM detalle_entregas WHERE id_detalle_entrega = ?");
        $stmt_insertar_detalle = $conexion->prepare("INSERT INTO detalle_entregas (id_entrega, id_materia_prima, cantidad) VALUES (?, ?, ?)");
        $stmt_actualizar_inventario = $conexion->prepare("UPDATE inventario SET cantidad_actual = cantidad_actual + ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id_materia_prima = ?");
        
        // Primero: Revertir todo el stock de la entrega original
        foreach ($detalles_actuales as $detalle_actual) {
            $cantidad_a_revertir = $detalle_actual['cantidad'];
            $id_materia_actual = $detalle_actual['id_materia_prima'];
            
            // RESTAR la cantidad original del inventario (revertir)
            $stmt_actualizar_inventario->bind_param("di", $cantidad_a_revertir, $id_materia_actual);
            if (!$stmt_actualizar_inventario->execute()) {
                throw new Exception("Error al revertir inventario: " . $stmt_actualizar_inventario->error);
            }
        }
        
        // Segundo: Eliminar todos los detalles actuales
        foreach ($detalles_actuales as $detalle_actual) {
            $stmt_eliminar_detalle->bind_param("i", $detalle_actual['id_detalle_entrega']);
            if (!$stmt_eliminar_detalle->execute()) {
                throw new Exception("Error al eliminar detalle: " . $stmt_eliminar_detalle->error);
            }
        }
        
        // Tercero: Insertar nuevos detalles y actualizar inventario con nuevas cantidades
        for ($i = 0; $i < count($id_materias_primas); $i++) {
            $id_materia_prima = intval($id_materias_primas[$i]);
            $nueva_cantidad = floatval($cantidades[$i]);
            
            if ($id_materia_prima > 0 && $nueva_cantidad > 0) {
                // Insertar nuevo detalle
                $stmt_insertar_detalle->bind_param("iid", $id_entrega, $id_materia_prima, $nueva_cantidad);
                if (!$stmt_insertar_detalle->execute()) {
                    throw new Exception("Error al insertar detalle: " . $stmt_insertar_detalle->error);
                }
                
                // SUMAR la nueva cantidad al inventario
                // Nota: Aquí SUMAMOS porque ya revertimos (restamos) todo anteriormente
                $stmt_actualizar_inventario->bind_param("di", $nueva_cantidad, $id_materia_prima);
                if (!$stmt_actualizar_inventario->execute()) {
                    throw new Exception("Error al actualizar inventario: " . $stmt_actualizar_inventario->error);
                }
            }
        }
        
        $conexion->commit();
        header("Location: entregas.php?success=1&action=edit");
        exit();
        
    } catch (Exception $e) {
        $conexion->rollback();
        header("Location: editar_entrega.php?id_entrega=" . $id_entrega . "&error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: entregas.php");
    exit();
}
?>