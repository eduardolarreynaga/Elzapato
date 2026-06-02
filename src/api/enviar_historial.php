<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
ob_clean();

$basePath = realpath(__DIR__ . '/../../');

require_once $basePath . '/src/config/auth.php';
require_once $basePath . '/helpers/MailHelper.php';
require_once $basePath . '/helpers/PDFHelper.php';
require_once $basePath . '/model/conexion.php';
require_once $basePath . '/helpers/LogHelper.php';

header('Content-Type: application/json');

if (!is_authenticated() || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Obtener parámetros
$para = $_POST['email'] ?? '';
$nombre_destinatario = $_POST['nombre'] ?? 'Administrador';
$fecha_desde = $_POST['fecha_desde'] ?? date('Y-m-01');
$fecha_hasta = $_POST['fecha_hasta'] ?? date('Y-m-d');
$usuario_filtro = $_POST['usuario_id'] ?? '0';
$tipo_accion = $_POST['tipo_accion'] ?? 'todos';

if (empty($para)) {
    echo json_encode(['success' => false, 'message' => 'El correo electrónico es requerido']);
    exit;
}

if (!filter_var($para, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Correo electrónico inválido']);
    exit;
}

try {
    $db = Conexion::conectar();
    $historial = [];

    // 1. LOGS DE LA TABLA historial_logs
    if ($tipo_accion == 'todos' || in_array($tipo_accion, ['login', 'logout', 'login_fallido', 'crear', 'editar', 'eliminar', 'asignar_dinero'])) {
        $logs = LogHelper::obtenerLogs($fecha_desde, $fecha_hasta, intval($usuario_filtro), ($tipo_accion == 'todos' ? null : $tipo_accion), 500);
        foreach ($logs as $log) {
            $historial[] = $log;
        }
    }

    // 2. VENTAS
    if ($tipo_accion == 'todos' || $tipo_accion == 'venta') {
        $sql = "SELECT 
                   'venta' as accion,
                   v.id_venta as registro_id,
                   v.fecha_venta as fecha,
                   u.nombre_usuario as nombre_usuario,
                   u.rol as rol_usuario,
                   CONCAT('Venta #', v.id_venta, ' - $', FORMAT(v.total_venta, 2)) as detalle
                FROM ventas v
                INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                WHERE DATE(v.fecha_venta) BETWEEN :fecha_desde AND :fecha_hasta";
        if ($usuario_filtro > 0) {
            $sql .= " AND u.id_usuario = :usuario_id";
        }
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':fecha_desde', $fecha_desde);
        $stmt->bindParam(':fecha_hasta', $fecha_hasta);
        if ($usuario_filtro > 0) {
            $stmt->bindParam(':usuario_id', $usuario_filtro);
        }
        $stmt->execute();
        $historial = array_merge($historial, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // 3. DEVOLUCIONES
    if ($tipo_accion == 'todos' || $tipo_accion == 'devolucion') {
        $sql = "SELECT 
                   'devolucion' as accion,
                   dv.id_devolucion as registro_id,
                   dv.fecha_devolucion as fecha,
                   u.nombre_usuario as nombre_usuario,
                   u.rol as rol_usuario,
                   CONCAT('Devolución de venta #', dv.id_venta, ' - ', dv.cantidad_devuelta, ' unidades - $', FORMAT(dv.total_devuelto, 2)) as detalle
                FROM devoluciones_venta dv
                INNER JOIN usuarios u ON dv.id_usuario = u.id_usuario
                WHERE DATE(dv.fecha_devolucion) BETWEEN :fecha_desde AND :fecha_hasta";
        if ($usuario_filtro > 0) {
            $sql .= " AND u.id_usuario = :usuario_id";
        }
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':fecha_desde', $fecha_desde);
        $stmt->bindParam(':fecha_hasta', $fecha_hasta);
        if ($usuario_filtro > 0) {
            $stmt->bindParam(':usuario_id', $usuario_filtro);
        }
        $stmt->execute();
        $historial = array_merge($historial, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // 4. APERTURAS DE CAJA
    if ($tipo_accion == 'todos' || $tipo_accion == 'apertura_caja') {
        $sql = "SELECT 
                   'apertura_caja' as accion,
                   ca.id_apertura as registro_id,
                   ca.fecha_apertura as fecha,
                   u.nombre_usuario as nombre_usuario,
                   u.rol as rol_usuario,
                   CONCAT('Apertura de caja - Caja: ', COALESCE(c.nombre_caja, 'N/A'), ' - Monto inicial: $', FORMAT(ca.monto_inicial, 2)) as detalle
                FROM caja_aperturas ca
                INNER JOIN usuarios u ON ca.id_usuario = u.id_usuario
                LEFT JOIN cajas c ON ca.id_caja = c.id_caja
                WHERE DATE(ca.fecha_apertura) BETWEEN :fecha_desde AND :fecha_hasta";
        if ($usuario_filtro > 0) {
            $sql .= " AND u.id_usuario = :usuario_id";
        }
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':fecha_desde', $fecha_desde);
        $stmt->bindParam(':fecha_hasta', $fecha_hasta);
        if ($usuario_filtro > 0) {
            $stmt->bindParam(':usuario_id', $usuario_filtro);
        }
        $stmt->execute();
        $historial = array_merge($historial, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // 5. CIERRES DE CAJA
    if ($tipo_accion == 'todos' || $tipo_accion == 'cierre_caja') {
        $sql = "SELECT 
                   'cierre_caja' as accion,
                   ca.id_apertura as registro_id,
                   ca.fecha_cierre as fecha,
                   u.nombre_usuario as nombre_usuario,
                   u.rol as rol_usuario,
                   CONCAT('Cierre de caja - Caja: ', COALESCE(c.nombre_caja, 'N/A'), ' - Ventas: ', ca.total_ventas, ' - Ingresos: $', FORMAT(ca.total_ingresos, 2)) as detalle
                FROM caja_aperturas ca
                INNER JOIN usuarios u ON ca.id_usuario = u.id_usuario
                LEFT JOIN cajas c ON ca.id_caja = c.id_caja
                WHERE ca.fecha_cierre IS NOT NULL 
                AND DATE(ca.fecha_cierre) BETWEEN :fecha_desde AND :fecha_hasta";
        if ($usuario_filtro > 0) {
            $sql .= " AND u.id_usuario = :usuario_id";
        }
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':fecha_desde', $fecha_desde);
        $stmt->bindParam(':fecha_hasta', $fecha_hasta);
        if ($usuario_filtro > 0) {
            $stmt->bindParam(':usuario_id', $usuario_filtro);
        }
        $stmt->execute();
        $historial = array_merge($historial, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Ordenar por fecha descendente
    usort($historial, function($a, $b) {
        return strtotime($b['fecha']) - strtotime($a['fecha']);
    });

    // Obtener nombre del usuario filtrado
    $nombre_filtro = 'Todos';
    if ($usuario_filtro > 0) {
        $stmt = $db->prepare("SELECT nombre_usuario FROM usuarios WHERE id_usuario = :id");
        $stmt->bindParam(':id', $usuario_filtro);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $nombre_filtro = $user['nombre_usuario'] ?? 'Todos';
    }

    $tipos = [
        'todos' => 'Todos',
        'venta' => 'Ventas',
        'devolucion' => 'Devoluciones',
        'apertura_caja' => 'Aperturas de Caja',
        'cierre_caja' => 'Cierres de Caja',
        'login' => 'Inicios de Sesión',
        'logout' => 'Cierres de Sesión',
        'login_fallido' => 'Intentos Fallidos',
        'crear' => 'Creaciones',
        'editar' => 'Ediciones',
        'eliminar' => 'Eliminaciones'
    ];
    $tipo_texto = $tipos[$tipo_accion] ?? 'Todos';

    $filtros = [
        'fecha_desde' => $fecha_desde,
        'fecha_hasta' => $fecha_hasta,
        'usuario' => $nombre_filtro,
        'tipo' => $tipo_texto
    ];

    // Generar PDF del historial
    $pdf_content = PDFHelper::generarPDFHistorial($historial, $filtros, $_SESSION['usuario']);
    
    // Enviar correo con PDF adjunto
    $result = MailHelper::enviarHistorialPDF($para, $nombre_destinatario, $pdf_content, $filtros, count($historial));
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>