<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

$activeMenu = 'reportes';
$pageTitle = 'Reportes';
$pageStyles = ['/ElZapato/Assets/css/pages/admin-stats.css', '/ElZapato/Assets/css/pages/admin-reportes.css'];
require __DIR__ . '/../layouts/admin-shell-start.php';

$pageHeading = 'Reportes';
$searchInputId = 'searchReporte';
$searchPlaceholder = 'Buscar en reportes...';
$showSearch = true;
require __DIR__ . '/../layouts/admin-header.php';
?>

			<div class="stats-grid stats-list">
				<div class="stats-list-item">
					<span class="stats-list-label"><i class="fas fa-receipt"></i> Tickets Generados</span>
					<span class="stats-list-value">245</span>
				</div>
				<div class="stats-list-item">
					<span class="stats-list-label"><i class="fas fa-cash-register"></i> Flujo Neto</span>
					<span class="stats-list-value">$8,460</span>
				</div>
				<div class="stats-list-item">
					<span class="stats-list-label"><i class="fas fa-exclamation-triangle"></i> Alertas de Stock</span>
					<span class="stats-list-value">8</span>
				</div>
				<div class="stats-list-item">
					<span class="stats-list-label"><i class="fas fa-users"></i> Clientes con Compras</span>
					<span class="stats-list-value">97</span>
				</div>
			</div>

			<div class="tables-grid" id="reportesContent">
				<div class="table-card">
					<div class="table-header">
						<h3>Stock actual por producto, talla y color</h3>
						<a href="#" class="view-all"><i class="fas fa-sync"></i> Actualizar</a>
					</div>
					<div class="table-responsive">
						<table class="data-table">
							<thead>
								<tr>
									<th>Producto</th>
									<th>Talla</th>
									<th>Color</th>
									<th>Código</th>
									<th>Stock</th>
									<th>Precio Venta</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Tenis Running</td>
									<td>40</td>
									<td>Negro</td>
									<td>TRN-40-NEG</td>
									<td>24</td>
									<td>$70.00</td>
								</tr>
								<tr>
									<td>Tenis Running</td>
									<td>41</td>
									<td>Azul</td>
									<td>TRN-41-AZU</td>
									<td>18</td>
									<td>$70.00</td>
								</tr>
								<tr>
									<td>Zapato Casual</td>
									<td>39</td>
									<td>Café</td>
									<td>ZPC-39-CAF</td>
									<td>33</td>
									<td>$45.00</td>
								</tr>
								<tr>
									<td>Botín Cuero</td>
									<td>42</td>
									<td>Marrón</td>
									<td>BTC-42-MAR</td>
									<td>9</td>
									<td>$95.00</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<div class="table-card">
					<div class="table-header">
						<h3>Alertas de stock mínimo (umbral: 10)</h3>
						<a href="#" class="view-all"><i class="fas fa-exclamation-circle"></i> Revisar</a>
					</div>
					<div class="table-responsive">
						<table class="data-table">
							<thead>
								<tr>
									<th>Producto</th>
									<th>Talla</th>
									<th>Color</th>
									<th>Stock Actual</th>
									<th>Nivel</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Botín Cuero</td>
									<td>42</td>
									<td>Marrón</td>
									<td>9</td>
									<td><span class="status-badge pending">Bajo</span></td>
								</tr>
								<tr>
									<td>Mocasín</td>
									<td>40</td>
									<td>Negro</td>
									<td>7</td>
									<td><span class="status-badge pending">Bajo</span></td>
								</tr>
								<tr>
									<td>Sandalia Playa</td>
									<td>38</td>
									<td>Rosa</td>
									<td>5</td>
									<td><span class="status-badge pending">Crítico</span></td>
								</tr>
								<tr>
									<td>Zapato Formal</td>
									<td>41</td>
									<td>Negro</td>
									<td>6</td>
									<td><span class="status-badge pending">Crítico</span></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<div class="table-card">
					<div class="table-header">
						<h3>Historial de movimientos (entradas, salidas, ajustes)</h3>
						<a href="#" class="view-all"><i class="fas fa-stream"></i> Ver todo</a>
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
								<tr>
									<td>21/03/2026 10:15</td>
									<td><span class="status-badge completed">Entrada</span></td>
									<td>Compra C-00091</td>
									<td>Tenis Running 40 Negro</td>
									<td>+20</td>
								</tr>
								<tr>
									<td>21/03/2026 13:05</td>
									<td><span class="status-badge pending">Salida</span></td>
									<td>Venta V-000245</td>
									<td>Tenis Running 40 Negro</td>
									<td>-2</td>
								</tr>
								<tr>
									<td>21/03/2026 16:20</td>
									<td><span class="status-badge pending">Ajuste</span></td>
									<td>Ajuste AJ-0019</td>
									<td>Botín Cuero 42 Marrón</td>
									<td>-1</td>
								</tr>
								<tr>
									<td>22/03/2026 09:42</td>
									<td><span class="status-badge completed">Entrada</span></td>
									<td>Compra C-00092</td>
									<td>Mocasín 40 Negro</td>
									<td>+12</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<div class="table-card">
					<div class="table-header">
						<h3>Flujo de Caja</h3>
						<a href="#" class="view-all"><i class="fas fa-file-export"></i> Exportar</a>
					</div>
					<div class="table-responsive">
						<table class="data-table">
							<thead>
								<tr>
									<th>Concepto</th>
									<th>Cantidad de Operaciones</th>
									<th>Monto</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Ingresos por ventas</td>
									<td>245 tickets</td>
									<td>$18,940.00</td>
								</tr>
								<tr>
									<td>Egresos por compras</td>
									<td>91 compras</td>
									<td>$10,480.00</td>
								</tr>
								<tr>
									<td>Ajustes de caja</td>
									<td>3 movimientos</td>
									<td>$0.00</td>
								</tr>
								<tr>
									<td><strong>Flujo neto</strong></td>
									<td><strong>-</strong></td>
									<td><strong>$8,460.00</strong></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<div class="table-card">
					<div class="table-header">
						<h3>Historial de compras por cliente</h3>
						<a href="#" class="view-all"><i class="fas fa-user-clock"></i> Detalle</a>
					</div>
					<div class="table-responsive">
						<table class="data-table">
							<thead>
								<tr>
									<th>Cliente</th>
									<th>Tickets</th>
									<th>Total Comprado</th>
									<th>Última Compra</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Juan Pérez</td>
									<td>18</td>
									<td>$1,480.00</td>
									<td>21/03/2026</td>
								</tr>
								<tr>
									<td>María García</td>
									<td>14</td>
									<td>$1,215.50</td>
									<td>20/03/2026</td>
								</tr>
								<tr>
									<td>Ana Martínez</td>
									<td>9</td>
									<td>$740.00</td>
									<td>19/03/2026</td>
								</tr>
								<tr>
									<td>Carlos López</td>
									<td>12</td>
									<td>$1,032.00</td>
									<td>20/03/2026</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<div class="table-card">
					<div class="table-header">
						<h3>Número de tickets generados</h3>
						<a href="#" class="view-all"><i class="fas fa-receipt"></i> Ver ventas</a>
					</div>
					<div class="table-responsive">
						<table class="data-table">
							<thead>
								<tr>
									<th>Periodo</th>
									<th>Tickets</th>
									<th>Promedio por Día</th>
									<th>Ticket Promedio</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Hoy</td>
									<td>17</td>
									<td>17</td>
									<td>$78.40</td>
								</tr>
								<tr>
									<td>Últimos 7 días</td>
									<td>105</td>
									<td>15</td>
									<td>$74.20</td>
								</tr>
								<tr>
									<td>Mes actual</td>
									<td>245</td>
									<td>14</td>
									<td>$77.30</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="table-card table-card-full">
                    <div class="table-header">
                        <h3>Últimas Compras (Tablas compras y detalle_compra)</h3>
                        <a href="#" class="view-all"><i class="fas fa-file-invoice"></i> Ver historial</a>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th># Compra</th>
                                    <th>Proveedor</th>
                                    <th>Fecha</th>
                                    <th>Ítems</th>
                                    <th>Total Estimado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>C-00091</td>
                                    <td>Calzado Andino SAC</td>
                                    <td>21/03/2026 10:15</td>
                                    <td>4</td>
                                    <td>$1,920.00</td>
                                </tr>
                                <tr>
                                    <td>C-00090</td>
                                    <td>Distribuidora Nova</td>
                                    <td>21/03/2026 08:30</td>
                                    <td>6</td>
                                    <td>$2,140.00</td>
                                </tr>
                                <tr>
                                    <td>C-00089</td>
                                    <td>Importaciones Lima Shoes</td>
                                    <td>20/03/2026 16:22</td>
                                    <td>3</td>
                                    <td>$1,150.00</td>
                                </tr>
                                <tr>
                                    <td>C-00088</td>
                                    <td>Textiles y Calzado Sur</td>
                                    <td>20/03/2026 11:09</td>
                                    <td>5</td>
                                    <td>$1,840.00</td>
                                </tr>
                                <tr>
                                    <td>C-00087</td>
                                    <td>Mayorista del Pacífico</td>
                                    <td>19/03/2026 15:43</td>
                                    <td>2</td>
                                    <td>$780.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
				</div>
			</div>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>

	<script>
		document.getElementById('searchReporte').addEventListener('input', function(e) {
			const term = e.target.value.toLowerCase().trim();
			document.querySelectorAll('#reportesContent .table-card tbody tr').forEach(row => {
				row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
			});
		});
	</script>

<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>
