<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../model/conexion.php';

session_start();

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$idVenta = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idVenta <= 0) {
    echo json_encode(['error' => 'ID de venta inválido']);
    exit;
}

try {
    $conexion = Conexion::conectar();
    
    $stmt = $conexion->prepare("
        SELECT 
            dv.id_detalle_venta,
            dv.cantidad as cantidad_original,
            dv.cantidad as cantidad_maxima,
            dv.precio_unitario,
            dv.subtotal,
            p.nombre_producto,
            pv.talla,
            pv.color,
            pv.id_variante
        FROM detalle_venta dv
        INNER JOIN producto_variante pv ON dv.id_variante = pv.id_variante
        INNER JOIN productos p ON pv.id_producto = p.id_producto
        WHERE dv.id_venta = :id_venta
        ORDER BY dv.id_detalle_venta ASC
    ");
    
    $stmt->bindParam(':id_venta', $idVenta, PDO::PARAM_INT);
    $stmt->execute();
    
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'detalles' => $detalles
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener detalles: ' . $e->getMessage()]);
}
?>