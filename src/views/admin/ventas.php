<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

// 1. CARGAR CONTROLADORES Y MODELOS
$basePath = realpath(__DIR__ . '/../../../');
require_once $basePath . "/controller/ventasController.php";
require_once $basePath . "/model/VentasModel.php";

// 2. OBTENER TODAS LAS VENTAS DE LA BD
$ventas = VentasController::ctrMostrarVentas();

// 3. CÁLCULO DE ESTADÍSTICAS PARA LOS WIDGETS
$totalVentas = count($ventas);
$totalFacturado = 0;
$pagosTarjeta = 0;
$ventasConCliente = 0;

foreach ($ventas as $v) {
    $totalFacturado += (float)$v["total_venta"];
    if (isset($v["metodo_pago"]) && strtolower($v["metodo_pago"]) == 'tarjeta') {
        $pagosTarjeta++;
    }
    if (isset($v["nombre_cliente"]) && $v["nombre_cliente"] != 'Cliente Mostrador') {
        $ventasConCliente++;
    }
}

$activeMenu = 'ventas';
$pageTitle = 'Ventas';
// Mantenemos tus estilos originales
$pageStyles = ['/ElZapato/Assets/css/pages/admin-stats.css', '/ElZapato/Assets/css/pages/admin-ventas.css'];
require __DIR__ . '/../layouts/admin-shell-start.php';

// CONFIGURACIÓN DEL HEADER (Aquí es donde se genera la fecha y el buscador)
$pageHeading = 'Ventas';
$searchInputId = 'searchVenta';
$searchPlaceholder = 'Buscar por cliente o # venta...';
$showSearch = true;
require __DIR__ . '/../layouts/admin-header.php';
?>

<div class="ventas-page">
    
    <div class="stats-grid stats-list">
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-receipt"></i> Ventas Registradas</span>
            <span class="stats-list-value"><?= $totalVentas ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-dollar-sign"></i> Total Facturado</span>
            <span class="stats-list-value">$<?= number_format($totalFacturado, 2) ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-credit-card"></i> Pagos con Tarjeta</span>
            <span class="stats-list-value"><?= $pagosTarjeta ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-user-check"></i> Ventas con Cliente</span>
            <span class="stats-list-value"><?= $ventasConCliente ?></span>
        </div>
    </div>

    <div class="actions-bar">
        <div class="actions-left">
            <div class="filters">
                <select class="filter-select" id="filterVentaMetodo">
                    <option value="">Método de Pago</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                </select>
                <button class="btn-outline-primary" id="btnResetVentaFiltros" type="button">
                    <i class="fas fa-times"></i> Limpiar
                </button>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Listado de Ventas Realizadas</h3>
            <a href="javascript:location.reload()" class="view-all"><i class="fas fa-sync"></i> Actualizar</a>
        </div>
        <div class="table-responsive">
            <table class="data-table" id="ventasTable">
                <thead>
                    <tr>
                        <th># Venta</th>
                        <th>Cliente</th>
                        <th>Cajero</th>
                        <th>Método de Pago</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($ventas)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #999;">No se encontraron registros de ventas.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($ventas as $venta): ?>
                        <tr>
                            <td style="font-weight: bold; color: #A67C52;">
                                V-<?= str_pad($venta["id_venta"], 6, "0", STR_PAD_LEFT) ?>
                            </td>
                            <td><?= htmlspecialchars($venta["nombre_cliente"]) ?></td>
                            <td><small><?= htmlspecialchars($venta["nombre_usuario"]) ?></small></td>
                            <td><?= htmlspecialchars($venta["metodo_pago"]) ?></td>
                            <td><?= date("d/m/Y H:i", strtotime($venta["fecha_venta"])) ?></td>
                            <td style="font-weight: bold;">$<?= number_format($venta["total_venta"], 2) ?></td>
                            <td>
                                <span class="status-badge completed">Completada</span>
                            </td>
                            <td>
                                <button class="btn-icon small" onclick="editVenta(this)"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Filtrado JS
    const searchVenta = document.getElementById('searchVenta');
    const filterMetodo = document.getElementById('filterVentaMetodo');

    function applyVentasFilters() {
        const term = (searchVenta?.value || '').toLowerCase().trim();
        const metodo = (filterMetodo?.value || '').toLowerCase();

        document.querySelectorAll('#ventasTable tbody tr').forEach(row => {
            if(row.cells.length < 2) return;
            const text = row.textContent.toLowerCase();
            const metodoCell = row.cells[3]?.textContent.toLowerCase() || '';
            const passSearch = term === '' || text.includes(term);
            const passMetodo = metodo === '' || metodoCell.includes(metodo);
            row.style.display = (passSearch && passMetodo) ? '' : 'none';
        });
    }

    searchVenta?.addEventListener('input', applyVentasFilters);
    filterMetodo?.addEventListener('change', applyVentasFilters);

    document.getElementById('btnResetVentaFiltros')?.addEventListener('click', function () {
        if (filterMetodo) filterMetodo.value = '';
        if (searchVenta) searchVenta.value = '';
        applyVentasFilters();
    });
</script>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>
<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>