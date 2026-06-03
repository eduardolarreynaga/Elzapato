<?php
/**
 * BackupHelper - Clase para manejar respaldos de base de datos
 */

require_once __DIR__ . '/../model/conexion.php';

class BackupHelper {
    
    private $db;
    private $backupDir;
    
    public function __construct() {
        $this->db = Conexion::conectar();
        $this->backupDir = dirname(__DIR__) . '/Database/backups/';
        
        // Crear directorios si no existen
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0777, true);
        }
        if (!is_dir($this->backupDir . 'completo/')) {
            mkdir($this->backupDir . 'completo/', 0777, true);
        }
        if (!is_dir($this->backupDir . 'tablas/')) {
            mkdir($this->backupDir . 'tablas/', 0777, true);
        }
        if (!is_dir($this->backupDir . 'hora/')) {
            mkdir($this->backupDir . 'hora/', 0777, true);
        }
    }
    
    /**
     * Generar backup completo - pos_zapateria.sql.gz
     */
    public function generarBackupCompleto($tipo = 'completo') {
        try {
            $stmt = $this->db->query("SHOW TABLES");
            $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $nombreArchivo = "pos_zapateria";
            
            $sql = "-- =============================================\n";
            $sql .= "-- RESPALDO COMPLETO - pos_zapateria\n";
            $sql .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- =============================================\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
            
            foreach ($tablas as $tabla) {
                $sql .= $this->getTableStructure($tabla);
                $sql .= $this->getTableData($tabla);
                $sql .= "\n";
            }
            
            $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
            
            $ruta = $this->backupDir . $tipo . '/' . $nombreArchivo . '.sql';
            file_put_contents($ruta, $sql);
            
            $this->comprimirBackup($ruta, $tipo, $nombreArchivo);
            
            return [
                'success' => true,
                'archivo' => $nombreArchivo . '.sql.gz',
                'tamano' => $this->formatearTamano(filesize($ruta . '.gz'))
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Generar backup de una tabla especifica - nombre_tabla.sql.gz
     */
    public function generarBackupTabla($tabla, $tipo = 'tablas') {
        try {
            $stmt = $this->db->prepare("SHOW TABLES LIKE :tabla");
            $stmt->bindParam(":tabla", $tabla);
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                return ['success' => false, 'error' => "La tabla '$tabla' no existe"];
            }
            
            $nombreArchivo = $tabla;
            
            $sql = "-- =============================================\n";
            $sql .= "-- RESPALDO DE TABLA: $tabla\n";
            $sql .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- =============================================\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
            
            $sql .= $this->getTableStructure($tabla);
            $sql .= $this->getTableData($tabla);
            
            $sql .= "\nSET FOREIGN_KEY_CHECKS = 1;\n";
            
            $ruta = $this->backupDir . $tipo . '/' . $nombreArchivo . '.sql';
            file_put_contents($ruta, $sql);
            
            $this->comprimirBackup($ruta, $tipo, $nombreArchivo);
            
            return [
                'success' => true,
                'archivo' => $nombreArchivo . '.sql.gz',
                'tamano' => $this->formatearTamano(filesize($ruta . '.gz')),
                'tabla' => $tabla
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Backup por hora - genera backup de todas las tablas esenciales del POS
     */
    public function generarBackupHoraPOS() {
        $tablasEsenciales = [
            'productos', 'producto_variante', 'clientes', 'ventas', 
            'detalle_venta', 'usuarios', 'cajas', 'caja_aperturas', 
            'caja_movimientos', 'proveedores', 'categorias', 'marcas'
        ];
        
        $resultados = [];
        $errores = [];
        
        foreach ($tablasEsenciales as $tabla) {
            $stmt = $this->db->prepare("SHOW TABLES LIKE :tabla");
            $stmt->bindParam(":tabla", $tabla);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $resultado = $this->generarBackupTabla($tabla, 'hora');
                if ($resultado['success']) {
                    $resultados[] = $tabla;
                } else {
                    $errores[] = $tabla . ': ' . $resultado['error'];
                }
            } else {
                $errores[] = $tabla . ': tabla no existe';
            }
        }
        
        return [
            'success' => count($resultados) > 0,
            'tablas_respaldadas' => $resultados,
            'errores' => $errores,
            'total' => count($resultados)
        ];
    }
    
    /**
     * Backup automatico diario
     */
    public function backupAutomaticoDiario() {
        return $this->generarBackupCompleto('completo');
    }
    
    /**
     * Limpiar todos los backups de todas las carpetas
     */
    public function limpiarTodosLosBackups() {
        $eliminados = 0;
        $tipos = ['completo', 'tablas', 'hora'];
        
        foreach ($tipos as $tipo) {
            $ruta = $this->backupDir . $tipo . '/';
            if (is_dir($ruta)) {
                $archivos = glob($ruta . '*.sql.gz');
                foreach ($archivos as $archivo) {
                    if (unlink($archivo)) {
                        $eliminados++;
                    }
                }
            }
        }
        
        return $eliminados;
    }
    
    /**
     * Obtener estructura CREATE TABLE
     */
    public function getTableStructure($tabla) {
        $stmt = $this->db->prepare("SHOW CREATE TABLE $tabla");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $sql = "-- --------------------------------------------------------\n";
        $sql .= "-- Estructura de tabla: `$tabla`\n";
        $sql .= "-- --------------------------------------------------------\n";
        $sql .= "DROP TABLE IF EXISTS `$tabla`;\n";
        $sql .= $row['Create Table'] . ";\n\n";
        
        return $sql;
    }
    
    /**
     * Obtener datos INSERT de la tabla
     */
    public function getTableData($tabla) {
        $stmt = $this->db->query("SELECT * FROM $tabla");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($rows) == 0) {
            return "-- Tabla `$tabla` vacia\n\n";
        }
        
        $sql = "-- --------------------------------------------------------\n";
        $sql .= "-- Datos de tabla: `$tabla`\n";
        $sql .= "-- --------------------------------------------------------\n";
        
        $columnas = array_keys($rows[0]);
        $columnasStr = implode('`, `', $columnas);
        
        $sql .= "INSERT INTO `$tabla` (`$columnasStr`) VALUES\n";
        
        $valores = [];
        foreach ($rows as $row) {
            $valoresFila = [];
            foreach ($row as $valor) {
                if ($valor === null) {
                    $valoresFila[] = 'NULL';
                } else {
                    $valoresFila[] = $this->db->quote($valor);
                }
            }
            $valores[] = "(" . implode(', ', $valoresFila) . ")";
        }
        
        $sql .= implode(",\n", $valores) . ";\n\n";
        
        return $sql;
    }
    
    /**
     * Comprimir backup en GZIP
     */
    private function comprimirBackup($rutaSql, $tipo, $nombreArchivo) {
        $contenido = file_get_contents($rutaSql);
        $rutaGz = $rutaSql . '.gz';
        $gz = gzopen($rutaGz, 'wb9');
        gzwrite($gz, $contenido);
        gzclose($gz);
        unlink($rutaSql);
        return $rutaGz;
    }
    
    /**
     * Restaurar backup
     */
    public function restaurarBackup($archivo, $tipo) {
        try {
            $rutaArchivo = $this->backupDir . $tipo . '/' . $archivo;
            
            if (!file_exists($rutaArchivo)) {
                return ['success' => false, 'error' => 'Archivo no encontrado'];
            }
            
            if (pathinfo($rutaArchivo, PATHINFO_EXTENSION) == 'gz') {
                $contenido = gzfile($rutaArchivo);
                $sql = implode('', $contenido);
            } else {
                $sql = file_get_contents($rutaArchivo);
            }
            
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            $comandos = explode(';', $sql);
            foreach ($comandos as $comando) {
                $comando = trim($comando);
                if (!empty($comando) && $comando != '') {
                    try {
                        $this->db->exec($comando);
                    } catch (PDOException $e) {
                        if (strpos($e->getMessage(), 'already exists') === false && 
                            strpos($e->getMessage(), 'Duplicate') === false) {
                            throw $e;
                        }
                    }
                }
            }
            
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            return ['success' => true, 'mensaje' => 'Base de datos restaurada correctamente'];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Listar backups disponibles
     */
    public function listarBackups() {
        $backups = [
            'completo' => [],
            'tablas' => [],
            'hora' => []
        ];
        
        foreach (['completo', 'tablas', 'hora'] as $tipo) {
            $ruta = $this->backupDir . $tipo . '/';
            if (!is_dir($ruta)) continue;
            
            $archivos = glob($ruta . '*.sql.gz');
            rsort($archivos);
            
            foreach ($archivos as $archivo) {
                $nombre = basename($archivo);
                $backups[$tipo][] = [
                    'nombre' => $nombre,
                    'tamano' => $this->formatearTamano(filesize($archivo)),
                    'fecha' => date('Y-m-d H:i:s', filemtime($archivo)),
                    'peso_bytes' => filesize($archivo)
                ];
            }
        }
        
        return $backups;
    }
    
    /**
     * Descargar backup
     */
    public function descargarBackup($archivo, $tipo) {
        $rutaArchivo = $this->backupDir . $tipo . '/' . $archivo;
        
        if (file_exists($rutaArchivo)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $archivo . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($rutaArchivo));
            readfile($rutaArchivo);
            exit;
        }
        
        return false;
    }
    
    /**
     * Obtener lista de todas las tablas
     */
    public function getListaTablas() {
        $stmt = $this->db->query("SHOW TABLES");
        $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $infoTablas = [];
        foreach ($tablas as $tabla) {
            $stmtCount = $this->db->query("SELECT COUNT(*) FROM $tabla");
            $count = $stmtCount->fetchColumn();
            
            $infoTablas[] = [
                'nombre' => $tabla,
                'registros' => $count
            ];
        }
        
        return $infoTablas;
    }
    
    /**
     * Formatear tamano de archivo
     */
    public function formatearTamano($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' B';
        }
    }
    
    /**
     * Obtener estadisticas
     */
    public function getEstadisticas() {
        $backups = $this->listarBackups();
        $total = 0;
        $tamanoTotal = 0;
        
        foreach (['completo', 'tablas', 'hora'] as $tipo) {
            foreach ($backups[$tipo] as $b) {
                $total++;
                $tamanoTotal += $b['peso_bytes'];
            }
        }
        
        return [
            'total_backups' => $total,
            'tamano_total_formateado' => $this->formatearTamano($tamanoTotal)
        ];
    }
}
?>