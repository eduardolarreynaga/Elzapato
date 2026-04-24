<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('pcre.backtrack_limit', '8000000');
ini_set('pcre.recursion_limit', '1000000');

require_once __DIR__ . '/../config/auth.php';
require_auth('admin');

$vendorPath = realpath(__DIR__ . '/../../vendor/autoload.php');
if (!$vendorPath || !file_exists($vendorPath)) {
    http_response_code(500);
    die('Vendor autoload no encontrado.');
}

require_once $vendorPath;

$basePath = realpath(__DIR__ . '/../../');
require_once $basePath . '/controller/ProductoVarianteController.php';
require_once $basePath . '/model/ProductoVarianteModel.php';
require_once $basePath . '/model/VentasModel.php';
require_once $basePath . '/controller/ventasController.php';
require_once $basePath . '/model/conexion.php';

use Mpdf\Mpdf;

function esc($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money($value) {
    return '$' . number_format((float)$value, 2);
}

$tab = $_GET['tab'] ?? 'panel1';
$tabsPermitidas = ['panel1', 'panel2', 'panel3', 'panel4', 'panel5', 'panel6'];
if (!in_array($tab, $tabsPermitidas, true)) {
    $tab = 'panel1';
}

$nombreSistema = defined('SYSTEM_NAME') ? SYSTEM_NAME : 'EL ZAPATO';
$stockBajoUmbral = defined('LOW_STOCK_THRESHOLD') ? (int)LOW_STOCK_THRESHOLD : 10;

$fechaDesdeInput = trim((string)($_GET['fecha_desde'] ?? ''));
$fechaHastaInput = trim((string)($_GET['fecha_hasta'] ?? ''));
$fechaDesde = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesdeInput) ? $fechaDesdeInput : date('Y-m-01');
$fechaHasta = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHastaInput) ? $fechaHastaInput : date('Y-m-d');
if ($fechaDesde > $fechaHasta) {
    $tmpFecha = $fechaDesde;
    $fechaDesde = $fechaHasta;
    $fechaHasta = $tmpFecha;
}

$fechaDesdeSql = $fechaDesde . ' 00:00:00';
$fechaHastaSql = $fechaHasta . ' 23:59:59';

$rangoImpresion = '<p class="helper"><strong>Rango:</strong> ' . esc($fechaDesde) . ' a ' . esc($fechaHasta) . '</p>';

$tituloPanel = [
    'panel1' => 'Reporte de Stock',
    'panel2' => 'Reporte de Alertas',
    'panel3' => 'Reporte de Movimientos',
    'panel4' => 'Reporte de Caja',
    'panel5' => 'Reporte de Clientes',
    'panel6' => 'Reporte de Tickets'
][$tab];

$htmlPanel = '';

if ($tab === 'panel1') {
    $rows = ProductoVarianteController::ctrMostrarTodoElStock();
    $htmlPanel .= '<table><thead><tr><th>Producto</th><th>Talla</th><th>Color</th><th>Código</th><th>Stock</th><th>Precio</th></tr></thead><tbody>';
    foreach ($rows as $f) {
        $htmlPanel .= '<tr>'
            . '<td>' . esc($f['nombre_producto'] ?? '') . '</td>'
            . '<td>' . esc($f['talla'] ?? '') . '</td>'
            . '<td>' . esc($f['color'] ?? '') . '</td>'
            . '<td>' . esc($f['codigo_barras'] ?? '') . '</td>'
            . '<td class="right">' . (int)($f['stock'] ?? 0) . '</td>'
            . '<td class="right">' . money($f['precio_venta'] ?? 0) . '</td>'
            . '</tr>';
    }
    if (empty($rows)) {
        $htmlPanel .= '<tr><td colspan="6" class="empty">Sin datos de stock.</td></tr>';
    }
    $htmlPanel .= '</tbody></table>';
}

