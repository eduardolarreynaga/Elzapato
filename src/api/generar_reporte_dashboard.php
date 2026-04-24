<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('pcre.backtrack_limit', '10000000');
ini_set('pcre.recursion_limit', '1000000');

require_once __DIR__ . '/../config/auth.php';
require_auth('admin');

$vendorPath = realpath(__DIR__ . '/../../vendor/autoload.php');
if (!$vendorPath || !file_exists($vendorPath)) {
    http_response_code(500);
    die('Vendor autoload no encontrado.');
}

require_once $vendorPath;
require_once __DIR__ . '/../../model/conexion.php';

use Mpdf\Mpdf;

function escapeHtml($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function normalizarValoresGrafica(array $valores) {
    $normalizados = [];
    foreach ($valores as $valor) {
        $normalizados[] = max(0, (float)$valor);
    }
    return $normalizados;
}

function generarGraficaLineaSVG(array $labels, array $values) {
    $labels = array_values($labels);
    $values = normalizarValoresGrafica($values);
    $width = 700;
    $height = 260;
    $padLeft = 55;
    $padRight = 20;
    $padTop = 20;
    $padBottom = 50;
    $plotWidth = $width - $padLeft - $padRight;
    $plotHeight = $height - $padTop - $padBottom;
    $max = max($values ?: [1]);
    if ($max <= 0) {
        $max = 1;
    }
    $count = max(count($values), 1);
    $stepX = $count > 1 ? $plotWidth / ($count - 1) : 0;
    $points = [];
    foreach ($values as $index => $value) {
        $x = $padLeft + ($stepX * $index);
        $y = $padTop + $plotHeight - (($value / $max) * $plotHeight);
        $points[] = round($x, 2) . ',' . round($y, 2);
    }

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">';
    $svg .= '<rect width="100%" height="100%" fill="#ffffff"/>';
    for ($i = 0; $i <= 4; $i++) {
        $y = $padTop + ($plotHeight / 4) * $i;
        $valor = $max - (($max / 4) * $i);
        $svg .= '<line x1="' . $padLeft . '" y1="' . round($y, 2) . '" x2="' . ($width - $padRight) . '" y2="' . round($y, 2) . '" stroke="#ece7e2" stroke-width="1"/>';
        $svg .= '<text x="10" y="' . round($y + 4, 2) . '" font-size="10" fill="#666">$' . number_format($valor, 0) . '</text>';
    }
    $svg .= '<line x1="' . $padLeft . '" y1="' . ($padTop + $plotHeight) . '" x2="' . ($width - $padRight) . '" y2="' . ($padTop + $plotHeight) . '" stroke="#b9a79a" stroke-width="1.5"/>';
    if (!empty($points)) {
        $svg .= '<polyline fill="none" stroke="#A67C52" stroke-width="3" points="' . implode(' ', $points) . '"/>';
        foreach ($values as $index => $value) {
            [$x, $y] = explode(',', $points[$index]);
            $svg .= '<circle cx="' . $x . '" cy="' . $y . '" r="4" fill="#A67C52"/>';
            $svg .= '<text x="' . $x . '" y="' . ($padTop + $plotHeight + 18) . '" text-anchor="middle" font-size="10" fill="#555">' . escapeHtml((string)($labels[$index] ?? '')) . '</text>';
        }
    }
    $svg .= '</svg>';
    return $svg;
}

function generarGraficaBarrasSVG(array $labels, array $values) {
    $labels = array_values($labels);
    $values = normalizarValoresGrafica($values);
    $width = 700;
    $height = 260;
    $padLeft = 55;
    $padRight = 20;
    $padTop = 20;
    $padBottom = 65;
    $plotWidth = $width - $padLeft - $padRight;
    $plotHeight = $height - $padTop - $padBottom;
    $max = max($values ?: [1]);
    if ($max <= 0) {
        $max = 1;
    }
    $count = max(count($values), 1);
    $barSpace = $plotWidth / $count;
    $barWidth = max(18, $barSpace * 0.55);

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">';
    $svg .= '<rect width="100%" height="100%" fill="#ffffff"/>';
    for ($i = 0; $i <= 4; $i++) {
        $y = $padTop + ($plotHeight / 4) * $i;
        $svg .= '<line x1="' . $padLeft . '" y1="' . round($y, 2) . '" x2="' . ($width - $padRight) . '" y2="' . round($y, 2) . '" stroke="#ece7e2" stroke-width="1"/>';
    }
    $svg .= '<line x1="' . $padLeft . '" y1="' . ($padTop + $plotHeight) . '" x2="' . ($width - $padRight) . '" y2="' . ($padTop + $plotHeight) . '" stroke="#b9a79a" stroke-width="1.5"/>';
    foreach ($values as $index => $value) {
        $barHeight = ($value / $max) * $plotHeight;
        $x = $padLeft + ($barSpace * $index) + (($barSpace - $barWidth) / 2);
        $y = $padTop + $plotHeight - $barHeight;
        $svg .= '<rect x="' . round($x, 2) . '" y="' . round($y, 2) . '" width="' . round($barWidth, 2) . '" height="' . round($barHeight, 2) . '" rx="6" fill="#A67C52"/>';
        $svg .= '<text x="' . round($x + ($barWidth / 2), 2) . '" y="' . ($padTop + $plotHeight + 18) . '" text-anchor="middle" font-size="10" fill="#555">' . escapeHtml((string)($labels[$index] ?? '')) . '</text>';
    }
    $svg .= '</svg>';
    return $svg;
}

function generarGraficaDonaSVG(array $labels, array $values) {
    $labels = array_values($labels);
    $values = normalizarValoresGrafica($values);
    $total = array_sum($values);
    if ($total <= 0) {
        $total = 1;
    }
    $width = 700;
    $height = 260;
    $cx = 150;
    $cy = 120;
    $radius = 65;
    $circ = 2 * M_PI * $radius;
    $colors = ['#A67C52', '#D6C0B3', '#242424', '#8a6d51', '#C0A080'];
    $offset = 0;

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">';
    $svg .= '<rect width="100%" height="100%" fill="#ffffff"/>';
    $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $radius . '" fill="none" stroke="#f0ece8" stroke-width="32"/>';
    foreach ($values as $index => $value) {
        $portion = $value / $total;
        $length = $portion * $circ;
        $color = $colors[$index % count($colors)];
        $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $radius . '" fill="none" stroke="' . $color . '" stroke-width="32" stroke-dasharray="' . round($length, 2) . ' ' . round($circ, 2) . '" stroke-dashoffset="-' . round($offset, 2) . '" transform="rotate(-90 ' . $cx . ' ' . $cy . ')"/>';
        $offset += $length;
    }
    $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="34" fill="#ffffff"/>';
    $svg .= '<text x="' . $cx . '" y="' . ($cy + 5) . '" text-anchor="middle" font-size="14" font-weight="bold" fill="#444">' . (int)$total . '</text>';
    foreach ($labels as $index => $label) {
        $color = $colors[$index % count($colors)];
        $y = 48 + ($index * 24);
        $valor = $values[$index] ?? 0;
        $svg .= '<rect x="330" y="' . ($y - 10) . '" width="12" height="12" fill="' . $color . '" rx="2"/>';
        $svg .= '<text x="350" y="' . $y . '" font-size="11" fill="#444">' . escapeHtml((string)$label) . ' (' . (int)$valor . ')</text>';
    }
    $svg .= '</svg>';
    return $svg;
}

function decodificarGraficaBase64($dataUrl) {
    if (!is_string($dataUrl) || !preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $dataUrl)) {
        return null;
    }

    $base64 = preg_replace('/^data:image\/(png|jpeg|jpg);base64,/', '', $dataUrl);
    $binario = base64_decode($base64, true);

    if ($binario === false || $binario === '') {
        return null;
    }

    return $binario;
}

