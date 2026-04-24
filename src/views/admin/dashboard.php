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
$pageStyles = ['/ElZapato/Assets/css/pages/admin-dashboard-report.css'];
require __DIR__ . '/../layouts/admin-shell-start.php';
$pageHeading = 'Dashboard El Zapato';
$showSearch = false;
require __DIR__ . '/../layouts/admin-header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<div class="dashboard-content" style="padding: 20px; background-color: #f8f9fa; min-height: 100vh;">
    <div style="display:flex; justify-content:flex-end; margin-bottom:14px;">
        <button type="button" class="btn-outline-primary" id="btnImprimirDashboard">
            <i class="fas fa-print"></i> Imprimir Estadísticas
        </button>
    </div>
    
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
                <i class="fas fa-exclamation-triangle" style="color: #bc6e32;"></i> Stock bajo
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

<div id="modalImprimirDashboard" class="modal">
    <div class="modal-content">
        <span class="close-modal" id="cerrarModalImprimirDashboard">&times;</span>
        <h3><i class="fas fa-file-pdf"></i> Configurar impresión de estadísticas</h3>

        <div class="form-group">
            <label for="reportePeriodo">Periodo de ventas</label>
            <select id="reportePeriodo">
                <option value="semana">Semana actual</option>
                <option value="mes">Mes actual</option>
                <option value="anio">Año actual</option>
                <option value="personalizado">Elegir fecha personalizada</option>
            </select>
        </div>

        <div id="rangoPersonalizado">
            <div>
                <label for="fechaInicioReporte">Fecha inicio</label>
                <input type="date" id="fechaInicioReporte">
            </div>
            <div>
                <label for="fechaFinReporte">Fecha fin</label>
                <input type="date" id="fechaFinReporte">
            </div>
        </div>

        <p>El PDF incluirá ventas por fecha y las primeras 3 gráficas del dashboard.</p>

        <div class="modal-footer">
            <button type="button" class="btn-modal-cancel" id="cancelarImpresionDashboard">
                Cancelar
            </button>
            <button type="button" class="btn-modal-primary" id="confirmarImpresionDashboard">
                Imprimir
            </button>
        </div>
    </div>
</div>