if ($tab === 'panel2') {
    $rows = ProductoVarianteController::ctrMostrarStockBajo($stockBajoUmbral);
    $htmlPanel .= '<p class="helper">Umbral aplicado de stock bajo: ≤ ' . (int)$stockBajoUmbral . '</p>';
    $htmlPanel .= '<table><thead><tr><th>Producto</th><th>Talla</th><th>Stock</th><th>Nivel</th></tr></thead><tbody>';
    foreach ($rows as $a) {
        $stock = (int)($a['stock'] ?? 0);
        $nivel = $stock <= 5 ? 'Crítico' : 'Bajo';
        $cssNivel = $stock <= 5 ? 'critico' : 'bajo';
        $htmlPanel .= '<tr>'
            . '<td>' . esc($a['nombre_producto'] ?? '') . '</td>'
            . '<td>' . esc($a['talla'] ?? '') . '</td>'
            . '<td class="right">' . $stock . '</td>'
            . '<td><span class="badge ' . $cssNivel . '">' . $nivel . '</span></td>'
            . '</tr>';
    }
    if (empty($rows)) {
        $htmlPanel .= '<tr><td colspan="4" class="empty">No hay alertas en el umbral configurado.</td></tr>';
    }
    $htmlPanel .= '</tbody></table>';
}

if ($tab === 'panel3') {
    try {
        $con = Conexion::conectar();
        $stmtMov = $con->prepare("\n            (SELECT\n                c.fecha_compra as fecha,\n                'Entrada' as tipo,\n                CONCAT('Compra #', c.id_compra) as referencia,\n                p.nombre_producto as producto,\n                CONCAT('+', dc.cantidad) as cantidad\n            FROM compras c\n            INNER JOIN detalle_compra dc ON c.id_compra = dc.id_compra\n            INNER JOIN producto_variante pv ON dc.id_variante = pv.id_variante\n            INNER JOIN productos p ON pv.id_producto = p.id_producto\n            WHERE c.fecha_compra BETWEEN :desde AND :hasta)\n\n            UNION ALL\n\n            (SELECT\n                v.fecha_venta as fecha,\n                'Salida' as tipo,\n                CONCAT('Venta #', v.id_venta) as referencia,\n                p.nombre_producto as producto,\n                CONCAT('-', dv.cantidad) as cantidad\n            FROM ventas v\n            INNER JOIN detalle_venta dv ON v.id_venta = dv.id_venta\n            INNER JOIN producto_variante pv ON dv.id_variante = pv.id_variante\n            INNER JOIN productos p ON pv.id_producto = p.id_producto\n            WHERE v.fecha_venta BETWEEN :desde2 AND :hasta2)\n\n            ORDER BY fecha DESC\n            LIMIT 300\n        ");
        $stmtMov->bindParam(':desde', $fechaDesdeSql, PDO::PARAM_STR);
        $stmtMov->bindParam(':hasta', $fechaHastaSql, PDO::PARAM_STR);
        $stmtMov->bindParam(':desde2', $fechaDesdeSql, PDO::PARAM_STR);
        $stmtMov->bindParam(':hasta2', $fechaHastaSql, PDO::PARAM_STR);
        $stmtMov->execute();
        $rows = $stmtMov->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        $rows = [];
    }

    $htmlPanel .= $rangoImpresion;
    $htmlPanel .= '<table><thead><tr><th>Fecha</th><th>Tipo</th><th>Ref</th><th>Producto</th><th>Cantidad</th></tr></thead><tbody>';
    foreach ($rows as $m) {
        $cantidad = (string)($m['cantidad'] ?? '0');
        $cssMov = strpos($cantidad, '+') !== false ? 'entrada' : 'salida';
        $htmlPanel .= '<tr>'
            . '<td>' . (!empty($m['fecha']) ? date('d/m/Y H:i', strtotime($m['fecha'])) : '—') . '</td>'
            . '<td>' . esc($m['tipo'] ?? '') . '</td>'
            . '<td>' . esc($m['referencia'] ?? '') . '</td>'
            . '<td>' . esc($m['producto'] ?? '') . '</td>'
            . '<td class="right ' . $cssMov . '">' . esc($cantidad) . '</td>'
            . '</tr>';
    }
    if (empty($rows)) {
        $htmlPanel .= '<tr><td colspan="5" class="empty">No hay movimientos disponibles.</td></tr>';
    }
    $htmlPanel .= '</tbody></table>';
}

