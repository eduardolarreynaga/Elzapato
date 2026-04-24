<?php
require_once "conexion.php";

class ProductoVarianteModel {

    private static function detectarEsquemaCajas(PDO $con) {
        $tablaCajas = false;
        $columnaIdCajaUsuario = false;

        $stmtTabla = $con->query("SHOW TABLES LIKE 'cajas'");
        if ($stmtTabla && $stmtTabla->fetch()) {
            $tablaCajas = true;
        }

        $stmtColumna = $con->query("SHOW COLUMNS FROM usuarios LIKE 'id_caja'");
        if ($stmtColumna && $stmtColumna->fetch()) {
            $columnaIdCajaUsuario = true;
        }

        return [
            'tabla_cajas' => $tablaCajas,
            'usuarios_id_caja' => $columnaIdCajaUsuario
        ];
    }

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
                WHERE v.stock > 0 AND v.stock <= :umbral AND v.estado = 'activo'
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
        $umbralStockBajo = defined('LOW_STOCK_THRESHOLD') ? (int)LOW_STOCK_THRESHOLD : 10;

        // 1. Total Tickets
        $stmtTickets = $con->prepare("SELECT COUNT(*) FROM ventas");
        $stmtTickets->execute();
        $tickets = $stmtTickets->fetchColumn();

        // 2. Alertas actuales
        $stmtAlertas = $con->prepare("SELECT COUNT(*) FROM producto_variante WHERE stock > 0 AND stock <= :umbral AND estado = 'activo'");
        $stmtAlertas->bindParam(':umbral', $umbralStockBajo, PDO::PARAM_INT);
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

    static public function mdlTopClientes() {
        try {
            $con = Conexion::conectar();

            $stmt = $con->prepare("
                SELECT 
                    c.nombre AS cliente,
                    COUNT(v.id_venta) AS tickets,
                    SUM(v.total_venta) AS total_comprado,
                    MAX(v.fecha_venta) AS ultima_compra
                FROM ventas v
                INNER JOIN clientes c ON v.id_cliente = c.id_cliente
                GROUP BY c.id_cliente
                ORDER BY total_comprado DESC
                LIMIT 10
            ");

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en mdlTopClientes: " . $e->getMessage());
            return [];
        }
    }

    static public function mdlTodosClientesConResumen($fechaInicio = null, $fechaFin = null) {
        try {
            $con = Conexion::conectar();

            $usaRango = is_string($fechaInicio) && is_string($fechaFin)
                && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio)
                && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin);

            $joinRango = $usaRango ? ' AND DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin ' : '';

            $sql = "
                SELECT
                    c.nombre AS cliente,
                    COUNT(v.id_venta) AS tickets,
                    IFNULL(SUM(CASE WHEN COALESCE(v.estado, 'completada') <> 'anulada' THEN v.total_venta ELSE 0 END), 0) AS total_comprado,
                    MAX(v.fecha_venta) AS ultima_compra
                FROM clientes c
                LEFT JOIN ventas v ON v.id_cliente = c.id_cliente $joinRango
                GROUP BY c.id_cliente, c.nombre
                ORDER BY c.nombre ASC
            ";

            $stmt = $con->prepare($sql);
            if ($usaRango) {
                $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
                $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        } catch (PDOException $e) {
            error_log("Error en mdlTodosClientesConResumen: " . $e->getMessage());
            return [];
        }
    }

