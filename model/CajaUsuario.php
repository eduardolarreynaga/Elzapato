<?php
require_once __DIR__ . '/conexion.php';

class CajaUsuario {
    
    private $db;
    
    public function __construct() {
        $this->db = Conexion::conectar();
    }
    
    public function getMontoAsignado($id_usuario) {
        $stmt = $this->db->prepare("SELECT monto_caja FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$id_usuario]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['monto_caja'] : 0;
    }
    
    public function getCajaAsignada($id_usuario) {
        $stmt = $this->db->prepare("SELECT id_caja FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$id_usuario]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['id_caja'] : null;
    }
    
    public function tieneCajaAbierta($id_usuario) {
        $stmt = $this->db->prepare("
            SELECT * FROM caja_aperturas 
            WHERE id_usuario = ? AND estado = 'abierta'
            ORDER BY id_apertura DESC LIMIT 1
        ");
        $stmt->execute([$id_usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getSaldoActual($id_usuario) {
        $stmt = $this->db->prepare("
            SELECT ca.monto_inicial, ca.total_ingresos, ca.total_vuelto,
                   COALESCE(SUM(dv.total_devuelto), 0) as total_devoluciones
            FROM caja_aperturas ca
            LEFT JOIN ventas v ON ca.id_usuario = v.id_usuario
            LEFT JOIN devoluciones_venta dv ON v.id_venta = dv.id_venta
            WHERE ca.id_usuario = ? AND ca.estado = 'abierta'
            GROUP BY ca.id_apertura
        ");
        $stmt->execute([$id_usuario]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            return $data['monto_inicial'] + $data['total_ingresos'] - $data['total_vuelto'] - ($data['total_devoluciones'] ?? 0);
        }
        return 0;
    }
    
    public function getEstadisticasTurno($id_apertura) {
        $stmt = $this->db->prepare("
            SELECT total_ventas, total_ingresos, total_vuelto 
            FROM caja_aperturas 
            WHERE id_apertura = ?
        ");
        $stmt->execute([$id_apertura]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function abrirCaja($id_usuario, $id_caja, $monto_inicial) {
        try {
            // Verificar si ya hay una caja abierta
            $stmtCheck = $this->db->prepare("
                SELECT id_apertura FROM caja_aperturas 
                WHERE id_usuario = ? AND estado = 'abierta'
            ");
            $stmtCheck->execute([$id_usuario]);
            if ($stmtCheck->fetch()) {
                return ['success' => false, 'error' => 'Ya tienes una caja abierta'];
            }
            
            // Crear nueva apertura
            $stmt = $this->db->prepare("
                INSERT INTO caja_aperturas (id_usuario, id_caja, monto_inicial, total_ventas, total_ingresos, total_vuelto, fecha_apertura, estado)
                VALUES (?, ?, ?, 0, 0, 0, NOW(), 'abierta')
            ");
            $stmt->execute([$id_usuario, $id_caja, $monto_inicial]);
            $id_apertura = $this->db->lastInsertId();
            
            // Registrar movimiento de apertura
            $stmtMov = $this->db->prepare("
                INSERT INTO caja_movimientos (id_apertura, id_usuario, tipo_movimiento, concepto, monto, fecha_movimiento)
                VALUES (?, ?, 'apertura', 'Apertura de caja', ?, NOW())
            ");
            $stmtMov->execute([$id_apertura, $id_usuario, $monto_inicial]);
            
            return ['success' => true, 'message' => 'Caja abierta exitosamente', 'id_apertura' => $id_apertura];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function cerrarCaja($id_usuario) {
        try {
            // Obtener apertura actual
            $stmtApertura = $this->db->prepare("
                SELECT id_apertura, monto_inicial, total_ingresos, total_vuelto, fecha_apertura
                FROM caja_aperturas 
                WHERE id_usuario = ? AND estado = 'abierta'
            ");
            $stmtApertura->execute([$id_usuario]);
            $apertura = $stmtApertura->fetch(PDO::FETCH_ASSOC);
            
            if (!$apertura) {
                return ['success' => false, 'error' => 'No hay una caja abierta para cerrar'];
            }
            
            // Obtener devoluciones del turno
            $stmtDev = $this->db->prepare("
                SELECT COALESCE(SUM(dv.total_devuelto), 0) as total_devoluciones 
                FROM devoluciones_venta dv
                INNER JOIN ventas v ON dv.id_venta = v.id_venta
                WHERE v.id_usuario = ? AND dv.fecha_devolucion >= ?
            ");
            $stmtDev->execute([$id_usuario, $apertura['fecha_apertura']]);
            $devoluciones = $stmtDev->fetch(PDO::FETCH_ASSOC);
            
            // Calcular saldo final
            $saldo_final = $apertura['monto_inicial'] + $apertura['total_ingresos'] - $apertura['total_vuelto'] - ($devoluciones['total_devoluciones'] ?? 0);
            
            // Cerrar caja
            $stmt = $this->db->prepare("
                UPDATE caja_aperturas 
                SET monto_cierre = ?, fecha_cierre = NOW(), estado = 'cerrada'
                WHERE id_apertura = ?
            ");
            $stmt->execute([$saldo_final, $apertura['id_apertura']]);
            
            // Registrar movimiento de cierre
            $stmtMov = $this->db->prepare("
                INSERT INTO caja_movimientos (id_apertura, id_usuario, tipo_movimiento, concepto, monto, fecha_movimiento)
                VALUES (?, ?, 'cierre', 'Cierre de caja', ?, NOW())
            ");
            $stmtMov->execute([$apertura['id_apertura'], $id_usuario, $saldo_final]);
            
            return [
                'success' => true, 
                'message' => 'Caja cerrada exitosamente',
                'saldo_esperado' => $saldo_final,
                'stats' => [
                    'monto_inicial' => $apertura['monto_inicial'],
                    'total_ventas' => $apertura['total_ventas'],
                    'total_ingresos' => $apertura['total_ingresos'],
                    'total_vuelto' => $apertura['total_vuelto'],
                    'total_devoluciones' => $devoluciones['total_devoluciones']
                ]
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>