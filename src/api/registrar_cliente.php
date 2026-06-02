<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../model/ClientesModel.php';

$data = json_decode(file_get_contents('php://input'), true);
$nombre = $data['nombre'] ?? '';
$telefono = $data['telefono'] ?? '';
$email = $data['email'] ?? '';

if (empty($nombre) || empty($telefono)) {
    echo json_encode(['success' => false, 'message' => 'Nombre y teléfono son requeridos']);
    exit;
}

// Verificar si ya existe un cliente con ese teléfono
$existe = ClientesModel::mdlBuscarClientePorTelefono($telefono);
if ($existe) {
    echo json_encode(['success' => false, 'message' => 'Ya existe un cliente con ese teléfono']);
    exit;
}

$datos = [
    "nombre" => $nombre,
    "telefono" => $telefono,
    "email" => $email
];

$resultado = ClientesModel::mdlRegistrarClienteRapido($datos);

if ($resultado != "error") {
    echo json_encode(['success' => true, 'id_cliente' => $resultado]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al registrar cliente']);
}
?>