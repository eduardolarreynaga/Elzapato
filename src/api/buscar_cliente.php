<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../model/conexion.php';
require_once __DIR__ . '/../../model/ClientesModel.php';

$data = json_decode(file_get_contents('php://input'), true);
$criterio = $data['criterio'] ?? 'telefono';
$valor = $data['valor'] ?? '';

if (empty($valor)) {
    echo json_encode(['success' => false, 'message' => 'Debe ingresar un valor para buscar']);
    exit;
}

$db = Conexion::conectar();

if ($criterio === 'telefono') {
    $stmt = $db->prepare("SELECT id_cliente, nombre, telefono, email, total_compras, total_gastado FROM clientes WHERE telefono = :valor");
    $stmt->bindParam(':valor', $valor);
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Búsqueda por nombre (coincidencia parcial)
    $valorLike = '%' . $valor . '%';
    $stmt = $db->prepare("SELECT id_cliente, nombre, telefono, email, total_compras, total_gastado FROM clientes WHERE nombre LIKE :valor ORDER BY total_gastado DESC");
    $stmt->bindParam(':valor', $valorLike);
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (!empty($clientes)) {
    $resultados = [];
    foreach ($clientes as $cliente) {
        // Calcular descuento
        $total_compras = $cliente['total_compras'] ?? 0;
        $total_gastado = $cliente['total_gastado'] ?? 0;
        
        if ($total_compras >= 20 || $total_gastado >= 2000) {
            $descuento = 15;
            $nivel = 'VIP Diamante';
        } elseif ($total_compras >= 10 || $total_gastado >= 1000) {
            $descuento = 10;
            $nivel = 'VIP Oro';
        } elseif ($total_compras >= 5 || $total_gastado >= 500) {
            $descuento = 5;
            $nivel = 'VIP Plata';
        } else {
            $descuento = 0;
            $nivel = 'Regular';
        }
        
        $resultados[] = [
            'id_cliente' => $cliente['id_cliente'],
            'nombre' => $cliente['nombre'],
            'telefono' => $cliente['telefono'],
            'email' => $cliente['email'],
            'total_compras' => $total_compras,
            'total_gastado' => $total_gastado,
            'descuento' => $descuento,
            'nivel' => $nivel
        ];
    }
    
    echo json_encode([
        'success' => true,
        'clientes' => $resultados,
        'total' => count($resultados)
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se encontraron clientes']);
}
?>