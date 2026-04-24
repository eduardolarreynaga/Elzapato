<?php
// 1. Cargar dependencias y configuración
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../model/conexion.php';

// Definimos la raíz del proyecto para las inclusiones de controladores/modelos
$basePath = realpath(__DIR__ . '/../../'); 

// Incluir los controladores y modelos correctos
require_once $basePath . "/controller/ProductoVarianteController.php";
require_once $basePath . "/model/ProductoVarianteModel.php";
require_once $basePath . "/controller/productosController.php";
require_once $basePath . "/model/ProductosModel.php";

use Mpdf\Mpdf;

$tipo = $_GET['tipo'] ?? '1';
$tituloReporte = "";
$tablaHtml = "";

// --- PALETA DE COLORES OFICIAL ---
$c_primary_light = "#E4E0E1";
$c_primary_soft  = "#D6C0B3";
$c_primary_dark  = "#AB886D";
$c_nocolor       = "#772C24";
$c_text          = "#000000";

try {
    switch ($tipo) {
        case '1': // STOCK COMPLETO
            $tituloReporte = "Reporte de Inventario General - Stock";
            $datos = ProductoVarianteController::ctrMostrarTodoElStock();
            $tablaHtml = '<table class="table-data" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;">PRODUCTO</th>
                        <th style="text-align:center; width:10%;">TALLA</th>
                        <th style="text-align:left; width:15%;">COLOR</th>
                        <th style="text-align:left; width:20%;">CODIGO</th>
                        <th style="text-align:center; width:10%;">STOCK</th>
                        <th style="text-align:right; width:15%;">PRECIO</th>
                    </tr>
                </thead>
                <tbody>';
            foreach ($datos as $d) {
                $tablaHtml .= "
                    <tr>
                        <td style='padding:8px; border-bottom:1px solid #eee;'>" . htmlspecialchars($d['nombre_producto']) . "</td>
                        <td style='padding:8px; border-bottom:1px solid #eee; text-align:center;'>{$d['talla']}</td>
                        <td style='padding:8px; border-bottom:1px solid #eee;'>{$d['color']}</td>
                        <td style='padding:8px; border-bottom:1px solid #eee; font-family:monospace;'>{$d['codigo_barras']}</td>
                        <td style='padding:8px; border-bottom:1px solid #eee; text-align:center;'>{$d['stock']}</td>
                        <td style='padding:8px; border-bottom:1px solid #eee; text-align:right;'>$ " . number_format($d['precio_venta'], 2) . "</td>
                    </tr>";
            }
            $tablaHtml .= '</tbody>
            </table>';
            break;

        case '2': // ALERTAS DE STOCK
            $tituloReporte = "Reporte - Alertas de Reabastecimiento";
            $datos = ProductoVarianteController::ctrMostrarStockBajo(10);
            $tablaHtml = '<table class="table-data" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;">PRODUCTO CRITICO</th>
                        <th style="text-align:center; width:20%;">TALLA</th>
                        <th style="text-align:center; width:20%;">COLOR</th>
                        <th style="text-align:center; width:15%;">STOCK ACTUAL</th>
                        <th style="text-align:center; width:20%;">ESTADO</th>
                    </tr>
                </thead>
                <tbody>';
            foreach ($datos as $d) {
                $statusColor = ($d['stock'] <= 5) ? $c_nocolor : '#e67e22';
                $statusText = ($d['stock'] <= 5) ? 'CRITICO' : 'BAJO';
                $tablaHtml .= "
                    <tr>
                        <td style='padding:8px; border-bottom:1px solid #eee;'>" . htmlspecialchars($d['nombre_producto']) . "</td>
                        <td style='padding:8px; border-bottom:1px solid #eee; text-align:center;'>{$d['talla']}</td>
                        <td style='padding:8px; border-bottom:1px solid #eee; text-align:center;'>{$d['color']}</td>
                        <td style='padding:8px; border-bottom:1px solid #eee; text-align:center; color: $statusColor; font-weight: bold;'>{$d['stock']}</td>
                        <td style='padding:8px; border-bottom:1px solid #eee; text-align:center; color: $statusColor; font-weight: bold;'>$statusText</td>
                    </tr>";
            }
            $tablaHtml .= '</tbody>
            </table>';
            break;

        case '3': // MOVIMIENTOS DE INVENTARIO
            $tituloReporte = "Reporte - Historial de Movimientos de Inventario";
            $movimientos = ProductoVarianteController::ctrMostrarHistorialMovimientos();
            $tablaHtml = '<table class="table-data" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left; width:18%;">FECHA</th>
                        <th style="text-align:left; width:12%;">TIPO</th>
                        <th style="text-align:left; width:15%;">REF.</th>
                        <th style="text-align:left;">PRODUCTO</th>
                        <th style="text-align:right; width:12%;">CANT.</th>
                    </tr>
                </thead>
                <tbody>';
            foreach ($movimientos as $m) {
                $color = (strpos($m['cantidad'], '+') !== false) ? $c_primary_dark : $c_nocolor;
                $tipoBadge = ($m['tipo'] == 'Entrada') ? 'COMPRA' : 'VENTA';
                $tablaHtml .= "
                    <tr>
                        <td style='padding:8px; border-bottom:1px solid #eee;'>" . date("d/m/Y H:i", strtotime($m['fecha'])) . "</td>
                        <td style='padding:8px; border-bottom:1px solid #eee;'><strong style='color: $color'>$tipoBadge</strong></td>
                        <td style='padding:8px; border-bottom:1px solid #eee;'>" . htmlspecialchars($m['referencia']) . "</td>
                        <td style='padding:8px; border-bottom:1px solid #eee;'>" . htmlspecialchars($m['producto']) . "</td>
                        <td style='padding:8px; border-bottom:1px solid #eee; text-align:right;'><strong style='color: $color'>{$m['cantidad']}</strong></td>
                    </tr>";
            }
            $tablaHtml .= '</tbody>
            </table>';
            break;

        case '4': // FLUJO DE CAJA
            $tituloReporte = "Reporte - Flujo de Caja y Balance";
            $stats = ProductoVarianteController::ctrMostrarResumenReportes();
            $dataCaja = ProductoVarianteController::ctrResumenCaja();
            
            $tablaHtml = '
                <div style="margin-bottom: 30px;">
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 33%; text-align: center; border: none;">
                                    <div style="font-size: 11px; color: #666;">INGRESOS POR VENTAS</div>
                                    <div style="font-size: 24px; font-weight: bold; color: ' . $c_primary_dark . ';">$ ' . number_format($dataCaja['ingresos'] ?? 0, 2) . '</div>
                                    <div style="font-size: 9px;">' . ($dataCaja['total_tickets'] ?? 0) . ' tickets</div>
                                </td>
                                <td style="width: 33%; text-align: center; border: none;">
                                    <div style="font-size: 11px; color: #666;">EGRESOS POR COMPRAS</div>
                                    <div style="font-size: 24px; font-weight: bold; color: ' . $c_nocolor . ';">$ ' . number_format($dataCaja['egresos'] ?? 0, 2) . '</div>
                                </td>
                                <td style="width: 33%; text-align: center; border: none;">
                                    <div style="font-size: 11px; color: #666;">FLUJO NETO</div>
                                    <div style="font-size: 24px; font-weight: bold; color: ' . $c_text . ';">$ ' . number_format($stats['flujo_neto'] ?? 0, 2) . '</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <h3 style="margin-top: 20px; border-bottom: 2px solid ' . $c_primary_dark . '; padding-bottom: 5px;">DETALLE DE OPERACIONES</h3>
                <table style="width: 100%; margin-top: 15px;">
                    <thead>
                        <tr>
                            <th style="text-align: left;">CONCEPTO</th>
                            <th style="text-align: right;">VALOR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;">Total Ventas Procesadas</td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right; color: ' . $c_primary_dark . '; font-weight: bold;">+ $ ' . number_format($dataCaja['ingresos'] ?? 0, 2) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;">Total Compras a Proveedores</td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right; color: ' . $c_nocolor . '; font-weight: bold;">- $ ' . number_format($dataCaja['egresos'] ?? 0, 2) . '</td>
                        </tr>
                        <tr style="background: #f5f5f5;">
                            <td style="padding: 10px; font-weight: bold;">BALANCE OPERATIVO</td>
                            <td style="padding: 10px; text-align: right; font-weight: bold; font-size: 14px;">$ ' . number_format($stats['flujo_neto'] ?? 0, 2) . '</td>
                        </tr>
                    </tbody>
                </table>';
            break;

        case '5': // CLIENTES TOP
            $tituloReporte = "Reporte - Top Clientes";
            $dataClientes = ProductoVarianteController::ctrTopClientes();
            $tablaHtml = '<table class="table-data" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;">#</th>
                        <th style="text-align:left;">CLIENTE</th>
                        <th style="text-align:center;">TICKETS</th>
                        <th style="text-align:right;">TOTAL COMPRADO</th>
                        <th style="text-align:left;">ÚLTIMA COMPRA</th>
                    </tr>
                </thead>
                <tbody>';
            $contador = 1;
            foreach ($dataClientes as $c) {
                $tablaHtml .= "
                    <tr>
                        <td style='padding:8px; border-bottom:1px solid #eee; text-align:center;'>{$contador}</td>
                        <td style='padding:8px; border-bottom:1px solid #eee;'><strong>" . htmlspecialchars($c['cliente']) . "</strong></td>
                        <td style='padding:8px; border-bottom:1px solid #eee; text-align:center;'>{$c['tickets']}</td>
                        <td style='padding:8px; border-bottom:1px solid #eee; text-align:right; color: {$c_primary_dark}; font-weight: bold;'>$ " . number_format($c['total_comprado'], 2) . "</td>
                        <td style='padding:8px; border-bottom:1px solid #eee;'>" . date("d/m/Y", strtotime($c['ultima_compra'])) . "</td>
                    </tr>";
                $contador++;
            }
            $tablaHtml .= '</tbody>
            </table>';
            break;

        case '6': // RESÚMEN DE TICKETS
            $tituloReporte = "Reporte - Resumen de Tickets";
            $dataTickets = ProductoVarianteController::ctrResumenTickets();
            $tablaHtml = '<table class="table-data" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;">PERIODO</th>
                        <th style="text-align:center;">TICKETS</th>
                        <th style="text-align:center;">PROMEDIO/DÍA</th>
                        <th style="text-align:right;">TICKET PROMEDIO</th>
                    </tr>
                </thead>
                <tbody>';
            foreach ($dataTickets as $t) {
                $tablaHtml .= "
                    <tr>
                        <td style='padding:8px; border-bottom:1px solid #eee;'><strong>" . htmlspecialchars($t['periodo']) . "</strong></td>
                        <td style='padding:8px; border-bottom:1px solid #eee; text-align:center;'>{$t['tickets']}</td>
                        <td style='padding:8px; border-bottom:1px solid #eee; text-align:center;'>{$t['promedio_dia']}</td>
                        <td style='padding:8px; border-bottom:1px solid #eee; text-align:right; color: {$c_primary_dark}; font-weight: bold;'>$ " . number_format($t['ticket_promedio'], 2) . "</td>
                    </tr>";
            }
            $tablaHtml .= '</tbody>
            </table>';
            break;

        case '7': // ÚLTIMAS COMPRAS
            $tituloReporte = "Reporte - Últimas Compras Realizadas";
            $dataCompras = ProductoVarianteController::ctrUltimasCompras();
            $tablaHtml = '<table class="table-data" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:center;"># COMPRA</th>
                        <th style="text-align:left;">PROVEEDOR</th>
                        <th style="text-align:left;">FECHA</th>
                        <th style="text-align:center;">ÍTEMS</th>
                        <th style="text-align:right;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>';
            foreach ($dataCompras as $c) {
                $tablaHtml .= "
                    <tr>
                        <td style='padding:8px; border-bottom:1px solid #eee; text-align:center;'><strong>C-" . str_pad($c['id_compra'], 4, '0', STR_PAD_LEFT) . "</strong></td>
                        <td style='padding:8px; border-bottom:1px solid #eee;'>" . htmlspecialchars($c['proveedor']) . "</td>
                        <td style='padding:8px; border-bottom:1px solid #eee;'>" . date("d/m/Y H:i", strtotime($c['fecha_compra'])) . "</td>
                        <td style='padding:8px; border-bottom:1px solid #eee; text-align:center;'>{$c['items']}</td>
                        <td style='padding:8px; border-bottom:1px solid #eee; text-align:right; color: {$c_primary_dark}; font-weight: bold;'>$ " . number_format($c['total'], 2) . "</td>
                    </tr>";
            }
            $tablaHtml .= '</tbody>
            </table>';
            break;

        case '8': // ESTADÍSTICAS DEL DASHBOARD - USANDO EL CONTROLADOR CORRECTO
            $tituloReporte = "Reporte de Estadísticas de Ventas";
            
            // Usar ProductosController (el mismo que usa el dashboard)
            $topVendidos = ProductosController::ctrProductosMasVendidos();
            $ventasSemana = ProductosController::ctrVentasSemana();
            $ventasCategorias = ProductosController::ctrVentasPorCategoria();
            
            $totalVentasSemana = array_sum(array_column($ventasSemana, 'total'));
            $totalProductosVendidos = array_sum(array_column($topVendidos, 'total_vendido'));
            $promedioDiario = $totalVentasSemana / max(1, count($ventasSemana));
            
            $mejorDia = !empty($ventasSemana) ? array_reduce($ventasSemana, function($max, $item) {
                return ($item['total'] > $max['total']) ? $item : $max;
            }, $ventasSemana[0]) : ['dia' => 'N/A', 'total' => 0];
            
            $mejorCategoria = !empty($ventasCategorias) ? array_reduce($ventasCategorias, function($max, $item) {
                return ($item['valor'] > $max['valor']) ? $item : $max;
            }, $ventasCategorias[0]) : ['etiqueta' => 'N/A', 'valor' => 0];
            
            $ventasSemanaHtml = '';
            foreach ($ventasSemana as $v) {
                $porcentaje = $totalVentasSemana > 0 ? ($v['total'] / $totalVentasSemana) * 100 : 0;
                $ventasSemanaHtml .= '
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>' . htmlspecialchars($v['dia']) . '</strong></td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right;">$ ' . number_format($v['total'], 2) . '</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: center;">' . number_format($porcentaje, 1) . '%</td>
                </tr>';
            }
            
            $topProductosHtml = '';
            $contador = 1;
            foreach (array_slice($topVendidos, 0, 10) as $p) {
                $valorEstimado = $p['total_vendido'] * 45;
                $topProductosHtml .= '
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: center;">' . $contador++ . '</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>' . htmlspecialchars($p['nombre_producto']) . '</strong></td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: center;">' . number_format($p['total_vendido']) . ' uds</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right;">$ ' . number_format($valorEstimado, 2) . '</td>
                </tr>';
            }
            
            $categoriasHtml = '';
            $totalCategorias = array_sum(array_column($ventasCategorias, 'valor'));
            foreach ($ventasCategorias as $c) {
                $porcentajeCat = $totalCategorias > 0 ? ($c['valor'] / $totalCategorias) * 100 : 0;
                $esMejor = ($c['etiqueta'] == $mejorCategoria['etiqueta']);
                $categoriasHtml .= '
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #eee;">' . ($esMejor ? '★ ' : '') . '<strong>' . htmlspecialchars($c['etiqueta']) . '</strong>' . ($esMejor ? ' <span style="color:' . $c_primary_dark . ';">(Mas vendida)</span>' : '') . '</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: right;">$ ' . number_format($c['valor'], 2) . '</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; text-align: center;">' . number_format($porcentajeCat, 1) . '%</td>
                </tr>';
            }
            
            $tablaHtml = '
            <!-- Tarjetas KPIs con mejor espaciado -->
            <table style="width: 100%; margin-bottom: 20px; border-collapse: collapse;">
                <tr>
                    <td style="width: 25%; padding: 8px;">
                        <div style="background: #f8f9fa; border-radius: 8px; padding: 20px 15px; text-align: center; margin-bottom: 10px;">
                            <div style="font-size: 10px; color: #666; text-transform: uppercase; letter-spacing: 1px;">VENTAS TOTALES (SEMANA)</div>
                            <div style="font-size: 24px; font-weight: bold; color: ' . $c_primary_dark . '; margin: 10px 0;">$ ' . number_format($totalVentasSemana, 2) . '</div>
                            <div style="font-size: 9px; color: #999;">Periodo: 7 dias</div>
                        </div>
                      </td>
                    <td style="width: 25%; padding: 8px;">
                        <div style="background: #f8f9fa; border-radius: 8px; padding: 20px 15px; text-align: center; margin-bottom: 10px;">
                            <div style="font-size: 10px; color: #666; text-transform: uppercase; letter-spacing: 1px;">PRODUCTOS VENDIDOS</div>
                            <div style="font-size: 24px; font-weight: bold; color: ' . $c_primary_dark . '; margin: 10px 0;">' . number_format($totalProductosVendidos) . '</div>
                            <div style="font-size: 9px; color: #999;">Unidades totales</div>
                        </div>
                      </td>
                    <td style="width: 25%; padding: 8px;">
                        <div style="background: #f8f9fa; border-radius: 8px; padding: 20px 15px; text-align: center; margin-bottom: 10px;">
                            <div style="font-size: 10px; color: #666; text-transform: uppercase; letter-spacing: 1px;">PROMEDIO DIARIO</div>
                            <div style="font-size: 24px; font-weight: bold; color: ' . $c_primary_dark . '; margin: 10px 0;">$ ' . number_format($promedioDiario, 2) . '</div>
                            <div style="font-size: 9px; color: #999;">Ventas por dia</div>
                        </div>
                      </td>
                    <td style="width: 25%; padding: 8px;">
                        <div style="background: #f8f9fa; border-radius: 8px; padding: 20px 15px; text-align: center; margin-bottom: 10px;">
                            <div style="font-size: 10px; color: #666; text-transform: uppercase; letter-spacing: 1px;">MEJOR DIA</div>
                            <div style="font-size: 24px; font-weight: bold; color: ' . $c_primary_dark . '; margin: 10px 0;">' . htmlspecialchars($mejorDia['dia']) . '</div>
                            <div style="font-size: 9px; color: #999;">$ ' . number_format($mejorDia['total'], 2) . '</div>
                        </div>
                      </td>
                </tr>
            </table>
            
            <!-- Ventas por Dia -->
            <div style="background: ' . $c_primary_dark . '; color: white; padding: 10px 15px; margin: 25px 0 15px 0; font-size: 12px; font-weight: bold; border-radius: 4px;">VENTAS POR DIA (Ultima Semana)</div>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr>
                        <th style="background: ' . $c_primary_soft . '; color: #333; padding: 10px; font-size: 10px; text-align: left;">DIA</th>
                        <th style="background: ' . $c_primary_soft . '; color: #333; padding: 10px; font-size: 10px; text-align: right;">TOTAL VENTAS ($)</th>
                        <th style="background: ' . $c_primary_soft . '; color: #333; padding: 10px; font-size: 10px; text-align: center;">% DEL TOTAL</th>
                    </tr>
                </thead>
                <tbody>' . $ventasSemanaHtml . '</tbody>
            </table>
            
            <!-- Top Productos -->
            <div style="background: ' . $c_primary_dark . '; color: white; padding: 10px 15px; margin: 25px 0 15px 0; font-size: 12px; font-weight: bold; border-radius: 4px;">TOP 10 PRODUCTOS MAS VENDIDOS</div>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr>
                        <th style="background: ' . $c_primary_soft . '; color: #333; padding: 10px; font-size: 10px; text-align: center;">#</th>
                        <th style="background: ' . $c_primary_soft . '; color: #333; padding: 10px; font-size: 10px; text-align: left;">PRODUCTO</th>
                        <th style="background: ' . $c_primary_soft . '; color: #333; padding: 10px; font-size: 10px; text-align: center;">UNIDADES VENDIDAS</th>
                        <th style="background: ' . $c_primary_soft . '; color: #333; padding: 10px; font-size: 10px; text-align: right;">ESTIMADO VENTAS ($)</th>
                    </tr>
                </thead>
                <tbody>' . $topProductosHtml . '</tbody>
            </table>
            
            <!-- Ventas por Categoria -->
            <div style="background: ' . $c_primary_dark . '; color: white; padding: 10px 15px; margin: 25px 0 15px 0; font-size: 12px; font-weight: bold; border-radius: 4px;">VENTAS POR CATEGORIA</div>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr>
                        <th style="background: ' . $c_primary_soft . '; color: #333; padding: 10px; font-size: 10px; text-align: left;">CATEGORIA</th>
                        <th style="background: ' . $c_primary_soft . '; color: #333; padding: 10px; font-size: 10px; text-align: right;">VALOR VENDIDO ($)</th>
                        <th style="background: ' . $c_primary_soft . '; color: #333; padding: 10px; font-size: 10px; text-align: center;">% PARTICIPACION</th>
                    </tr>
                </thead>
                <tbody>' . $categoriasHtml . '</tbody>
            </table>
            
            <!-- Resumen -->
            <div style="margin-top: 30px; padding: 15px; background: ' . $c_primary_light . '; border-radius: 6px; font-size: 9px; text-align: center; color: #666;">
                <strong>RESUMEN EJECUTIVO</strong><br>
                • La categoria <strong>' . htmlspecialchars($mejorCategoria['etiqueta']) . '</strong> lidera las ventas con <strong>$ ' . number_format($mejorCategoria['valor'], 2) . '</strong><br>
                • El dia de mayor facturacion fue <strong>' . htmlspecialchars($mejorDia['dia']) . '</strong> con <strong>$ ' . number_format($mejorDia['total'], 2) . '</strong><br>
                • Se vendieron un total de <strong>' . number_format($totalProductosVendidos) . '</strong> productos en la ultima semana<br>
                • El ticket promedio diario es de <strong>$ ' . number_format($promedioDiario, 2) . '</strong>
            </div>';
            break;

        default:
            die("Error: Tipo de reporte no válido.");
    }

    // --- ESTILOS CSS PRINCIPALES ---
    $css = "
        body { font-family: 'Helvetica', sans-serif; color: $c_text; }
        .header { border-bottom: 3px solid $c_primary_dark; padding-bottom: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: $c_primary_dark; color: #ffffff; padding: 10px; text-align: left; font-size: 10px; }
        td { padding: 8px; border-bottom: 1px solid $c_primary_soft; font-size: 10px; }
        .center { text-align: center; }
        .right { text-align: right; }
        .footer { font-size: 8px; color: #95a5a6; text-align: center; border-top: 1px solid $c_primary_soft; padding-top: 10px; margin-top: 20px; }
        h3 { color: $c_primary_dark; font-size: 14px; }
    ";

    // Configuración de mPDF
    $mpdf = new Mpdf([
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 45,
        'margin_bottom' => 20,
        'format' => 'Letter'
    ]);

    // Ruta del Logo
    $logoPath = $_SERVER['DOCUMENT_ROOT'] . "/ElZapato/Assets/img/logo.png";
    $imgHtml = file_exists($logoPath) ? '<img src="'.$logoPath.'" style="width: 70px;">' : '<div style="width: 70px; height: 70px; background: #f0f0f0;"></div>';

    $headerHtml = '
    <div class="header">
        <table style="border: none; width: 100%;">
            <tr>
                <td style="border: none; width: 15%;">'.$imgHtml.'</td>
                <td style="border: none; width: 50%; vertical-align: middle;">
                    <div style="font-size: 24px; font-weight: bold; letter-spacing: 2px;">EL ZAPATO</div>
                    <div style="font-size: 11px; color: #666; text-transform: uppercase;">'.$tituloReporte.'</div>
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
    
    $mpdf->Output("Reporte_".str_replace(" ", "_", $tituloReporte)."_".date('Ymd_His').".pdf", \Mpdf\Output\Destination::INLINE);

} catch (Exception $e) { 
    echo "Error crítico en el motor de reportes: " . $e->getMessage(); 
}
?>