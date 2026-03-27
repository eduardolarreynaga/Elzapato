<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

require_once "../../../controller/productosController.php";
require_once "../../../model/ProductosModel.php";

// --- DATOS REALES ---

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

// 4. Stock Bajo
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
            <h3 style="font-size: 1.1rem; margin-bottom: 15px; color: #333;"><i class="fas fa-exclamation-triangle" style="color: #A67C52;"></i> Stock Bajo</h3>
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
</div>

<script>
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    };

    // 1. Rendimiento Ventas
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

    // 2. Ventas por Categoría
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

    // 3. Más Vendidos
new Chart(document.getElementById('topProductsChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($nombresTop) ?>,
        datasets: [{
            label: 'Unidades',
            data: <?= json_encode($cantidadesTop) ?>,
            backgroundColor: '#A67C52',
            borderRadius: 8,
            // --- ESTO ARREGLA EL ANCHO DE LAS BARRAS ---
            maxBarThickness: 50, // Evita que la barra sea gigante si hay pocos datos
            barPercentage: 0.5   // Ajusta el espacio que ocupa la barra en su columna
        }]
    },
    options: { 
        ...commonOptions, 
        plugins: { 
            legend: { display: false } 
        },
        scales: {
            y: {
                beginAtZero: true, // Siempre empezar en 0
                ticks: {
                    stepSize: 1, // Solo números enteros (1, 2, 3...) ya que vendes unidades
                    precision: 0
                },
                grid: {
                    drawBorder: false,
                    color: '#f1f1f1'
                }
            },
            x: {
                grid: {
                    display: false // Limpia el fondo para que se vea más moderno
                }
            }
        }
    }
});
</script>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>
<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>