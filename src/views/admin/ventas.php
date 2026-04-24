
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


<style>
    /* Estos son esenciales para el diseño del modal */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 15px;
    }

    .info-item {
        background: #f9f9f9;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #eee;
    }

    .info-item label {
        display: block;
        font-size: 0.7rem;
        color: #999;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .info-item span {
        display: block;
        font-size: 0.9rem;
        color: #333;
        font-weight: 500;
    }

    /* --- COPIA ESTO DENTRO DE TU <style> --- */
    .switch {
        position: relative;
        display: inline-block;
        width: 42px;
        height: 22px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* FONDO */
    .slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background-color: #d1d1d1;
        transition: 0.3s ease;
        border-radius: 34px;
    }

    /* CÍRCULO */
    .slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background-color: #fff;
        transition: 0.3s ease;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    /* ACTIVO */
    .switch input:checked+.slider {
        background-color: #AB886D;
        /* tu color */
    }

    .switch input:checked+.slider:before {
        transform: translateX(20px);
    }
</style>

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
                    <?php if (empty($ventas)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #999;">No se encontraron registros de ventas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ventas as $venta):
                            $codigo = "V-" . str_pad($venta["id_venta"], 6, "0", STR_PAD_LEFT);
                            $fecha = date("d/m/Y H:i", strtotime($venta["fecha_venta"]));
                            $total = number_format($venta["total_venta"], 2);
                        ?>
                            <tr>
                                <td style="font-weight: bold; color: #A67C52;"><?= $codigo ?></td>
                                <td><?= htmlspecialchars($venta["nombre_cliente"]) ?></td>
                                <td><small><?= htmlspecialchars($venta["nombre_usuario"]) ?></small></td>
                                <td><?= htmlspecialchars($venta["metodo_pago"]) ?></td>
                                <td><?= $fecha ?></td>
                                <td style="font-weight: bold;">$<?= $total ?></td>
                                <td><span class="status-badge completed">Completada</span></td>

                                <td class="acciones-celda">
                                    <div class="botones-acciones">
                                        <!-- BOTÓN VER -->
                                        <button class="btn-icon view" title="Ver"
                                            onclick="viewVenta(this)"
                                            data-codigo="<?= $codigo ?>"
                                            data-cliente="<?= htmlspecialchars($venta['nombre_cliente']) ?>"
                                            data-cajero="<?= htmlspecialchars($venta['nombre_usuario']) ?>"
                                            data-pago="<?= htmlspecialchars($venta['metodo_pago']) ?>"
                                            data-fecha="<?= $fecha ?>"
                                            data-total="<?= $total ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <button class="btn-icon edit" title="Editar"
                                            onclick="editVenta(this)"
                                            data-id="<?= $venta['id_venta'] ?>"
                                            data-cliente="<?= htmlspecialchars($venta['nombre_cliente']) ?>"
                                            data-pago="<?= htmlspecialchars($venta['metodo_pago']) ?>"
                                            data-total="<?= $venta['total_venta'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button class="btn-icon delete" title="Eliminar"
                                            onclick="deleteVenta(this)"
                                            data-id="<?= $venta['id_venta'] ?>">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // FILTROS (igual que el tuyo)
    const searchVenta = document.getElementById('searchVenta');
    const filterMetodo = document.getElementById('filterVentaMetodo');

    function applyVentasFilters() {
        const term = (searchVenta?.value || '').toLowerCase().trim();
        const metodo = (filterMetodo?.value || '').toLowerCase();

        document.querySelectorAll('#ventasTable tbody tr').forEach(row => {
            if (row.cells.length < 2) return;
            const text = row.textContent.toLowerCase();
            const metodoCell = row.cells[3]?.textContent.toLowerCase() || '';
            const passSearch = term === '' || text.includes(term);
            const passMetodo = metodo === '' || metodoCell.includes(metodo);
            row.style.display = (passSearch && passMetodo) ? '' : 'none';
        });
    }

    searchVenta?.addEventListener('input', applyVentasFilters);
    filterMetodo?.addEventListener('change', applyVentasFilters);


    // VER VENTA (sin cambios)
    function viewVenta(btn) {
        const d = btn.dataset;

        Swal.fire({
            html: `
        <div style="text-align:left">
            <div style="margin-bottom: 15px;">
                <h2 style="margin:0; color:#4D3B2E;">${d.codigo}</h2>
                <small style="color:#999;">Venta registrada</small>
            </div>

            <div class="info-grid">
                <div class="info-item"><label>Cliente</label><span>${d.cliente}</span></div>
                <div class="info-item"><label>Cajero</label><span>${d.cajero}</span></div>
                <div class="info-item"><label>Método de Pago</label><span>${d.pago}</span></div>
                <div class="info-item"><label>Fecha</label><span>${d.fecha}</span></div>

                <div class="info-item" style="grid-column: span 2;">
                    <label>Total</label>
                    <span style="font-size:1.4rem; font-weight:bold; color:#4D3B2E;">
                        $${d.total}
                    </span>
                </div>
            </div>
        </div>
        `,
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#AB886D',
            width: '420px'
        });
    }


    // EDITAR VENTA (CORREGIDO)
    function editVenta(btn) {

        const d = btn.dataset;

        Swal.fire({
            title: 'Editar Venta',
            width: '500px',
            html: `
            <div class="form-group">
                <label>Cliente *</label>
                <input type="text" id="editCliente" value="${d.cliente}">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Método de Pago *</label>
                    <select id="editPago">
                        <option value="efectivo" ${d.pago.toLowerCase() === 'efectivo' ? 'selected' : ''}>Efectivo</option>
                        <option value="tarjeta" ${d.pago.toLowerCase() === 'tarjeta' ? 'selected' : ''}>Tarjeta</option>
                        <option value="transferencia" ${d.pago.toLowerCase() === 'transferencia' ? 'selected' : ''}>Transferencia</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Total *</label>
                    <input type="number" id="editTotal" value="${d.total}">
                </div>
            </div>
        `,
            showCancelButton: true,
            confirmButtonText: 'Guardar Cambios',
            confirmButtonColor: '#AB886D',

            preConfirm: () => {

                const cliente = document.getElementById('editCliente').value.trim();
                const pago = document.getElementById('editPago').value;
                const total = document.getElementById('editTotal').value;

                if (!cliente || !total) {
                    Swal.showValidationMessage('Completa todos los campos');
                    return false;
                }

                return {
                    id: d.id,
                    cliente,
                    pago,
                    total
                };
            }

        }).then((result) => {

            if (result.isConfirmed) {

                fetch('actualizar_venta.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(result.value)
                    })
                    .then(res => res.json())
                    .then(res => {

                        if (res.success) {

                            // ✅ ALERTA MEJORADA (tu diseño)
                            Swal.fire({
                                icon: 'success',
                                title: '¡Operación Exitosa!',
                                text: 'Cambios guardados con éxito',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#AB886D'
                            }).then(() => location.reload());

                        } else {
                            Swal.fire('Error', 'No se pudo actualizar', 'error');
                        }

                    });
            }
        });
    }


    // ELIMINAR (YA FUERA Y CORRECTO)
    function deleteVenta(btn) {

        Swal.fire({
            title: '¿Cancelar venta?',
            text: "La venta no se eliminará, solo se marcará como cancelada",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'Volver'
        }).then((result) => {

            if (result.isConfirmed) {

                const fila = btn.closest("tr");

                // 🔁 CAMBIAR ESTADO VISUAL
                const estadoCell = fila.cells[6]; // columna Estado

                estadoCell.innerHTML = `
                    <span class="status-badge cancelled">
                        Cancelada
                    </span>
                `;

                // ❌ DESACTIVAR BOTONES (opcional pero recomendado)
                fila.querySelectorAll("button").forEach(b => {
                    b.disabled = true;
                    b.style.opacity = "0.5";
                    b.style.cursor = "not-allowed";
                });


                Swal.fire({
                    icon: 'success',
                    title: 'Cancelada',
                    text: 'La venta fue marcada como cancelada',
                    confirmButtonColor: '#AB886D'
                });

            }
        });
    }

</script>


<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>
<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>