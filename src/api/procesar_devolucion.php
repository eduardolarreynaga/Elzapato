<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../model/conexion.php';

session_start();

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['error' => 'No se recibieron datos']);
    exit;
}

$idVenta = isset($data['id_venta']) ? (int)$data['id_venta'] : 0;
$productosDevolver = isset($data['productos']) ? $data['productos'] : [];

if ($idVenta <= 0) {
    echo json_encode(['error' => 'ID de venta inválido']);
    exit;
}

if (empty($productosDevolver)) {
    echo json_encode(['error' => 'No hay productos para devolver']);
    exit;
}

$conexion = null;
try {
    $conexion = Conexion::conectar();
    $conexion->beginTransaction();
    
    $totalDevuelto = 0;
    $productosDevueltos = [];
    
    // Verificar que la venta existe y está completada
    $stmtVenta = $conexion->prepare("
        SELECT id_venta, total_venta, estado 
        FROM ventas 
        WHERE id_venta = :id_venta 
        AND (estado = 'completada' OR estado IS NULL)
        FOR UPDATE
    ");
    $stmtVenta->bindParam(':id_venta', $idVenta, PDO::PARAM_INT);
    $stmtVenta->execute();
    $venta = $stmtVenta->fetch(PDO::FETCH_ASSOC);
    
    if (!$venta) {
        throw new Exception('Venta no encontrada o ya anulada');
    }
    
    // Procesar cada producto a devolver
    foreach ($productosDevolver as $producto) {
        $idDetalleVenta = (int)$producto['id_detalle'];
        $cantidadDevolver = (int)$producto['cantidad'];
        $idVariante = (int)$producto['id_variante'];
        
        if ($cantidadDevolver <= 0) continue;
        
        // Verificar que la cantidad a devolver no excede la cantidad original
        $stmtDetalle = $conexion->prepare("
            SELECT cantidad, precio_unitario, id_variante 
            FROM detalle_venta 
            WHERE id_detalle_venta = :id_detalle AND id_venta = :id_venta
            FOR UPDATE
        ");
        $stmtDetalle->bindParam(':id_detalle', $idDetalleVenta, PDO::PARAM_INT);
        $stmtDetalle->bindParam(':id_venta', $idVenta, PDO::PARAM_INT);
        $stmtDetalle->execute();
        $detalle = $stmtDetalle->fetch(PDO::FETCH_ASSOC);
        
        if (!$detalle) {
            throw new Exception('Detalle de venta no encontrado');
        }
        
        if ($cantidadDevolver > $detalle['cantidad']) {
            throw new Exception('La cantidad a devolver excede la cantidad vendida');
        }
        
        // Registrar en tabla de devoluciones
        $stmtDevolucion = $conexion->prepare("
            INSERT INTO devoluciones_venta (
                id_venta, 
                id_detalle_venta, 
                id_variante, 
                cantidad_devuelta, 
                precio_unitario,
                total_devuelto,
                fecha_devolucion,
                id_usuario
            ) VALUES (
                :id_venta, 
                :id_detalle, 
                :id_variante, 
                :cantidad, 
                :precio_unitario,
                :total_devuelto,
                NOW(),
                :id_usuario
            )
        ");
        
        $totalProductoDevuelto = $detalle['precio_unitario'] * $cantidadDevolver;
        
        $stmtDevolucion->bindParam(':id_venta', $idVenta, PDO::PARAM_INT);
        $stmtDevolucion->bindParam(':id_detalle', $idDetalleVenta, PDO::PARAM_INT);
        $stmtDevolucion->bindParam(':id_variante', $idVariante, PDO::PARAM_INT);
        $stmtDevolucion->bindParam(':cantidad', $cantidadDevolver, PDO::PARAM_INT);
        $stmtDevolucion->bindParam(':precio_unitario', $detalle['precio_unitario']);
        $stmtDevolucion->bindParam(':total_devuelto', $totalProductoDevuelto);
        $stmtDevolucion->bindParam(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT);
        $stmtDevolucion->execute();
        
        // Actualizar cantidad en detalle_venta
        $nuevaCantidad = $detalle['cantidad'] - $cantidadDevolver;
        $nuevoSubtotal = $nuevaCantidad * $detalle['precio_unitario'];
        
        if ($nuevaCantidad > 0) {
            $stmtUpdateDetalle = $conexion->prepare("
                UPDATE detalle_venta 
                SET cantidad = :nueva_cantidad,
                    subtotal = :nuevo_subtotal
                WHERE id_detalle_venta = :id_detalle
            ");
            $stmtUpdateDetalle->bindParam(':nueva_cantidad', $nuevaCantidad, PDO::PARAM_INT);
            $stmtUpdateDetalle->bindParam(':nuevo_subtotal', $nuevoSubtotal);
            $stmtUpdateDetalle->bindParam(':id_detalle', $idDetalleVenta, PDO::PARAM_INT);
            $stmtUpdateDetalle->execute();
        } else {
            // Si no queda cantidad, eliminar el detalle
            $stmtDeleteDetalle = $conexion->prepare("
                DELETE FROM detalle_venta WHERE id_detalle_venta = :id_detalle
            ");
            $stmtDeleteDetalle->bindParam(':id_detalle', $idDetalleVenta, PDO::PARAM_INT);
            $stmtDeleteDetalle->execute();
        }
        
        // Actualizar stock en producto_variante
        $stmtStock = $conexion->prepare("
            UPDATE producto_variante 
            SET stock = stock + :cantidad 
            WHERE id_variante = :id_variante
        ");
        $stmtStock->bindParam(':cantidad', $cantidadDevolver, PDO::PARAM_INT);
        $stmtStock->bindParam(':id_variante', $idVariante, PDO::PARAM_INT);
        $stmtStock->execute();
        
        $totalDevuelto += $totalProductoDevuelto;
        $productosDevueltos[] = [
            'nombre' => $producto['nombre'],
            'cantidad' => $cantidadDevolver,
            'total' => $totalProductoDevuelto
        ];
    }
    
    // Actualizar total de la venta
    $stmtUpdateVenta = $conexion->prepare("
        UPDATE ventas 
        SET total_venta = total_venta - :total_devuelto
        WHERE id_venta = :id_venta
    ");
    $stmtUpdateVenta->bindParam(':total_devuelto', $totalDevuelto);
    $stmtUpdateVenta->bindParam(':id_venta', $idVenta, PDO::PARAM_INT);
    $stmtUpdateVenta->execute();
    
    // Si la venta queda en 0, anularla
    $stmtCheckTotal = $conexion->prepare("
        SELECT total_venta FROM ventas WHERE id_venta = :id_venta
    ");
    $stmtCheckTotal->bindParam(':id_venta', $idVenta, PDO::PARAM_INT);
    $stmtCheckTotal->execute();
    $nuevoTotal = $stmtCheckTotal->fetch(PDO::FETCH_ASSOC)['total_venta'];
    
    if ($nuevoTotal <= 0) {
        $stmtAnular = $conexion->prepare("
            UPDATE ventas SET estado = 'anulada' WHERE id_venta = :id_venta
        ");
        $stmtAnular->bindParam(':id_venta', $idVenta, PDO::PARAM_INT);
        $stmtAnular->execute();
    }
    
    $conexion->commit();
    
    echo json_encode([
        'success' => true,
        'mensaje' => 'Devolución procesada correctamente',
        'total_devuelto' => $totalDevuelto,
        'productos_devueltos' => $productosDevueltos
    ]);
    
} catch (Exception $e) {
    if ($conexion && $conexion->inTransaction()) {
        $conexion->rollBack();
    }
    echo json_encode(['error' => $e->getMessage()]);
} catch (PDOException $e) {
    if ($conexion && $conexion->inTransaction()) {
        $conexion->rollBack();
    }
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>