    static public function mdlResumenTickets() {
        try {
            $con = Conexion::conectar();

            $stmt = $con->prepare("
                SELECT 
                    'Hoy' AS periodo,
                    COUNT(*) AS tickets,
                    COUNT(*) AS promedio_dia,
                    IFNULL(AVG(total_venta),0) AS ticket_promedio
                FROM ventas
                WHERE DATE(fecha_venta) = CURDATE()

                UNION ALL

                SELECT 
                    'Últimos 7 días',
                    COUNT(*),
                    ROUND(COUNT(*)/7),
                    IFNULL(AVG(total_venta),0)
                FROM ventas
                WHERE fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)

                UNION ALL

                SELECT 
                    'Mes actual',
                    COUNT(*),
                    ROUND(COUNT(*) / DAY(CURDATE())),
                    IFNULL(AVG(total_venta),0)
                FROM ventas
                WHERE MONTH(fecha_venta) = MONTH(CURDATE())
                AND YEAR(fecha_venta) = YEAR(CURDATE())
            ");

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    static public function mdlObtenerCajasReporte() {
        try {
            $con = Conexion::conectar();
            $schema = self::detectarEsquemaCajas($con);

            if ($schema['tabla_cajas'] && $schema['usuarios_id_caja']) {
                $stmt = $con->prepare("\n                    SELECT\n                        c.id_caja,\n                        c.nombre_caja\n                    FROM cajas c\n                    WHERE c.estado = 'activa'\n                    ORDER BY c.nombre_caja ASC\n                ");
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

                if (!empty($rows)) {
                    return $rows;
                }
            }

            $stmtFallback = $con->prepare("\n                SELECT\n                    u.id_usuario AS id_caja,\n                    CONCAT('Caja ', u.nombre_usuario) AS nombre_caja\n                FROM usuarios u\n                ORDER BY u.nombre_usuario ASC\n            ");
            $stmtFallback->execute();
            return $stmtFallback->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error en mdlObtenerCajasReporte: ' . $e->getMessage());
            return [];
        }
    }

    static public function mdlObtenerReporteCaja($fechaInicio, $fechaFin, $idCaja = 0) {
        try {
            $con = Conexion::conectar();
            $schema = self::detectarEsquemaCajas($con);
            $usaCajas = $schema['tabla_cajas'] && $schema['usuarios_id_caja'];

            $fechaInicio = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$fechaInicio) ? $fechaInicio : date('Y-m-01');
            $fechaFin = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$fechaFin) ? $fechaFin : date('Y-m-d');
            $idCaja = (int)$idCaja;

            $joinCaja = $usaCajas ? 'LEFT JOIN cajas c ON u.id_caja = c.id_caja' : '';
            $campoCaja = $usaCajas
                ? "COALESCE(c.nombre_caja, CONCAT('Caja ', u.nombre_usuario))"
                : "CONCAT('Caja ', u.nombre_usuario)";
            $filtroCaja = '';

            if ($idCaja > 0) {
                $filtroCaja = $usaCajas ? ' AND c.id_caja = :id_caja ' : ' AND u.id_usuario = :id_caja ';
            }

            $sqlResumen = "
                SELECT
                    COUNT(*) AS total_ventas,
                    SUM(CASE WHEN v.estado = 'anulada' THEN 1 ELSE 0 END) AS total_anuladas,
                    IFNULL(SUM(CASE WHEN v.estado <> 'anulada' THEN v.total_venta ELSE 0 END), 0) AS total_ingresos,
                    IFNULL(SUM(CASE WHEN LOWER(COALESCE(mp.nombre_metodo, '')) = 'efectivo' AND v.estado <> 'anulada' THEN v.total_venta ELSE 0 END), 0) AS total_efectivo,
                    IFNULL(SUM(CASE WHEN LOWER(COALESCE(mp.nombre_metodo, '')) = 'tarjeta' AND v.estado <> 'anulada' THEN v.total_venta ELSE 0 END), 0) AS total_tarjeta,
                    IFNULL(SUM(CASE WHEN LOWER(COALESCE(mp.nombre_metodo, '')) = 'transferencia' AND v.estado <> 'anulada' THEN v.total_venta ELSE 0 END), 0) AS total_transferencia,
                    COUNT(DISTINCT u.id_usuario) AS cajeros_operando
                FROM ventas v
                INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                LEFT JOIN metodos_pago mp ON v.id_metodo_pago = mp.id_metodo_pago
                $joinCaja
                WHERE DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin
                $filtroCaja
            ";

            $stmtResumen = $con->prepare($sqlResumen);
            $stmtResumen->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmtResumen->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            if ($idCaja > 0) {
                $stmtResumen->bindParam(':id_caja', $idCaja, PDO::PARAM_INT);
            }
            $stmtResumen->execute();
            $resumen = $stmtResumen->fetch(PDO::FETCH_ASSOC) ?: [];

            $sqlPorCaja = "
                SELECT
                    $campoCaja AS caja,
                    COUNT(*) AS total_ventas,
                    SUM(CASE WHEN v.estado = 'anulada' THEN 1 ELSE 0 END) AS total_anuladas,
                    IFNULL(SUM(CASE WHEN v.estado <> 'anulada' THEN v.total_venta ELSE 0 END), 0) AS total_ingresos,
                    MIN(v.fecha_venta) AS primera_venta,
                    MAX(v.fecha_venta) AS ultima_venta
                FROM ventas v
                INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                LEFT JOIN metodos_pago mp ON v.id_metodo_pago = mp.id_metodo_pago
                $joinCaja
                WHERE DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin
                $filtroCaja
                GROUP BY caja
                ORDER BY total_ingresos DESC
            ";

            $stmtPorCaja = $con->prepare($sqlPorCaja);
            $stmtPorCaja->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmtPorCaja->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            if ($idCaja > 0) {
                $stmtPorCaja->bindParam(':id_caja', $idCaja, PDO::PARAM_INT);
            }
            $stmtPorCaja->execute();
            $porCaja = $stmtPorCaja->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $sqlDetalle = "
                SELECT
                    v.id_venta,
                    v.fecha_venta,
                    v.total_venta,
                    v.estado,
                    u.nombre_usuario,
                    $campoCaja AS caja,
                    COALESCE(mp.nombre_metodo, 'N/A') AS metodo_pago
                FROM ventas v
                INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                LEFT JOIN metodos_pago mp ON v.id_metodo_pago = mp.id_metodo_pago
                $joinCaja
                WHERE DATE(v.fecha_venta) BETWEEN :fecha_inicio AND :fecha_fin
                $filtroCaja
                ORDER BY v.fecha_venta DESC, v.id_venta DESC
                LIMIT 300
            ";

            $stmtDetalle = $con->prepare($sqlDetalle);
            $stmtDetalle->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmtDetalle->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
            if ($idCaja > 0) {
                $stmtDetalle->bindParam(':id_caja', $idCaja, PDO::PARAM_INT);
            }
            $stmtDetalle->execute();
            $detalle = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC) ?: [];

            return [
                'filtros' => [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'id_caja' => $idCaja
                ],
                'resumen' => [
                    'total_ventas' => (int)($resumen['total_ventas'] ?? 0),
                    'total_anuladas' => (int)($resumen['total_anuladas'] ?? 0),
                    'total_ingresos' => (float)($resumen['total_ingresos'] ?? 0),
                    'total_efectivo' => (float)($resumen['total_efectivo'] ?? 0),
                    'total_tarjeta' => (float)($resumen['total_tarjeta'] ?? 0),
                    'total_transferencia' => (float)($resumen['total_transferencia'] ?? 0),
                    'cajeros_operando' => (int)($resumen['cajeros_operando'] ?? 0)
                ],
                'por_caja' => $porCaja,
                'detalle' => $detalle
            ];
        } catch (PDOException $e) {
            error_log('Error en mdlObtenerReporteCaja: ' . $e->getMessage());
            return [
                'filtros' => [
                    'fecha_inicio' => date('Y-m-01'),
                    'fecha_fin' => date('Y-m-d'),
                    'id_caja' => 0
                ],
                'resumen' => [
                    'total_ventas' => 0,
                    'total_anuladas' => 0,
                    'total_ingresos' => 0,
                    'total_efectivo' => 0,
                    'total_tarjeta' => 0,
                    'total_transferencia' => 0,
                    'cajeros_operando' => 0
                ],
                'por_caja' => [],
                'detalle' => []
            ];
        }
    }
}
