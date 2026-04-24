<?php
require_once __DIR__ . '/../config/auth.php';
require_auth('admin');
require_once __DIR__ . '/../../controller/ventasController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
$idVenta = (int)($payload['id_venta'] ?? 0);
$accion = (string)($payload['accion'] ?? '');
$estado = (string)($payload['estado'] ?? '');

if ($idVenta <= 0) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

if ($accion === 'cambiar_estado') {
    $resultado = VentasController::cambiarEstadoVenta($idVenta, $estado);
    echo json_encode($resultado);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Acción no soportada']);
exit;