if ($tab === 'panel4') {
    $fechaInicio = $_GET['caja_desde'] ?? $fechaDesde;
    $fechaFin = $_GET['caja_hasta'] ?? $fechaHasta;
    $idCaja = isset($_GET['caja_id']) ? (int)$_GET['caja_id'] : 0;

    $reporteCaja = ProductoVarianteController::ctrObtenerReporteCaja($fechaInicio, $fechaFin, $idCaja);
    $resumen = $reporteCaja['resumen'] ?? [];
    $porCaja = $reporteCaja['por_caja'] ?? [];
    $detalle = $reporteCaja['detalle'] ?? [];
    $filtros = $reporteCaja['filtros'] ?? ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin, 'id_caja' => $idCaja];

    $htmlPanel .= '<div class="filtro-box">'
        . '<span><strong>Desde:</strong> ' . esc($filtros['fecha_inicio'] ?? '') . '</span>'
        . '<span><strong>Hasta:</strong> ' . esc($filtros['fecha_fin'] ?? '') . '</span>'
        . '<span><strong>Caja:</strong> ' . (((int)($filtros['id_caja'] ?? 0) === 0) ? 'Todas' : ('#' . (int)$filtros['id_caja'])) . '</span>'
        . '</div>';

    $htmlPanel .= '<div class="mini-stats">'
        . '<div class="mini-item"><span>Ventas</span><strong>' . (int)($resumen['total_ventas'] ?? 0) . '</strong></div>'
        . '<div class="mini-item"><span>Anuladas</span><strong>' . (int)($resumen['total_anuladas'] ?? 0) . '</strong></div>'
        . '<div class="mini-item"><span>Ingresos</span><strong>' . money($resumen['total_ingresos'] ?? 0) . '</strong></div>'
        . '<div class="mini-item"><span>Efectivo</span><strong>' . money($resumen['total_efectivo'] ?? 0) . '</strong></div>'
        . '<div class="mini-item"><span>Tarjeta</span><strong>' . money($resumen['total_tarjeta'] ?? 0) . '</strong></div>'
        . '<div class="mini-item"><span>Transferencia</span><strong>' . money($resumen['total_transferencia'] ?? 0) . '</strong></div>'
        . '</div>';

    $htmlPanel .= '<h4>Resumen por caja</h4>';
    $htmlPanel .= '<table><thead><tr><th>Caja</th><th>Ventas</th><th>Anuladas</th><th>Ingresos</th><th>Primera Venta</th><th>Última Venta</th></tr></thead><tbody>';
    foreach ($porCaja as $row) {
        $htmlPanel .= '<tr>'
            . '<td>' . esc($row['caja'] ?? '') . '</td>'
            . '<td class="right">' . (int)($row['total_ventas'] ?? 0) . '</td>'
            . '<td class="right">' . (int)($row['total_anuladas'] ?? 0) . '</td>'
            . '<td class="right">' . money($row['total_ingresos'] ?? 0) . '</td>'
            . '<td>' . (!empty($row['primera_venta']) ? date('d/m/Y H:i', strtotime($row['primera_venta'])) : '—') . '</td>'
            . '<td>' . (!empty($row['ultima_venta']) ? date('d/m/Y H:i', strtotime($row['ultima_venta'])) : '—') . '</td>'
            . '</tr>';
    }
    if (empty($porCaja)) {
        $htmlPanel .= '<tr><td colspan="6" class="empty">No hay resumen por caja para el rango seleccionado.</td></tr>';
    }
    $htmlPanel .= '</tbody></table>';

    $htmlPanel .= '<h4>Detalle de ventas</h4>';
    $htmlPanel .= '<table><thead><tr><th># Venta</th><th>Caja</th><th>Cajero</th><th>Método</th><th>Fecha</th><th>Total</th><th>Estado</th></tr></thead><tbody>';
    foreach ($detalle as $venta) {
        $estado = strtolower((string)($venta['estado'] ?? 'completada'));
        $estadoTxt = $estado === 'anulada' ? 'Anulada' : 'Completada';
        $estadoCss = $estado === 'anulada' ? 'anulada' : 'ok';
        $htmlPanel .= '<tr>'
            . '<td>V-' . str_pad((string)((int)($venta['id_venta'] ?? 0)), 6, '0', STR_PAD_LEFT) . '</td>'
            . '<td>' . esc($venta['caja'] ?? 'N/A') . '</td>'
            . '<td>' . esc($venta['nombre_usuario'] ?? 'N/A') . '</td>'
            . '<td>' . esc($venta['metodo_pago'] ?? 'N/A') . '</td>'
            . '<td>' . (!empty($venta['fecha_venta']) ? date('d/m/Y H:i', strtotime($venta['fecha_venta'])) : '—') . '</td>'
            . '<td class="right">' . money($venta['total_venta'] ?? 0) . '</td>'
            . '<td><span class="badge ' . $estadoCss . '">' . $estadoTxt . '</span></td>'
            . '</tr>';
    }
    if (empty($detalle)) {
        $htmlPanel .= '<tr><td colspan="7" class="empty">Sin ventas para los filtros indicados.</td></tr>';
    }
    $htmlPanel .= '</tbody></table>';
}

