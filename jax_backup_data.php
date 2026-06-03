<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

header('Content-Type: application/json');

$basePath = realpath(__DIR__ . '/../../../');
require_once $basePath . "/controller/backupController.php";

$backupController = new BackupController();
$backups = $backupController->obtenerBackups();
$estadisticas = $backupController->obtenerEstadisticas();

// Obtener backups por hora
$backupHoraDir = realpath(__DIR__ . '/../../../Database/backups_hora/');
$hourlyBackups = [];

if ($backupHoraDir && is_dir($backupHoraDir)) {
    $archivos = glob($backupHoraDir . 'pos_zapateria_backup_hora_*.sql.gz');
    rsort($archivos);
    
    foreach ($archivos as $archivo) {
        $hourlyBackups[] = [
            'nombre' => basename($archivo),
            'tamano' => formatSize(filesize($archivo)),
            'fecha' => date('Y-m-d H:i:s', filemtime($archivo))
        ];
    }
}

// Preparar respuesta
$response = [
    'success' => true,
    'stats' => [
        'total_backups' => $estadisticas['total_backups'],
        'tamano_total' => $estadisticas['tamano_total_formateado'],
        'ultimo_backup' => $estadisticas['ultimo_backup_formateado'],
        'manual_count' => $estadisticas['por_tipo']['manual']['cantidad'],
        'auto_count' => $estadisticas['por_tipo']['automatico']['cantidad'],
        'hourly_count' => count($hourlyBackups)
    ],
    'manual_backups' => [],
    'auto_backups' => [],
    'hourly_backups' => $hourlyBackups
];

// Procesar backups manuales
foreach ($backups['manual'] as $backup) {
    $response['manual_backups'][] = [
        'nombre' => $backup['nombre'],
        'tamano' => $backup['tamano'],
        'fecha' => $backup['fecha'],
        'tipo_backup' => $backup['tipo_backup'],
        'tabla' => isset($backup['tabla']) ? $backup['tabla'] : null
    ];
}

// Procesar backups automaticos
foreach ($backups['automatico'] as $backup) {
    $response['auto_backups'][] = [
        'nombre' => $backup['nombre'],
        'tamano' => $backup['tamano'],
        'fecha' => $backup['fecha'],
        'tipo_backup' => $backup['tipo_backup'],
        'tabla' => isset($backup['tabla']) ? $backup['tabla'] : null
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);

function formatSize($bytes) {
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
?>