<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

$archivo = isset($_GET['archivo']) ? $_GET['archivo'] : null;

if (!$archivo) {
    die('Archivo no especificado');
}

// Seguridad: solo permitir archivos de backup_hora de pos_zapateria
if (!preg_match('/^pos_zapateria_backup_hora_.*\.sql\.gz$/', $archivo)) {
    die('Archivo no permitido');
}

$rutaArchivo = realpath(__DIR__ . '/../../../Database/backups_hora/' . $archivo);

if (!$rutaArchivo || !file_exists($rutaArchivo)) {
    die('Archivo no encontrado');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $archivo . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($rutaArchivo));
readfile($rutaArchivo);
exit;
?>