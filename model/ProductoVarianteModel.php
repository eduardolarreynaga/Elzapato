<?php
require_once "conexion.php";

class ProductoVarianteModel {

    /*=============================================
    MOSTRAR TODO EL STOCK
    =============================================*/
    static public function mdlObtenerTodoElStock() {
        $stmt = Conexion::conectar()->prepare("SELECT 
                    p.nombre_producto, 
                    v.talla, 
                    v.color, 
                    v.codigo_barras, 
                    v.precio_venta, 
                    v.stock 
                FROM productos p 
                INNER JOIN producto_variante v ON p.id_producto = v.id_producto 
                WHERE v.estado = 'activo'
                ORDER BY p.nombre_producto ASC");

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*=============================================
    OBTENER STOCK BAJO
    =============================================*/
    static public function mdlObtenerStockBajo($umbral) {
        $stmt = Conexion::conectar()->prepare("SELECT 
                    p.nombre_producto, 
                    v.talla, 
                    v.color, 
                    v.stock 
                FROM productos p 
                INNER JOIN producto_variante v ON p.id_producto = v.id_producto 
                WHERE v.stock < :umbral AND v.estado = 'activo'
                ORDER BY v.stock ASC");

        $stmt->bindParam(":umbral", $umbral, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*=============================================
    OBTENER RESUMEN DE ESTADÍSTICAS
    =============================================*/
    static public function mdlObtenerResumenReportes() {
        $con = Conexion::conectar();

        // 1. Total Tickets
        $stmtTickets = $con->prepare("SELECT COUNT(*) FROM ventas");
        $stmtTickets->execute();
        $tickets = $stmtTickets->fetchColumn();

        // 2. Alertas actuales
        $stmtAlertas = $con->prepare("SELECT COUNT(*) FROM producto_variante WHERE stock < 10 AND estado = 'activo'");
        $stmtAlertas->execute();
        $alertas = $stmtAlertas->fetchColumn();

        // 3. Clientes únicos con compras
        $stmtClientes = $con->prepare("SELECT COUNT(DISTINCT id_cliente) FROM ventas");
        $stmtClientes->execute();
        $clientes = $stmtClientes->fetchColumn();

        // 4. Ingresos (Ventas)
        $stmtIngresos = $con->prepare("SELECT IFNULL(SUM(total_venta), 0) FROM ventas");
        $stmtIngresos->execute();
        $ingresos = $stmtIngresos->fetchColumn();

        // 5. Egresos (Compras)
        $stmtEgresos = $con->prepare("SELECT IFNULL(SUM(cantidad * precio_unitario), 0) FROM detalle_compra");
        $stmtEgresos->execute();
        $egresos = $stmtEgresos->fetchColumn();

        return [
            "total_tickets" => $tickets ?: 0,
            "total_alertas" => $alertas ?: 0,
            "total_clientes" => $clientes ?: 0,
            "flujo_neto" => ($ingresos - $egresos)
        ];
    }

    /*=============================================
    MOSTRAR MOVIMIENTOS (UNION VENTAS Y COMPRAS)
    =============================================*/
    static public function mdlMostrarMovimientos($tabla) {
        $stmt = Conexion::conectar()->prepare("
            (SELECT 
                c.fecha_compra as fecha, 
                'Entrada' as tipo, 
                CONCAT('Compra #', c.id_compra) as referencia, 
                p.nombre_producto as producto, 
                CONCAT('+', dc.cantidad) as cantidad
            FROM compras c
            INNER JOIN detalle_compra dc ON c.id_compra = dc.id_compra
            /* Ajuste: Vinculamos con producto_variante para llegar al nombre del producto */
            INNER JOIN producto_variante pv ON dc.id_variante = pv.id_variante
            INNER JOIN productos p ON pv.id_producto = p.id_producto)
            
            UNION ALL

            (SELECT 
                v.fecha_venta as fecha, 
                'Salida' as tipo, 
                CONCAT('Venta #', v.id_venta) as referencia, 
                p.nombre_producto as producto, 
                CONCAT('-', dv.cantidad) as cantidad
            FROM ventas v
            INNER JOIN detalle_venta dv ON v.id_venta = dv.id_venta
            INNER JOIN producto_variante pv ON dv.id_variante = pv.id_variante
            INNER JOIN productos p ON pv.id_producto = p.id_producto)

            ORDER BY fecha DESC LIMIT 50");

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
