<?php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $conexion->begin_transaction();
    
    try {
        // 1. VALIDAR Y GUARDAR PRODUCTO
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $unidad_medida = $_POST['unidad_medida'];
        $precio_venta = !empty($_POST['precio_venta']) ? floatval($_POST['precio_venta']) : null;
        
        if (empty($nombre)) {
            throw new Exception("El nombre del producto es obligatorio");
        }
        
        // Insertar producto
        $stmt_producto = $conexion->prepare("INSERT INTO productos (nombre, descripcion, unidad_medida, precio_venta) VALUES (?, ?, ?, ?)");
        $stmt_producto->bind_param("sssd", $nombre, $descripcion, $unidad_medida, $precio_venta);
        
        if (!$stmt_producto->execute()) {
            throw new Exception("Error al guardar el producto: " . $stmt_producto->error);
        }
        
        // Obtener ID del producto insertado
        $id_producto = $conexion->insert_id;
        
        // 2. VALIDAR Y GUARDAR RECETA
        if (!isset($_POST['id_materia_prima']) || !isset($_POST['cantidad_necesaria'])) {
            throw new Exception("Debe agregar al menos una materia prima a la receta");
        }
        
        $id_materias_primas = $_POST['id_materia_prima'];
        $cantidades_necesarias = $_POST['cantidad_necesaria'];
        $instrucciones = $_POST['instrucciones'] ?? [];
        
        if (count($id_materias_primas) != count($cantidades_necesarias)) {
            throw new Exception("Error en los datos de la receta");
        }
        
        // Preparar statement para receta
        $stmt_receta = $conexion->prepare("INSERT INTO recetas (id_producto, id_materia_prima, cantidad_necesaria, instrucciones) VALUES (?, ?, ?, ?)");
        
        $items_receta_guardados = 0;
        
        // Insertar cada item de la receta
        for ($i = 0; $i < count($id_materias_primas); $i++) {
            $id_materia_prima = intval($id_materias_primas[$i]);
            $cantidad_necesaria = floatval($cantidades_necesarias[$i]);
            $instruccion = trim($instrucciones[$i] ?? '');
            
            if ($id_materia_prima > 0 && $cantidad_necesaria > 0) {
                $stmt_receta->bind_param("iids", $id_producto, $id_materia_prima, $cantidad_necesaria, $instruccion);
                
                if (!$stmt_receta->execute()) {
                    throw new Exception("Error al guardar item de receta: " . $stmt_receta->error);
                }
                
                $items_receta_guardados++;
            }
        }
        
        // Validar que se guardó al menos un item de receta
        if ($items_receta_guardados == 0) {
            throw new Exception("No se guardó ningún item válido en la receta");
        }
        
        $conexion->commit();
        header("Location: registro_producto.php?success=1&producto=" . urlencode($nombre) . "&items=" . $items_receta_guardados);
        exit();
        
    } catch (Exception $e) {
        $conexion->rollback();
        header("Location: registro_producto.php?error=" . urlencode($e->getMessage()));
        exit();
    }
    
} else {
    header("Location: registro_producto.php");
    exit();
}
?>