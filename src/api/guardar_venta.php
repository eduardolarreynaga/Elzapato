<?php
require_once __DIR__ . '/../../model/conexion.php';
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    guardarVenta();
}

function guardarVenta() {
    try {
        if (!isset($_SESSION['id_usuario'])) {
            echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
            return;
        }
        
        $idUsuario = $_SESSION['id_usuario'];
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['productos']) || empty($input['productos'])) {
            echo json_encode(['success' => false, 'error' => 'Datos de venta inválidos']);
            return;
        }
        
        $productos = $input['productos'];
        $total = floatval($input['total']);
        $metodoPago = intval($input['metodo_pago']);
        $cambio = isset($input['cambio']) ? floatval($input['cambio']) : 0;
        $descuentosGlobales = isset($input['descuentos']) ? $input['descuentos'] : [];
        $idCliente = isset($input['id_cliente']) && $input['id_cliente'] ? intval($input['id_cliente']) : null;
        $descuentoFidelidad = isset($input['descuento_fidelidad']) ? floatval($input['descuento_fidelidad']) : 0;
        
        $pdo = Conexion::conectar();
        $pdo->beginTransaction();
        
        // Verificar que el usuario tenga caja abierta
        $stmtVerificar = $pdo->prepare("SELECT COUNT(*) FROM caja_aperturas WHERE id_usuario = ? AND estado = 'abierta'");
        $stmtVerificar->execute([$idUsuario]);
        $cajaAbierta = $stmtVerificar->fetchColumn();
        
        if ($cajaAbierta == 0) {
            echo json_encode(['success' => false, 'error' => 'Debe abrir la caja antes de realizar ventas']);
            return;
        }
        
        // Insertar venta
        $stmt = $pdo->prepare("INSERT INTO ventas (id_usuario, id_cliente, id_metodo_pago, total_venta, cambio, fecha_venta) 
                               VALUES (?, ?, ?, ?, ?, NOW())");
        
        $stmt->execute([$idUsuario, $idCliente, $metodoPago, $total, $cambio]);
        $idVenta = $pdo->lastInsertId();
        
        // Insertar detalles de venta
        $stmtDetalle = $pdo->prepare("INSERT INTO detalle_venta (id_venta, id_variante, cantidad, precio_unitario, subtotal, porcentaje_descuento) 
                                      VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($productos as $p) {
            $idVariante = $p['id'];
            $cantidad = intval($p['cantidad']);
            $precioUnitario = floatval($p['precio']);
            $subtotalProducto = $precioUnitario * $cantidad;
            
            // Buscar descuento para este producto
            $porcentajeDescuento = 0;
            foreach ($descuentosGlobales as $desc) {
                if (isset($desc['id']) && $desc['id'] == $idVariante && isset($desc['porcentaje'])) {
                    $porcentajeDescuento = floatval($desc['porcentaje']);
                    break;
                }
            }
            
            // Si hay descuento por fidelidad y es mayor, usarlo
            if ($descuentoFidelidad > 0 && $idCliente && $descuentoFidelidad > $porcentajeDescuento) {
                $porcentajeDescuento = $descuentoFidelidad;
            }
            
            // Calcular subtotal con descuento
            $descuentoAplicado = $subtotalProducto * ($porcentajeDescuento / 100);
            $subtotalConDescuento = $subtotalProducto - $descuentoAplicado;
            
            $stmtDetalle->execute([
                $idVenta,
                $idVariante,
                $cantidad,
                $precioUnitario,
                $subtotalConDescuento,
                $porcentajeDescuento
            ]);
            
            // Actualizar stock
            $stmtStock = $pdo->prepare("UPDATE producto_variante SET stock = stock - ? WHERE id_variante = ?");
            $stmtStock->execute([$cantidad, $idVariante]);
            
            // Si stock llega a 0, marcar como inactivo
            $stmtCheckStock = $pdo->prepare("UPDATE producto_variante SET estado = 'inactivo' WHERE id_variante = ? AND stock <= 0");
            $stmtCheckStock->execute([$idVariante]);
        }
        
        // Obtener ID de apertura de caja actual
        $stmtApertura = $pdo->prepare("SELECT id_apertura FROM caja_aperturas WHERE id_usuario = ? AND estado = 'abierta'");
        $stmtApertura->execute([$idUsuario]);
        $idApertura = $stmtApertura->fetchColumn();
        
        if ($idApertura) {
            // Actualizar estadísticas en caja_aperturas
            $stmtUpdateCaja = $pdo->prepare("UPDATE caja_aperturas 
                                             SET total_ventas = total_ventas + 1, 
                                                 total_ingresos = total_ingresos + ?, 
                                                 total_vuelto = total_vuelto + ? 
                                             WHERE id_apertura = ?");
            $stmtUpdateCaja->execute([$total, $cambio, $idApertura]);
            
            // Registrar movimiento en caja_movimientos
            $stmtMovimiento = $pdo->prepare("INSERT INTO caja_movimientos (id_apertura, id_usuario, tipo_movimiento, concepto, monto, id_venta) 
                                             VALUES (?, ?, 'venta', ?, ?, ?)");
            $concepto = 'Venta #' . $idVenta;
            $stmtMovimiento->execute([$idApertura, $idUsuario, $concepto, $total, $idVenta]);
        }
        
        $pdo->commit();
        
        // Guardar datos para el ticket
        $_SESSION['ultima_venta'] = [
            'id_venta' => $idVenta,
            'cambio' => $cambio,
            'descuentos' => $descuentosGlobales,
            'descuento_fidelidad' => $descuentoFidelidad
        ];
        
        echo json_encode([
            'success' => true,
            'id_venta' => $idVenta,
            'message' => 'Venta guardada exitosamente'
        ]);
        
    } catch (Exception $e) {
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        error_log("Error en guardar_venta: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>