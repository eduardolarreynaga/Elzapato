<?php
require_once __DIR__ . '/../helpers/BackupHelper.php';

class BackupController {
    
    private $backupHelper;
    
    public function __construct() {
        $this->backupHelper = new BackupHelper();
    }
    
    /**
     * Procesar acciones de backup
     */
    public function procesarBackup() {
        // Backup completo manual
        if (isset($_POST['accion_backup']) && $_POST['accion_backup'] == 'crear_completo') {
            $resultado = $this->backupHelper->generarBackupCompleto('completo');
            $_SESSION['backup_resultado'] = $resultado;
            header("Location: backup.php?res=creado");
            exit;
        }
        
        // Backup por tabla manual
        if (isset($_POST['accion_backup_tabla'])) {
            $tabla = $_POST['tabla_seleccionada'] ?? null;
            if ($tabla) {
                $resultado = $this->backupHelper->generarBackupTabla($tabla, 'tablas');
                $_SESSION['backup_resultado'] = $resultado;
                header("Location: backup.php?res=tabla_creado");
                exit;
            }
        }
        
        // Backup por hora (automatico desde cron)
        if (isset($_POST['accion_backup_hora'])) {
            $resultado = $this->backupHelper->generarBackupHoraPOS();
            $_SESSION['backup_hora_resultado'] = $resultado;
            header("Location: backup.php?res=hora_creado");
            exit;
        }
        
        // Limpiar todos los backups
        if (isset($_POST['accion_backup']) && $_POST['accion_backup'] == 'limpiar_todo') {
            $eliminados = $this->limpiarTodosLosBackups();
            $_SESSION['limpieza_resultado'] = ['eliminados' => $eliminados];
            header("Location: backup.php");
            exit;
        }
    }
    
    /**
     * Limpiar todos los backups de todas las carpetas
     */
    private function limpiarTodosLosBackups() {
        $eliminados = 0;
        $tipos = ['completo', 'tablas', 'hora'];
        
        foreach ($tipos as $tipo) {
            $ruta = dirname(__DIR__) . '/Database/backups/' . $tipo . '/';
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
     * Procesar descarga de backup
     */
    public function procesarDescarga() {
        if (isset($_GET['accion_backup']) && $_GET['accion_backup'] == 'descargar') {
            $archivo = $_GET['archivo'] ?? null;
            $tipo = $_GET['tipo'] ?? 'completo';
            if ($archivo) {
                $this->backupHelper->descargarBackup($archivo, $tipo);
            }
        }
    }
    
    /**
     * Obtener listado de backups
     */
    public function obtenerBackups() {
        return $this->backupHelper->listarBackups();
    }
    
    /**
     * Obtener estadisticas
     */
    public function obtenerEstadisticas() {
        return $this->backupHelper->getEstadisticas();
    }
    
    /**
     * Obtener lista de tablas
     */
    public function obtenerTablas() {
        return $this->backupHelper->getListaTablas();
    }
    
    /**
     * Backup automatico diario (llamado por cron)
     */
    public function backupAutomaticoDiario() {
        return $this->backupHelper->backupAutomaticoDiario();
    }
    
    /**
     * Backup por hora POS (llamado por cron)
     */
    public function backupHoraPOS() {
        return $this->backupHelper->generarBackupHoraPOS();
    }
}
?>