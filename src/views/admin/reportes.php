<?php
// --- 1. LÓGICA DE DATOS ---
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin'); 

$basePath = realpath(__DIR__ . '/../../../');
require_once $basePath . "/model/ProductoVarianteModel.php";
require_once $basePath . "/controller/ProductoVarianteController.php";
require_once $basePath . "/model/VentasModel.php";
require_once $basePath . "/controller/ventasController.php";

// Datos dinámicos
$dataStock     = ProductoVarianteController::ctrMostrarTodoElStock();
$stockBajoUmbral = defined('LOW_STOCK_THRESHOLD') ? (int)LOW_STOCK_THRESHOLD : 10;
$dataAlertas   = ProductoVarianteController::ctrMostrarStockBajo($stockBajoUmbral);
$stats         = ProductoVarianteController::ctrMostrarResumenReportes();
$dataClientes  = ProductoVarianteController::ctrTodosClientesConResumen();
$ticketsVentas = VentasController::ctrMostrarVentas();
$movs          = ProductoVarianteController::ctrMostrarHistorialMovimientos();

$fechaCajaInicio = $_GET['caja_desde'] ?? date('Y-m-01');
$fechaCajaFin = $_GET['caja_hasta'] ?? date('Y-m-d');
$idCajaFiltro = isset($_GET['caja_id']) ? (int)$_GET['caja_id'] : 0;
$tabActiva = $_GET['tab'] ?? 'panel1';
$printFechaDesde = $_GET['fecha_desde'] ?? date('Y-m-01');
$printFechaHasta = $_GET['fecha_hasta'] ?? date('Y-m-d');

$cajasDisponibles = ProductoVarianteController::ctrObtenerCajasReporte();
$reporteCaja = ProductoVarianteController::ctrObtenerReporteCaja($fechaCajaInicio, $fechaCajaFin, $idCajaFiltro);
$cajaResumen = $reporteCaja['resumen'] ?? [];
$cajaPorCaja = $reporteCaja['por_caja'] ?? [];
$cajaDetalle = $reporteCaja['detalle'] ?? [];
$filtrosCajaAplicados = $reporteCaja['filtros'] ?? [
    'fecha_inicio' => date('Y-m-01'),
    'fecha_fin' => date('Y-m-d'),
    'id_caja' => 0
];

// --- CONFIG ---
$activeMenu = 'reportes';
$pageTitle = 'Reportes';
$pageStyles = [
    '/ElZapato/Assets/css/pages/admin-stats.css',
    '/ElZapato/Assets/css/pages/admin-reportes.css'
];

require __DIR__ . '/../layouts/admin-shell-start.php';

$pageHeading = 'Centro de Reportes';
$searchInputId = 'searchReporte';
$searchPlaceholder = 'Filtrar datos del panel...';
$showSearch = false;

require __DIR__ . '/../layouts/admin-header.php';
?>