if ($tab === 'panel5') {
    $rows = ProductoVarianteController::ctrTodosClientesConResumen($fechaDesde, $fechaHasta);

    $htmlPanel .= $rangoImpresion;
    $htmlPanel .= '<table><thead><tr><th>Cliente</th><th>Tickets</th><th>Total</th><th>Última compra</th></tr></thead><tbody>';
    foreach ($rows as $c) {
        $htmlPanel .= '<tr>'
            . '<td>' . esc($c['cliente'] ?? '') . '</td>'
            . '<td class="right">' . (int)($c['tickets'] ?? 0) . '</td>'
            . '<td class="right">' . money($c['total_comprado'] ?? 0) . '</td>'
            . '<td>' . (!empty($c['ultima_compra']) ? date('d/m/Y', strtotime($c['ultima_compra'])) : '—') . '</td>'
            . '</tr>';
    }
    if (empty($rows)) {
        $htmlPanel .= '<tr><td colspan="4" class="empty">No hay clientes con compras para mostrar.</td></tr>';
    }
    $htmlPanel .= '</tbody></table>';
}

if ($tab === 'panel6') {
    $rows = VentasController::ctrMostrarVentas();
    if (!empty($rows)) {
        $rows = array_values(array_filter($rows, function ($ticket) use ($fechaDesde, $fechaHasta) {
            $fechaVenta = isset($ticket['fecha_venta']) ? substr((string)$ticket['fecha_venta'], 0, 10) : '';
            if ($fechaVenta === '') return false;
            return $fechaVenta >= $fechaDesde && $fechaVenta <= $fechaHasta;
        }));
    }

    $htmlPanel .= $rangoImpresion;
    $htmlPanel .= '<table><thead><tr><th># Venta</th><th>Cliente</th><th>Cajero</th><th>Método</th><th>Fecha</th><th>Total</th><th>Estado</th></tr></thead><tbody>';
    foreach ($rows as $t) {
        $estado = strtolower((string)($t['estado_venta'] ?? 'completada'));
        $estadoTxt = $estado === 'anulada' ? 'Anulada' : 'Completada';
        $estadoCss = $estado === 'anulada' ? 'anulada' : 'ok';
        $htmlPanel .= '<tr>'
            . '<td>V-' . str_pad((string)((int)($t['id_venta'] ?? 0)), 6, '0', STR_PAD_LEFT) . '</td>'
            . '<td>' . esc($t['nombre_cliente'] ?? 'Cliente Mostrador') . '</td>'
            . '<td>' . esc($t['nombre_usuario'] ?? 'N/A') . '</td>'
            . '<td>' . esc($t['metodo_pago'] ?? 'N/A') . '</td>'
            . '<td>' . (!empty($t['fecha_venta']) ? date('d/m/Y H:i', strtotime($t['fecha_venta'])) : '—') . '</td>'
            . '<td class="right">' . money($t['total_venta'] ?? 0) . '</td>'
            . '<td><span class="badge ' . $estadoCss . '">' . $estadoTxt . '</span></td>'
            . '</tr>';
    }
    if (empty($rows)) {
        $htmlPanel .= '<tr><td colspan="7" class="empty">No hay tickets de venta para mostrar.</td></tr>';
    }
    $htmlPanel .= '</tbody></table>';
}

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

