<?php
include("conexion.php");

// Verificar si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Iniciar transacción para asegurar la consistencia de datos
    $conexion->begin_transaction();
    
    try {
        // 1. VALIDAR Y OBTENER DATOS DE LA ENTREGA
        $id_proveedor = filter_var($_POST['id_proveedor'], FILTER_VALIDATE_INT);
        $fecha_entrega = $_POST['fecha_entrega'];
        $numero_factura = trim($_POST['numero_factura']);
        
        // Validaciones básicas
        if (!$id_proveedor || !$fecha_entrega) {
            throw new Exception("Datos de entrega incompletos o inválidos");
        }
        
        // Verificar si el número de factura ya existe (si se proporcionó uno)
        if (!empty($numero_factura)) {
            $sql_check = "SELECT id_entrega FROM entregas WHERE numero_factura = ?";
            $stmt_check = $conexion->prepare($sql_check);
            $stmt_check->bind_param("s", $numero_factura);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows > 0) {
                throw new Exception("El número de factura ya existe en el sistema. Por favor, verifica el número o deja el campo en blanco si no aplica.");
            }
        }
        
        // 2. INSERTAR EN TABLA 'entregas'
        $stmt_entrega = $conexion->prepare("INSERT INTO entregas (id_proveedor, fecha_entrega, numero_factura) VALUES (?, ?, ?)");
        $stmt_entrega->bind_param("iss", $id_proveedor, $fecha_entrega, $numero_factura);
        
        if (!$stmt_entrega->execute()) {
            throw new Exception("Error al guardar la entrega: " . $stmt_entrega->error);
        }
        
        // 3. OBTENER EL ID DE LA ENTREGA RECIÉN INSERTADA
        $id_entrega = $conexion->insert_id;
        
        // 4. VALIDAR Y PROCESAR DETALLES DE LA ENTREGA
        if (!isset($_POST['id_materia_prima']) || !isset($_POST['cantidad'])) {
            throw new Exception("No se recibieron detalles de la entrega");
        }
        
        $id_materias_primas = $_POST['id_materia_prima'];
        $cantidades = $_POST['cantidad'];
        
        // Validar que haya la misma cantidad de materias primas y cantidades
        if (count($id_materias_primas) != count($cantidades)) {
            throw new Exception("Error en los datos de detalles: cantidad de items no coincide");
        }
        
        // 5. PREPARAR STATEMENTS
        $stmt_detalle = $conexion->prepare("INSERT INTO detalle_entregas (id_entrega, id_materia_prima, cantidad) VALUES (?, ?, ?)");
        $stmt_check_inventario = $conexion->prepare("SELECT id_inventario, cantidad_actual FROM inventario WHERE id_materia_prima = ?");
        $stmt_insert_inventario = $conexion->prepare("INSERT INTO inventario (id_materia_prima, cantidad_actual) VALUES (?, ?)");
        $stmt_update_inventario = $conexion->prepare("UPDATE inventario SET cantidad_actual = cantidad_actual + ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id_materia_prima = ?");
        
        $detalles_guardados = 0;
        
        // 6. INSERTAR CADA DETALLE Y ACTUALIZAR INVENTARIO
        for ($i = 0; $i < count($id_materias_primas); $i++) {
            $id_materia_prima = filter_var($id_materias_primas[$i], FILTER_VALIDATE_INT);
            $cantidad = filter_var($cantidades[$i], FILTER_VALIDATE_FLOAT);
            
            // Validar datos del detalle
            if ($id_materia_prima && $cantidad && $cantidad > 0) {
                
                // 6.1. INSERTAR DETALLE DE ENTREGA
                $stmt_detalle->bind_param("iid", $id_entrega, $id_materia_prima, $cantidad);
                
                if (!$stmt_detalle->execute()) {
                    throw new Exception("Error al guardar detalle: " . $stmt_detalle->error);
                }
                
                // 6.2. ACTUALIZAR INVENTARIO
                // Verificar si existe registro en inventario para esta materia prima
                $stmt_check_inventario->bind_param("i", $id_materia_prima);
                $stmt_check_inventario->execute();
                $result_inventario = $stmt_check_inventario->get_result();
                
                if ($result_inventario->num_rows > 0) {
                    // Actualizar registro existente
                    $stmt_update_inventario->bind_param("di", $cantidad, $id_materia_prima);
                    if (!$stmt_update_inventario->execute()) {
                        throw new Exception("Error al actualizar inventario: " . $stmt_update_inventario->error);
                    }
                } else {
                    // Insertar nuevo registro en inventario
                    $stmt_insert_inventario->bind_param("id", $id_materia_prima, $cantidad);
                    if (!$stmt_insert_inventario->execute()) {
                        throw new Exception("Error al insertar en inventario: " . $stmt_insert_inventario->error);
                    }
                }
                
                $detalles_guardados++;
            }
        }
        
        // Validar que se guardó al menos un detalle
        if ($detalles_guardados == 0) {
            throw new Exception("No se guardó ningún detalle válido");
        }
        
        // 7. CONFIRMAR TRANSACCIÓN
        $conexion->commit();
        
        // 8. REDIRIGIR CON MENSAJE DE ÉXITO
        header("Location: entregas.php?success=1&detalles=" . $detalles_guardados);
        exit();
        
    } catch (Exception $e) {
        // 9. REVERTIR TRANSACCIÓN EN CASO DE ERROR
        $conexion->rollback();
        
        // 10. REDIRIGIR CON MENSAJE DE ERROR
        header("Location: entregas.php?error=" . urlencode($e->getMessage()));
        exit();
    }
    
    // Cerrar statements
    if (isset($stmt_entrega)) $stmt_entrega->close();
    if (isset($stmt_detalle)) $stmt_detalle->close();
    if (isset($stmt_check_inventario)) $stmt_check_inventario->close();
    if (isset($stmt_insert_inventario)) $stmt_insert_inventario->close();
    if (isset($stmt_update_inventario)) $stmt_update_inventario->close();
    
} else {
    // Si no es POST, redirigir al formulario
    header("Location: añadir_entrega.php");
    exit();
}

$conexion->close();
?>