<?php
require_once __DIR__ . '/conexion.php';

class VentasModel {
    
    // Guardar una nueva venta
    public static function guardarVenta($idUsuario, $idMetodoPago, $idCliente, $total, $productos) {
        try {
            $conexion = Conexion::conectar();
            $conexion->beginTransaction();
            
            // Insertar venta principal
            $stmt = $conexion->prepare("
                INSERT INTO ventas (id_usuario, id_cliente, id_metodo_pago, total_venta, fecha_venta) 
                VALUES (:id_usuario, :id_cliente, :id_metodo_pago, :total, NOW())
            ");
            $stmt->bindParam(":id_usuario", $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(":id_cliente", $idCliente, PDO::PARAM_INT);
            $stmt->bindParam(":id_metodo_pago", $idMetodoPago, PDO::PARAM_INT);
            $stmt->bindParam(":total", $total, PDO::PARAM_STR);
            $stmt->execute();
            
            $idVenta = $conexion->lastInsertId();
            
            // Insertar detalles de venta (SOLO ESTO, el trigger actualizará el stock automáticamente)
            $stmtDetalle = $conexion->prepare("
                INSERT INTO detalle_venta (id_venta, id_variante, cantidad, precio_unitario, subtotal) 
                VALUES (:id_venta, :id_variante, :cantidad, :precio_unitario, :subtotal)
            ");
            
            foreach ($productos as $producto) {
                $subtotal = $producto['precio'] * $producto['cantidad'];
                
                $stmtDetalle->bindParam(":id_venta", $idVenta, PDO::PARAM_INT);
                $stmtDetalle->bindParam(":id_variante", $producto['id'], PDO::PARAM_INT);
                $stmtDetalle->bindParam(":cantidad", $producto['cantidad'], PDO::PARAM_INT);
                $stmtDetalle->bindParam(":precio_unitario", $producto['precio'], PDO::PARAM_STR);
                $stmtDetalle->bindParam(":subtotal", $subtotal, PDO::PARAM_STR);
                $stmtDetalle->execute();
                
                // ELIMINA ESTA PARTE - EL TRIGGER SE ENCARGA DEL STOCK
                /*
                // Actualizar stock
                $stmtStock = $conexion->prepare("
                    UPDATE producto_variante SET stock = stock - :cantidad WHERE id_variante = :id_variante
                ");
                $stmtStock->bindParam(":cantidad", $producto['cantidad'], PDO::PARAM_INT);
                $stmtStock->bindParam(":id_variante", $producto['id'], PDO::PARAM_INT);
                $stmtStock->execute();
                */
            }
            
            $conexion->commit();
            return ["success" => true, "id_venta" => $idVenta];
            
        } catch (PDOException $e) {
            if (isset($conexion)) $conexion->rollBack();
            return ["success" => false, "error" => $e->getMessage()];
        }
    }
    
    // El resto de tus métodos permanecen igual...
    
    // Obtener últimas ventas
    public static function obtenerUltimasVentas($limite = 5) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT 
                    v.id_venta, 
                    v.fecha_venta, 
                    v.total_venta,
                    u.nombre_usuario as usuario,
                    mp.nombre_metodo as metodo_pago
                FROM ventas v 
                INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                LEFT JOIN metodos_pago mp ON v.id_metodo_pago = mp.id_metodo_pago
                ORDER BY v.id_venta DESC 
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
    
    // Obtener detalles de una venta
    public static function obtenerDetalleVenta($idVenta) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT 
                    dv.cantidad, 
                    dv.precio_unitario, 
                    dv.subtotal, 
                    p.nombre_producto,
                    pv.talla,
                    pv.color
                FROM detalle_venta dv
                INNER JOIN producto_variante pv ON dv.id_variante = pv.id_variante
                INNER JOIN productos p ON pv.id_producto = p.id_producto
                WHERE dv.id_venta = :id_venta
                ORDER BY dv.id_detalle_venta ASC
            ");
            $stmt->bindParam(":id_venta", $idVenta, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
    
    // Obtener información de una venta
    public static function obtenerInfoVenta($idVenta) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT 
                    v.id_venta, 
                    v.fecha_venta, 
                    v.total_venta,
                    u.nombre_usuario as usuario,
                    mp.nombre_metodo as metodo_pago
                FROM ventas v 
                INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                LEFT JOIN metodos_pago mp ON v.id_metodo_pago = mp.id_metodo_pago
                WHERE v.id_venta = :id_venta
            ");
            $stmt->bindParam(":id_venta", $idVenta, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
    
    // Obtener TODAS las ventas para el panel de administración
    public static function obtenerTodasLasVentas() {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT 
                    v.id_venta, 
                    v.fecha_venta, 
                    v.total_venta,
                    u.nombre_usuario as nombre_usuario,
                    mp.nombre_metodo as metodo_pago,
                    COALESCE(c.nombre, 'Cliente Mostrador') as nombre_cliente
                FROM ventas v 
                INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                LEFT JOIN metodos_pago mp ON v.id_metodo_pago = mp.id_metodo_pago
                LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                ORDER BY v.id_venta DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }
    
    // Inicializar métodos de pago
    public static function inicializarMetodosPago() {
        try {
            $conexion = Conexion::conectar();
            $metodos = ['Efectivo', 'Tarjeta', 'Transferencia'];
            
            foreach ($metodos as $metodo) {
                $stmt = $conexion->prepare("INSERT IGNORE INTO metodos_pago (nombre_metodo) VALUES (:nombre)");
                $stmt->bindParam(":nombre", $metodo);
                $stmt->execute();
            }
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>