<?php
// --- 1. LÓGICA DE DATOS ---
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin'); 

$basePath = realpath(__DIR__ . '/../../../');
require_once $basePath . "/model/ProductoVarianteModel.php";
require_once $basePath . "/controller/ProductoVarianteController.php";

// Datos dinámicos
$dataStock     = ProductoVarianteController::ctrMostrarTodoElStock();
$dataAlertas   = ProductoVarianteController::ctrMostrarStockBajo(10);
$stats         = ProductoVarianteController::ctrMostrarResumenReportes();
$dataClientes  = ProductoVarianteController::ctrTopClientes();
$dataTickets   = ProductoVarianteController::ctrResumenTickets();

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
$showSearch = true;

require __DIR__ . '/../layouts/admin-header.php';
?>

<!-- STATS -->
<div class="stats-grid stats-list">
    <div class="stats-list-item">
        <span>Tickets Generados <strong><?= $stats['total_tickets'] ?></strong></span>
    </div>
    <div class="stats-list-item">
        <span>Flujo Neto <strong>$<?= number_format($stats['flujo_neto'], 2) ?></strong></span>
    </div>
    <div class="stats-list-item">
        <span>Alertas <strong><?= $stats['total_alertas'] ?></strong></span>
    </div>
    <div class="stats-list-item">
        <span>Clientes <strong><?= $stats['total_clientes'] ?></strong></span>
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
<table class="data-table">
<thead>
<tr><th>Fecha</th><th>Tipo</th><th>Ref</th><th>Producto</th><th>Cantidad</th></tr>
</thead>
<tbody>
<?php 
$movs = ProductoVarianteController::ctrMostrarHistorialMovimientos();
foreach($movs as $m): ?>
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
<table class="data-table">
<tr>
<td>Flujo Neto</td>
<td><strong>$<?= number_format($stats['flujo_neto'],2) ?></strong></td>
</tr>
</table>
</div>

<!-- CLIENTES -->
<div class="tabpanel" id="panel5">
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
<table class="data-table">
<thead>
<tr><th>Periodo</th><th>Tickets</th><th>Promedio Día</th><th>Ticket Promedio</th></tr>
</thead>
<tbody>
<?php if(!empty($dataTickets)): foreach($dataTickets as $t): ?>
<tr>
<td><?= $t['periodo'] ?></td>
<td><?= $t['tickets'] ?></td>
<td><?= $t['promedio_dia'] ?></td>
<td>$<?= number_format($t['ticket_promedio'],2) ?></td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

</div>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>

<script>
(function(){
const tabs=document.querySelectorAll('.tab a');
const panels=document.querySelectorAll('.tabpanel');

function activar(e){
e.preventDefault();
tabs.forEach(t=>t.parentNode.classList.remove('tab-active'));
this.parentNode.classList.add('tab-active');

panels.forEach(p=>p.classList.remove('show'));
document.querySelector(this.getAttribute('href')).classList.add('show');
}

tabs.forEach(t=>t.addEventListener('click',activar));
tabs[0].click();
})();
</script>

<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>