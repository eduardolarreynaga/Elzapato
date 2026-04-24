<?php
require_once __DIR__ . '/../../controller/ventasController.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$idVenta = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idVenta <= 0) {
    echo json_encode(["error" => "ID de venta no válido"]);
    exit;
}

VentasController::obtenerDetalleVenta($idVenta);
?>