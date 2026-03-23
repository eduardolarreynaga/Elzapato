<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

$activeMenu = 'ventas';
$pageTitle = 'Ventas';
$pageStyles = ['/ElZapato/Assets/css/pages/admin-stats.css', '/ElZapato/Assets/css/pages/admin-ventas.css'];
require __DIR__ . '/../layouts/admin-shell-start.php';

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
                    <span class="stats-list-value">245</span>
                </div>
                <div class="stats-list-item">
                    <span class="stats-list-label"><i class="fas fa-dollar-sign"></i> Total Facturado</span>
                    <span class="stats-list-value">$18,940</span>
                </div>
                <div class="stats-list-item">
                    <span class="stats-list-label"><i class="fas fa-credit-card"></i> Pagos con Tarjeta</span>
                    <span class="stats-list-value">98</span>
                </div>
                <div class="stats-list-item">
                    <span class="stats-list-label"><i class="fas fa-user-check"></i> Ventas con Cliente</span>
                    <span class="stats-list-value">210</span>
                </div>
            </div>

            <!-- Barra de acciones -->
            <div class="actions-bar">
                <div class="actions-left">
                    <div class="filters">
                        <select class="filter-select" id="filterVentaMetodo">
                            <option value="">Método de Pago</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                        <select class="filter-select" id="filterVentaEstado">
                            <option value="">Estado</option>
                            <option value="completada">Completada</option>
                            <option value="pendiente">Pendiente</option>
                        </select>
                        <button class="btn-outline-primary" id="btnResetVentaFiltros" type="button" title="Limpiar filtros">
                            <i class="fas fa-times"></i> Limpiar
                        </button>
                    </div>
                </div>
                <div class="actions-right">
                    <button class="btn-icon" title="Exportar" type="button"><i class="fas fa-upload"></i></button>
                </div>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <h3>Listado de Ventas</h3>
                    <a href="#" class="view-all"><i class="fas fa-sync"></i> Actualizar</a>
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
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>V-000245</td>
                                <td>Juan Pérez</td>
                                <td>admin</td>
                                <td>Efectivo</td>
                                <td>16/03/2026 10:15</td>
                                <td>$120.00</td>
                                <td><span class="status-badge completed">Completada</span></td>
                            </tr>
                            <tr>
                                <td>V-000244</td>
                                <td>María García</td>
                                <td>admin</td>
                                <td>Tarjeta</td>
                                <td>16/03/2026 09:48</td>
                                <td>$89.50</td>
                                <td><span class="status-badge completed">Completada</span></td>
                            </tr>
                            <tr>
                                <td>V-000243</td>
                                <td>Cliente Mostrador</td>
                                <td>admin</td>
                                <td>Transferencia</td>
                                <td>15/03/2026 18:10</td>
                                <td>$150.00</td>
                                <td><span class="status-badge pending">Pendiente</span></td>
                            </tr>
                            <tr>
                                <td>V-000242</td>
                                <td>Ana Martínez</td>
                                <td>admin</td>
                                <td>Tarjeta</td>
                                <td>15/03/2026 17:02</td>
                                <td>$73.00</td>
                                <td><span class="status-badge completed">Completada</span></td>
                            </tr>
                            <tr>
                                <td>V-000241</td>
                                <td>Carlos López</td>
                                <td>admin</td>
                                <td>Efectivo</td>
                                <td>15/03/2026 16:37</td>
                                <td>$210.00</td>
                                <td><span class="status-badge completed">Completada</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            </div>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>

<script>

        const searchVenta     = document.getElementById('searchVenta');
        const filterMetodo      = document.getElementById('filterVentaMetodo');
        const filterEstadoVenta = document.getElementById('filterVentaEstado');

        function applyVentasFilters() {
            const term   = (searchVenta?.value || '').toLowerCase().trim();
            const metodo = (filterMetodo?.value || '').toLowerCase();
            const estado = (filterEstadoVenta?.value || '').toLowerCase();

            document.querySelectorAll('#ventasTable tbody tr').forEach(row => {
                const text        = row.textContent.toLowerCase();
                const metodoCell  = row.children[3]?.textContent.toLowerCase() || '';
                const estadoCell  = row.querySelector('.status-badge')?.textContent.toLowerCase() || '';

                const passSearch = term   === '' || text.includes(term);
                const passMetodo = metodo === '' || metodoCell.includes(metodo);
                const passEstado = estado === '' || estadoCell.includes(estado);

                row.style.display = passSearch && passMetodo && passEstado ? '' : 'none';
            });
        }

        searchVenta?.addEventListener('input', applyVentasFilters);
        filterMetodo?.addEventListener('change', applyVentasFilters);
        filterEstadoVenta?.addEventListener('change', applyVentasFilters);

        document.getElementById('btnResetVentaFiltros')?.addEventListener('click', function () {
            if (filterMetodo)      filterMetodo.value = '';
            if (filterEstadoVenta) filterEstadoVenta.value = '';
            if (searchVenta)       searchVenta.value = '';
            applyVentasFilters();
        });
    </script>

<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>
