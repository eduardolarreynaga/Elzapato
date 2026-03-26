<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

// 1. CARGAR CONTROLADORES Y MODELOS
require_once "../../../controller/productosController.php";
require_once "../../../model/ProductosModel.php";

// 2. OBTENER DATOS REALES PARA LOS GRÁFICOS
$topVendidos = ProductosController::ctrProductosMasVendidos(); 

$nombresTop = [];
$cantidadesTop = [];
foreach ($topVendidos as $top) {
    $nombresTop[] = $top["nombre_producto"];
    $cantidadesTop[] = (int)$top["total_vendido"];
}

// Datos para Stock Bajo (Filtrado de la lista general)
$todosLosProductos = ProductosController::ctrMostrarProductos(); 
$stockBajo = array_filter($todosLosProductos, fn($p) => ($p['stock'] ?? 0) <= 10);

$activeMenu = 'dashboard';
$pageTitle = 'Dashboard';
require __DIR__ . '/../layouts/admin-shell-start.php';

$pageHeading = 'Dashboard El Zapato';
require __DIR__ . '/../layouts/admin-header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<div class="dashboard-content" style="padding: 20px; background-color: #f8f9fa; min-height: 100vh;">
    
    <div style="display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 20px; margin-bottom: 25px;">
        
        <div class="card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; flex-direction: column; min-height: 380px;">
            <h3 style="font-size: 1.1rem; margin-bottom: 15px; color: #333;">Rendimiento de Ventas Semana</h3>
            <div style="flex-grow: 1; position: relative; width: 100%; height: 300px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <div class="card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; flex-direction: column; min-height: 380px;">
            <h3 style="font-size: 1.1rem; margin-bottom: 15px; color: #333;">Ventas por Categoría</h3>
            <div style="flex-grow: 1; position: relative; width: 100%; height: 300px;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 20px; margin-bottom: 25px;">
        
        <div class="card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); min-height: 380px;">
            <h3 style="font-size: 1.1rem; margin-bottom: 15px; color: #333;">Productos Más Vendidos</h3>
            <div style="position: relative; width: 100%; height: 300px;">
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>

        <div class="card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); min-height: 380px;">
            <h3 style="font-size: 1.1rem; margin-bottom: 15px; color: #333;">
                <i class="fas fa-exclamation-triangle" style="color: #A67C52;"></i> Stock Bajo
            </h3>
            <div style="max-height: 300px; overflow-y: auto;">
                <?php if(empty($stockBajo)): ?>
                    <p style="text-align: center; color: #999; padding-top: 50px;">No hay productos con stock bajo.</p>
                <?php else: ?>
                    <?php foreach (array_slice($stockBajo, 0, 6) as $s): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f1f1f1;">
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-weight: 600; font-size: 0.9rem;"><?= htmlspecialchars($s['nombre_producto']) ?></span>
                            <small style="color: #888;">Talla: <?= $s['talla'] ?? 'N/A' ?></small>
                        </div>
                        <span style="color: #e74c3c; font-weight: bold; background: #fff5f5; padding: 4px 8px; border-radius: 6px;"><?= $s['stock'] ?> uds</span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="table-card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <div class="table-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="font-size: 1.1rem; color: #333;">Últimas Ventas Realizadas</h3>
            <a href="../admin/ventas.php" style="color: #A67C52; text-decoration: none; font-size: 0.9rem; font-weight: 600;">Ver historial completo <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; min-width: 600px;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #f1f1f1; color: #777; font-size: 0.85rem;">
                        <th style="padding: 15px;"># VENTA</th>
                        <th style="padding: 15px;">CLIENTE</th>
                        <th style="padding: 15px;">FECHA</th>
                        <th style="padding: 15px;">TOTAL</th>
                        <th style="padding: 15px;">ESTADO</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.9rem;">
                    <tr style="border-bottom: 1px solid #f9f9f9;">
                        <td style="padding: 15px; font-weight: bold;">#V00125</td>
                        <td style="padding: 15px;">Juan Pérez</td>
                        <td style="padding: 15px;">12/03/2026</td>
                        <td style="padding: 15px; font-weight: bold;">$120.00</td>
                        <td style="padding: 15px;"><span style="background: #e1f7ec; color: #2ecc71; padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">COMPLETADA</span></td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f9f9f9;">
                        <td style="padding: 15px; font-weight: bold;">#V00124</td>
                        <td style="padding: 15px;">María García</td>
                        <td style="padding: 15px;">12/03/2026</td>
                        <td style="padding: 15px; font-weight: bold;">$85.50</td>
                        <td style="padding: 15px;"><span style="background: #fff4e5; color: #f39c12; padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">PENDIENTE</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Configuración universal para prevenir desbordes
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 20 } }
        }
    };

    // 1. Rendimiento Ventas (Línea)
    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            datasets: [{
                label: 'Ventas ($)',
                data: [850, 1200, 950, 1450, 1680, 2100, 1850],
                borderColor: '#A67C52',
                backgroundColor: 'rgba(166, 124, 82, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: commonOptions
    });

    // 2. Ventas por Categoría (Dona)
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: ['Deportivo', 'Casual', 'Botas', 'Formal'],
            datasets: [{
                data: [35, 25, 20, 20],
                backgroundColor: ['#A67C52', '#D6C0B3', '#242424', '#8a6d51'],
                borderWidth: 0
            }]
        },
        options: { ...commonOptions, cutout: '70%' }
    });

    // 3. Productos Más Vendidos (BARRAS VERTICALES REALES)
    new Chart(document.getElementById('topProductsChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($nombresTop); ?>,
            datasets: [{
                label: 'Unidades Vendidas',
                data: <?php echo json_encode($cantidadesTop); ?>,
                backgroundColor: '#A67C52',
                borderRadius: 8,
                barThickness: 35
            }]
        },
        options: {
            ...commonOptions,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f1f1' }, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });
</script>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>