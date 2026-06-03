<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../model/conexion.php';
require_once __DIR__ . '/../../model/CajaUsuario.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$db = Conexion::conectar();
$cajaUsuario = new CajaUsuario();
$id_usuario = $_SESSION['id_usuario'];

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'verificar_estado':
        $apertura = $cajaUsuario->tieneCajaAbierta($id_usuario);
        $monto_asignado = $cajaUsuario->getMontoAsignado($id_usuario);
        
        if ($apertura) {
            // Obtener total de devoluciones del turno actual
            $stmtDev = $db->prepare("
                SELECT COALESCE(SUM(dv.total_devuelto), 0) as total_devoluciones 
                FROM devoluciones_venta dv
                INNER JOIN ventas v ON dv.id_venta = v.id_venta
                WHERE v.id_usuario = ? AND dv.fecha_devolucion >= ?
            ");
            $stmtDev->execute([$id_usuario, $apertura['fecha_apertura']]);
            $devoluciones = $stmtDev->fetch(PDO::FETCH_ASSOC);
            
            // Saldo real = monto_inicial + total_ingresos - total_vuelto - devoluciones
            $saldoReal = $apertura['monto_inicial'] + $apertura['total_ingresos'] - $apertura['total_vuelto'] - ($devoluciones['total_devoluciones'] ?? 0);
            
            echo json_encode([
                'success' => true,
                'abierta' => true,
                'monto_inicial' => $apertura['monto_inicial'],
                'saldo_actual' => $saldoReal,
                'total_ingresos' => $apertura['total_ingresos'],
                'total_vuelto' => $apertura['total_vuelto'],
                'total_devoluciones' => $devoluciones['total_devoluciones'],
                'monto_asignado' => $monto_asignado,
                'fecha_apertura' => $apertura['fecha_apertura']
            ]);
        } else {
            echo json_encode([
                'success' => true, 
                'abierta' => false,
                'monto_asignado' => $monto_asignado
            ]);
        }
        break;
        
    case 'abrir_caja':
        $monto_asignado = $cajaUsuario->getMontoAsignado($id_usuario);
        $caja_asignada = $cajaUsuario->getCajaAsignada($id_usuario);
        
        if (!$caja_asignada) {
            echo json_encode(['success' => false, 'error' => 'No tienes una caja asignada. Contacta al administrador.']);
            break;
        }
        
        $result = $cajaUsuario->abrirCaja($id_usuario, $caja_asignada, $monto_asignado);
        echo json_encode($result);
        break;
        
    case 'obtener_estadisticas':
        $apertura = $cajaUsuario->tieneCajaAbierta($id_usuario);
        if ($apertura) {
            $stats = $cajaUsuario->getEstadisticasTurno($apertura['id_apertura']);
            
            // Obtener devoluciones
            $stmtDev = $db->prepare("
                SELECT COALESCE(SUM(dv.total_devuelto), 0) as total_devoluciones 
                FROM devoluciones_venta dv
                INNER JOIN ventas v ON dv.id_venta = v.id_venta
                WHERE v.id_usuario = ? AND dv.fecha_devolucion >= ?
            ");
            $stmtDev->execute([$id_usuario, $apertura['fecha_apertura']]);
            $devoluciones = $stmtDev->fetch(PDO::FETCH_ASSOC);
            
            $saldo_actual = $apertura['monto_inicial'] + $stats['total_ingresos'] - $stats['total_vuelto'] - ($devoluciones['total_devoluciones'] ?? 0);
            
            echo json_encode([
                'success' => true, 
                'total_ventas' => $stats['total_ventas'],
                'total_ingresos' => $stats['total_ingresos'],
                'total_vuelto' => $stats['total_vuelto'],
                'total_devoluciones' => $devoluciones['total_devoluciones'],
                'monto_inicial' => $apertura['monto_inicial'],
                'saldo_esperado' => $saldo_actual
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No hay caja abierta']);
        }
        break;
        
    case 'cerrar_caja':
        $result = $cajaUsuario->cerrarCaja($id_usuario);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
        break;
}
?>