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
$pageTitle = 'Reportes';
$pageStyles = ['/ElZapato/Assets/css/pages/admin-stats.css', '/ElZapato/Assets/css/pages/admin-reportes.css'];
require __DIR__ . '/../layouts/admin-shell-start.php';

$pageHeading = 'Reportes';
$searchInputId = 'searchReporte';
$searchPlaceholder = 'Buscar en el panel activo...';
$showSearch = true;
require __DIR__ . '/../layouts/admin-header.php';
?>

<!-- Sección de Estadísticas (Fila superior según tu imagen) -->
<div class="stats-grid stats-list">
    <div class="stats-list-item">
        <span class="stats-list-label"><i class="fas fa-receipt"></i> Tickets Generados <strong><?= $stats['total_tickets'] ?></strong></span>
    </div>
    <div class="stats-list-item">
        <span class="stats-list-label"><i class="fas fa-cash-register"></i> Flujo Neto <strong>$<?= number_format($stats['flujo_neto'], 2) ?></strong></span>
    </div>
    <div class="stats-list-item">
        <span class="stats-list-label"><i class="fas fa-exclamation-triangle"></i> Alertas de Stock <strong><?= $stats['total_alertas'] ?></strong></span>
    </div>
    <div class="stats-list-item">
        <span class="stats-list-label"><i class="fas fa-users"></i> Clientes con Compras <strong><?= $stats['total_clientes'] ?></strong></span>
    </div>
</div>

<!-- Sistema de Pestañas -->
<ul class="tablist" role="tablist">
    <li class="tab" role="tab"><a href="#panel1">STOCK</a></li>
    <li class="tab" role="tab"><a href="#panel2">ALERTAS</a></li>
    <li class="tab" role="tab"><a href="#panel3">MOVIMIENTOS</a></li>
    <li class="tab" role="tab"><a href="#panel4">CAJA</a></li>
    <li class="tab" role="tab"><a href="#panel5">CLIENTES</a></li>
    <li class="tab" role="tab"><a href="#panel6">TICKETS</a></li>
    <li class="tab" role="tab"><a href="#panel7">COMPRAS</a></li>
</ul>

<div id="reportesContent">
    <!-- PANEL 1: STOCK (Igual a la imagen) -->
    <div class="tabpanel" id="panel1" role="tabpanel">
        <div class="table-card">
            <div class="table-header">
                <h3>Stock actual por producto, talla y color</h3>
                <a href="javascript:location.reload()" class="view-all"><i class="fas fa-sync"></i> Actualizar</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Producto</th><th>Talla</th><th>Color</th><th>Código</th><th>Stock</th><th>Precio Venta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dataStock as $fila): ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['nombre_producto']) ?></td>
                            <td><?= $fila['talla'] ?></td>
                            <td><?= $fila['color'] ?></td>
                            <td><code><?= $fila['codigo_barras'] ?></code></td>
                            <td><?= $fila['stock'] ?></td>
                            <td>$<?= number_format($fila['precio_venta'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PANEL 2: ALERTAS -->
    <div class="tabpanel" id="panel2" role="tabpanel">
        <div class="table-card">
            <div class="table-header">
                <h3>Alertas de stock mínimo (Umbral: 10)</h3>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>Producto</th><th>Talla</th><th>Stock Actual</th><th>Nivel</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($dataAlertas as $alerta): ?>
                        <tr>
                            <td><?= htmlspecialchars($alerta['nombre_producto']) ?></td>
                            <td><?= $alerta['talla'] ?></td>
                            <td><strong><?= $alerta['stock'] ?></strong></td>
                            <td>
                                <span class="status-badge <?= ($alerta['stock'] <= 5) ? 'pending' : 'completed' ?>">
                                    <?= ($alerta['stock'] <= 5) ? 'Crítico' : 'Bajo' ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PANEL 3: MOVIMIENTOS -->
    <!-- PANEL 3: MOVIMIENTOS -->
    <div class="tabpanel" id="panel3" role="tabpanel">
        <div class="table-card">
            <div class="table-header">
                <h3>Historial de movimientos (entradas, salidas, ajustes)</h3>
                <a href="#" class="view-all"><i class="fas fa-list"></i> Ver todo</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Referencia</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $movimientos = ProductoVarianteController::ctrMostrarHistorialMovimientos();
                        foreach($movimientos as $mov): 
                            // Lógica de color para el badge
                            $claseBadge = 'pending'; // Ajuste por defecto
                            if($mov['tipo'] == 'Entrada') $claseBadge = 'completed';
                            if($mov['tipo'] == 'Salida') $claseBadge = 'warning'; // O la clase que uses para naranja
                        ?>
                        <tr>
                            <td><?= date("d/m/Y H:i", strtotime($mov['fecha'])) ?></td>
                            <td>
                                <span class="status-badge <?= $claseBadge ?>">
                                    <?= $mov['tipo'] ?>
                                </span>
                            </td>
                            <td><?= $mov['referencia'] ?></td>
                            <td><?= htmlspecialchars($mov['producto']) ?></td>
                            <td style="font-weight: bold; color: <?= (strpos($mov['cantidad'], '+') !== false) ? '#28a745' : '#772C24' ?>;">
                                <?= $mov['cantidad'] ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- PANELES RESTANTES (3 al 7 se llenan con la misma lógica dinámica) -->
    <div class="tabpanel" id="panel4" role="tabpanel">
        <div class="table-card">
            <div class="table-header"><h3>Resumen de Caja</h3></div>
            <table class="data-table">
                <tr><td>Balance Neto en Sistema</td><td><strong>$<?= number_format($stats['flujo_neto'], 2) ?></strong></td></tr>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>

<script>
// Manteniendo tu lógica de navegación IIFE original
(function() {
    var doc = document,
        tabs = doc.querySelectorAll('.tab a'),
        panels = doc.querySelectorAll('.tabpanel'),
        searchInput = doc.getElementById('searchReporte'),
        activeTab = tabs[0], // Iniciar con la primera pestaña
        activePanel;

    function activateTab(e) {
        if(e) e.preventDefault();
        if(activeTab && activeTab.parentNode) activeTab.parentNode.classList.remove('tab-active');
        
        this.parentNode.classList.add('tab-active');
        activeTab = this;
        activePanel = doc.getElementById(activeTab.getAttribute('href').substring(1));
        
        panels.forEach(p => p.classList.remove('show'));
        if(activePanel) activePanel.classList.add('show');
    }

    tabs.forEach(t => t.addEventListener('click', activateTab));
    activateTab.call(activeTab); // Disparar la primera pestaña al cargar

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
