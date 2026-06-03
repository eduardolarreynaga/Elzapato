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
    $idUsuario = $_SESSION['id_usuario'];
    
    // Obtener el id_apertura de caja actual del usuario
    $stmtApertura = $conexion->prepare("
        SELECT id_apertura FROM caja_aperturas 
        WHERE id_usuario = :id_usuario AND estado = 'abierta'
        FOR UPDATE
    ");
    $stmtApertura->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
    $stmtApertura->execute();
    $apertura = $stmtApertura->fetch(PDO::FETCH_ASSOC);
    
    if (!$apertura) {
        throw new Exception('No hay una caja abierta para realizar la devolución');
    }
    
    $idApertura = $apertura['id_apertura'];
    
    // Verificar que la venta existe y está completada
    $stmtVenta = $conexion->prepare("
        SELECT id_venta, total_venta, estado, id_usuario as vendedor_original
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
            SELECT cantidad, precio_unitario, id_variante, subtotal
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
        
        // Calcular total a devolver para este producto
        $totalProductoDevuelto = $detalle['precio_unitario'] * $cantidadDevolver;
        
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
                id_usuario,
                motivo
            ) VALUES (
                :id_venta, 
                :id_detalle, 
                :id_variante, 
                :cantidad, 
                :precio_unitario,
                :total_devuelto,
                NOW(),
                :id_usuario,
                :motivo
            )
        ");
        
        $motivo = 'Devolución de ' . $cantidadDevolver . ' unidades de ' . $producto['nombre'];
        
        $stmtDevolucion->bindParam(':id_venta', $idVenta, PDO::PARAM_INT);
        $stmtDevolucion->bindParam(':id_detalle', $idDetalleVenta, PDO::PARAM_INT);
        $stmtDevolucion->bindParam(':id_variante', $idVariante, PDO::PARAM_INT);
        $stmtDevolucion->bindParam(':cantidad', $cantidadDevolver, PDO::PARAM_INT);
        $stmtDevolucion->bindParam(':precio_unitario', $detalle['precio_unitario']);
        $stmtDevolucion->bindParam(':total_devuelto', $totalProductoDevuelto);
        $stmtDevolucion->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmtDevolucion->bindParam(':motivo', $motivo);
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
        
        // Actualizar stock en producto_variante (SUMAR lo devuelto)
        $stmtStock = $conexion->prepare("
            UPDATE producto_variante 
            SET stock = stock + :cantidad,
                estado = 'activo'
            WHERE id_variante = :id_variante
        ");
        $stmtStock->bindParam(':cantidad', $cantidadDevolver, PDO::PARAM_INT);
        $stmtStock->bindParam(':id_variante', $idVariante, PDO::PARAM_INT);
        $stmtStock->execute();
        
        $totalDevuelto += $totalProductoDevuelto;
        $productosDevueltos[] = [
            'nombre' => $producto['nombre'],
            'cantidad' => $cantidadDevolver,
            'total' => (float)$totalProductoDevuelto
        ];
    }
    
    // Actualizar total de la venta (RESTAR lo devuelto)
    $stmtUpdateVenta = $conexion->prepare("
        UPDATE ventas 
        SET total_venta = total_venta - :total_devuelto
        WHERE id_venta = :id_venta
    ");
    $stmtUpdateVenta->bindParam(':total_devuelto', $totalDevuelto);
    $stmtUpdateVenta->bindParam(':id_venta', $idVenta, PDO::PARAM_INT);
    $stmtUpdateVenta->execute();
    
    // ==================== ACTUALIZAR CAJA ====================
    // 1. Restar el monto devuelto del saldo de la caja
    $stmtUpdateCajaSaldo = $conexion->prepare("
        UPDATE caja_aperturas 
        SET total_vuelto = total_vuelto + :total_devuelto,
            total_ingresos = total_ingresos - :total_devuelto
        WHERE id_apertura = :id_apertura
    ");
    $stmtUpdateCajaSaldo->bindParam(':total_devuelto', $totalDevuelto);
    $stmtUpdateCajaSaldo->bindParam(':id_apertura', $idApertura);
    $stmtUpdateCajaSaldo->execute();
    
    // 2. Registrar movimiento de egreso en caja_movimientos
    $stmtMovimiento = $conexion->prepare("
        INSERT INTO caja_movimientos (
            id_apertura, 
            id_usuario, 
            tipo_movimiento, 
            concepto, 
            monto, 
            id_venta,
            fecha_movimiento
        ) VALUES (
            :id_apertura,
            :id_usuario,
            'devolucion',
            :concepto,
            :monto,
            :id_venta,
            NOW()
        )
    ");
    
    $concepto = 'Devolución de venta #' . $idVenta . ' - Total: $' . number_format($totalDevuelto, 2);
    $stmtMovimiento->bindParam(':id_apertura', $idApertura);
    $stmtMovimiento->bindParam(':id_usuario', $idUsuario);
    $stmtMovimiento->bindParam(':concepto', $concepto);
    $stmtMovimiento->bindParam(':monto', $totalDevuelto);
    $stmtMovimiento->bindParam(':id_venta', $idVenta);
    $stmtMovimiento->execute();
    
    // Si la venta queda en 0 o negativa, anularla
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
        'total_devuelto' => (float)$totalDevuelto,
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