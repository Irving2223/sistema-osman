<?php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $conexion->begin_transaction();
    
    try {
        $id_producto = intval($_POST['id_producto']);
        $fecha_salida = $_POST['fecha_salida'];
        $cantidad_producto = round(floatval($_POST['cantidad_producto']), 2);
        $observaciones = trim($_POST['observaciones']);
        
        if (!$id_producto || !$fecha_salida || $cantidad_producto <= 0) {
            throw new Exception("Datos de producción incompletos o inválidos");
        }
        
        $sql_producto = "SELECT nombre, unidad_medida FROM productos WHERE id_producto = ? AND activo = 1";
        $stmt_producto = $conexion->prepare($sql_producto);
        $stmt_producto->bind_param("i", $id_producto);
        $stmt_producto->execute();
        $producto = $stmt_producto->get_result()->fetch_assoc();
        
        if (!$producto) {
            throw new Exception("Producto no válido o inactivo");
        }
        
        $sql_receta = "SELECT id_materia_prima, cantidad_necesaria FROM recetas WHERE id_producto = ?";
        $stmt_receta = $conexion->prepare($sql_receta);
        $stmt_receta->bind_param("i", $id_producto);
        $stmt_receta->execute();
        $materiales_receta = $stmt_receta->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $materiales = [];
        $cantidades = [];
        
        foreach ($materiales_receta as $item) {
            $materiales[] = intval($item['id_materia_prima']);
            $cantidades[] = round($item['cantidad_necesaria'] * $cantidad_producto, 2);
        }
        
        $id_materias_extra = $_POST['id_materia_prima_extra'] ?? [];
        $cantidades_extra = $_POST['cantidad_utilizada_extra'] ?? [];
        
        if (count($id_materias_extra) != count($cantidades_extra)) {
            throw new Exception("Error en los datos de materiales adicionales");
        }
        
        for ($i = 0; $i < count($id_materias_extra); $i++) {
            $id_extra = intval($id_materias_extra[$i]);
            $cant_extra = round(floatval($cantidades_extra[$i]), 2);
            if ($id_extra > 0 && $cant_extra > 0) {
                $materiales[] = $id_extra;
                $cantidades[] = $cant_extra;
            }
        }
        
        if (count($materiales) == 0) {
            throw new Exception("No se especificaron materiales para la producción");
        }
        
        $errores_stock = [];
        
        $sql_stock = "SELECT cantidad_actual FROM inventario WHERE id_materia_prima = ?";
        $stmt_verificar_stock = $conexion->prepare($sql_stock);
        
        $sql_nombre = "SELECT nombre FROM materias_primas WHERE id_materia_prima = ?";
        $stmt_nombre = $conexion->prepare($sql_nombre);
        
        for ($i = 0; $i < count($materiales); $i++) {
            $id_materia = $materiales[$i];
            $cantidad = $cantidades[$i];
            
            $stmt_verificar_stock->bind_param("i", $id_materia);
            $stmt_verificar_stock->execute();
            $result_stock = $stmt_verificar_stock->get_result();
            
            if ($result_stock->num_rows == 0) {
                $stmt_nombre->bind_param("i", $id_materia);
                $stmt_nombre->execute();
                $materia = $stmt_nombre->get_result()->fetch_assoc();
                $errores_stock[] = "{$materia['nombre']}: No existe en inventario";
                continue;
            }
            
            $stock = $result_stock->fetch_assoc();
            $stock_actual = floatval($stock['cantidad_actual']);
            
            if ($stock_actual < $cantidad) {
                $stmt_nombre->bind_param("i", $id_materia);
                $stmt_nombre->execute();
                $materia = $stmt_nombre->get_result()->fetch_assoc();
                $errores_stock[] = "{$materia['nombre']}: Stock {$stock_actual}, Se requiere {$cantidad}";
            }
        }
        
        if (!empty($errores_stock)) {
            throw new Exception("Stock insuficiente. " . implode(" | ", $errores_stock));
        }
        
        $stmt_salida = $conexion->prepare("INSERT INTO salidas (id_producto, producto, fecha_salida, cantidad_producto, observaciones) VALUES (?, ?, ?, ?, ?)");
        $producto_nombre = $producto['nombre'] . " (Producción)";
        $stmt_salida->bind_param("issds", $id_producto, $producto_nombre, $fecha_salida, $cantidad_producto, $observaciones);
        
        if (!$stmt_salida->execute()) {
            throw new Exception("Error al guardar la producción: " . $stmt_salida->error);
        }
        
        $id_salida = $conexion->insert_id;
        
        $stmt_detalle = $conexion->prepare("INSERT INTO detalle_salidas (id_salida, id_materia_prima, cantidad_utilizada) VALUES (?, ?, ?)");
        $stmt_actualizar_inventario = $conexion->prepare("UPDATE inventario SET cantidad_actual = cantidad_actual - ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id_materia_prima = ?");
        
        for ($i = 0; $i < count($materiales); $i++) {
            $id_materia = $materiales[$i];
            $cantidad = $cantidades[$i];
            
            $stmt_detalle->bind_param("iid", $id_salida, $id_materia, $cantidad);
            if (!$stmt_detalle->execute()) {
                throw new Exception("Error al guardar detalle: " . $stmt_detalle->error);
            }
            
            $stmt_actualizar_inventario->bind_param("di", $cantidad, $id_materia);
            if (!$stmt_actualizar_inventario->execute()) {
                throw new Exception("Error al actualizar inventario: " . $stmt_actualizar_inventario->error);
            }
        }
        
        $stmt_stock_producto = $conexion->prepare("UPDATE productos SET cantidad_stock = cantidad_stock + ? WHERE id_producto = ?");
        $stmt_stock_producto->bind_param("di", $cantidad_producto, $id_producto);
        
        if (!$stmt_stock_producto->execute()) {
            throw new Exception("Error al actualizar stock del producto terminado: " . $stmt_stock_producto->error);
        }
        
        $conexion->commit();
        header("Location: salidas.php?success=1&materiales=" . count($materiales) . "&cantidad=" . $cantidad_producto);
        exit();
        
    } catch (Exception $e) {
        $conexion->rollback();
        header("Location: registro_salidas.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: registro_salidas.php");
    exit();
}
?>