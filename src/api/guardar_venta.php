<?php
require_once __DIR__ . '/../../controller/ventasController.php';


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    VentasController::guardarVenta();
} elseif ($method === 'GET') {
    if (isset($_GET['action'])) {
        if ($_GET['action'] === 'inicializar') {
            VentasController::inicializarMetodosPago();
        }
    }
}
?>

