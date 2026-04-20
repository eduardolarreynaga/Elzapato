<?php
// 1. Cargar dependencias y configuración
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/conexion.php';

$basePath = realpath(__DIR__ . '/../../../');
// Ajuste de rutas según tu estructura
require_once $basePath . "/ElZapato/controller/ProductoVarianteController.php";
require_once $basePath . "/ElZapato/model/ProductoVarianteModel.php";

use Mpdf\Mpdf;

$tipo = $_GET['tipo'] ?? '1';
$db = Conexion::conectar();
$tituloReporte = "";
$tablaHtml = "";

// --- PALETA DE COLORES OFICIAL ---
$c_primary_light = "#E4E0E1"; // Fondo muy claro
$c_primary_soft  = "#D6C0B3"; // Tono suave intermedio
$c_primary_dark  = "#AB886D"; // Tono oscuro para detalles
$c_nocolor       = "#772C24"; // Rojo para egresos/alertas
$c_text          = "#000000";

try {
    switch ($tipo) {
        case '1': // STOCK COMPLETO
            $tituloReporte = "Reporte de Inventario General";
            $datos = ProductoVarianteController::ctrMostrarTodoElStock();
            $tablaHtml = '<table><thead><tr><th>PRODUCTO</th><th width="10%">TALLA</th><th width="15%">COLOR</th><th width="20%">CÓDIGO</th><th width="10%">STOCK</th><th width="15%">PRECIO</th></tr></thead><tbody>';
            foreach ($datos as $d) {
                $tablaHtml .= "<tr><td>".htmlspecialchars($d['nombre_producto'])."</td><td class='center'>{$d['talla']}</td><td>{$d['color']}</td><td class='code'>{$d['codigo_barras']}</td><td class='center'>{$d['stock']}</td><td class='right'>$ " . number_format($d['precio_venta'], 2) . "</td></tr>";
            }
            $tablaHtml .= '</tbody></table>';
            break;

        case '2': // ALERTAS
            $tituloReporte = "Alertas de Reabastecimiento";
            $datos = ProductoVarianteController::ctrMostrarStockBajo(10);
            $tablaHtml = '<table><thead><tr><th>PRODUCTO CRÍTICO</th><th width="20%">TALLA</th><th width="20%">STOCK ACTUAL</th><th width="20%">ESTADO</th></tr></thead><tbody>';
            foreach ($datos as $d) {
                $statusColor = ($d['stock'] <= 5) ? $c_nocolor : '#e67e22';
                $statusText = ($d['stock'] <= 5) ? 'CRÍTICO' : 'BAJO';
                $tablaHtml .= "<tr><td>".htmlspecialchars($d['nombre_producto'])."</td><td class='center'>{$d['talla']}</td><td class='center' style='color: $statusColor; font-weight: bold;'>{$d['stock']}</td><td class='center' style='color: $statusColor; font-weight: bold;'>$statusText</td></tr>";
            }
            $tablaHtml .= '</tbody></table>';
            break;

        case '3': // MOVIMIENTOS
            $tituloReporte = "Historial de Movimientos de Inventario";
            $movimientos = ProductoVarianteController::ctrMostrarHistorialMovimientos();
            $tablaHtml = '<table><thead><tr><th width="18%">FECHA</th><th width="12%">TIPO</th><th width="15%">REF.</th><th>PRODUCTO</th><th width="12%" class="right">CANT.</th></tr></thead><tbody>';
            foreach ($movimientos as $m) {
                $color = (strpos($m['cantidad'], '+') !== false) ? '#AB886D' : $c_nocolor;
                $tablaHtml .= "<tr><td>".date("d/m/Y H:i", strtotime($m['fecha']))."</td><td>".htmlspecialchars($m['tipo'])."</td><td>".htmlspecialchars($m['referencia'])."</td><td>".htmlspecialchars($m['producto'])."</td><td class='right'><strong style='color: $color'>{$m['cantidad']}</strong></td></tr>";
            }
            $tablaHtml .= '</tbody></table>';
            break;

        case '4': // CAJA (SINCRONIZADO CON LA VISTA WEB)
            $tituloReporte = "Reporte de Flujo de Caja y Balance";
            $stats = ProductoVarianteController::ctrMostrarResumenReportes();
            $inventario = ProductoVarianteController::ctrMostrarTodoElStock();
            
            // Cálculo de egresos (Inversión estimada al 70% del valor venta)
            $egresos = 0;
            foreach($inventario as $inv) { $egresos += ($inv['precio_venta'] * 0.7) * $inv['stock']; }
            
            // Usamos abs() para asegurar que los ingresos se vean positivos como en la vista
            $ingresosPositivos = abs($stats['flujo_neto']);
            $balance = $ingresosPositivos - $egresos;

            $tablaHtml = '
                <div style="background-color: '.$c_primary_light.'; padding: 25px; border: 1px solid '.$c_primary_soft.'; border-radius: 8px;">
                    <table style="border: none; width: 100%;">
                        <tr>
                            <td style="border: none; width: 50%;">
                                <h4 style="color: #AB886D; margin-bottom: 5px; font-size: 11px;">INGRESOS TOTALES (VENTAS)</h4>
                                <span style="font-size: 22px; font-weight: bold; color: #AB886D;">$ '.number_format($ingresosPositivos, 2).'</span>
                            </td>
                            <td style="border: none; width: 50%; text-align: right;">
                                <h4 style="color: '.$c_nocolor.'; margin-bottom: 5px; font-size: 11px;">EGRESOS TOTALES (INVERSIÓN STOCK)</h4>
                                <span style="font-size: 22px; font-weight: bold; color: '.$c_nocolor.';">$ '.number_format($egresos, 2).'</span>
                            </td>
                        </tr>
                    </table>
                    <div style="border-top: 1px solid '.$c_primary_dark.'; margin: 15px 0; padding-top: 15px; text-align: center;">
                        <span style="font-size: 12px; color: #555; text-transform: uppercase;">Balance Operativo Estimado</span><br>
                        <span style="font-size: 26px; font-weight: bold; color: '.$c_text.';">$ '.number_format($balance, 2).'</span>
                    </div>
                </div>
                
                <h3 style="margin-top: 30px; border-bottom: 2px solid '.$c_primary_dark.'; padding-bottom: 5px; font-size: 14px;">DETALLES DE TRANSACCIONES</h3>
                <table style="margin-top: 10px;">
                    <thead>
                        <tr>
                            <th>CONCEPTO</th>
                            <th width="30%" class="right">MONTO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Ventas totales procesadas en sistema</td>
                            <td class="right" style="color: #AB886D; font-weight: bold;">+ $ '.number_format($ingresosPositivos, 2).'</td>
                        </tr>
                        <tr>
                            <td>Costo estimado de inventario actual</td>
                            <td class="right" style="color: '.$c_nocolor.'; font-weight: bold;">- $ '.number_format($egresos, 2).'</td>
                        </tr>
                        <tr>
                            <td>Tickets emitidos en el periodo</td>
                            <td class="right">'.$stats['total_tickets'].' transacciones</td>
                        </tr>
                    </tbody>
                </table>';
            break;

        default:
            die("<h1>Error: Tipo de reporte no válido.</h1>");
    }

    // --- ESTILOS CSS ---
    $css = "
        body { font-family: 'Helvetica', sans-serif; color: $c_text; }
        .header { border-bottom: 3px solid $c_primary_dark; padding-bottom: 10px; }
        .logo { width: 80px; float: left; }
        .brand-container { float: left; margin-left: 15px; margin-top: 5px; }
        .brand { font-size: 24px; font-weight: bold; letter-spacing: 3px; }
        .subtitle { font-size: 11px; color: #666; text-transform: uppercase; margin-top: 3px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: $c_primary_dark; color: #ffffff; padding: 10px; text-align: left; font-size: 9px; border: 1px solid $c_primary_dark; }
        td { padding: 8px; border-bottom: 1px solid $c_primary_soft; font-size: 10px; border-left: 0.1mm solid $c_primary_soft; border-right: 0.1mm solid $c_primary_soft; }
        
        .center { text-align: center; }
        .right { text-align: right; }
        .code { font-family: 'Courier', monospace; font-size: 9px; background-color: $c_primary_light; padding: 2px; }
        .footer { font-size: 8px; color: #95a5a6; text-align: center; border-top: 1px solid $c_primary_soft; padding-top: 5px; }
    ";

    $mpdf = new Mpdf([
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 45,
        'margin_bottom' => 20,
        'format' => 'Letter'
    ]);

    // --- ENCABEZADO CON LOGO ---
    $headerHtml = '
    <div class="header">
        <table style="border: none; width: 100%;">
            <tr>
                <td style="border: none; width: 15%;">
                    <img src="'.$basePath.'/ElZapato/Assets/img/logo.png" style="width: 70px;">
                </td>
                <td style="border: none; width: 50%; vertical-align: middle;">
                    <div class="brand">EL ZAPATO</div>
                    <div class="subtitle">'.$tituloReporte.'</div>
                </td>
                <td style="border: none; width: 35%; text-align: right; vertical-align: bottom; font-size: 8px;">
                    <b>EMISIÓN:</b> '.date('d/m/Y h:i A').'<br>
                    <b>GENERADO POR:</b> ADMINISTRADOR
                </td>
            </tr>
        </table>
    </div>';

    $mpdf->SetHTMLHeader($headerHtml);
    $mpdf->SetHTMLFooter('<div class="footer">Documento oficial generado por el Sistema de Gestión ElZapato. Página {PAGENO} de {nbpg}</div>');
    
    $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($tablaHtml, \Mpdf\HTMLParserMode::HTML_BODY);
    
    $mpdf->Output("Reporte_ElZapato_".date('Ymd_His').".pdf", \Mpdf\Output\Destination::INLINE);

} catch (Exception $e) { 
    echo "Error crítico en el motor de reportes: " . $e->getMessage(); 
}