function guardarGraficaTemporal($dataUrl, $directorioDestino, $nombreBase) {
    if (!is_string($dataUrl) || !preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $dataUrl, $coincidencias)) {
        return null;
    }

    $extension = ($coincidencias[1] === 'jpeg' || $coincidencias[1] === 'jpg') ? 'jpg' : 'png';
    $base64 = preg_replace('/^data:image\/(png|jpeg|jpg);base64,/', '', $dataUrl);
    $binario = base64_decode($base64, true);
    if ($binario === false || $binario === '') {
        return null;
    }

    if (!is_dir($directorioDestino)) {
        @mkdir($directorioDestino, 0775, true);
    }

    $rutaArchivo = $directorioDestino . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $nombreBase) . '_' . md5($binario) . '.' . $extension;
    if (@file_put_contents($rutaArchivo, $binario) === false) {
        return null;
    }

    return $rutaArchivo;
}

$imprimirVentasFecha = true;
$incluirGraficas = true;
$periodo = $_POST['periodo'] ?? 'semana';
$fechaInicioPost = trim($_POST['fecha_inicio'] ?? '');
$fechaFinPost = trim($_POST['fecha_fin'] ?? '');
$graficasDataRaw = $_POST['graficas_data'] ?? '[]';

$hoy = new DateTime('today');
$fechaInicio = clone $hoy;
$fechaFin = clone $hoy;
$etiquetaPeriodo = 'Semana actual';

