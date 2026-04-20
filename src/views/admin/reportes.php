<?php
// --- 1. LÓGICA DE DATOS ---
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin'); 

$basePath = realpath(__DIR__ . '/../../../');
require_once $basePath . "/model/ProductoVarianteModel.php";
require_once $basePath . "/controller/ProductoVarianteController.php";

// Carga de datos dinámicos
$dataStock   = ProductoVarianteController::ctrMostrarTodoElStock();
$dataAlertas = ProductoVarianteController::ctrMostrarStockBajo(10);
$stats       = ProductoVarianteController::ctrMostrarResumenReportes();

// --- 2. CONFIGURACIÓN DE VISTA ---
$activeMenu = 'reportes';
$pageTitle = 'Reportes y Auditoría';
$pageStyles = [
    '/ElZapato/Assets/css/pages/admin-stats.css', 
    '/ElZapato/Assets/css/pages/admin-reportes.css'
];

require __DIR__ . '/../layouts/admin-shell-start.php';

$pageHeading = 'Centro de Reportes';
$searchInputId = 'searchReporte';
$searchPlaceholder = 'Filtrar datos del panel...';
$showSearch = true;

require __DIR__ . '/../layouts/admin-header.php';
?>

<style>
    :root {
        --primary-light: #E4E0E1;
        --primary-soft: #D6C0B3;
        --primary-dark: #AB886D;
        --text-dark: #000000;
        --font-family: "Roboto", sans-serif;
        --nocolor: #772c24;
    }

    /* Estilos de Componentes */
    .view-all { background: var(--primary-dark) !important; color: white !important; border: none; }
    .tablist .tab-active a { border-bottom: 3px solid var(--primary-dark) !important; color: var(--primary-dark) !important; }
    .data-table thead tr { background: var(--primary-dark); color: white; }

    /* Ajustes específicos para la pestaña de CAJA */
    .caja-wrapper { 
        max-width: 850px; 
        margin: 0 auto; 
        padding: 10px;
    }

    .tabla-caja-ajustada {
        width: 100% !important;
        border-collapse: collapse;
        margin-top: 10px;
        background: white;
    }

    .tabla-caja-ajustada td {
        padding: 15px;
        border-bottom: 1px solid var(--primary-soft);
        vertical-align: middle;
    }

    .col-descripcion { text-align: left; width: 65%; font-size: 15px; }
    .col-valor { text-align: right; width: 35%; font-weight: bold; font-size: 16px; }
</style>

<div class="stats-grid stats-list">
    <div class="stats-list-item" style="border-bottom: 2px solid var(--primary-soft);">
        <span class="stats-list-label"><i class="fas fa-receipt"></i> Tickets Generados <strong><?= $stats['total_tickets'] ?></strong></span>
    </div>
    <div class="stats-list-item" style="border-bottom: 2px solid var(--primary-dark);">
        <span class="stats-list-label"><i class="fas fa-cash-register"></i> Flujo Neto <strong>$<?= number_format(abs($stats['flujo_neto']), 2) ?></strong></span>
    </div>
    <div class="stats-list-item" style="border-bottom: 2px solid var(--nocolor);">
        <span class="stats-list-label"><i class="fas fa-exclamation-triangle"></i> Alertas de Stock <strong><?= $stats['total_alertas'] ?></strong></span>
    </div>
    <div class="stats-list-item" style="border-bottom: 2px solid var(--primary-dark);">
        <span class="stats-list-label"><i class="fas fa-users"></i> Clientes Activos <strong><?= $stats['total_clientes'] ?></strong></span>
    </div>
</div>

<div class="report-toolbar" style="margin-bottom: 20px; display: flex; justify-content: flex-end; gap: 10px;">
    <a href="#" id="btnExportarPDF" class="view-all" style="padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; transition: 0.3s;">
        <i class="fas fa-file-pdf"></i> DESCARGAR REPORTE PDF
    </a>
</div>

<ul class="tablist" role="tablist">
    <li class="tab" role="tab"><a href="#panel1">INVENTARIO</a></li>
    <li class="tab" role="tab"><a href="#panel2">ALERTAS</a></li>
    <li class="tab" role="tab"><a href="#panel3">MOVIMIENTOS</a></li>
    <li class="tab" role="tab"><a href="#panel4">CAJA</a></li>
    <li class="tab" role="tab"><a href="#panel5">CLIENTES</a></li>
    <li class="tab" role="tab"><a href="#panel6">TICKETS</a></li>
</ul>