$css = '
body { font-family: sans-serif; color: #3d2e24; }
.header-wrap { border-bottom: 2px solid #D6C0B3; padding-bottom: 10px; margin-bottom: 8px; }
.brand { font-size: 20px; font-weight: bold; color: #A67C52; }
.subtitle { font-size: 11px; color: #6f5d50; text-transform: uppercase; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
thead th { background: #AB886D; color: #fff; font-size: 10px; padding: 8px; text-align: left; border: 1px solid #AB886D; }
tbody td { font-size: 10px; padding: 7px; border: 1px solid #efe7e2; }
.right { text-align: right; }
.empty { text-align: center; color: #8f7d70; }
.helper { color: #7b6658; font-size: 10px; margin: 4px 0; }
.filtro-box { border: 1px solid #e7ddd4; background: #fbf8f6; padding: 8px; margin: 6px 0 10px; }
.filtro-box span { margin-right: 12px; font-size: 10px; }
.mini-stats { width: 100%; margin-bottom: 10px; }
.mini-item { display: inline-block; width: 31%; margin: 0 1% 8px 0; border: 1px solid #e8dfd8; padding: 6px; }
.mini-item span { display: block; font-size: 8px; text-transform: uppercase; color: #7b6658; }
.mini-item strong { display: block; margin-top: 2px; color: #4D3B2E; font-size: 12px; }
h4 { margin: 12px 0 6px; color: #4D3B2E; }
.badge { border-radius: 10px; padding: 2px 6px; font-size: 9px; font-weight: bold; }
.badge.bajo { background: #fff3e7; color: #bc6e32; }
.badge.critico, .badge.anulada { background: #fbe9e7; color: #772C24; }
.badge.ok { background: #eaf6ec; color: #8f7d70; }
.entrada { color: #8f7d70; font-weight: bold; }
.salida { color: #772C24; font-weight: bold; }
.footer { text-align: center; color: #8f7d70; font-size: 9px; border-top: 1px solid #eadfd7; padding-top: 5px; }
';

$header = '<div class="header-wrap"><table style="width:100%; border:none; margin:0;"><tr>'
    . '<td style="width:80px; border:none;">' . ($logoSrc !== '' ? '<img src="' . esc($logoSrc) . '" style="width:60px;">' : '') . '</td>'
    . '<td style="border:none;">'
    . '<div class="brand">' . esc($nombreSistema) . '</div>'
    . '<div class="subtitle">' . esc($tituloPanel) . '</div>'
    . '</td>'
    . '<td style="border:none; text-align:right; font-size:9px; color:#7b6658;">'
    . '<strong>Emisión:</strong> ' . date('d/m/Y h:i A') . '<br>'
    . '<strong>Usuario:</strong> ' . esc($_SESSION['usuario'] ?? 'admin')
    . '</td></tr></table></div>';

$html = '<!doctype html><html><head><meta charset="utf-8"><title>' . esc($tituloPanel) . '</title></head><body>'
    . $header
    . $htmlPanel
    . '</body></html>';

try {
    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'Letter',
        'margin_left' => 12,
        'margin_right' => 12,
        'margin_top' => 12,
        'margin_bottom' => 14,
        'tempDir' => $mpdfTempDir
    ]);

    $mpdf->SetHTMLFooter('<div class="footer">Documento generado por ' . esc($nombreSistema) . ' · Página {PAGENO}/{nbpg}</div>');
    $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

    $fileName = 'reporte_' . preg_replace('/[^a-z0-9_]+/i', '_', strtolower($tituloPanel)) . '_' . date('Ymd_His') . '.pdf';
    $mpdf->Output($fileName, \Mpdf\Output\Destination::INLINE);
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error al generar PDF: ' . esc($e->getMessage());
}