switch ($periodo) {
    case 'mes':
        $fechaInicio = new DateTime('first day of this month');
        $fechaFin = new DateTime('last day of this month');
        $etiquetaPeriodo = 'Mes actual';
        break;
    case 'anio':
        $fechaInicio = new DateTime(date('Y-01-01'));
        $fechaFin = new DateTime(date('Y-12-31'));
        $etiquetaPeriodo = 'Año actual';
        break;
    case 'personalizado':
        if ($fechaInicioPost === '' || $fechaFinPost === '') {
            die('Debes seleccionar fecha inicio y fecha fin.');
        }
        $fechaInicio = DateTime::createFromFormat('Y-m-d', $fechaInicioPost) ?: null;
        $fechaFin = DateTime::createFromFormat('Y-m-d', $fechaFinPost) ?: null;
        if (!$fechaInicio || !$fechaFin) {
            die('Formato de fecha inválido.');
        }
        if ($fechaInicio > $fechaFin) {
            die('La fecha de inicio no puede ser mayor que la fecha fin.');
        }
        $etiquetaPeriodo = 'Rango personalizado';
        break;
    case 'semana':
    default:
        $fechaInicio = new DateTime('monday this week');
        $fechaFin = new DateTime('sunday this week');
        $etiquetaPeriodo = 'Semana actual';
        break;
}

$inicioSql = $fechaInicio->format('Y-m-d') . ' 00:00:00';
$finSql = $fechaFin->format('Y-m-d') . ' 23:59:59';

try {
    $db = Conexion::conectar();
} catch (Exception $e) {
    http_response_code(500);
    die('Error de conexión a BD: ' . $e->getMessage());
}

if (!$db) {
    http_response_code(500);
    die('No se pudo conectar a la base de datos.');
}

$ventasPorFecha = [];
$resumenVentas = ['cantidad_ventas' => 0, 'total_vendido' => 0];

if ($imprimirVentasFecha) {
    try {
        $stmtResumen = $db->prepare("SELECT COUNT(*) AS cantidad_ventas, COALESCE(SUM(total_venta), 0) AS total_vendido FROM ventas WHERE fecha_venta BETWEEN :inicio AND :fin");
        $stmtResumen->execute([':inicio' => $inicioSql, ':fin' => $finSql]);
        $resumenVentas = $stmtResumen->fetch(PDO::FETCH_ASSOC) ?: $resumenVentas;
    } catch (PDOException $e) {
        http_response_code(500);
        die('Error al consultar ventas: ' . $e->getMessage());
    }

    try {
        $stmtVentas = $db->prepare("SELECT DATE(fecha_venta) AS fecha, COUNT(*) AS cantidad, COALESCE(SUM(total_venta), 0) AS total FROM ventas WHERE fecha_venta BETWEEN :inicio AND :fin GROUP BY DATE(fecha_venta) ORDER BY DATE(fecha_venta) ASC");
        $stmtVentas->execute([':inicio' => $inicioSql, ':fin' => $finSql]);
        $ventasPorFecha = $stmtVentas->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        http_response_code(500);
        die('Error al consultar ventas detalladas: ' . $e->getMessage());
    }
}

$topProductos = [];
$ventasCategoria = [];
$ventasSemana = [];

