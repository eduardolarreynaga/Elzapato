<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../model/conexion.php';

session_start();

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Obtener parámetros
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 5;
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

$offset = ($pagina - 1) * $limite;

try {
    $conexion = Conexion::conectar();
    
    // Construir consulta base
    $sqlBase = "FROM ventas v 
                INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                LEFT JOIN metodos_pago mp ON v.id_metodo_pago = mp.id_metodo_pago
                WHERE (v.estado = 'completada' OR v.estado IS NULL)";
    
    $params = [];
    
    if (!empty($buscar)) {
        $sqlBase .= " AND (v.id_venta LIKE :buscar OR u.nombre_usuario LIKE :buscar)";
        $params[':buscar'] = "%$buscar%";
    }
    
    // Contar total
    $stmtCount = $conexion->prepare("SELECT COUNT(*) as total " . $sqlBase);
    foreach ($params as $key => $value) {
        $stmtCount->bindValue($key, $value);
    }
    $stmtCount->execute();
    $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Obtener ventas
    $sql = "SELECT 
                v.id_venta, 
                v.fecha_venta, 
                v.total_venta,
                u.nombre_usuario as usuario,
                mp.nombre_metodo as metodo_pago
            " . $sqlBase . "
            ORDER BY v.id_venta DESC
            LIMIT :limite OFFSET :offset";
    
    $stmt = $conexion->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'ventas' => $ventas,
        'total' => $total,
        'pagina' => $pagina,
        'limite' => $limite,
        'total_paginas' => ceil($total / $limite)
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener ventas: ' . $e->getMessage()]);
}
?>