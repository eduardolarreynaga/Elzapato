<?php
require_once "conexion.php";

class VentasModel {
    static public function mdlMostrarVentas($tabla) {
        $stmt = Conexion::conectar()->prepare("
            SELECT 
                v.id_venta,
                v.fecha_venta,
                v.total_venta,
                v.id_metodo_pago AS metodo_pago, 
                c.nombre AS nombre_cliente, 
                u.nombre_usuario
            FROM $tabla v
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
            ORDER BY v.id_venta DESC
        ");

        $stmt->execute();
        return $stmt->fetchAll();
    }


}