if ($incluirGraficas) {
    $stmtTop = $db->query("SELECT p.nombre_producto, SUM(dv.cantidad) AS total_vendido FROM detalle_venta dv INNER JOIN producto_variante pv ON dv.id_variante = pv.id_variante INNER JOIN productos p ON pv.id_producto = p.id_producto GROUP BY p.id_producto, p.nombre_producto ORDER BY total_vendido DESC LIMIT 10");
    $topProductos = $stmtTop->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $stmtCat = $db->query("SELECT c.nombre_categoria AS etiqueta, COALESCE(SUM(dv.cantidad), 0) AS valor FROM categorias c LEFT JOIN productos p ON p.id_categoria = c.id_categoria LEFT JOIN producto_variante pv ON pv.id_producto = p.id_producto LEFT JOIN detalle_venta dv ON dv.id_variante = pv.id_variante GROUP BY c.id_categoria, c.nombre_categoria ORDER BY valor DESC");
    $ventasCategoria = $stmtCat->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $stmtSemana = $db->query("SELECT DATE(fecha_venta) AS dia, COALESCE(SUM(total_venta), 0) AS total FROM ventas WHERE fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(fecha_venta) ORDER BY DATE(fecha_venta) ASC");
    $ventasSemana = $stmtSemana->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

$graficasDecodificadas = json_decode($graficasDataRaw, true);
if (!is_array($graficasDecodificadas)) {
    $graficasDecodificadas = [];
}

$mapaPorKey = [];
foreach ($graficasDecodificadas as $grafica) {
    if (!is_array($grafica)) {
        continue;
    }
    $key = $grafica['key'] ?? null;
    if (is_string($key) && $key !== '') {
        $mapaPorKey[$key] = $grafica;
    }
}

$tmpDirBase = realpath(__DIR__ . '/../../tmp');
if ($tmpDirBase === false) {
    $tmpDirBase = __DIR__ . '/../../tmp';
    if (!is_dir($tmpDirBase)) {
        @mkdir($tmpDirBase, 0775, true);
    }
}

if (!is_writable($tmpDirBase)) {
    $tmpDirBase = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
}

$graficasTempDir = $tmpDirBase . '/elzapato_mpdf_charts';
$rutasGraficas = [];
$graficasBinarias = [];
if (!is_dir($graficasTempDir)) {
    @mkdir($graficasTempDir, 0775, true);
}

foreach (['salesChart', 'categoryChart', 'topProductsChart'] as $chartKey) {
    $fileKey = 'grafica_' . $chartKey;
    if (!empty($_FILES[$fileKey]['tmp_name']) && is_uploaded_file($_FILES[$fileKey]['tmp_name'])) {
        $contenidoArchivo = @file_get_contents($_FILES[$fileKey]['tmp_name']);
        if ($contenidoArchivo !== false && $contenidoArchivo !== '') {
            $graficasBinarias[$chartKey] = $contenidoArchivo;

            $nombreOriginal = $_FILES[$fileKey]['name'] ?? ($chartKey . '.png');
            $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
            if (!in_array($extension, ['png', 'jpg', 'jpeg'], true)) {
                $extension = 'png';
            }

            $rutaArchivo = $graficasTempDir . '/' . $chartKey . '_' . md5($contenidoArchivo) . '.' . $extension;
            @file_put_contents($rutaArchivo, $contenidoArchivo);
            if (file_exists($rutaArchivo)) {
                $rutasGraficas[$chartKey] = $rutaArchivo;
            }
        }
    }
}

foreach ($mapaPorKey as $key => $grafica) {
    if (isset($graficasBinarias[$key])) {
        continue;
    }
    $binarioGrafica = decodificarGraficaBase64($grafica['dataUrl'] ?? '');
    if ($binarioGrafica) {
        $graficasBinarias[$key] = $binarioGrafica;
    }

    $rutaTemporal = guardarGraficaTemporal($grafica['dataUrl'] ?? '', $graficasTempDir, $key);
    if ($rutaTemporal) {
        $rutasGraficas[$key] = $rutaTemporal;
    }
}

$nombreSistema = defined('SYSTEM_NAME') ? SYSTEM_NAME : 'ElZapato';

$css = "
body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #222; }
h1 { font-size: 18pt; margin: 0 0 6px 0; color: #AB886D; }
h2 { font-size: 12pt; margin: 18px 0 8px; color: #772C24; border-bottom: 1px solid #D6C0B3; padding-bottom: 4px; }
.meta { font-size: 9pt; color: #666; margin-bottom: 10px; }
.kpi-table { width: 100%; border-collapse: collapse; margin-top: 8px; margin-bottom: 8px; }
.kpi-table td { width: 33.33%; background: #E4E0E1; border: 1px solid #D6C0B3; padding: 8px; text-align: center; }
.kpi-title { display: block; font-size: 8pt; color: #666; text-transform: uppercase; }
.kpi-value { display: block; font-size: 13pt; font-weight: bold; color: #AB886D; margin-top: 4px; }
.table { width: 100%; border-collapse: collapse; margin-top: 8px; }
.table th { background: #AB886D; color: #fff; padding: 7px; border: 1px solid #AB886D; font-size: 9pt; }
.table td { padding: 6px; border: 1px solid #D6C0B3; font-size: 9pt; }
.center { text-align: center; }
.right { text-align: right; }
.chart-box { margin-top: 10px; margin-bottom: 16px; padding: 8px; border: 1px solid #D6C0B3; border-radius: 6px; }
.chart-title { font-size: 10pt; font-weight: bold; margin-bottom: 6px; color: #AB886D; }
.note { font-size: 8pt; color: #666; }
";

$html = '<h1>Reporte de Estadísticas - ' . escapeHtml($nombreSistema) . '</h1>';
$html .= '<div class="meta">Generado: ' . date('d/m/Y H:i') . ' | Periodo seleccionado: ' . escapeHtml($etiquetaPeriodo) . ' (' . $fechaInicio->format('d/m/Y') . ' - ' . $fechaFin->format('d/m/Y') . ')</div>';

if ($imprimirVentasFecha) {
    $html .= '<h2>Cantidad de venta</h2>';

    $cantidadVentas = (int)($resumenVentas['cantidad_ventas'] ?? 0);
    $totalVendido = (float)($resumenVentas['total_vendido'] ?? 0);

    $html .= '<table class="kpi-table"><tbody><tr>';
    $html .= '<td><span class="kpi-title">Tickets: </span><span class="kpi-value">' . $cantidadVentas . '</span></td>';
    $html .= '<td><span class="kpi-title">Total vendido: </span><span class="kpi-value">$' . number_format($totalVendido, 2) . '</span></td>';
    $html .= '<td><span class="kpi-title">Periodo: </span><span class="kpi-value">' . escapeHtml($etiquetaPeriodo) . '</span></td>';
    $html .= '</tr></tbody></table>';

    if (empty($ventasPorFecha)) {
        $html .= '<p>No hay ventas registradas en el rango seleccionado.</p>';
    } else {
        $html .= '<table class="table"><thead><tr><th>Fecha</th><th class="center">Cantidad de ventas</th><th class="right">Total ($)</th></tr></thead><tbody>';
        foreach ($ventasPorFecha as $fila) {
            $html .= '<tr>';
            $html .= '<td>' . date('d/m/Y', strtotime($fila['fecha'])) . '</td>';
            $html .= '<td class="center">' . (int)$fila['cantidad'] . '</td>';
            $html .= '<td class="right">$' . number_format((float)$fila['total'], 2) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
    }
}

if ($incluirGraficas) {
    $html .= '<h2>Gráficas del dashboard</h2>';

    $svgGraficas = [
        'salesChart' => generarGraficaLineaSVG(
            array_map(static function ($row) {
                return date('d/m', strtotime($row['dia']));
            }, $ventasSemana),
            array_map(static function ($row) {
                return (float)($row['total'] ?? 0);
            }, $ventasSemana)
        ),
        'categoryChart' => generarGraficaDonaSVG(
            array_map(static function ($row) {
                return $row['etiqueta'] ?? 'N/A';
            }, $ventasCategoria),
            array_map(static function ($row) {
                return (float)($row['valor'] ?? 0);
            }, $ventasCategoria)
        ),
        'topProductsChart' => generarGraficaBarrasSVG(
            array_map(static function ($row) {
                return $row['nombre_producto'] ?? 'N/A';
            }, $topProductos),
            array_map(static function ($row) {
                return (float)($row['total_vendido'] ?? 0);
            }, $topProductos)
        )
    ];

    $graficasEsperadas = [
        ['key' => 'salesChart', 'titulo' => 'Rendimiento de Ventas Semana'],
        ['key' => 'categoryChart', 'titulo' => 'Ventas por Categoría'],
        ['key' => 'topProductsChart', 'titulo' => 'Productos Más Vendidos']
    ];

    foreach ($graficasEsperadas as $graficaEsperada) {
        $tituloEsperado = $graficaEsperada['titulo'];
        $keyEsperada = $graficaEsperada['key'];
        $svgGrafica = $svgGraficas[$keyEsperada] ?? '';

        if ($keyEsperada === 'categoryChart') {
            $html .= '<pagebreak />';
        }

        $html .= '<div class="chart-box"><div class="chart-title">' . escapeHtml($tituloEsperado) . '</div>';
        if ($svgGrafica !== '') {
            $html .= $svgGrafica;
        } else {
            $html .= '<p class="note">No se pudo renderizar la gráfica en el servidor.</p>';
        }

        if ($tituloEsperado === 'Rendimiento de Ventas Semana') {
            if (!empty($ventasSemana)) {
                $html .= '<table class="table"><thead><tr><th>Día</th><th class="right">Ventas ($)</th></tr></thead><tbody>';
                foreach ($ventasSemana as $row) {
                    $html .= '<tr><td>' . date('d/m/Y', strtotime($row['dia'])) . '</td><td class="right">$' . number_format((float)$row['total'], 2) . '</td></tr>';
                }
                $html .= '</tbody></table>';
            } else {
                $html .= '<p>Sin datos disponibles.</p>';
            }
        }

        if ($tituloEsperado === 'Ventas por Categoría') {
            if (!empty($ventasCategoria)) {
                $html .= '<table class="table"><thead><tr><th>Categoría</th><th class="center">Unidades</th></tr></thead><tbody>';
                foreach ($ventasCategoria as $row) {
                    $html .= '<tr><td>' . escapeHtml($row['etiqueta']) . '</td><td class="center">' . (int)$row['valor'] . '</td></tr>';
                }
                $html .= '</tbody></table>';
            } else {
                $html .= '<p>Sin datos disponibles.</p>';
            }
        }

        if ($tituloEsperado === 'Productos Más Vendidos') {
            if (!empty($topProductos)) {
                $html .= '<table class="table"><thead><tr><th>Producto</th><th class="center">Unidades</th></tr></thead><tbody>';
                foreach ($topProductos as $row) {
                    $html .= '<tr><td>' . escapeHtml($row['nombre_producto']) . '</td><td class="center">' . (int)$row['total_vendido'] . '</td></tr>';
                }
                $html .= '</tbody></table>';
            } else {
                $html .= '<p>Sin datos disponibles.</p>';
            }
        }

        $html .= '</div><!--CHART_END-->';
    }
}

try {
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

    $mpdf = new Mpdf([
        'format' => 'A4',
        'margin_left' => 12,
        'margin_right' => 12,
        'margin_top' => 24,
        'margin_bottom' => 12,
        'tempDir' => $mpdfTempDir
    ]);

    $logoPath = realpath(__DIR__ . '/../../Assets/img/logopdf.png');
    if ($logoPath && file_exists($logoPath)) {
        $logoData = @file_get_contents($logoPath);
        if ($logoData !== false && $logoData !== '') {
            $mpdf->imageVars['logo_header'] = $logoData;
            $html = '<div style="position: fixed; top: 0.5mm; right: 4mm; width: 27.3mm; text-align: right;"><img src="var:logo_header" style="width: 100%; height: auto; display: block; margin-left: auto;"></div>' . $html;
        }
    }

    foreach ($graficasBinarias as $key => $binario) {
        $mpdf->imageVars['chart_' . $key] = $binario;
    }

    $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);

    $bloquesHtml = preg_split(
        '/(<!--CHART_END-->|<pagebreak\s*\/?\s*>)/i',
        $html,
        -1,
        PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
    );

    foreach ($bloquesHtml as $bloque) {
        if (trim($bloque) === '' || stripos($bloque, 'CHART_END') !== false) {
            continue;
        }

        $mpdf->WriteHTML($bloque, \Mpdf\HTMLParserMode::HTML_BODY);
    }

    $mpdf->Output('reporte_dashboard_' . date('Ymd_His') . '.pdf', \Mpdf\Output\Destination::INLINE);
} catch (Exception $e) {
    http_response_code(500);
    die('Error al generar PDF: ' . $e->getMessage());
}
