<?php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto = intval($_POST['id_producto']);
    
    $conexion->begin_transaction();
    
    try {
        // 1. VALIDAR DATOS
        $id_recetas = $_POST['id_receta'] ?? [];
        $id_materias_primas = $_POST['id_materia_prima'] ?? [];
        $cantidades_necesarias = $_POST['cantidad_necesaria'] ?? [];
        $instrucciones = $_POST['instrucciones'] ?? [];
        
        if (count($id_materias_primas) == 0) {
            throw new Exception("Debe mantener al menos un material en la receta");
        }
        
        // 2. ELIMINAR RECETA ACTUAL
        $stmt_eliminar = $conexion->prepare("DELETE FROM recetas WHERE id_producto = ?");
        $stmt_eliminar->bind_param("i", $id_producto);
        
        if (!$stmt_eliminar->execute()) {
            throw new Exception("Error al eliminar receta anterior: " . $stmt_eliminar->error);
        }
        
        // 3. INSERTAR NUEVA RECETA
        $stmt_insertar = $conexion->prepare("INSERT INTO recetas (id_producto, id_materia_prima, cantidad_necesaria, instrucciones) VALUES (?, ?, ?, ?)");
        
        $items_guardados = 0;
        
        for ($i = 0; $i < count($id_materias_primas); $i++) {
            $id_materia_prima = intval($id_materias_primas[$i]);
            $cantidad_necesaria = floatval($cantidades_necesarias[$i]);
            $instruccion = trim($instrucciones[$i] ?? '');
            
            if ($id_materia_prima > 0 && $cantidad_necesaria > 0) {
                $stmt_insertar->bind_param("iids", $id_producto, $id_materia_prima, $cantidad_necesaria, $instruccion);
                
                if (!$stmt_insertar->execute()) {
                    throw new Exception("Error al guardar item de receta: " . $stmt_insertar->error);
                }
                
                $items_guardados++;
            }
        }
        
        if ($items_guardados == 0) {
            throw new Exception("No se guardó ningún item válido en la receta");
        }
        
        $conexion->commit();
        header("Location: productos.php?success=1&action=edit_receta&items=" . $items_guardados);
        exit();
        
    } catch (Exception $e) {
        $conexion->rollback();
        header("Location: editar_receta.php?id_producto=" . $id_producto . "&error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: productos.php");
    exit();
}
?>