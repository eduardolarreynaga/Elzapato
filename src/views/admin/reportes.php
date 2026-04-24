<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

$basePath = realpath(__DIR__ . '/../../../');
require_once $basePath . "/model/ProductoVarianteModel.php";
require_once $basePath . "/controller/ProductoVarianteController.php";

// DATOS
$dataStock     = ProductoVarianteController::ctrMostrarTodoElStock();
$dataAlertas   = ProductoVarianteController::ctrMostrarStockBajo(10);
$stats         = ProductoVarianteController::ctrMostrarResumenReportes();
$dataClientes  = ProductoVarianteController::ctrTopClientes();
$dataTickets   = ProductoVarianteController::ctrResumenTickets();
$dataCompras   = ProductoVarianteController::ctrUltimasCompras();
$dataCaja      = ProductoVarianteController::ctrResumenCaja();

$activeMenu = 'reportes';
$pageTitle = 'Reportes';
$pageStyles = ['/ElZapato/Assets/css/pages/admin-stats.css', '/ElZapato/Assets/css/pages/admin-reportes.css'];

require __DIR__ . '/../layouts/admin-shell-start.php';

$pageHeading = 'Reportes';
$searchInputId = 'searchReporte';
$searchPlaceholder = 'Buscar en el panel activo...';
$showSearch = true;

require __DIR__ . '/../layouts/admin-header.php';
?>


<!-- STATS -->
<div class="stats-grid stats-list">
    <div class="stats-list-item">
        <span class="stats-list-label"><i class="fas fa-receipt"></i> Tickets</span>
        <span class="stats-list-value"><?= $stats['total_tickets'] ?></span>
    </div>
    <div class="stats-list-item">
        <span class="stats-list-label"><i class="fas fa-cash-register"></i> Flujo</span>
        <span class="stats-list-value">$<?= number_format($stats['flujo_neto'],2) ?></span>
    </div>
    <div class="stats-list-item">
        <span class="stats-list-label"><i class="fas fa-exclamation-triangle"></i> Alertas</span>
        <span class="stats-list-value"><?= $stats['total_alertas'] ?></span>
    </div>
    <div class="stats-list-item">
        <span class="stats-list-label"><i class="fas fa-users"></i> Clientes con Compras</span>
        <span class="stats-list-value"><?= $stats['total_clientes'] ?></span>
    </div>
</div>

<!-- TABS -->
<ul class="tablist" role="tablist">
    <li class="tab"><a href="#panel1">Stock</a></li>
    <li class="tab"><a href="#panel2">Alertas</a></li>
    <li class="tab"><a href="#panel3">Movimientos</a></li>
    <li class="tab"><a href="#panel4">Caja</a></li>
    <li class="tab"><a href="#panel5">Clientes</a></li>
    <li class="tab"><a href="#panel6">Tickets</a></li>
    <li class="tab"><a href="#panel7">Compras</a></li>
</ul>

<div id="reportesContent">

<!-- STOCK -->
<div class="tabpanel" id="panel1">
<div class="table-card">
<div class="table-header">
<h3>Stock actual por producto</h3>

<div class="botones-grupo">
<a href="javascript:location.reload()" class="view-all"><i class="fas fa-sync"></i> Actualizar</a>

<!-- Botón PDF -->
<a href="javascript:void(0)" onclick="imprimirPDF()" class="view-all" style="color: #e74c3c;">
    <i class="fas fa-file-pdf"></i> PDF
</a>
</div>

</div>
<div class="table-responsive">
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
</div>
</div>

<!-- ALERTAS -->
<div class="tabpanel" id="panel2">
<div class="table-card">
<div class="table-header">
    <h3>Alertas de stock</h3>
    <div class="botones-grupo">
        <a href="javascript:location.reload()" class="view-all"><i class="fas fa-sync"></i> Actualizar</a>
        <!-- Botón PDF -->
        <a href="javascript:void(0)" onclick="imprimirPDF()" class="view-all" style="color: #e74c3c;">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
    </div>
</div>


<div class="table-responsive">
<table class="data-table">
<thead>
<tr>
<th>Producto</th><th>Talla</th><th>Color</th><th>Stock</th><th>Nivel</th>
</tr>
</thead>
<tbody>

<?php if(!empty($dataAlertas)): ?>
<?php foreach($dataAlertas as $a): ?>
<tr>
<td><?= htmlspecialchars($a['nombre_producto']) ?></td>
<td><?= $a['talla'] ?></td>
<td><?= $a['color'] ?></td>
<td><?= $a['stock'] ?></td>
<td>
<span class="status-badge <?= ($a['stock'] <= 5) ? 'pending' : 'completed' ?>">
<?= ($a['stock'] <= 5) ? 'Crítico' : 'Bajo' ?>
</span>
</td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="5">Sin alertas</td></tr>
<?php endif; ?>

