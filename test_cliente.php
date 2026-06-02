<?php
require_once __DIR__ . '/model/conexion.php';
require_once __DIR__ . '/model/ClientesModel.php';

echo "<h1>Prueba de actualización de cliente</h1>";

// Probar actualización del cliente 2
$id_cliente = 2;
$monto = 100;

echo "<p>Actualizando cliente ID: $id_cliente con monto: $$monto</p>";

$resultado = ClientesModel::mdlActualizarEstadisticasCliente($id_cliente, $monto);

echo "<p>Resultado: " . ($resultado ? "ÉXITO" : "FALLO") . "</p>";

// Verificar
$db = Conexion::conectar();
$stmt = $db->prepare("SELECT total_compras, total_gastado, ultima_compra FROM clientes WHERE id_cliente = :id");
$stmt->bindParam(':id', $id_cliente);
$stmt->execute();
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>Datos actualizados del cliente:</h3>";
echo "<pre>";
print_r($cliente);
echo "</pre>";
?>