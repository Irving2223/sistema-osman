<?php
include("conexion.php");

// Verificar si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Iniciar transacción para consistencia de datos
    $conexion->begin_transaction();
    
    try {
        // 1. VALIDAR Y OBTENER DATOS PRINCIPALES DE LA SALIDA
        $producto = trim($_POST['producto']);
        $fecha_salida = $_POST['fecha_salida'];
        
        // Validaciones básicas
        if (empty($producto) || $producto === "0") {
            throw new Exception("Debe seleccionar un producto");
        }
        
        if (empty($fecha_salida)) {
            throw new Exception("La fecha de salida es obligatoria");
        }
        
        // 2. INSERTAR EN TABLA 'salidas'
        $stmt_salida = $conexion->prepare("INSERT INTO salidas (producto, fecha_salida) VALUES (?, ?)");
        $stmt_salida->bind_param("ss", $producto, $fecha_salida);
        
        if (!$stmt_salida->execute()) {
            throw new Exception("Error al guardar la salida: " . $stmt_salida->error);
        }
        
        // 3. OBTENER EL ID DE LA SALIDA RECIÉN INSERTADA
        $id_salida = $conexion->insert_id;
        
        // 4. VALIDAR Y PROCESAR DETALLES DE MATERIAS PRIMAS
        if (!isset($_POST['id_materia_prima']) || !isset($_POST['cantidad'])) {
            throw new Exception("No se recibieron materiales utilizados");
        }
        
        $id_materias_primas = $_POST['id_materia_prima'];
        $cantidades = $_POST['cantidad'];
        
        // Validar que haya la misma cantidad de materias primas y cantidades
        if (count($id_materias_primas) != count($cantidades)) {
            throw new Exception("Error en los datos: cantidad de materiales no coincide");
        }
        
        // 5. PREPARAR STATEMENTS
        $stmt_detalle = $conexion->prepare("INSERT INTO detalle_salidas (id_salida, id_materia_prima, cantidad) VALUES (?, ?, ?)");
        $stmt_check_inventario = $conexion->prepare("SELECT id_inventario, cantidad_actual FROM inventario WHERE id_materia_prima = ?");
        $stmt_update_inventario = $conexion->prepare("UPDATE inventario SET cantidad_actual = cantidad_actual - ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id_materia_prima = ?");
        $stmt_insert_inventario = $conexion->prepare("INSERT INTO inventario (id_materia_prima, cantidad_actual) VALUES (?, ?)");
        
        $detalles_guardados = 0;
        $errores_stock = [];
        
        // 6. INSERTAR CADA DETALLE Y ACTUALIZAR INVENTARIO
        for ($i = 0; $i < count($id_materias_primas); $i++) {
            $id_materia_prima = filter_var($id_materias_primas[$i], FILTER_VALIDATE_INT);
            $cantidad = filter_var($cantidades[$i], FILTER_VALIDATE_FLOAT);
            
            // Validar datos del detalle
            if ($id_materia_prima && $cantidad && $cantidad > 0) {
                
                // 6.1. VERIFICAR STOCK DISPONIBLE
                $stmt_check_inventario->bind_param("i", $id_materia_prima);
                $stmt_check_inventario->execute();
                $result_inventario = $stmt_check_inventario->get_result();
                
                $stock_suficiente = true;
                $stock_actual = 0;
                
                if ($result_inventario->num_rows > 0) {
                    $inventario = $result_inventario->fetch_assoc();
                    $stock_actual = $inventario['cantidad_actual'];
                    
                    // Verificar si hay stock suficiente
                    if ($stock_actual < $cantidad) {
                        $stock_suficiente = false;
                        
                        // Obtener nombre de la materia prima para el mensaje de error
                        $sql_nombre = "SELECT nombre FROM materias_primas WHERE id_materia_prima = ?";
                        $stmt_nombre = $conexion->prepare($sql_nombre);
                        $stmt_nombre->bind_param("i", $id_materia_prima);
                        $stmt_nombre->execute();
                        $result_nombre = $stmt_nombre->get_result();
                        $materia_prima = $result_nombre->fetch_assoc();
                        $nombre_materia = $materia_prima['nombre'];
                        
                        $errores_stock[] = "Stock insuficiente para: $nombre_materia. Stock actual: $stock_actual, Se requiere: $cantidad";
                        $stmt_nombre->close();
                    }
                } else {
                    $stock_suficiente = false;
                    $errores_stock[] = "No existe stock para la materia prima ID: $id_materia_prima";
                }
                
                // Si hay stock suficiente, procesar el detalle
                if ($stock_suficiente) {
                    // 6.2. INSERTAR DETALLE DE SALIDA
                    $stmt_detalle->bind_param("iid", $id_salida, $id_materia_prima, $cantidad);
                    
                    if (!$stmt_detalle->execute()) {
                        throw new Exception("Error al guardar detalle: " . $stmt_detalle->error);
                    }
                    
                    // 6.3. ACTUALIZAR INVENTARIO (RESTAR CANTIDAD)
                    $stmt_update_inventario->bind_param("di", $cantidad, $id_materia_prima);
                    if (!$stmt_update_inventario->execute()) {
                        throw new Exception("Error al actualizar inventario: " . $stmt_update_inventario->error);
                    }
                    
                    $detalles_guardados++;
                }
            }
        }
        
        // Validar que se guardó al menos un detalle
        if ($detalles_guardados == 0) {
            if (!empty($errores_stock)) {
                throw new Exception(implode(" | ", $errores_stock));
            } else {
                throw new Exception("No se guardó ningún detalle válido");
            }
        }
        
        // Mostrar advertencias si hubo problemas de stock en algunos items
        $mensaje_advertencia = "";
        if (!empty($errores_stock)) {
            $mensaje_advertencia = "&advertencia=" . urlencode("Algunos materiales no se procesaron por stock insuficiente. Detalles: " . implode("; ", $errores_stock));
        }
        
        // 7. CONFIRMAR TRANSACCIÓN
        $conexion->commit();
        
        // 8. REDIRIGIR CON MENSAJE DE ÉXITO
        header("Location: salidas.php?success=1&id_salida=" . $id_salida . "&detalles=" . $detalles_guardados . $mensaje_advertencia);
        exit();
        
    } catch (Exception $e) {
        // 9. REVERTIR TRANSACCIÓN EN CASO DE ERROR
        $conexion->rollback();
        
        // 10. REDIRIGIR CON MENSAJE DE ERROR
        header("Location: salidas.php?error=" . urlencode($e->getMessage()));
        exit();
    }
    
    // Cerrar statements
    if (isset($stmt_salida)) $stmt_salida->close();
    if (isset($stmt_detalle)) $stmt_detalle->close();
    if (isset($stmt_check_inventario)) $stmt_check_inventario->close();
    if (isset($stmt_update_inventario)) $stmt_update_inventario->close();
    if (isset($stmt_insert_inventario)) $stmt_insert_inventario->close();
    
} else {
    // Si no es POST, redirigir al formulario
    header("Location: registro_salidas.php");
    exit();
}

$conexion->close();
?>