</tbody>
</table>
</div>
</div>
</div>

<!-- MOVIMIENTOS-->
<div class="tabpanel" id="panel3">
<div class="table-card">
<div class="table-header">
<h3>Historial de movimientos</h3>
<div class="botones-grupo">
    <a href="javascript:location.reload()" class="view-all"><i class="fas fa-sync"></i> Actualizar</a>
    <!-- Botón PDF -->
    <a href="javascript:void(0)" onclick="imprimirPDF()" class="view-all" style="color: #e74c3c;">
        <i class="fas fa-file-pdf"></i> PDF
    </a>
</div>
</div>

<div class="table-responsive">
<table class="data-table">
<thead>
<tr>
<th>Fecha</th><th>Tipo</th><th>Referencia</th><th>Producto</th><th>Cantidad</th>
</tr>
</thead>
<tbody>

<?php 
$movimientos = ProductoVarianteController::ctrMostrarHistorialMovimientos();
?>

<?php if(!empty($movimientos)): ?>
<?php foreach($movimientos as $m): ?>
<tr>
<td><?= date("d/m/Y H:i", strtotime($m['fecha'])) ?></td>
<td>
<span class="status-badge <?= ($m['tipo']=='Entrada') ? 'completed' : 'pending' ?>">
<?= $m['tipo'] ?>
</span>
</td>
<td><?= $m['referencia'] ?></td>
<td><?= htmlspecialchars($m['producto']) ?></td>
<td><?= $m['cantidad'] ?></td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="5">Sin movimientos</td></tr>
<?php endif; ?>

</tbody>
</table>
</div>
</div>
</div>

<!-- CAJA (RESPETANDO DISEÑO ORIGINAL) -->
<div class="tabpanel" id="panel4">
<div class="table-card">
<div class="table-header">
<h3>Flujo de Caja</h3>
<div class="botones-grupo">
    <a href="javascript:location.reload()" class="view-all"><i class="fas fa-sync"></i> Actualizar</a>
    <!-- Botón PDF -->
    <a href="javascript:void(0)" onclick="imprimirPDF()" class="view-all" style="color: #e74c3c;">
        <i class="fas fa-file-pdf"></i> PDF
    </a>
    </div>
</div>
<div class="table-responsive">
<table class="data-table">
<thead>
<tr><th>Concepto</th><th>Operaciones</th><th>Monto</th></tr>
</thead>
<tbody>

<tr>
<td>Ingresos por ventas</td>
<td><?= $dataCaja['total_tickets'] ?> tickets</td>
<td>$<?= number_format($dataCaja['ingresos'],2) ?></td>
</tr>

<tr>
<td>Egresos por compras</td>
<td>-</td>
<td>$<?= number_format($dataCaja['egresos'] ?? 0,2) ?></td>
</tr>

<tr>
<td><strong>Flujo neto</strong></td>
<td>-</td>
<td><strong>$<?= number_format($stats['flujo_neto'],2) ?></strong></td>
</tr>

</tbody>
</table>
</div>
</div>
</div>

<!-- CLIENTES -->
<div class="tabpanel" id="panel5">
<div class="table-card">
<div class="table-header">
<h3>Clientes con compras</h3>
<div class="botones-grupo">
    <a href="javascript:location.reload()" class="view-all"><i class="fas fa-sync"></i> Actualizar</a>
    <!-- Botón PDF -->
    <a href="javascript:void(0)" onclick="imprimirPDF()" class="view-all" style="color: #e74c3c;">
        <i class="fas fa-file-pdf"></i> PDF
    </a>
    </div>
</div>

<div class="table-responsive">
<table class="data-table">
<thead>
<tr>
<th>Cliente</th><th>Tickets</th><th>Total</th><th>Última Compra</th>
</tr>
</thead>
<tbody>

<?php if(!empty($dataClientes)): ?>
<?php foreach($dataClientes as $c): ?>
<tr>
<td><?= htmlspecialchars($c['cliente']) ?></td>
<td><?= $c['tickets'] ?></td>
<td>$<?= number_format($c['total_comprado'],2) ?></td>
<td><?= date("d/m/Y", strtotime($c['ultima_compra'])) ?></td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="4">Sin datos</td></tr>
<?php endif; ?>

</tbody>
</table>
</div>
</div>
</div>

