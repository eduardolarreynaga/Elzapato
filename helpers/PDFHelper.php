<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class PDFHelper {
    
    /**
     * Generar PDF del historial
     */
    public static function generarPDFHistorial($datos_historial, $filtros, $usuario) {
        // Configurar opciones de Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        
        // Generar HTML para el PDF
        $html = self::getPDFTemplate($datos_historial, $filtros, $usuario);
        
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return $dompdf->output();
    }
    
    private static function getPDFTemplate($datos, $filtros, $usuario) {
        $fecha_actual = date('d/m/Y H:i:s');
        $fecha_desde = $filtros['fecha_desde'] ?? 'Todos';
        $fecha_hasta = $filtros['fecha_hasta'] ?? 'Todos';
        $usuario_filtro = $filtros['usuario'] ?? 'Todos';
        $tipo_filtro = $filtros['tipo'] ?? 'Todos';
        
        // Ruta del logo - usar URL absoluta
        $logoUrl = 'http://localhost/ElZapato/Assets/img/logo.original.backup.png';
        
        // Verificar si el logo existe localmente y usar ruta de archivo como respaldo
        $logoFile = __DIR__ . '/ElZapato/Assets/img/logo.original.backup.png';
        if (file_exists($logoFile)) {
            $logoPath = 'file:///' . str_replace('\\', '/', realpath($logoFile));
            $logoHtml = '<img src="' . $logoPath . '" class="logo" alt="Logo ElZapato">';
        } else {
            // Si no existe el logo, mostrar texto en lugar de imagen
            $logoHtml = '<div style="font-size: 20px; font-weight: bold; color: #AB886D;">ELZAPATO</div>';
        }
        
        // Separar datos por tipo
        $ventas = array_filter($datos, function($item) { return $item['accion'] == 'venta'; });
        $devoluciones = array_filter($datos, function($item) { return $item['accion'] == 'devolucion'; });
        $aperturas = array_filter($datos, function($item) { return $item['accion'] == 'apertura_caja'; });
        $cierres = array_filter($datos, function($item) { return $item['accion'] == 'cierre_caja'; });
        $logs = array_filter($datos, function($item) { 
            return in_array($item['accion'], ['login', 'logout', 'login_fallido', 'crear', 'editar', 'eliminar']); 
        });
        
        $total_ventas = count($ventas);
        $total_devoluciones = count($devoluciones);
        $total_aperturas = count($aperturas);
        $total_cierres = count($cierres);
        $total_logs = count($logs);
        $total_registros = count($datos);
        
        // Construir tablas separadas
        $tabla_ventas = self::generarTabla($ventas, 'Ventas Realizadas');
        $tabla_devoluciones = self::generarTabla($devoluciones, 'Devoluciones Procesadas');
        $tabla_aperturas = self::generarTabla($aperturas, 'Aperturas de Caja');
        $tabla_cierres = self::generarTabla($cierres, 'Cierres de Caja');
        $tabla_logs = self::generarTabla($logs, 'Actividades del Sistema (Login/Logout/CRUD)');
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Historial de Actividades - ElZapato</title>
            <style>
                body {
                    font-family: "DejaVu Sans", Arial, sans-serif;
                    margin: 20px;
                    font-size: 10px;
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                    padding-bottom: 15px;
                    border-bottom: 2px solid #AB886D;
                }
                .logo {
                    max-width: 80px;
                    margin-bottom: 10px;
                }
                .header h1 {
                    color: #AB886D;
                    margin: 5px 0;
                    font-size: 18px;
                }
                .header p {
                    color: #666;
                    margin: 3px 0;
                    font-size: 10px;
                }
                .info-box {
                    background: #f5f5f5;
                    padding: 10px;
                    margin-bottom: 15px;
                    border-radius: 5px;
                }
                .info-grid {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 10px;
                }
                .info-item {
                    flex: 1;
                    min-width: 120px;
                }
                .info-item .label {
                    font-size: 8px;
                    color: #888;
                    text-transform: uppercase;
                }
                .info-item .value {
                    font-weight: bold;
                    color: #333;
                    font-size: 11px;
                }
                .stats-grid {
                    width: 100%;
                    margin-bottom: 15px;
                    text-align: center;
                }
                .stat-card {
                    display: inline-block;
                    width: 18%;
                    background: #AB886D;
                    color: white;
                    padding: 10px 0;
                    border-radius: 8px;
                    text-align: center;
                    margin-right: 2%;
                    vertical-align: top;
                }
                .stat-card:last-child {
                    margin-right: 0;
                }
                .stat-card .number {
                    font-size: 18px;
                    font-weight: bold;
                }
                .stat-card .label {
                    font-size: 9px;
                    margin-top: 5px;
                }
                .section-title {
                    background: #AB886D;
                    color: white;
                    padding: 6px 10px;
                    margin: 15px 0 8px 0;
                    font-size: 11px;
                    font-weight: bold;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 15px;
                    font-size: 9px;
                }
                th {
                    background: #D6C0B3;
                    color: #333;
                    padding: 6px;
                    text-align: left;
                    font-weight: bold;
                }
                td {
                    padding: 5px;
                    border-bottom: 1px solid #E4E0E1;
                    vertical-align: top;
                }
                .footer {
                    margin-top: 20px;
                    text-align: center;
                    font-size: 8px;
                    color: #999;
                    padding-top: 10px;
                    border-top: 1px solid #E4E0E1;
                }
                .empty-table {
                    text-align: center;
                    padding: 15px;
                    color: #999;
                    font-style: italic;
                }
            </style>
        </head>
        <body>
            <div class="header">
                ' . $logoHtml . '
                <h1>Historial de Actividades</h1>
                <p>ElZapato - Sistema de Ventas</p>
            </div>
            
            <div class="info-box">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="label">Fecha de generacion</div>
                        <div class="value">' . $fecha_actual . '</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Generado por</div>
                        <div class="value">' . htmlspecialchars($usuario) . '</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Total registros</div>
                        <div class="value">' . $total_registros . '</div>
                    </div>
                </div>
            </div>
            
            <div class="info-box">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="label">Periodo</div>
                        <div class="value">' . $fecha_desde . ' - ' . $fecha_hasta . '</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Filtro usuario</div>
                        <div class="value">' . $usuario_filtro . '</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Filtro tipo</div>
                        <div class="value">' . $tipo_filtro . '</div>
                    </div>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="number">' . $total_ventas . '</div>
                    <div class="label">Ventas</div>
                </div>
                <div class="stat-card">
                    <div class="number">' . $total_devoluciones . '</div>
                    <div class="label">Devoluciones</div>
                </div>
                <div class="stat-card">
                    <div class="number">' . $total_aperturas . '</div>
                    <div class="label">Aperturas</div>
                </div>
                <div class="stat-card">
                    <div class="number">' . $total_cierres . '</div>
                    <div class="label">Cierres</div>
                </div>
                <div class="stat-card">
                    <div class="number">' . $total_logs . '</div>
                    <div class="label">Logs</div>
                </div>
            </div>
            
            ' . $tabla_ventas . '
            ' . $tabla_devoluciones . '
            ' . $tabla_aperturas . '
            ' . $tabla_cierres . '
            ' . $tabla_logs . '
            
            <div class="footer">
                <p>Reporte generado automaticamente por ElZapato - Sistema de Ventas</p>
                <p>(c) ' . date('Y') . ' ElZapato - Todos los derechos reservados.</p>
            </div>
        </body>
        </html>
        ';
    }
    
    private static function generarTabla($datos, $titulo) {
        if (empty($datos)) {
            return '
            <div class="section-title">' . $titulo . '</div>
            <div class="empty-table">No hay registros en este periodo</div>
            ';
        }
        
        $filas = '';
        foreach ($datos as $item) {
            $fecha = date('d/m/Y H:i:s', strtotime($item['fecha']));
            $nombre = htmlspecialchars($item['nombre_usuario'] ?? 'Sistema');
            $accion = self::getAccionTexto($item['accion']);
            $detalle = htmlspecialchars($item['detalle'] ?? '');
            
            $filas .= '
            <tr>
                <td style="width: 15%;">' . $fecha . '</td>
                <td style="width: 15%;">' . $nombre . '</td>
                <td style="width: 15%;">' . $accion . '</td>
                <td style="width: 55%;">' . $detalle . '</td>
            </tr>';
        }
        
        return '
        <div class="section-title">' . $titulo . ' (' . count($datos) . ' registros)</div>
        <table>
            <thead>
                <tr>
                    <th>Fecha/Hora</th>
                    <th>Usuario</th>
                    <th>Tipo</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                ' . $filas . '
            </tbody>
        </table>
        ';
    }
    
    private static function getAccionTexto($accion) {
        $acciones = [
            'venta' => 'Venta',
            'devolucion' => 'Devolucion',
            'apertura_caja' => 'Apertura Caja',
            'cierre_caja' => 'Cierre Caja',
            'login' => 'Login',
            'logout' => 'Logout',
            'login_fallido' => 'Login Fallido',
            'crear' => 'Creacion',
            'editar' => 'Edicion',
            'eliminar' => 'Eliminacion'
        ];
        return $acciones[$accion] ?? ucfirst($accion);
    }
}
?>