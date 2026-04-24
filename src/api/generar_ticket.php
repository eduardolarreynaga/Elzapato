<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/conexion.php';
ini_set('pcre.backtrack_limit', '5000000');

// --- INICIO DE CAMBIO: Carga de configuración dinámica ---
require_once __DIR__ . '/../config/auth.php';
$nombreSistema = defined('SYSTEM_NAME') ? SYSTEM_NAME : 'EL ZAPATO';
// --- FIN DE CAMBIO ---

try {
    $pdo = Conexion::conectar();
} catch (Exception $e) {
    die('Error de conexión: ' . $e->getMessage());
}

$id_venta = $_GET['id'] ?? null;

if (!$id_venta) {
    die('ID de venta no proporcionado');
}

try {
    $queryVenta = "SELECT v.*, u.nombre_usuario, c.nombre as cliente_nombre, m.nombre_metodo 
                   FROM ventas v
                   INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                   LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                   INNER JOIN metodos_pago m ON v.id_metodo_pago = m.id_metodo_pago
                   WHERE v.id_venta = ?";
    $stmt = $pdo->prepare($queryVenta);
    $stmt->execute([$id_venta]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        die('Venta no encontrada');
    }

    $queryDetalle = "SELECT dv.*, p.nombre_producto, pv.talla, pv.color, dv.precio_unitario
                      FROM detalle_venta dv
                      INNER JOIN producto_variante pv ON dv.id_variante = pv.id_variante
                      INNER JOIN productos p ON pv.id_producto = p.id_producto
                      WHERE dv.id_venta = ?";
    $stmtDetalle = $pdo->prepare($queryDetalle);
    $stmtDetalle->execute([$id_venta]);
    $productos = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

    $logoPath = realpath(__DIR__ . '/../../Assets/img/logopdf.png');
    if (!$logoPath || !file_exists($logoPath)) {
        $logoPath = realpath(__DIR__ . '/../../Assets/img/logo.png');
    }
    $logoSrc = ($logoPath && file_exists($logoPath)) ? str_replace('\\', '/', $logoPath) : '';

    $tmpDir = realpath(__DIR__ . '/../../tmp');
    if ($tmpDir === false) {
        $tmpDir = __DIR__ . '/../../tmp';
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }
    }
    $mpdfTempDir = $tmpDir . '/mpdf';
    if (!is_dir($mpdfTempDir)) {
        @mkdir($mpdfTempDir, 0775, true);
    }
    if (!is_writable($mpdfTempDir)) {
        $mpdfTempDir = sys_get_temp_dir();
    }

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Ticket de Venta #' . $venta['id_venta'] . '</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: "Courier New", "Lucida Console", monospace; margin: 0; padding: 8px 6px; background: white; font-size: 9px; line-height: 1.3; color: #000000; }
            .ticket-container { width: 100%; max-width: 280px; margin: 0 auto; }
            .header { text-align: center; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px dashed #000; }
            .logo-container { text-align: center; margin-bottom: 5px; }
            .logo { max-width: 60px; max-height: 60px; width: auto; height: auto; display: inline-block; }
            .store-name { font-size: 14px; font-weight: bold; letter-spacing: 1px; margin: 3px 0; text-transform: uppercase; }
            .store-info { font-size: 8px; color: #333; margin: 2px 0; }
            .divider { border-top: 1px dashed #000; margin: 6px 0; }
            .info-section { margin-bottom: 8px; font-size: 8px; }
            .info-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
            .info-label { font-weight: bold; }
            .products-table { width: 100%; border-collapse: collapse; margin: 6px 0; }
            .products-table th { text-align: left; padding: 4px 0; border-bottom: 1px solid #000; font-size: 8px; font-weight: bold; }
            .products-table td { padding: 4px 0; border-bottom: 1px dotted #ccc; vertical-align: top; }
            .product-name { font-size: 8px; font-weight: bold; }
            .product-variant { font-size: 7px; color: #555; margin-top: 1px; }
            .text-right { text-align: right; }
            .totals-section { margin: 8px 0; padding-top: 5px; border-top: 1px dashed #000; }
            .total-row { display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 8px; }
            .total-row-grand { margin-top: 5px; padding-top: 5px; border-top: 1px solid #000; font-size: 10px; font-weight: bold; }
            .payment-section { margin: 8px 0; padding: 5px; text-align: center; border: 1px solid #000; }
            .footer { text-align: center; margin-top: 8px; padding-top: 6px; border-top: 1px dashed #000; }
            .thanks { font-size: 10px; font-weight: bold; margin-bottom: 4px; }
            .barcode { text-align: center; margin: 6px 0; font-family: "Courier New", monospace; font-size: 8px; letter-spacing: 1px; }
        </style>
    </head>
    <body>
        <div class="ticket-container">
            <div class="header">';
    
    if ($logoSrc !== '') {
        $html .= '<div class="logo-container"><img src="' . htmlspecialchars($logoSrc, ENT_QUOTES, 'UTF-8') . '" class="logo"></div>';
    }
    
    $html .= '  <div class="store-name">' . $nombreSistema . '</div>
                <div class="store-info">Ilobasco, Cabañas, El Salvador</div>
                <div class="store-info">Tel: 2222-2222</div>
            </div>
            
            <div class="info-section">
                <div class="info-row"><span class="info-label">TICKET:</span><span>#' . str_pad($venta['id_venta'], 8, "0", STR_PAD_LEFT) . '</span></div>
                <div class="info-row"><span class="info-label">FECHA:</span><span>' . date('d/m/Y H:i', strtotime($venta['fecha_venta'])) . '</span></div>
                <div class="info-row"><span class="info-label">CAJERO:</span><span>' . htmlspecialchars(substr($venta['nombre_usuario'], 0, 15)) . '</span></div>
                <div class="info-row"><span class="info-label">CLIENTE:</span><span>' . (isset($venta['cliente_nombre']) ? htmlspecialchars(substr($venta['cliente_nombre'], 0, 20)) : 'Consumidor Final') . '</span></div>
            </div>
            
            <div class="divider"></div>
            
            <table class="products-table">
                <thead>
                    <tr><th width="50%">PRODUCTO</th><th width="15%" class="text-right">CANT</th><th width="17%" class="text-right">P.UNIT</th><th width="18%" class="text-right">SUBT</th></tr>
                </thead>
                <tbody>';

    $subtotalGeneral = 0;
    foreach ($productos as $item) {
        $precioUnitario = floatval($item['precio_unitario']);
        $cantidad = intval($item['cantidad']);
        $subtotal = $precioUnitario * $cantidad;
        $subtotalGeneral += $subtotal;
        
        $variantInfo = '';
        if ($item['talla'] || $item['color']) {
            $variantInfo = '<div class="product-variant">' . ($item['talla'] ? 'T:'.$item['talla'] : '') . ($item['talla'] && $item['color'] ? ' | ' : '') . ($item['color'] ? 'C:'.$item['color'] : '') . '</div>';
        }
        
        $html .= '<tr>
                    <td><div class="product-name">' . htmlspecialchars(substr($item['nombre_producto'], 0, 20)) . '</div>' . $variantInfo . '</td>
                    <td class="text-right">' . $cantidad . '</td>
                    <td class="text-right">$' . number_format($precioUnitario, 2) . '</td>
                    <td class="text-right">$' . number_format($subtotal, 2) . '</td>
                </tr>';
    }

    $html .= '</tbody></table>
            
            <div class="totals-section">
                <div class="total-row"><span>SUBTOTAL</span><span>$' . number_format($subtotalGeneral, 2) . '</span></div>
                <div class="total-row-grand"><span>TOTAL</span><span>$' . number_format($venta['total_venta'], 2) . '</span></div>
            </div>
            
            <div class="payment-section"><div><strong>PAGO:</strong> ' . htmlspecialchars($venta['nombre_metodo']) . '</div></div>
            <div class="barcode">' . str_pad($venta['id_venta'], 10, "0", STR_PAD_LEFT) . '</div>
            
            <div class="footer">
                <div class="thanks">¡GRACIAS POR SU COMPRA!</div>
                <div style="font-size: 8px;">Vuelva pronto - ' . $nombreSistema . '</div>
            </div>
        </div>
    </body>
    </html>';

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8', 'format' => [72, 150], 'margin_left' => 4, 'margin_right' => 4, 'margin_top' => 4, 'margin_bottom' => 4, 'default_font' => 'courier', 'tempDir' => $mpdfTempDir,
    ]);

    $mpdf->WriteHTML($html);
    $mpdf->Output('ticket_' . $venta['id_venta'] . '.pdf', 'I');
    
} catch (Exception $e) {
    die('Error al generar el ticket: ' . $e->getMessage());
}