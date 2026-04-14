<?php
require_once __DIR__ . '/../../controller/ventasController.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

VentasController::obtenerUltimasVentas();
?>