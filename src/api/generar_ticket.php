<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/conexion.php';

// Obtener la conexión usando tu clase
try {
    $pdo = Conexion::conectar();
} catch (Exception $e) {
    die('Error de conexión: ' . $e->getMessage());
}

// 1. Obtener ID de venta
$id_venta = $_GET['id'] ?? null;

if (!$id_venta) {
    die('ID de venta no proporcionado');
}

try {
    // 2. Consultar datos de la venta
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

    // 3. Consultar detalles (productos)
    $queryDetalle = "SELECT dv.*, p.nombre_producto, pv.talla, pv.color, dv.precio_unitario
                      FROM detalle_venta dv
                      INNER JOIN producto_variante pv ON dv.id_variante = pv.id_variante
                      INNER JOIN productos p ON pv.id_producto = p.id_producto
                      WHERE dv.id_venta = ?";
    $stmtDetalle = $pdo->prepare($queryDetalle);
    $stmtDetalle->execute([$id_venta]);
    $productos = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

    // 4. Preparar el HTML del ticket con logo optimizado
    $logoPath = __DIR__ . '/../../Assets/img/logo.png';
    $logoBase64 = '';
    
    if (file_exists($logoPath)) {
        // Optimizar la imagen del logo
        $logoData = file_get_contents($logoPath);
        
        // Intentar optimizar la imagen si es PNG
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $logoPath);
        finfo_close($finfo);
        
        if ($mimeType == 'image/png') {
            // Redimensionar y optimizar PNG
            $src = imagecreatefrompng($logoPath);
            if ($src) {
                $width = imagesx($src);
                $height = imagesy($src);
                $newWidth = min($width, 150); // Máximo 150px de ancho
                $newHeight = ($newWidth / $width) * $height;
                
                $dst = imagecreatetruecolor($newWidth, $newHeight);
                
                // Preservar transparencia
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
                
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                
                // Guardar imagen optimizada en buffer
                ob_start();
                imagepng($dst, null, 9); // Compresión máxima
                $logoData = ob_get_clean();
                imagedestroy($src);
                imagedestroy($dst);
            }
        } elseif ($mimeType == 'image/jpeg') {
            // Optimizar JPEG
            $src = imagecreatefromjpeg($logoPath);
            if ($src) {
                $width = imagesx($src);
                $height = imagesy($src);
                $newWidth = min($width, 150);
                $newHeight = ($newWidth / $width) * $height;
                
                $dst = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                
                ob_start();
                imagejpeg($dst, null, 85); // Calidad 85%
                $logoData = ob_get_clean();
                imagedestroy($src);
                imagedestroy($dst);
            }
        }
        
        $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($logoData);
    }

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Ticket de Venta #' . $venta['id_venta'] . '</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: "Courier New", "Lucida Console", monospace;
                margin: 0;
                padding: 8px 6px;
                background: white;
                font-size: 9px;
                line-height: 1.3;
                color: #000000;
            }
            
            .ticket-container {
                width: 100%;
                max-width: 280px;
                margin: 0 auto;
            }
            
            .header {
                text-align: center;
                margin-bottom: 8px;
                padding-bottom: 6px;
                border-bottom: 1px dashed #000;
            }
            
            .logo-container {
                text-align: center;
                margin-bottom: 5px;
            }
            
            .logo {
                max-width: 60px;
                max-height: 60px;
                width: auto;
                height: auto;
                display: inline-block;
            }
            
            .store-name {
                font-size: 14px;
                font-weight: bold;
                letter-spacing: 1px;
                margin: 3px 0;
            }
            
            .store-info {
                font-size: 8px;
                color: #333;
                margin: 2px 0;
            }
            
            .divider {
                border-top: 1px dashed #000;
                margin: 6px 0;
            }
            
            .divider-solid {
                border-top: 1px solid #000;
                margin: 5px 0;
            }
            
            .info-section {
                margin-bottom: 8px;
                font-size: 8px;
            }
            
            .info-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 3px;
            }
            
            .info-label {
                font-weight: bold;
            }
            
            .products-table {
                width: 100%;
                border-collapse: collapse;
                margin: 6px 0;
            }
            
            .products-table th {
                text-align: left;
                padding: 4px 0;
                border-bottom: 1px solid #000;
                font-size: 8px;
                font-weight: bold;
            }
            
            .products-table td {
                padding: 4px 0;
                border-bottom: 1px dotted #ccc;
                vertical-align: top;
            }
            
            .product-name {
                font-size: 8px;
                font-weight: bold;
            }
            
            .product-variant {
                font-size: 7px;
                color: #555;
                margin-top: 1px;
            }
            
            .text-right {
                text-align: right;
            }
            
            .text-center {
                text-align: center;
            }
            
            .totals-section {
                margin: 8px 0;
                padding-top: 5px;
                border-top: 1px dashed #000;
            }
            
            .total-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 4px;
                font-size: 8px;
            }
            
            .total-row-grand {
                margin-top: 5px;
                padding-top: 5px;
                border-top: 1px solid #000;
                font-size: 10px;
                font-weight: bold;
            }
            
            .payment-section {
                margin: 8px 0;
                padding: 5px;
                text-align: center;
                border: 1px solid #000;
            }
            
            .footer {
                text-align: center;
                margin-top: 8px;
                padding-top: 6px;
                border-top: 1px dashed #000;
            }
            
            .thanks {
                font-size: 10px;
                font-weight: bold;
                margin-bottom: 4px;
            }
            
            .barcode {
                text-align: center;
                margin: 6px 0;
                font-family: "Courier New", monospace;
                font-size: 8px;
                letter-spacing: 1px;
            }
        </style>
    </head>
    <body>
        <div class="ticket-container">
            <!-- Header con Logo Optimizado -->
            <div class="header">';
    
    if ($logoBase64) {
        $html .= '<div class="logo-container">
                    <img src="' . $logoBase64 . '" class="logo" alt="Logo El Zapato">
                  </div>';
    }
    
    $html .= '  <div class="store-name">EL ZAPATO</div>
                <div class="store-info">Ilobasco, Cabañas, El Salvador</div>
                <div class="store-info">Tel: 2222-2222</div>
            </div>
            
            <!-- Info Cliente/Venta -->
            <div class="info-section">
                <div class="info-row">
                    <span class="info-label">TICKET:</span>
                    <span>#' . str_pad($venta['id_venta'], 8, "0", STR_PAD_LEFT) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">FECHA:</span>
                    <span>' . date('d/m/Y H:i', strtotime($venta['fecha_venta'])) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">CAJERO:</span>
                    <span>' . htmlspecialchars(substr($venta['nombre_usuario'], 0, 15)) . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">CLIENTE:</span>
                    <span>' . (isset($venta['cliente_nombre']) ? htmlspecialchars(substr($venta['cliente_nombre'], 0, 20)) : 'Consumidor Final') . '</span>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <!-- Productos -->
            <table class="products-table">
                <thead>
                    <tr>
                        <th width="50%">PRODUCTO</th>
                        <th width="15%" class="text-right">CANT</th>
                        <th width="17%" class="text-right">P.UNIT</th>
                        <th width="18%" class="text-right">SUBT</th>
                    </tr>
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
            $variantInfo = '<div class="product-variant">';
            if ($item['talla']) $variantInfo .= 'Talla: ' . $item['talla'];
            if ($item['talla'] && $item['color']) $variantInfo .= ' | ';
            if ($item['color']) $variantInfo .= 'Color: ' . $item['color'];
            $variantInfo .= '</div>';
        }
        
        $html .= '
                <tr>
                    <td>
                        <div class="product-name">' . htmlspecialchars(substr($item['nombre_producto'], 0, 20)) . '</div>
                        ' . $variantInfo . '
                    </td>
                    <td class="text-right">' . $cantidad . '</td>
                    <td class="text-right">$' . number_format($precioUnitario, 2) . '</td>
                    <td class="text-right">$' . number_format($subtotal, 2) . '</td>
                </tr>';
    }

    $html .= '
                </tbody>
            </table>
            
            <div class="divider"></div>
            
            <!-- Totales -->
            <div class="totals-section">
                <div class="total-row">
                    <span>SUBTOTAL</span>
                    <span>$' . number_format($subtotalGeneral, 2) . '</span>
                </div>
                <div class="total-row-grand">
                    <span>TOTAL</span>
                    <span>$' . number_format($venta['total_venta'], 2) . '</span>
                </div>
            </div>
            
            <!-- Información de Pago -->
            <div class="payment-section">
                <div><strong>PAGO:</strong> ' . htmlspecialchars($venta['nombre_metodo']) . '</div>
            </div>
            
            <!-- Código de Barras Simulado -->
            <div class="barcode">
                ' . str_pad($venta['id_venta'], 10, "0", STR_PAD_LEFT) . '
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <div class="thanks">¡GRACIAS POR SU COMPRA!</div>
                <div style="font-size: 8px;">Vuelva pronto</div>
            </div>
        </div>
    </body>
    </html>';

    // Configurar mPDF para ticket térmico estándar
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => [72, 150],
        'margin_left' => 4,
        'margin_right' => 4,
        'margin_top' => 4,
        'margin_bottom' => 4,
        'default_font_size' => 9,
        'default_font' => 'courier',
    ]);

    $mpdf->WriteHTML($html);

    // Si se solicita descargar (parámetro download=1)
    if (isset($_GET['download']) && $_GET['download'] == 1) {
        $nombreArchivo = 'ticket_' . $venta['id_venta'] . '.pdf';
        $mpdf->Output($nombreArchivo, 'D');
    } else {
        $mpdf->Output('ticket_' . $venta['id_venta'] . '.pdf', 'I');
    }
    
} catch (Exception $e) {
    die('Error al generar el ticket: ' . $e->getMessage());
}
?>