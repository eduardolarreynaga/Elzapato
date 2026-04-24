<?php
require_once __DIR__ . '/../model/VentasModel.php';

class VentasController {
    
    // Guardar una nueva venta
    public static function guardarVenta() {
        session_start();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            echo json_encode(["error" => "No se recibieron datos"]);
            return;
        }
        
        $idUsuario = $_SESSION['id_usuario'] ?? 1;
        $idCliente = $data['id_cliente'] ?? null;
        $idMetodoPago = $data['metodo_pago'] ?? 1;
        $total = $data['total'] ?? 0;
        $productos = $data['productos'] ?? [];
        
        if (empty($productos)) {
            echo json_encode(["error" => "No hay productos en la venta"]);
            return;
        }
        
        $result = VentasModel::guardarVenta($idUsuario, $idMetodoPago, $idCliente, $total, $productos);
        
        if ($result['success']) {
            echo json_encode(["success" => true, "id_venta" => $result['id_venta']]);
        } else {
            echo json_encode(["error" => $result['error']]);
        }
    }
    
    // Obtener últimas ventas
    public static function obtenerUltimasVentas() {
        $ventas = VentasModel::obtenerUltimasVentas(5);
        echo json_encode($ventas);
    }

    // Obtener ventas (últimas o todas)
    public static function obtenerVentas($limite = 5) {
        if ($limite === null) {
            $ventas = VentasModel::obtenerTodasLasVentas();
        } else {
            $ventas = VentasModel::obtenerUltimasVentas((int)$limite);
        }
        echo json_encode($ventas);
    }
    
    // Obtener detalle de venta
    public static function obtenerDetalleVenta($idVenta) {
        $detalle = VentasModel::obtenerDetalleVenta($idVenta);
        echo json_encode($detalle);
    }
    
    // Obtener información de venta
    public static function obtenerInfoVenta($idVenta) {
        $info = VentasModel::obtenerInfoVenta($idVenta);
        echo json_encode($info);
    }
    
    // MOSTRAR TODAS LAS VENTAS PARA EL PANEL DE ADMINISTRACIÓN
    public static function ctrMostrarVentas() {
        $ventas = VentasModel::obtenerTodasLasVentas();
        
        // Si hay error, devolver array vacío
        if (isset($ventas['error'])) {
            return [];
        }
        
        return $ventas;
    }

    public static function ctrObtenerMetodosPago() {
        return VentasModel::obtenerMetodosPago();
    }

    public static function actualizarMetodoPagoVenta($idVenta, $idMetodoPago) {
        return VentasModel::actualizarMetodoPagoVenta((int)$idVenta, (int)$idMetodoPago);
    }

    public static function cambiarEstadoVenta($idVenta, $estado) {
        return VentasModel::cambiarEstadoVenta((int)$idVenta, (string)$estado);
    }
    
    // Inicializar métodos de pago
    public static function inicializarMetodosPago() {
        $result = VentasModel::inicializarMetodosPago();
        echo json_encode(["success" => $result]);
    }
}
?>