<!-- TICKETS -->
<div class="tabpanel" id="panel6">
<div class="table-card">
<div class="table-header">
<h3>Resumen de tickets</h3>
<div class="botones-grupo">
        <a href="javascript:location.reload()" class="view-all"><i class="fas fa-sync"></i> Actualizar</a>
        <!-- Botón PDF -->
    <a href="javascript:void(0)" onclick="imprimirPDF()" class="view-all" style="color: #e74c3c;">
        <i class="fas fa-file-pdf"></i> PDF
    </a>
    </div>
</div>

<div class="table-responsive">
<table class="data-table">
<thead>
<tr>
<th>Periodo</th><th>Tickets</th><th>Promedio Día</th><th>Ticket Promedio</th>
</tr>
</thead>
<tbody>

<?php if(!empty($dataTickets)): ?>
<?php foreach($dataTickets as $t): ?>
<tr>
<td><?= $t['periodo'] ?></td>
<td><?= $t['tickets'] ?></td>
<td><?= $t['promedio_dia'] ?></td>
<td>$<?= number_format($t['ticket_promedio'],2) ?></td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="4">Sin datos</td></tr>
<?php endif; ?>

</tbody>
</table>
</div>
</div>
</div>

<!-- COMPRAS (MISMO DISEÑO) -->
<div class="tabpanel" id="panel7">
<div class="table-card">
<div class="table-header">
<h3>Últimas Compras</h3>
<div class="botones-grupo">
        <a href="javascript:location.reload()" class="view-all"><i class="fas fa-sync"></i> Actualizar</a>
        <!-- Botón PDF -->
    <a href="javascript:void(0)" onclick="imprimirPDF()" class="view-all" style="color: #e74c3c;">
        <i class="fas fa-file-pdf"></i> PDF
    </a>
    </div>
</div>
<div class="table-responsive">
<table class="data-table">
<thead>
<tr><th># Compra</th><th>Proveedor</th><th>Fecha</th><th>Ítems</th><th>Total</th></tr>
</thead>
<tbody>
<?php foreach($dataCompras as $c): ?>
<tr>
<td>C-<?= str_pad($c['id_compra'],4,'0',STR_PAD_LEFT) ?></td>
<td><?= htmlspecialchars($c['proveedor']) ?></td>
<td><?= date("d/m/Y H:i", strtotime($c['fecha_compra'])) ?></td>
<td><?= $c['items'] ?></td>
<td>$<?= number_format($c['total'],2) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
</div>

</div>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>

<script>
(function() {
    const tabs = document.querySelectorAll('.tab a');
    const panels = document.querySelectorAll('.tabpanel');

    function activateTab(e){
        if(e) e.preventDefault();
        tabs.forEach(t=>t.parentNode.classList.remove('tab-active'));
        this.parentNode.classList.add('tab-active');

        panels.forEach(p=>p.classList.remove('show'));
        document.querySelector(this.getAttribute('href')).classList.add('show');
    }

    tabs.forEach(t=>t.addEventListener('click',activateTab));
    tabs[0].click();
})();


document.addEventListener("DOMContentLoaded", function() {
    
    const tabs = document.querySelectorAll('.tab');
    const panels = document.querySelectorAll('.tabpanel');

    function activarPestana(targetId) {
        // 1. Ocultar todos los paneles y quitar clase activa a las pestañas
        panels.forEach(p => {
            p.classList.remove('show');
            p.style.display = 'none';
        });
        tabs.forEach(t => t.classList.remove('tab-active'));

        // 2. Activar la pestaña seleccionada
        const link = document.querySelector(`.tab a[href="${targetId}"]`);
        if (link) {
            const parentTab = link.parentElement;
            parentTab.classList.add('tab-active');
            
            // 3. Mostrar el panel correspondiente
            const targetPanel = document.querySelector(targetId);
            if (targetPanel) {
                targetPanel.classList.add('show');
                targetPanel.style.display = 'block';
            }

            // 4. Guardar en localStorage para el reload
            localStorage.setItem('reporteTabActual', targetId);
        }
    }

    // Evento Click para las pestañas
    tabs.forEach(tab => {
        const link = tab.querySelector('a');
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            activarPestana(targetId);
        });
    });

    // --- LÓGICA DE RESTAURACIÓN TRAS ACTUALIZAR ---
    const tabGuardada = localStorage.getItem('reporteTabActual');
    
    if (tabGuardada && document.querySelector(tabGuardada)) {
        activarPestana(tabGuardada);
    } else {
        // Si no hay nada, por defecto la primera (Stock)
        activarPestana('#panel1');
    }
});

// --- FUNCIÓN IMPRIMIR PDF ---
function imprimirPDF() {
    // Usamos el comando nativo de impresión del navegador
    // El navegador permite "Guardar como PDF" por defecto
    window.print();
}

</script>

<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>