<style>
    .panel-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .panel-toolbar .btn-outline-primary {
        min-width: 170px;
    }

    .print-filtros {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .print-filtros label {
        font-size: 0.82rem;
        font-weight: 600;
        color: #4D3B2E;
    }

    .print-filtros input[type="date"] {
        border: 1px solid #D6C0B3;
        border-radius: 8px;
        padding: 8px 10px;
        background: #fff;
        color: #4D3B2E;
        min-width: 150px;
    }

    .caja-filtros {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
        margin-bottom: 15px;
        align-items: end;
    }

    .caja-filtros .field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .caja-filtros label {
        font-size: 0.82rem;
        font-weight: 600;
        color: #4D3B2E;
    }

    .caja-filtros input,
    .caja-filtros select {
        border: 1px solid #D6C0B3;
        border-radius: 8px;
        padding: 8px 10px;
        background: #fff;
        color: #4D3B2E;
    }

    .caja-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    @media print {
        body * {
            visibility: hidden;
        }

        .print-only,
        .print-only * {
            visibility: visible;
        }

        .print-only {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            background: #fff;
        }
    }
</style>

<!-- STATS -->
<div class="stats-grid stats-list">
    <div class="stats-list-item">
        <span>Tickets Generados <strong><?= (int)($stats['total_tickets'] ?? 0) ?></strong></span>
    </div>
    <div class="stats-list-item">
        <span>Flujo Neto <strong>$<?= number_format((float)($stats['flujo_neto'] ?? 0), 2) ?></strong></span>
    </div>
    <div class="stats-list-item">
        <span>Alertas de Stock Bajo <strong><?= (int)($stats['total_alertas'] ?? 0) ?></strong></span>
    </div>
    <div class="stats-list-item">
        <span>Clientes con Compra <strong><?= (int)($stats['total_clientes'] ?? 0) ?></strong></span>
    </div>
</div>

<!-- TABS -->
<ul class="tablist">
    <li class="tab"><a href="#panel1">STOCK</a></li>
    <li class="tab"><a href="#panel2">ALERTAS</a></li>
    <li class="tab"><a href="#panel3">MOVIMIENTOS</a></li>
    <li class="tab"><a href="#panel4">CAJA</a></li>
    <li class="tab"><a href="#panel5">CLIENTES</a></li>
    <li class="tab"><a href="#panel6">TICKETS</a></li>
</ul>

<div id="reportesContent">

<!-- STOCK -->
<div class="tabpanel" id="panel1">
<div class="panel-toolbar">
    <div class="print-filtros">
        <label>Desde</label>
        <input type="date" class="js-print-desde" value="<?= htmlspecialchars($printFechaDesde) ?>">
        <label>Hasta</label>
        <input type="date" class="js-print-hasta" value="<?= htmlspecialchars($printFechaHasta) ?>">
    </div>
    <button type="button" class="btn-outline-primary btn-imprimir-tab" data-panel="panel1"><i class="fas fa-print"></i> Imprimir Stock</button>
</div>
<table class="data-table">
<thead>
<tr><th>Producto</th><th>Talla</th><th>Color</th><th>Código</th><th>Stock</th><th>Precio</th></tr>
</thead>
<tbody>
<?php foreach($dataStock as $f): ?>
<tr>
<td><?= htmlspecialchars($f['nombre_producto']) ?></td>
<td><?= $f['talla'] ?></td>
<td><?= $f['color'] ?></td>
<td><?= $f['codigo_barras'] ?></td>
<td><?= $f['stock'] ?></td>
<td>$<?= number_format($f['precio_venta'],2) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<!-- ALERTAS -->
<div class="tabpanel" id="panel2">
<div class="panel-toolbar">
    <div class="print-filtros">
        <label>Desde</label>
        <input type="date" class="js-print-desde" value="<?= htmlspecialchars($printFechaDesde) ?>">
        <label>Hasta</label>
        <input type="date" class="js-print-hasta" value="<?= htmlspecialchars($printFechaHasta) ?>">
    </div>
    <button type="button" class="btn-outline-primary btn-imprimir-tab" data-panel="panel2"><i class="fas fa-print"></i> Imprimir Alertas</button>
</div>
<table class="data-table">
<thead>
<tr><th>Producto</th><th>Talla</th><th>Stock</th><th>Nivel</th></tr>
</thead>
<tbody>
<?php foreach($dataAlertas as $a): ?>
<tr>
<td><?= $a['nombre_producto'] ?></td>
<td><?= $a['talla'] ?></td>
<td><?= $a['stock'] ?></td>
<td><?= ($a['stock'] <= 5) ? 'Crítico' : 'Bajo' ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<!-- MOVIMIENTOS -->
<div class="tabpanel" id="panel3">
<div class="panel-toolbar">
    <div class="print-filtros">
        <label>Desde</label>
        <input type="date" class="js-print-desde" value="<?= htmlspecialchars($printFechaDesde) ?>">
        <label>Hasta</label>
        <input type="date" class="js-print-hasta" value="<?= htmlspecialchars($printFechaHasta) ?>">
    </div>
    <button type="button" class="btn-outline-primary btn-imprimir-tab" data-panel="panel3"><i class="fas fa-print"></i> Imprimir Movimientos</button>
</div>
<table class="data-table">
<thead>
<tr><th>Fecha</th><th>Tipo</th><th>Ref</th><th>Producto</th><th>Cantidad</th></tr>
</thead>
<tbody>
<?php foreach($movs as $m): ?>
<tr>
<td><?= date("d/m/Y H:i", strtotime($m['fecha'])) ?></td>
<td><?= $m['tipo'] ?></td>
<td><?= $m['referencia'] ?></td>
<td><?= $m['producto'] ?></td>
<td><?= $m['cantidad'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<!-- CAJA -->
<div class="tabpanel" id="panel4">
<div class="panel-toolbar">
    <div class="print-filtros">
        <label>Desde</label>
        <input type="date" class="js-print-desde" value="<?= htmlspecialchars($filtrosCajaAplicados['fecha_inicio'] ?? $printFechaDesde) ?>">
        <label>Hasta</label>
        <input type="date" class="js-print-hasta" value="<?= htmlspecialchars($filtrosCajaAplicados['fecha_fin'] ?? $printFechaHasta) ?>">
    </div>
    <button type="button" class="btn-outline-primary btn-imprimir-tab" data-panel="panel4"><i class="fas fa-print"></i> Imprimir Caja</button>
</div>

<form method="GET" class="caja-filtros">
    <input type="hidden" name="tab" value="panel4">
    <div class="field">
        <label for="caja_desde">Fecha desde</label>
        <input type="date" id="caja_desde" name="caja_desde" value="<?= htmlspecialchars($filtrosCajaAplicados['fecha_inicio'] ?? '') ?>">
    </div>
    <div class="field">
        <label for="caja_hasta">Fecha hasta</label>
        <input type="date" id="caja_hasta" name="caja_hasta" value="<?= htmlspecialchars($filtrosCajaAplicados['fecha_fin'] ?? '') ?>">
    </div>
    <div class="field">
        <label for="caja_id">Caja</label>
        <select id="caja_id" name="caja_id">
            <option value="0">Todas las cajas</option>
            <?php foreach ($cajasDisponibles as $caja): ?>
                <?php $idCaja = (int)($caja['id_caja'] ?? 0); ?>
                <option value="<?= $idCaja ?>" <?= ((int)($filtrosCajaAplicados['id_caja'] ?? 0) === $idCaja) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($caja['nombre_caja'] ?? ('Caja #' . $idCaja)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <button type="submit" class="btn-outline-primary"><i class="fas fa-filter"></i> Aplicar</button>
    </div>
</form>

<div class="stats-grid stats-list caja-grid">
    <div class="stats-list-item"><span class="stats-list-label">Ventas</span><span class="stats-list-value"><?= (int)($cajaResumen['total_ventas'] ?? 0) ?></span></div>
    <div class="stats-list-item"><span class="stats-list-label">Anuladas</span><span class="stats-list-value"><?= (int)($cajaResumen['total_anuladas'] ?? 0) ?></span></div>
    <div class="stats-list-item"><span class="stats-list-label">Ingresos</span><span class="stats-list-value">$<?= number_format((float)($cajaResumen['total_ingresos'] ?? 0), 2) ?></span></div>
    <div class="stats-list-item"><span class="stats-list-label">Efectivo</span><span class="stats-list-value">$<?= number_format((float)($cajaResumen['total_efectivo'] ?? 0), 2) ?></span></div>
    <div class="stats-list-item"><span class="stats-list-label">Tarjeta</span><span class="stats-list-value">$<?= number_format((float)($cajaResumen['total_tarjeta'] ?? 0), 2) ?></span></div>
    <div class="stats-list-item"><span class="stats-list-label">Transferencia</span><span class="stats-list-value">$<?= number_format((float)($cajaResumen['total_transferencia'] ?? 0), 2) ?></span></div>
    <div class="stats-list-item"><span class="stats-list-label">Cajeros Operando</span><span class="stats-list-value"><?= (int)($cajaResumen['cajeros_operando'] ?? 0) ?></span></div>
</div>

<h4 style="margin: 10px 0; color:#4D3B2E;">Resumen por caja</h4>
<table class="data-table" style="margin-bottom:14px;">
    <thead>
        <tr>
            <th>Caja</th>
            <th>Ventas</th>
            <th>Anuladas</th>
            <th>Ingresos</th>
            <th>Primera Venta</th>
            <th>Última Venta</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!empty($cajaPorCaja)): foreach($cajaPorCaja as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['caja'] ?? 'N/A') ?></td>
            <td><?= (int)($row['total_ventas'] ?? 0) ?></td>
            <td><?= (int)($row['total_anuladas'] ?? 0) ?></td>
            <td>$<?= number_format((float)($row['total_ingresos'] ?? 0), 2) ?></td>
            <td><?= !empty($row['primera_venta']) ? date('d/m/Y H:i', strtotime($row['primera_venta'])) : '—' ?></td>
            <td><?= !empty($row['ultima_venta']) ? date('d/m/Y H:i', strtotime($row['ultima_venta'])) : '—' ?></td>
        </tr>
    <?php endforeach; else: ?>
        <tr><td colspan="6" style="text-align:center; color:#888;">No hay datos para el rango seleccionado.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<h4 style="margin: 10px 0; color:#4D3B2E;">Detalle de ventas (máx. 300)</h4>
<table class="data-table">
<thead>
<tr><th># Venta</th><th>Caja</th><th>Cajero</th><th>Método</th><th>Fecha</th><th>Total</th><th>Estado</th></tr>
</thead>
<tbody>
<?php if (!empty($cajaDetalle)): foreach($cajaDetalle as $venta): ?>
<tr>
    <td>V-<?= str_pad((string)((int)($venta['id_venta'] ?? 0)), 6, '0', STR_PAD_LEFT) ?></td>
    <td><?= htmlspecialchars($venta['caja'] ?? 'N/A') ?></td>
    <td><?= htmlspecialchars($venta['nombre_usuario'] ?? 'N/A') ?></td>
    <td><?= htmlspecialchars($venta['metodo_pago'] ?? 'N/A') ?></td>
    <td><?= !empty($venta['fecha_venta']) ? date('d/m/Y H:i', strtotime($venta['fecha_venta'])) : '—' ?></td>
    <td>$<?= number_format((float)($venta['total_venta'] ?? 0), 2) ?></td>
    <td><?= htmlspecialchars(ucfirst((string)($venta['estado'] ?? 'completada'))) ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="7" style="text-align:center; color:#888;">Sin ventas para los filtros de caja.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<!-- CLIENTES -->
<div class="tabpanel" id="panel5">
<div class="panel-toolbar">
    <div class="print-filtros">
        <label>Desde</label>
        <input type="date" class="js-print-desde" value="<?= htmlspecialchars($printFechaDesde) ?>">
        <label>Hasta</label>
        <input type="date" class="js-print-hasta" value="<?= htmlspecialchars($printFechaHasta) ?>">
    </div>
    <button type="button" class="btn-outline-primary btn-imprimir-tab" data-panel="panel5"><i class="fas fa-print"></i> Imprimir Clientes</button>
</div>
<table class="data-table">
<thead>
<tr><th>Cliente</th><th>Tickets</th><th>Total</th><th>Última Compra</th></tr>
</thead>
<tbody>
<?php if(!empty($dataClientes)): foreach($dataClientes as $c): ?>
<tr>
<td><?= $c['cliente'] ?></td>
<td><?= $c['tickets'] ?></td>
<td>$<?= number_format($c['total_comprado'],2) ?></td>
<td><?= date("d/m/Y", strtotime($c['ultima_compra'])) ?></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

<!-- TICKETS -->
<div class="tabpanel" id="panel6">
<div class="panel-toolbar">
    <div class="print-filtros">
        <label>Desde</label>
        <input type="date" class="js-print-desde" value="<?= htmlspecialchars($printFechaDesde) ?>">
        <label>Hasta</label>
        <input type="date" class="js-print-hasta" value="<?= htmlspecialchars($printFechaHasta) ?>">
    </div>
    <button type="button" class="btn-outline-primary btn-imprimir-tab" data-panel="panel6"><i class="fas fa-print"></i> Imprimir Tickets</button>
</div>
<table class="data-table">
<thead>
<tr><th># Venta</th><th>Cliente</th><th>Cajero</th><th>Método</th><th>Fecha</th><th>Total</th><th>Estado</th></tr>
</thead>
<tbody>
<?php if(!empty($ticketsVentas)): foreach($ticketsVentas as $t): ?>
<tr>
<td>V-<?= str_pad((string)((int)($t['id_venta'] ?? 0)), 6, '0', STR_PAD_LEFT) ?></td>
<td><?= htmlspecialchars($t['nombre_cliente'] ?? 'Cliente Mostrador') ?></td>
<td><?= htmlspecialchars($t['nombre_usuario'] ?? 'N/A') ?></td>
<td><?= htmlspecialchars($t['metodo_pago'] ?? 'N/A') ?></td>
<td><?= !empty($t['fecha_venta']) ? date("d/m/Y H:i", strtotime($t['fecha_venta'])) : '—' ?></td>
<td>$<?= number_format((float)($t['total_venta'] ?? 0),2) ?></td>
<td><?= htmlspecialchars(ucfirst((string)($t['estado_venta'] ?? 'completada'))) ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="7" style="text-align:center; color:#888;">No hay tickets de venta para mostrar.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

</div>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>

<script>
(function(){
const tabs = document.querySelectorAll('.tab a');
const panels = document.querySelectorAll('.tabpanel');
const defaultTab = '<?= htmlspecialchars($tabActiva, ENT_QUOTES, 'UTF-8') ?>';

function activarTab(targetId, updateUrl = true) {
    const id = (targetId || '').replace('#', '');
    const panel = document.getElementById(id);
    if (!panel) return;

    tabs.forEach(link => {
        const li = link.parentNode;
        const active = link.getAttribute('href') === '#' + id;
        li.classList.toggle('tab-active', active);
        link.setAttribute('aria-selected', active ? 'true' : 'false');
        link.setAttribute('tabindex', active ? '0' : '-1');
    });

    panels.forEach(p => p.classList.toggle('show', p.id === id));

    if (updateUrl) {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', id);
        history.replaceState({}, '', url.toString());
    }
}

tabs.forEach(link => {
    link.setAttribute('role', 'tab');
    link.addEventListener('click', function(e) {
        e.preventDefault();
        activarTab(this.getAttribute('href'), true);
    });
});

const tabFromHash = window.location.hash ? window.location.hash.substring(1) : '';
activarTab(tabFromHash || defaultTab || 'panel1', false);

document.querySelectorAll('.btn-imprimir-tab').forEach(btn => {
    btn.addEventListener('click', function() {
        const panelId = this.getAttribute('data-panel');
        const url = new URL('/ElZapato/src/api/generar_reporte_reportes.php', window.location.origin);
        url.searchParams.set('tab', panelId);

        const toolbar = this.closest('.panel-toolbar');
        const inputDesde = toolbar?.querySelector('.js-print-desde');
        const inputHasta = toolbar?.querySelector('.js-print-hasta');

        const hoy = new Date();
        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth() + 1).padStart(2, '0');
        const dd = String(hoy.getDate()).padStart(2, '0');
        const fechaHoy = `${yyyy}-${mm}-${dd}`;
        const fechaInicioMes = `${yyyy}-${mm}-01`;

        let fechaDesde = inputDesde?.value || fechaInicioMes;
        let fechaHasta = inputHasta?.value || fechaHoy;

        const formatoValido = /^\d{4}-\d{2}-\d{2}$/;
        if (!formatoValido.test(fechaDesde)) fechaDesde = fechaInicioMes;
        if (!formatoValido.test(fechaHasta)) fechaHasta = fechaHoy;
        if (fechaDesde > fechaHasta) {
            const temp = fechaDesde;
            fechaDesde = fechaHasta;
            fechaHasta = temp;
        }

        if (inputDesde) inputDesde.value = fechaDesde;
        if (inputHasta) inputHasta.value = fechaHasta;

        url.searchParams.set('fecha_desde', fechaDesde);
        url.searchParams.set('fecha_hasta', fechaHasta);

        const cajaDesde = document.getElementById('caja_desde')?.value || '';
        const cajaHasta = document.getElementById('caja_hasta')?.value || '';
        const cajaId = document.getElementById('caja_id')?.value || '0';

        if (panelId === 'panel4') {
            url.searchParams.set('caja_desde', fechaDesde || cajaDesde);
            url.searchParams.set('caja_hasta', fechaHasta || cajaHasta);
        }
        if (cajaId) url.searchParams.set('caja_id', cajaId);

        window.open(url.toString(), '_blank');
    });
});
})();
</script>

<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>