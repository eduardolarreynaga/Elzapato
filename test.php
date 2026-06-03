<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

echo json_encode([
    'success' => true,
    'respuesta' => 'El API está funcionando correctamente ✅',
    'timestamp' => date('Y-m-d H:i:s')
]);