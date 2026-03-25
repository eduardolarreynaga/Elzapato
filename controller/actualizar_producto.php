<?php
require_once "../model/ProductosModel.php";

// Recibimos los datos del JSON
$datosJson = file_get_contents("php://input");
$res = json_decode($datosJson, true);

if($res) {
    // Llamamos a una nueva función en tu modelo
    $respuesta = ProductosModel::mdlActualizarProducto($res);
    echo $respuesta; // Devolverá "ok" o el error
}