<script>
    const dashboardCharts = {};

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    };

    // Gráfico de Ventas
    dashboardCharts.salesChart = new Chart(document.getElementById('salesChart'), {
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
    dashboardCharts.categoryChart = new Chart(document.getElementById('categoryChart'), {
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
    dashboardCharts.topProductsChart = new Chart(document.getElementById('topProductsChart'), {
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

    function abrirModalImprimirDashboard() {
        const modal = document.getElementById('modalImprimirDashboard');
        if (!modal) return;
        establecerFechasHoyPorDefecto();
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('active'), 10);
    }

    function cerrarModalImprimirDashboard() {
        const modal = document.getElementById('modalImprimirDashboard');
        if (!modal) return;
        modal.classList.remove('active');
        setTimeout(() => { modal.style.display = 'none'; }, 250);
    }

    function actualizarRangoPersonalizado() {
        const periodo = document.getElementById('reportePeriodo');
        const rango = document.getElementById('rangoPersonalizado');
        if (!periodo || !rango) return;
        rango.style.display = periodo.value === 'personalizado' ? 'flex' : 'none';
    }

    function establecerFechasHoyPorDefecto() {
        const fechaInicio = document.getElementById('fechaInicioReporte');
        const fechaFin = document.getElementById('fechaFinReporte');
        const hoy = new Date().toISOString().slice(0, 10);

        if (fechaInicio && !fechaInicio.value) {
            fechaInicio.value = hoy;
        }
        if (fechaFin && !fechaFin.value) {
            fechaFin.value = hoy;
        }
    }

    async function obtenerImagenesGraficas() {
        const imagenes = [];
        const mapeo = [
            { key: 'salesChart', titulo: 'Rendimiento de Ventas Semana' },
            { key: 'categoryChart', titulo: 'Ventas por Categoría' },
            { key: 'topProductsChart', titulo: 'Productos Más Vendidos' }
        ];

        const canvasToBlob = (canvas) => new Promise((resolve) => {
            canvas.toBlob((blob) => resolve(blob), 'image/png', 1.0);
        });

        for (const item of mapeo) {
            try {
                const chart = dashboardCharts[item.key];
                const canvasOriginal = chart?.canvas;
                if (chart && canvasOriginal) {
                    chart.update('none');
                    await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)));

                    const canvasExport = document.createElement('canvas');
                    const width = canvasOriginal.width || 900;
                    const height = canvasOriginal.height || 420;

                    canvasExport.width = width;
                    canvasExport.height = height;

                    const ctx = canvasExport.getContext('2d');
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, width, height);
                    ctx.drawImage(canvasOriginal, 0, 0, width, height);

                    const dataUrl = canvasExport.toDataURL('image/png', 1.0);
                    const blob = await canvasToBlob(canvasExport);
                    imagenes.push({
                        key: item.key,
                        titulo: item.titulo,
                        dataUrl,
                        blob,
                        size: blob?.size || 0
                    });
                } else {
                    imagenes.push({
                        key: item.key,
                        titulo: item.titulo,
                        dataUrl: '',
                        blob: null,
                        size: 0
                    });
                }
            } catch (e) {
                imagenes.push({
                    key: item.key,
                    titulo: item.titulo,
                    dataUrl: '',
                    blob: null,
                    size: 0
                });
            }
        }

        return imagenes;
    }

    async function imprimirDashboardPDF() {
        const periodo = document.getElementById('reportePeriodo');
        const fechaInicio = document.getElementById('fechaInicioReporte');
        const fechaFin = document.getElementById('fechaFinReporte');

        if (periodo.value === 'personalizado') {
            if (!fechaInicio.value || !fechaFin.value) {
                alert('Selecciona fecha de inicio y fecha de fin.');
                return;
            }
            if (fechaInicio.value > fechaFin.value) {
                alert('La fecha de inicio no puede ser mayor que la fecha de fin.');
                return;
            }
        }

        const imagenes = await obtenerImagenesGraficas();
        const capturasValidas = imagenes.filter((img) => img.size > 1000);
        if (capturasValidas.length === 0) {
            alert('No se pudo capturar ninguna gráfica del dashboard.');
            return;
        }

        const nuevaVentana = window.open('', '_blank');
        const formData = new FormData();
        formData.append('imprimir_ventas_fecha', '1');
        formData.append('incluir_graficas', '1');
        formData.append('periodo', periodo.value);
        formData.append('fecha_inicio', fechaInicio.value || '');
        formData.append('fecha_fin', fechaFin.value || '');
        formData.append('graficas_data', JSON.stringify(imagenes.map(({ key, titulo, dataUrl, size }) => ({ key, titulo, dataUrl, size }))));

        imagenes.forEach((imagen) => {
            if (imagen.blob && imagen.size > 0) {
                formData.append('grafica_' + imagen.key, imagen.blob, imagen.key + '.png');
            }
        });

        try {
            const response = await fetch('/ElZapato/src/api/generar_reporte_dashboard.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(errorText || 'No se pudo generar el PDF.');
            }

            const pdfBlob = await response.blob();
            const pdfUrl = URL.createObjectURL(pdfBlob);

            if (nuevaVentana) {
                nuevaVentana.location.href = pdfUrl;
            } else {
                window.open(pdfUrl, '_blank');
            }
        } catch (error) {
            if (nuevaVentana) {
                nuevaVentana.close();
            }
            alert('Error al generar PDF: ' + error.message);
            return;
        }

        cerrarModalImprimirDashboard();
    }

    document.getElementById('btnImprimirDashboard')?.addEventListener('click', abrirModalImprimirDashboard);
    document.getElementById('cerrarModalImprimirDashboard')?.addEventListener('click', cerrarModalImprimirDashboard);
    document.getElementById('cancelarImpresionDashboard')?.addEventListener('click', cerrarModalImprimirDashboard);
    document.getElementById('confirmarImpresionDashboard')?.addEventListener('click', imprimirDashboardPDF);
    document.getElementById('reportePeriodo')?.addEventListener('change', actualizarRangoPersonalizado);
    establecerFechasHoyPorDefecto();
    actualizarRangoPersonalizado();

    document.addEventListener('click', function(event) {
        const modal = document.getElementById('modalImprimirDashboard');
        if (!modal) return;
        if (event.target === modal) {
            cerrarModalImprimirDashboard();
        }
    });
</script>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>
<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>