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
            $saldo = $cajaUsuario->getSaldoActual($id_usuario);
            echo json_encode([
                'success' => true,
                'abierta' => true,
                'monto_inicial' => $apertura['monto_inicial'],
                'saldo_actual' => $saldo,
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
            $saldo_actual = $cajaUsuario->getSaldoActual($id_usuario);
            echo json_encode([
                'success' => true, 
                'total_ventas' => $stats['total_ventas'],
                'total_ingresos' => $stats['total_ingresos'],
                'total_vuelto' => $stats['total_vuelto'],
                'monto_inicial' => $apertura['monto_inicial'],
                'saldo_esperado' => $saldo_actual
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No hay caja abierta']);
        }
        break;
        
    case 'cerrar_caja':
        // ELIMINAMOS el monto_cierre manual, se calcula automático
        $result = $cajaUsuario->cerrarCaja($id_usuario);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
        break;
}
?>