<div id="reportesContent">
    
    <div class="tabpanel" id="panel1" role="tabpanel">
        <div class="table-card">
            <div class="table-header">
                <h3>Stock actual por variante</h3>
                <a href="javascript:location.reload()" class="view-all"><i class="fas fa-sync"></i> Actualizar</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>Producto</th><th>Talla</th><th>Color</th><th>Código</th><th>Stock</th><th>Precio Venta</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($dataStock as $fila): ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['nombre_producto']) ?></td>
                            <td><?= $fila['talla'] ?></td>
                            <td><?= $fila['color'] ?></td>
                            <td><code><?= $fila['codigo_barras'] ?></code></td>
                            <td><strong><?= $fila['stock'] ?></strong></td>
                            <td>$<?= number_format($fila['precio_venta'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tabpanel" id="panel2" role="tabpanel">
        <div class="table-card">
            <div class="table-header"><h3>Productos bajo umbral mínimo (10 unidades)</h3></div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Producto</th><th>Talla</th><th>Stock Actual</th><th>Estado</th></tr></thead>
                    <tbody>
                        <?php foreach($dataAlertas as $alerta): ?>
                        <tr>
                            <td><?= htmlspecialchars($alerta['nombre_producto']) ?></td>
                            <td><?= $alerta['talla'] ?></td>
                            <td><strong><?= $alerta['stock'] ?></strong></td>
                            <td><span class="status-badge <?= ($alerta['stock'] <= 5) ? 'pending' : 'completed' ?>"><?= ($alerta['stock'] <= 5) ? 'Crítico' : 'Bajo' ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tabpanel" id="panel3" role="tabpanel">
        <div class="table-card">
            <div class="table-header"><h3>Historial Reciente de Inventario</h3></div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Fecha</th><th>Tipo</th><th>Referencia</th><th>Producto</th><th>Cant.</th></tr></thead>
                    <tbody>
                        <?php 
                        $movimientos = ProductoVarianteController::ctrMostrarHistorialMovimientos();
                        foreach($movimientos as $mov): 
                            $claseBadge = ($mov['tipo'] == 'Entrada') ? 'completed' : (($mov['tipo'] == 'Salida') ? 'warning' : 'pending');
                        ?>
                        <tr>
                            <td><?= date("d/m/Y H:i", strtotime($mov['fecha'])) ?></td>
                            <td><span class="status-badge <?= $claseBadge ?>"><?= $mov['tipo'] ?></span></td>
                            <td><?= $mov['referencia'] ?></td>
                            <td><?= htmlspecialchars($mov['producto']) ?></td>
                            <td style="font-weight: bold; color: <?= (strpos($mov['cantidad'], '+') !== false) ? '#28a745' : '#772C24' ?>;"><?= $mov['cantidad'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tabpanel" id="panel4" role="tabpanel">
        <div class="caja-wrapper">
            <div class="stats-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="table-card" style="border-left: 5px solid #AB886D;">
                    <div class="table-header"><h3><i class="fas fa-arrow-up" style="color: #AB886D;"></i> INGRESOS</h3></div>
                    <div style="padding: 20px; text-align: center;">
                        <span style="font-size: 13px; color: #666;">Total Recaudado por Ventas</span>
                        <h2 style="color: #AB886D; font-size: 28px; margin-top: 10px;">$<?= number_format(abs($stats['flujo_neto']), 2) ?></h2>
                    </div>
                </div>
                <div class="table-card" style="border-left: 5px solid var(--nocolor);">
                    <div class="table-header"><h3><i class="fas fa-arrow-down" style="color: var(--nocolor);"></i> EGRESOS</h3></div>
                    <div style="padding: 20px; text-align: center;">
                        <span style="font-size: 13px; color: #666;">Inversión Estimada en Stock Actual</span>
                        <?php 
                            $totalInversion = 0;
                            foreach($dataStock as $s) { $totalInversion += ($s['precio_venta'] * 0.7) * $s['stock']; }
                        ?>
                        <h2 style="color: var(--nocolor); font-size: 28px; margin-top: 10px;">$<?= number_format($totalInversion, 2) ?></h2>
                    </div>
                </div>
            </div>

            <div class="table-card">
                <div class="table-header"><h3>Resumen Detallado de Caja</h3></div>
                <div style="padding: 10px;">
                    <table class="tabla-caja-ajustada">
                        <tbody>
                            <tr>
                                <td class="col-descripcion">Flujo Neto de Ventas (Ingresos)</td>
                                <td class="col-valor" style="color: #AB886D;">+ $<?= number_format(abs($stats['flujo_neto']), 2) ?></td>
                            </tr>
                            <tr>
                                <td class="col-descripcion">Costo de Mercadería en Inventario (Egresos)</td>
                                <td class="col-valor" style="color: var(--nocolor);">- $<?= number_format($totalInversion, 2) ?></td>
                            </tr>
                            <tr style="background: var(--primary-light);">
                                <td class="col-descripcion"><strong>BALANCE OPERATIVO TEÓRICO</strong></td>
                                <td class="col-valor" style="font-size: 1.2em;">$<?= number_format(abs($stats['flujo_neto']) - $totalInversion, 2) ?></td>
                            </tr>
                            <tr>
                                <td class="col-descripcion">Total de Transacciones (Tickets)</td>
                                <td class="col-valor"><?= $stats['total_tickets'] ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>

<script>
(function() {
    var doc = document,
        tabs = doc.querySelectorAll('.tab a'),
        panels = doc.querySelectorAll('.tabpanel'),
        searchInput = doc.getElementById('searchReporte'),
        btnExportar = doc.getElementById('btnExportarPDF'),
        activeTab = tabs[0],
        activePanel;

    function activateTab(e) {
        if(e) e.preventDefault();
        tabs.forEach(t => t.parentNode.classList.remove('tab-active'));
        this.parentNode.classList.add('tab-active');
        activeTab = this;
        const targetId = activeTab.getAttribute('href').substring(1);
        panels.forEach(p => p.classList.remove('show'));
        activePanel = doc.getElementById(targetId);
        if(activePanel) activePanel.classList.add('show');
        
        // Actualizar link de PDF según la pestaña
        const tipo = targetId.replace('panel', ''); 
        btnExportar.href = `/ElZapato/src/api/generar_reporte.php?tipo=${tipo}`;
        btnExportar.target = "_blank";
    }

    tabs.forEach(t => t.addEventListener('click', activateTab));
    activateTab.call(activeTab);

    if(searchInput) {
        searchInput.addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            if(activePanel) {
                activePanel.querySelectorAll('tbody tr').forEach(row => {
                    row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
                });
            }
        });
    }
})();
</script>

<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>