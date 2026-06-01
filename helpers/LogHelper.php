<?php
require_once __DIR__ . '/../model/conexion.php';

class LogHelper {
    
    /**
     * Registrar una acción en el historial
     */
    public static function registrar($accion, $tabla_afectada = null, $registro_id = null, $detalle = null) {
        // Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $id_usuario = $_SESSION['id_usuario'] ?? 0;
        $nombre_usuario = $_SESSION['usuario'] ?? 'Sistema';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        
        // Para login_fallido, no hay usuario autenticado
        if ($accion == 'login_fallido' && $detalle) {
            $nombre_usuario = 'Desconocido';
        }
        
        // Para logout, usar el usuario de la sesión (aún no destruida)
        if ($accion == 'logout' && $id_usuario == 0) {
            // Si por algún motivo no hay id_usuario, no registrar
            return false;
        }
        
        try {
            $db = Conexion::conectar();
            $sql = "INSERT INTO historial_logs (id_usuario, nombre_usuario, accion, tabla_afectada, registro_id, detalle, ip_address, fecha) 
                    VALUES (:id_usuario, :nombre_usuario, :accion, :tabla_afectada, :registro_id, :detalle, :ip_address, NOW())";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                ':id_usuario' => $id_usuario,
                ':nombre_usuario' => $nombre_usuario,
                ':accion' => $accion,
                ':tabla_afectada' => $tabla_afectada,
                ':registro_id' => $registro_id,
                ':detalle' => $detalle,
                ':ip_address' => $ip_address
            ]);
        } catch (PDOException $e) {
            error_log("Error al registrar log: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todos los logs con filtros
     */
    public static function obtenerLogs($fecha_desde = null, $fecha_hasta = null, $usuario_id = null, $accion = null, $limit = 500) {
        try {
            $db = Conexion::conectar();
            
            $sql = "SELECT * FROM historial_logs WHERE 1=1";
            $params = [];
            
            if ($fecha_desde) {
                $sql .= " AND DATE(fecha) >= :fecha_desde";
                $params[':fecha_desde'] = $fecha_desde;
            }
            if ($fecha_hasta) {
                $sql .= " AND DATE(fecha) <= :fecha_hasta";
                $params[':fecha_hasta'] = $fecha_hasta;
            }
            if ($usuario_id && $usuario_id > 0) {
                $sql .= " AND id_usuario = :usuario_id";
                $params[':usuario_id'] = $usuario_id;
            }
            if ($accion && $accion != 'todos' && $accion != '') {
                $sql .= " AND accion = :accion";
                $params[':accion'] = $accion;
            }
            
            $sql .= " ORDER BY fecha DESC LIMIT :limit";
            
            $stmt = $db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en obtenerLogs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas de logs
     */
    public static function obtenerEstadisticas($fecha_desde = null, $fecha_hasta = null) {
        try {
            $db = Conexion::conectar();
            
            $sql = "SELECT 
                        COUNT(*) as total,
                        COUNT(DISTINCT id_usuario) as usuarios_activos,
                        COUNT(DISTINCT accion) as tipos_accion
                    FROM historial_logs
                    WHERE 1=1";
            $params = [];
            
            if ($fecha_desde) {
                $sql .= " AND DATE(fecha) >= :fecha_desde";
                $params[':fecha_desde'] = $fecha_desde;
            }
            if ($fecha_hasta) {
                $sql .= " AND DATE(fecha) <= :fecha_hasta";
                $params[':fecha_hasta'] = $fecha_hasta;
            }
            
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticas: " . $e->getMessage());
            return ['total' => 0, 'usuarios_activos' => 0, 'tipos_accion' => 0];
        }
    }
}
?>