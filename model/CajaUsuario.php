<?php
require_once __DIR__ . '/conexion.php';

class CajaUsuario {
    private $db;
    
    public function __construct() {
        $this->db = Conexion::conectar();
    }
    
    // Obtener el monto que el admin asignó al usuario
    public function getMontoAsignado($id_usuario) {
        $sql = "SELECT monto_caja FROM usuarios WHERE id_usuario = :id_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return floatval($result['monto_caja'] ?? 0);
    }
    
    // Obtener la caja física asignada al usuario
    public function getCajaAsignada($id_usuario) {
        $sql = "SELECT id_caja FROM usuarios WHERE id_usuario = :id_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['id_caja'] ?? null;
    }
    
    public function tieneCajaAbierta($id_usuario) {
        $sql = "SELECT * FROM caja_aperturas 
                WHERE id_usuario = :id_usuario 
                AND estado = 'abierta'
                ORDER BY fecha_apertura DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_usuario' => $id_usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function abrirCaja($id_usuario, $id_caja, $monto_inicial) {
        try {
            $tiene_abierta = $this->tieneCajaAbierta($id_usuario);
            if ($tiene_abierta) {
                return ['success' => false, 'message' => 'Ya tienes una caja abierta'];
            }
            
            $sql = "INSERT INTO caja_aperturas (id_usuario, id_caja, monto_inicial, total_ventas, total_ingresos, total_vuelto, fecha_apertura, estado) 
                    VALUES (:id_usuario, :id_caja, :monto_inicial, 0, 0, 0, NOW(), 'abierta')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_usuario' => $id_usuario,
                ':id_caja' => $id_caja,
                ':monto_inicial' => $monto_inicial
            ]);
            
            $id_apertura = $this->db->lastInsertId();
            
            $this->registrarMovimiento($id_apertura, $id_usuario, 'apertura', 'Apertura de caja', $monto_inicial);
            
            return ['success' => true, 'id_apertura' => $id_apertura];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getSaldoActual($id_usuario) {
        $apertura = $this->tieneCajaAbierta($id_usuario);
        if (!$apertura) {
            return $this->getMontoAsignado($id_usuario);
        }
        
        $sql = "SELECT 
                    SUM(CASE WHEN tipo_movimiento IN ('apertura', 'venta') THEN monto ELSE 0 END) - 
                    SUM(CASE WHEN tipo_movimiento = 'vuelto' THEN monto ELSE 0 END) as saldo
                FROM caja_movimientos 
                WHERE id_apertura = :id_apertura";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_apertura' => $apertura['id_apertura']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['saldo'] ?? 0;
    }
    
    public function registrarMovimiento($id_apertura, $id_usuario, $tipo_movimiento, $concepto, $monto, $id_venta = null) {
        $sql = "INSERT INTO caja_movimientos (id_apertura, id_usuario, tipo_movimiento, concepto, monto, id_venta, fecha_movimiento) 
                VALUES (:id_apertura, :id_usuario, :tipo_movimiento, :concepto, :monto, :id_venta, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_apertura' => $id_apertura,
            ':id_usuario' => $id_usuario,
            ':tipo_movimiento' => $tipo_movimiento,
            ':concepto' => $concepto,
            ':monto' => $monto,
            ':id_venta' => $id_venta
        ]);
    }
    
    public function getEstadisticasTurno($id_apertura) {
        $sql = "SELECT 
                    COUNT(DISTINCT id_venta) as total_ventas,
                    SUM(CASE WHEN tipo_movimiento = 'venta' THEN monto ELSE 0 END) as total_ingresos,
                    SUM(CASE WHEN tipo_movimiento = 'vuelto' THEN monto ELSE 0 END) as total_vuelto
                FROM caja_movimientos 
                WHERE id_apertura = :id_apertura";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_apertura' => $id_apertura]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'total_ventas' => intval($result['total_ventas'] ?? 0),
            'total_ingresos' => floatval($result['total_ingresos'] ?? 0),
            'total_vuelto' => floatval($result['total_vuelto'] ?? 0)
        ];
    }
    
    public function registrarVenta($id_usuario, $id_venta, $monto, $vuelto = 0) {
        $apertura = $this->tieneCajaAbierta($id_usuario);
        if (!$apertura) {
            return false;
        }
        
        $this->registrarMovimiento($apertura['id_apertura'], $id_usuario, 'venta', 'Venta #' . $id_venta, $monto, $id_venta);
        
        if ($vuelto > 0) {
            $this->registrarMovimiento($apertura['id_apertura'], $id_usuario, 'vuelto', 'Vuelto venta #' . $id_venta, $vuelto, $id_venta);
        }
        
        $sql = "UPDATE caja_aperturas 
                SET total_ventas = total_ventas + 1,
                    total_ingresos = total_ingresos + :monto,
                    total_vuelto = total_vuelto + :vuelto
                WHERE id_apertura = :id_apertura";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':monto' => $monto,
            ':vuelto' => $vuelto,
            ':id_apertura' => $apertura['id_apertura']
        ]);
    }
    
    public function cerrarCaja($id_usuario) {
        $apertura = $this->tieneCajaAbierta($id_usuario);
        if (!$apertura) {
            return ['success' => false, 'message' => 'No hay caja abierta'];
        }
        
        // Calcular saldo esperado automáticamente
        $saldo_esperado = $this->getSaldoActual($id_usuario);
        $stats = $this->getEstadisticasTurno($apertura['id_apertura']);
        
        try {
            $sql = "UPDATE caja_aperturas 
                    SET monto_cierre = :monto_cierre, 
                        fecha_cierre = NOW(), 
                        estado = 'cerrada' 
                    WHERE id_apertura = :id_apertura";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':monto_cierre' => $saldo_esperado,
                ':id_apertura' => $apertura['id_apertura']
            ]);
            
            $this->registrarMovimiento($apertura['id_apertura'], $id_usuario, 'cierre', 'Cierre de caja', $saldo_esperado);
            
            return [
                'success' => true, 
                'saldo_esperado' => $saldo_esperado,
                'stats' => $stats
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>