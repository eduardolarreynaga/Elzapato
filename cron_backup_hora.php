<?php
/**
 * CRON PARA BACKUP POR HORA
 * Configurar en cPanel/CRON: 0 * * * * php /ruta/cron_backup_hora.php
 */

date_default_timezone_set('America/El_Salvador');

require_once dirname(__FILE__) . '/controller/backupController.php';

$logDir = dirname(__FILE__) . '/Database/';
if (!is_dir($logDir)) mkdir($logDir, 0777, true);
$logFile = $logDir . 'backup_hora.log';

try {
    $backupController = new BackupController();
    $resultado = $backupController->backupHoraPOS();
    
    $logEntry = date('Y-m-d H:i:s') . ' - ';
    if ($resultado['success']) {
        $logEntry .= 'EXITO: ' . $resultado['total'] . ' tablas';
    } else {
        $logEntry .= 'ERROR';
    }
    $logEntry .= "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
} catch (Exception $e) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . ' - EXCEPCION: ' . $e->getMessage() . "\n", FILE_APPEND);
}
?>