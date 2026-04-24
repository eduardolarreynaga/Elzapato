<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

require_once "../../../controller/productosController.php";
require_once "../../../model/ProductosModel.php";

// --- PROCESAMIENTO DE DATOS REALES ---

// 1. Top Productos (Barras)
$topVendidos = ProductosController::ctrProductosMasVendidos(); 
$nombresTop = array_column($topVendidos, 'nombre_producto');
$cantidadesTop = array_column($topVendidos, 'total_vendido');

// 2. Ventas Semana (Línea)
$ventasSemana = ProductosController::ctrVentasSemana();
$diasSemanales = array_column($ventasSemana, 'dia');
$totalesSemanales = array_column($ventasSemana, 'total');

// 3. Categorías (Dona)
$ventasCategorias = ProductosController::ctrVentasPorCategoria();
$nombresCats = array_column($ventasCategorias, 'etiqueta');
$valoresCats = array_column($ventasCategorias, 'valor');

// 4. Stock Bajo (Filtro configurado en 10 para mayor seguridad)
$todosLosProductos = ProductosController::ctrMostrarProductos(); 
$stockBajo = [];

foreach ($todosLosProductos as $p) {
    $info = $p['info_variantes'] ?? '';
    if ($info != '') {
        $variantes = explode("||", $info);
        foreach ($variantes as $v) {
            $d = explode("|", $v);
            
            // Estructura: 0:talla, 1:color, 2:precio, 3:stock
            $v_talla  = $d[0] ?? 'N/A';
            $v_color  = $d[1] ?? 'N/A';
            $v_stock  = (int)($d[3] ?? 0);

            // Filtramos los que tienen 10 o menos unidades
            if ($v_stock <= 10) {
                $stockBajo[] = [
                    "nombre" => $p['nombre_producto'],
                    "talla"  => $v_talla,
                    "color"  => $v_color,
                    "stock"  => $v_stock
                ];
            }
        }
    }
}

// Ordenar: Los agotados (0) primero
usort($stockBajo, fn($a, $b) => $a['stock'] <=> $b['stock']);

$activeMenu = 'dashboard';
$pageTitle = 'Dashboard';
require __DIR__ . '/../layouts/admin-shell-start.php';
$pageHeading = 'Dashboard El Zapato';
require __DIR__ . '/../layouts/admin-header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<div class="report-toolbar" style="margin-bottom: 20px; display: flex; justify-content: flex-end; gap: 10px;">
    <a href="/ElZapato/src/api/generar_reporte.php?tipo=8" target="_blank" class="view-all" style="padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; transition: 0.3s;">
        <i class="fas fa-file-pdf"></i> DESCARGAR REPORTE PDF
    </a>
</div>

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
                <i class="fas fa-exclamation-triangle" style="color: #bc6e32;"></i> Bajo stock
            </h3>
            <div style="max-height: 300px; overflow-y: auto; padding-right: 5px;">
                <?php if(empty($stockBajo)): ?>
                    <p style="text-align: center; color: #999; padding-top: 50px;">Inventario saludable.</p>
                <?php else: ?>
                    <?php foreach (array_slice($stockBajo, 0, 15) as $s): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f1f1f1;">
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-weight: 600; font-size: 0.9rem;"><?= htmlspecialchars($s['nombre']) ?></span>
                            <small style="color: #888;">Talla: <?= $s['talla'] ?> | Color: <?= $s['color'] ?></small>
                        </div>
                        <span style="color: <?= ($s['stock'] <= 5) ? '#e74c3c' : '#bc6e32' ?>; font-weight: bold; background: <?= ($s['stock'] <= 5) ? '#fff0f0' : '#fff5f5' ?>; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem;">
                            <?= $s['stock'] ?> uds
                        </span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    };

    // Gráfico de Ventas
    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($diasSemanales) ?>,
            datasets: [{
                label: 'Ventas ($)',
                data: <?= json_encode($totalesSemanales) ?>,
                borderColor: '#A67C52',
                backgroundColor: 'rgba(166, 124, 82, 0.1)',
                fill: true, tension: 0.4
            }]
        },
        options: commonOptions
    });

    // Gráfico de Categorías
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($nombresCats) ?>,
            datasets: [{
                data: <?= json_encode($valoresCats) ?>,
                backgroundColor: ['#A67C52', '#D6C0B3', '#242424', '#8a6d51', '#C0A080']
            }]
        },
        options: { ...commonOptions, cutout: '70%' }
    });

    // Gráfico de Barras
    new Chart(document.getElementById('topProductsChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($nombresTop) ?>,
            datasets: [{
                label: 'Unidades',
                data: <?= json_encode($cantidadesTop) ?>,
                backgroundColor: '#A67C52',
                borderRadius: 8,
                maxBarThickness: 50,
                barPercentage: 0.5
            }]
        },
        options: { 
            ...commonOptions, 
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, precision: 0 },
                    grid: { color: '#f1f1f1', drawBorder: false }
                },
                x: { grid: { display: false } }
            }
        }
    });
</script>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>
<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>