<?php
require_once __DIR__ . '/../../controller/ventasController.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$all = isset($_GET['all']) && $_GET['all'] === '1';

if ($all) {
	VentasController::obtenerVentas(null);
} else {
	VentasController::obtenerUltimasVentas();
}
?>