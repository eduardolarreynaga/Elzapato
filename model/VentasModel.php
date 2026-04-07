<?php
require_once "conexion.php";

class VentasModel {

    static public function mdlMostrarVentas($tabla) {
        // Unimos con usuarios, clientes y metodos_pago
        // Usamos LEFT JOIN en clientes por si id_cliente es NULL (venta al mostrador)
        $stmt = Conexion::conectar()->prepare("
            SELECT 
                v.id_venta,
                v.fecha_venta,
                v.total_venta,
                u.nombre_usuario,
                COALESCE(c.nombre, 'Cliente Mostrador') as nombre_cliente,
                m.nombre_metodo as metodo_pago
            FROM $tabla v
            INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
            INNER JOIN metodos_pago m ON v.id_metodo_pago = m.id_metodo_pago
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            ORDER BY v.id_venta DESC
        ");

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}