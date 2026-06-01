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

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .table-tools {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .acciones-venta {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .status-badge.cancelled {
        background: #772C24;
        color: #fff;
    }

    #filterVentaFecha {
        background: #E4E0E1;
        border: 1px solid #D6C0B3;
        color: #4D3B2E;
        border-radius: 6px;
        padding: 6px 8px;
    }

    #filterVentaFecha::-webkit-calendar-picker-indicator {
        cursor: pointer;
        filter: sepia(45%) saturate(220%) hue-rotate(335deg) brightness(0.75);
    }

    #filterVentaFecha:focus {
        outline: none;
        border-color: #AB886D;
        box-shadow: 0 0 0 2px rgba(171, 136, 109, 0.2);
    }

    .swal2-actions {
        gap: 12px !important;
    }

    /* Estilos para la selección de productos en devolución */
    .producto-devolucion-item {
        background: #ffffff;
        border: 1px solid #E4E0E1;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 10px;
        transition: 0.2s;
    }

    .producto-devolucion-item:hover {
        border-color: #AB886D;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .producto-devolucion-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
    }

    .producto-devolucion-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #AB886D;
    }

    .producto-devolucion-info {
        flex: 1;
    }

    .producto-devolucion-nombre {
        font-weight: bold;
        color: #1a1a1a;
        margin-bottom: 4px;
    }

    .producto-devolucion-detalle {
        font-size: 0.75rem;
        color: #666;
        display: flex;
        gap: 15px;
    }

    .producto-devolucion-cantidad {
        font-size: 0.8rem;
        color: #AB886D;
        margin-top: 5px;
    }

    .cantidad-devolucion-control {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #E4E0E1;
    }

    .cantidad-devolucion-control label {
        font-size: 0.75rem;
        color: #666;
    }

    .cantidad-range {
        flex: 1;
        accent-color: #AB886D;
    }

    .cantidad-value {
        width: 50px;
        text-align: center;
        font-weight: bold;
        color: #772c24;
        background: #E4E0E1;
        padding: 4px 8px;
        border-radius: 4px;
        border: 1px solid #D6C0B3;
    }

    .producto-devolucion-item.disabled {
        opacity: 0.5;
        filter: grayscale(0.3);
    }

    .btn-devolver-venta {
        background: #772C24;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 6px 12px;
        cursor: pointer;
        transition: 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
    }

    .btn-devolver-venta:hover {
        background: #5a1f19;
        transform: scale(1.02);
    }

    /* Estilos para la lista de ventas en devolución */
    .ventas-lista-item {
        border: 1px solid #E4E0E1;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 10px;
        transition: 0.2s;
    }

    .ventas-lista-item:hover {
        border-color: #AB886D;
    }

    .paginacion-devolucion {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .btn-pagina {
        background: #E4E0E1;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        transition: 0.2s;
    }

    .btn-pagina.active {
        background: #AB886D;
        color: white;
    }

    .btn-pagina:hover {
        background: #D6C0B3;
    }
</style>

<div class="ventas-page">
    
    <div class="stats-grid stats-list">
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-receipt"></i> Ventas Registradas</span>
            <span class="stats-list-value" id="statsVentasTotal"><?= $totalVentas ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-dollar-sign"></i> Total Facturado</span>
            <span class="stats-list-value" id="statsVentasFacturado">$<?= number_format($totalFacturado, 2) ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-credit-card"></i> Pagos con Tarjeta</span>
            <span class="stats-list-value" id="statsVentasTarjeta"><?= $pagosTarjeta ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-user-check"></i> Ventas con Cliente</span>
            <span class="stats-list-value" id="statsVentasCliente"><?= $ventasConCliente ?></span>
        </div>
    </div>

    <div class="actions-bar">
        <div class="actions-left">
            <div class="filters">
                <div class="header-search" style="min-width: 210px;">
                    <i class="fas fa-calendar-alt"></i>
                    <input type="date" id="filterVentaFecha">
                </div>
                <select class="filter-select" id="filterVentaMetodo">
                    <option value="">Método de Pago</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                </select>
                <button class="btn-outline-primary" id="btnResetVentaFiltros" type="button">
                    <i class="fas fa-times"></i> Limpiar
                </button>
                <button class="btn-outline-primary" id="btnDevoluciones" type="button" style="background: #AB886D; color: white; border-color: #AB886D;">
                    <i class="fas fa-undo-alt"></i> Devoluciones
                </button>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Listado de Ventas Realizadas</h3>
            <div class="table-tools">
                <a href="reportes.php" class="view-all"><i class="fas fa-chart-line"></i> Ver reportes</a>
                <a href="javascript:location.reload()" class="view-all"><i class="fas fa-sync"></i> Actualizar</a>
            </div>
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
                    <tr class="empty-row">
                        <td colspan="8" style="text-align: center; padding: 40px; color: #999;">No se encontraron registros de ventas.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($ventas as $venta): ?>
                        <?php $estadoVenta = strtolower((string)($venta['estado_venta'] ?? 'completada')); ?>
                        <tr data-sale-id="<?= (int)$venta['id_venta'] ?>">
                            <td style="font-weight: bold; color: #A67C52;">
                                V-<?= str_pad($venta["id_venta"], 6, "0", STR_PAD_LEFT) ?>
                            </td>
                            <td><?= htmlspecialchars($venta["nombre_cliente"]) ?></td>
                            <td><small><?= htmlspecialchars($venta["nombre_usuario"]) ?></small></td>
                            <td><?= htmlspecialchars($venta["metodo_pago"]) ?></td>
                            <td><?= date("d/m/Y H:i", strtotime($venta["fecha_venta"])) ?></td>
                            <td style="font-weight: bold;">$<?= number_format($venta["total_venta"], 2) ?></td>
                            <td>
                                <span class="status-badge <?= $estadoVenta === 'anulada' ? 'cancelled' : 'completed' ?>">
                                    <?= $estadoVenta === 'anulada' ? 'Anulada' : 'Completada' ?>
                                </span>
                            </td>
                            <td class="acciones-venta acciones-admin">
                                <button class="btn-icon small" onclick="editarVenta(<?= (int)$venta['id_venta'] ?>, '<?= $estadoVenta ?>', event)" title="Editar venta"><i class="fas fa-edit"></i></button>
                                <button class="btn-icon small" onclick="verDetalleVenta(<?= (int)$venta['id_venta'] ?>, event)" title="Ver detalle"><i class="fas fa-eye"></i></button>
                                <button class="btn-icon small" onclick="imprimirTicketVenta(<?= (int)$venta['id_venta'] ?>, event)" title="Imprimir ticket"><i class="fas fa-print"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL DE SELECCIÓN DE VENTA PARA DEVOLUCIÓN -->
<div id="modalSeleccionVentaDevolucion" class="modal">
    <div class="modal-content" style="width: 650px; max-width: 90%;">
        <span class="close-modal" onclick="cerrarModalDevolucion('modalSeleccionVentaDevolucion')">&times;</span>
        <h3 style="margin-bottom: 20px; color: var(--primary-dark);">
            <i class="fa-solid fa-rotate-left"></i> Seleccionar Venta para Devolución
        </h3>
        
        <div class="form-group">
            <div class="search-container" style="margin-bottom: 15px;">
                <i class="fa fa-search"></i>
                <input type="text" id="buscarVentaDevolucion" placeholder="Buscar por # venta o usuario..." style="width: 100%; padding: 10px 15px 10px 40px;">
            </div>
        </div>
        
        <div id="listaVentasDevolucion" style="max-height: 400px; overflow-y: auto;">
            <div class="loading-text">
                <i class="fa-solid fa-spinner fa-pulse"></i> Cargando ventas...
            </div>
        </div>
        
        <div id="paginacionVentasDevolucion" style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;"></div>
    </div>
</div>

<!-- MODAL DE SELECCIÓN DE PRODUCTOS A DEVOLVER -->
<div id="modalSeleccionProductosDevolucion" class="modal">
    <div class="modal-content" style="width: 700px; max-width: 95%; max-height: 80vh; overflow-y: auto;">
        <span class="close-modal" onclick="cerrarModalDevolucion('modalSeleccionProductosDevolucion')">&times;</span>
        <h3 style="margin-bottom: 20px; color: var(--primary-dark);">
            <i class="fa-solid fa-boxes"></i> Seleccionar Productos a Devolver
            <span id="ventaSeleccionadaInfo" style="font-size: 0.8rem; display: block; color: #666; margin-top: 5px;"></span>
        </h3>
        
        <div id="listaProductosDevolucion" style="max-height: 400px; overflow-y: auto;">
            <div class="loading-text">
                <i class="fa-solid fa-spinner fa-pulse"></i> Cargando productos...
            </div>
        </div>
        
        <div class="totals-section" style="margin-top: 20px;">
            <div class="total-row">
                <span>Total a devolver:</span>
                <span id="totalDevolucion" style="font-weight: bold; color: var(--nocolor);">$0.00</span>
            </div>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button class="btn-action btn-discount" onclick="cerrarModalDevolucion('modalSeleccionProductosDevolucion')" style="flex: 1;">
                <i class="fa-solid fa-times"></i> Cancelar
            </button>
            <button class="btn-action btn-sell" onclick="confirmarDevolucion()" style="flex: 1;">
                <i class="fa-solid fa-check"></i> Procesar Devolución
            </button>
        </div>
    </div>
</div>

<script>
    function mostrarAlertaSistema({ titulo = 'Aviso', mensaje = '', icono = 'info', color = '#AB886D' } = {}) {
        Swal.fire({
            title: titulo,
            html: mensaje,
            icon: icono,
            confirmButtonColor: color
        });
    }

    function imprimirTicketVenta(idVenta, event) {
        if (event) event.stopPropagation();
        window.open('/ElZapato/src/api/generar_ticket.php?id=' + idVenta, '_blank');
    }

    async function editarVenta(idVenta, estadoVenta, event) {
        if (event) event.stopPropagation();

        const estadoActual = (estadoVenta || 'completada').toLowerCase();
        if (estadoActual === 'anulada') {
            mostrarAlertaSistema({
                titulo: 'Venta ya anulada',
                mensaje: `La venta #${idVenta} ya se encuentra en estado anulada.`,
                icono: 'info',
                color: '#AB886D'
            });
            return;
        }

        const confirmacion = await Swal.fire({
            title: '¿Anular venta?',
            html: `La venta <strong>#${idVenta}</strong> cambiará a <strong>ANULADA</strong>.<br>Se reincorporará el stock y se eliminará el ingreso de la venta.`,
            icon: 'warning',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn-modal-primary',
                cancelButton: 'btn-modal-cancel'
            },
            showCancelButton: true,
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar'

        });

        if (!confirmacion.isConfirmed) return;

        try {
            const resp = await fetch('/ElZapato/src/api/actualizar_venta.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_venta: idVenta,
                    estado: 'anulada',
                    accion: 'cambiar_estado'
                })
            });

            const data = await resp.json();
            if (!resp.ok || !data.success) {
                throw new Error(data.error || 'No se pudo anular la venta.');
            }

            const row = document.querySelector(`#ventasTable tbody tr[data-sale-id="${idVenta}"]`);
            if (row) {
                if (row.children[5]) row.children[5].textContent = '$0.00';
                if (row.children[6]) {
                    row.children[6].innerHTML = '<span class="status-badge cancelled">Anulada</span>';
                }
                const btnEditar = row.querySelector('button[title="Editar venta"]');
                if (btnEditar) {
                    btnEditar.setAttribute('onclick', `editarVenta(${idVenta}, 'anulada', event)`);
                }
            }

            applyVentasFilters();
            mostrarAlertaSistema({
                titulo: 'Venta anulada',
                mensaje: `La venta #${idVenta} fue anulada correctamente.`,
                icono: 'success',
                color: '#AB886D'
            });
        } catch (error) {
            mostrarAlertaSistema({
                titulo: 'No se pudo anular',
                mensaje: error.message || 'Ocurrió un error al anular la venta.',
                icono: 'error',
                color: '#772C24'
            });
        }
    }

    function construirFilaVenta(venta) {
        const idVenta = parseInt(venta.id_venta || 0, 10);
        const cliente = (venta.nombre_cliente || 'Cliente Mostrador');
        const usuario = (venta.nombre_usuario || venta.usuario || 'Usuario');
        const metodo = (venta.metodo_pago || 'N/A');
        const total = parseFloat(venta.total_venta || 0);
        const fecha = venta.fecha_venta ? new Date(venta.fecha_venta) : null;
        const fechaTxt = fecha
            ? fecha.toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
            : 'N/A';
        const estado = (venta.estado_venta || 'completada').toString().toLowerCase();
        const badgeClass = estado === 'anulada' ? 'cancelled' : 'completed';
        const badgeText = estado === 'anulada' ? 'Anulada' : 'Completada';
        const totalDisplay = estado === 'anulada' ? 0 : total;

        const fila = document.createElement('tr');
        fila.setAttribute('data-sale-id', String(idVenta));
        fila.innerHTML = `
            <td style="font-weight: bold; color: #A67C52;">V-${String(idVenta).padStart(6, '0')}</td>
            <td>${cliente}</td>
            <td><small>${usuario}</small></td>
            <td>${metodo}</td>
            <td>${fechaTxt}</td>
            <td style="font-weight: bold;">$${totalDisplay.toFixed(2)}</td>
            <td><span class="status-badge ${badgeClass}">${badgeText}</span></td>
            <td class="acciones-venta">
                <button class="btn-icon small" onclick="editarVenta(${idVenta}, '${estado}', event)" title="Editar venta"><i class="fas fa-edit"></i></button>
                <button class="btn-icon small" onclick="verDetalleVenta(${idVenta}, event)" title="Ver detalle"><i class="fas fa-eye"></i></button>
                <button class="btn-icon small" onclick="imprimirTicketVenta(${idVenta}, event)" title="Imprimir ticket"><i class="fas fa-print"></i></button>
            </td>
        `;
        return fila;
    }

    function actualizarStatsVentas() {
        const filas = Array.from(document.querySelectorAll('#ventasTable tbody tr[data-sale-id]'))
            .filter(row => row.style.display !== 'none');

        const total = filas.length;
        let facturado = 0;
        let tarjeta = 0;
        let conCliente = 0;

        filas.forEach(row => {
            const cliente = (row.children[1]?.textContent || '').trim().toLowerCase();
            const metodo = (row.children[3]?.textContent || '').trim().toLowerCase();
            const estado = (row.children[6]?.textContent || '').trim().toLowerCase();
            const totalTxt = (row.children[5]?.textContent || '').replace(/[^0-9.\-]/g, '');
            const totalNum = parseFloat(totalTxt || '0');

            if (estado.includes('anulada')) {
                return;
            }

            facturado += Number.isFinite(totalNum) ? totalNum : 0;
            if (metodo.includes('tarjeta')) tarjeta++;
            if (cliente !== '' && cliente !== 'cliente mostrador') conCliente++;
        });

        const totalEl = document.getElementById('statsVentasTotal');
        const facturadoEl = document.getElementById('statsVentasFacturado');
        const tarjetaEl = document.getElementById('statsVentasTarjeta');
        const clienteEl = document.getElementById('statsVentasCliente');

        if (totalEl) totalEl.textContent = String(total);
        if (facturadoEl) facturadoEl.textContent = '$' + facturado.toFixed(2);
        if (tarjetaEl) tarjetaEl.textContent = String(tarjeta);
        if (clienteEl) clienteEl.textContent = String(conCliente);
    }

    async function cargarVentasSiTablaVacia() {
        const tbody = document.querySelector('#ventasTable tbody');
        if (!tbody) return;

        const filasConVenta = tbody.querySelectorAll('tr[data-sale-id]').length;
        if (filasConVenta > 0) return;

        try {
            const resp = await fetch('/ElZapato/src/api/obtener_ventas.php?all=1');
            if (!resp.ok) throw new Error('HTTP ' + resp.status);

            const ventasData = await resp.json();
            if (!Array.isArray(ventasData) || ventasData.length === 0 || ventasData.error) {
                return;
            }

            tbody.innerHTML = '';
            ventasData.forEach(v => tbody.appendChild(construirFilaVenta(v)));
            applyVentasFilters();
            actualizarStatsVentas();
        } catch (error) {
            mostrarAlertaSistema({
                titulo: 'No se pudo actualizar Ventas',
                mensaje: 'No fue posible cargar el historial de ventas en este momento.',
                icono: 'warning',
                color: '#AB886D'
            });
        }
    }

    async function verDetalleVenta(idVenta, event) {
        if (event) event.stopPropagation();

        try {
            const [detalleResp, infoResp] = await Promise.all([
                fetch('/ElZapato/src/api/obtener_detalle_venta.php?id=' + idVenta),
                fetch('/ElZapato/src/api/obtener_info_venta.php?id=' + idVenta)
            ]);

            if (!detalleResp.ok || !infoResp.ok) {
                throw new Error('No se pudo consultar la venta.');
            }

            const detalles = await detalleResp.json();
            const infoVenta = await infoResp.json();

            if (!Array.isArray(detalles) || detalles.length === 0 || detalles.error) {
                mostrarAlertaSistema({
                    titulo: 'Sin detalle disponible',
                    mensaje: 'No se encontraron productos para esta venta.',
                    icono: 'info',
                    color: '#AB886D'
                });
                return;
            }

            let filas = '';
            let total = 0;
            detalles.forEach(item => {
                const cantidad = parseInt(item.cantidad || 0, 10);
                const precio = parseFloat(item.precio_unitario || 0);
                const subtotal = parseFloat(item.subtotal || (cantidad * precio));
                total += subtotal;
                filas += `
                    <tr>
                        <td style="padding:8px; border-bottom:1px solid #eee;">${cantidad}</td>
                        <td style="padding:8px; border-bottom:1px solid #eee;">${item.nombre_producto || 'Producto'}</td>
                        <td style="padding:8px; border-bottom:1px solid #eee;">${item.talla || '—'}</td>
                        <td style="padding:8px; border-bottom:1px solid #eee;">${item.color || '—'}</td>
                        <td style="padding:8px; border-bottom:1px solid #eee; text-align:right;">$${precio.toFixed(2)}</td>
                        <td style="padding:8px; border-bottom:1px solid #eee; text-align:right; font-weight:600;">$${subtotal.toFixed(2)}</td>
                    </tr>
                `;
            });

            const fecha = infoVenta?.fecha_venta ? new Date(infoVenta.fecha_venta).toLocaleString('es-MX') : 'N/A';
            const metodo = infoVenta?.metodo_pago || 'N/A';
            const usuario = infoVenta?.usuario || 'N/A';

            const html = `
                <div style="text-align:left; margin-bottom:12px; background:#f8f6f4; padding:10px 12px; border-radius:8px;">
                    <div><strong>Venta:</strong> #${idVenta}</div>
                    <div><strong>Fecha:</strong> ${fecha}</div>
                    <div><strong>Cajero:</strong> ${usuario}</div>
                    <div><strong>Método:</strong> ${metodo}</div>
                </div>
                <div style="max-height:360px; overflow:auto; border:1px solid #eee; border-radius:8px;">
                    <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                        <thead>
                            <tr style="background:#AB886D; color:white;">
                                <th style="padding:8px; text-align:left;">Cant.</th>
                                <th style="padding:8px; text-align:left;">Producto</th>
                                <th style="padding:8px; text-align:left;">Talla</th>
                                <th style="padding:8px; text-align:left;">Color</th>
                                <th style="padding:8px; text-align:right;">P. Unit.</th>
                                <th style="padding:8px; text-align:right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>${filas}</tbody>
                        <tfoot>
                            <tr style="background:#f4f1ef;">
                                <td colspan="5" style="padding:10px; text-align:right; font-weight:700;">TOTAL</td>
                                <td style="padding:10px; text-align:right; font-weight:700;">$${total.toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;

            Swal.fire({
                title: 'Detalle de Venta',
                html,
                width: '900px',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn-modal-primary',
                    cancelButton: 'btn-modal-cancel'
                },
                showCancelButton: true,
                cancelButtonText: 'Cerrar',
                confirmButtonText: '<i class="fas fa-print"></i> Imprimir Ticket'
            }).then(result => {
                if (result.isConfirmed) {
                    imprimirTicketVenta(idVenta);
                }
            });
        } catch (error) {
            mostrarAlertaSistema({
                titulo: 'No se pudo abrir la venta',
                mensaje: 'Ocurrió un problema al consultar el detalle. Intenta nuevamente.',
                icono: 'error',
                color: '#772C24'
            });
        }
    }

    // ==================== DEVOLUCIONES ====================
    let ventaSeleccionadaDevolucion = null;
    let productosDevolucionData = [];
    let paginaActualDevolucion = 1;
    let totalPaginasDevolucion = 1;

    function abrirModalDevoluciones() {
        paginaActualDevolucion = 1;
        cargarVentasParaDevolucion();
        var modal = document.getElementById('modalSeleccionVentaDevolucion');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(function() { modal.classList.add('active'); }, 10);
        }
    }

    function cerrarModalDevolucion(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('active');
            setTimeout(function() { modal.style.display = 'none'; }, 300);
        }
    }

    async function cargarVentasParaDevolucion() {
        var container = document.getElementById('listaVentasDevolucion');
        if (!container) return;
        
        var buscar = document.getElementById('buscarVentaDevolucion')?.value || '';
        
        try {
            container.innerHTML = '<div class="loading-text"><i class="fa-solid fa-spinner fa-pulse"></i> Cargando ventas...</div>';
            
            var url = '/ElZapato/src/api/obtener_ventas_devolucion.php?pagina=' + paginaActualDevolucion + '&limite=5';
            if (buscar) {
                url += '&buscar=' + encodeURIComponent(buscar);
            }
            
            var resp = await fetch(url);
            var data = await resp.json();
            
            if (data.error) {
                container.innerHTML = '<div class="loading-text">Error: ' + data.error + '</div>';
                return;
            }
            
            if (!data.ventas || data.ventas.length === 0) {
                container.innerHTML = '<div class="loading-text">No hay ventas disponibles para devolución</div>';
                return;
            }
            
            totalPaginasDevolucion = data.total_paginas;
            
            var html = '';
            for (var i = 0; i < data.ventas.length; i++) {
                var venta = data.ventas[i];
                var fecha = new Date(venta.fecha_venta);
                var fechaFormateada = fecha.toLocaleString('es-MX');
                
                html += `
                    <div class="ventas-lista-item">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <div>
                                <div style="font-weight: bold; color: #AB886D;">Venta #${venta.id_venta}</div>
                                <div style="font-size: 0.7rem; color: #888;">${fechaFormateada}</div>
                                <div style="font-size: 0.7rem;">Usuario: ${venta.usuario}</div>
                                <div style="font-size: 0.8rem; font-weight: bold;">Total: $${parseFloat(venta.total_venta).toFixed(2)}</div>
                                <div style="font-size: 0.7rem;">Método: ${venta.metodo_pago || 'Efectivo'}</div>
                            </div>
                            <button class="btn-devolver-venta" onclick="seleccionarVentaParaDevolucion(${venta.id_venta})">
                                <i class="fa-solid fa-undo-alt"></i> Seleccionar
                            </button>
                        </div>
                    </div>
                `;
            }
            
            container.innerHTML = html;
            actualizarPaginacionDevolucion();
            
        } catch (error) {
            console.error('Error al cargar ventas:', error);
            container.innerHTML = '<div class="loading-text">Error al cargar las ventas</div>';
        }
    }

    function actualizarPaginacionDevolucion() {
        var paginacionDiv = document.getElementById('paginacionVentasDevolucion');
        if (!paginacionDiv) return;
        
        if (totalPaginasDevolucion <= 1) {
            paginacionDiv.innerHTML = '';
            return;
        }
        
        var html = '';
        for (var i = 1; i <= totalPaginasDevolucion; i++) {
            var activeClass = (i === paginaActualDevolucion) ? 'active' : '';
            html += `<button class="btn-pagina ${activeClass}" onclick="irPaginaDevolucion(${i})">${i}</button>`;
        }
        paginacionDiv.innerHTML = html;
    }

    function irPaginaDevolucion(pagina) {
        paginaActualDevolucion = pagina;
        cargarVentasParaDevolucion();
    }

    async function seleccionarVentaParaDevolucion(idVenta) {
        cerrarModalDevolucion('modalSeleccionVentaDevolucion');
        
        mostrarAlertaSistema({
            titulo: 'Cargando...',
            mensaje: 'Cargando productos de la venta...',
            icono: 'info',
            color: '#AB886D'
        });
        
        try {
            var resp = await fetch('/ElZapato/src/api/obtener_detalle_venta_devolucion.php?id=' + idVenta);
            var data = await resp.json();
            
            if (data.error) {
                mostrarAlertaSistema({
                    titulo: 'Error',
                    mensaje: data.error,
                    icono: 'error',
                    color: '#772C24'
                });
                return;
            }
            
            if (!data.detalles || data.detalles.length === 0) {
                mostrarAlertaSistema({
                    titulo: 'Sin productos',
                    mensaje: 'No se encontraron productos en esta venta',
                    icono: 'warning',
                    color: '#AB886D'
                });
                return;
            }
            
            ventaSeleccionadaDevolucion = idVenta;
            productosDevolucionData = data.detalles.map(function(d) {
                return {
                    id_detalle: d.id_detalle_venta,
                    id_variante: d.id_variante,
                    nombre: d.nombre_producto,
                    talla: d.talla,
                    color: d.color,
                    cantidad_original: d.cantidad_original,
                    cantidad_maxima: d.cantidad_maxima,
                    cantidad_a_devolver: 0,
                    precio_unitario: parseFloat(d.precio_unitario)
                };
            });
            
            Swal.close();
            mostrarModalProductosDevolucion();
            
        } catch (error) {
            console.error('Error al cargar detalles:', error);
            mostrarAlertaSistema({
                titulo: 'Error',
                mensaje: 'Error al cargar los productos',
                icono: 'error',
                color: '#772C24'
            });
        }
    }

    function mostrarModalProductosDevolucion() {
        var container = document.getElementById('listaProductosDevolucion');
        var ventaInfo = document.getElementById('ventaSeleccionadaInfo');
        
        if (!container) return;
        
        if (ventaInfo) {
            ventaInfo.innerHTML = 'Venta #' + ventaSeleccionadaDevolucion;
        }
        
        var html = '';
        for (var i = 0; i < productosDevolucionData.length; i++) {
            var p = productosDevolucionData[i];
            
            html += `
                <div class="producto-devolucion-item" id="item_${i}">
                    <div class="producto-devolucion-header">
                        <input type="checkbox" class="producto-devolucion-checkbox" id="chk_${i}" onchange="toggleProductoDevolucion(${i})">
                        <div class="producto-devolucion-info">
                            <div class="producto-devolucion-nombre">${p.nombre}</div>
                            <div class="producto-devolucion-detalle">
                                <span><i class="fas fa-ruler"></i> Talla: ${p.talla || 'N/A'}</span>
                                <span><i class="fas fa-palette"></i> Color: ${p.color || 'N/A'}</span>
                            </div>
                            <div class="producto-devolucion-cantidad">
                                <i class="fas fa-box"></i> Vendido: ${p.cantidad_original} unidades | <i class="fas fa-dollar-sign"></i> Precio: $${p.precio_unitario.toFixed(2)}
                            </div>
                        </div>
                    </div>
                    <div class="cantidad-devolucion-control" id="control_${i}" style="display: none;">
                        <label><i class="fa-solid fa-arrow-left"></i> Cantidad a devolver:</label>
                        <input type="range" class="cantidad-range" id="range_${i}" min="0" max="${p.cantidad_maxima}" value="0" step="1" onchange="actualizarCantidadDevolucion(${i})">
                        <input type="number" class="cantidad-value" id="value_${i}" min="0" max="${p.cantidad_maxima}" value="0" step="1" onchange="actualizarRangeDevolucion(${i})">
                    </div>
                </div>
            `;
        }
        
        container.innerHTML = html;
        actualizarTotalDevolucion();
        
        var modal = document.getElementById('modalSeleccionProductosDevolucion');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(function() { modal.classList.add('active'); }, 10);
        }
    }

    function toggleProductoDevolucion(index) {
        var checkbox = document.getElementById('chk_' + index);
        var control = document.getElementById('control_' + index);
        
        if (checkbox.checked) {
            control.style.display = 'flex';
            if (productosDevolucionData[index].cantidad_a_devolver === 0) {
                productosDevolucionData[index].cantidad_a_devolver = productosDevolucionData[index].cantidad_maxima;
                actualizarControlesDevolucion(index);
            }
        } else {
            control.style.display = 'none';
            productosDevolucionData[index].cantidad_a_devolver = 0;
            var rangeInput = document.getElementById('range_' + index);
            var valueInput = document.getElementById('value_' + index);
            if (rangeInput) rangeInput.value = 0;
            if (valueInput) valueInput.value = 0;
        }
        
        actualizarTotalDevolucion();
    }

    function actualizarCantidadDevolucion(index) {
        var rangeInput = document.getElementById('range_' + index);
        var valueInput = document.getElementById('value_' + index);
        var cantidad = parseInt(rangeInput.value);
        
        valueInput.value = cantidad;
        productosDevolucionData[index].cantidad_a_devolver = cantidad;
        
        actualizarTotalDevolucion();
    }

    function actualizarRangeDevolucion(index) {
        var valueInput = document.getElementById('value_' + index);
        var rangeInput = document.getElementById('range_' + index);
        var cantidad = parseInt(valueInput.value);
        var maximo = productosDevolucionData[index].cantidad_maxima;
        
        if (isNaN(cantidad)) cantidad = 0;
        if (cantidad < 0) cantidad = 0;
        if (cantidad > maximo) cantidad = maximo;
        
        valueInput.value = cantidad;
        rangeInput.value = cantidad;
        productosDevolucionData[index].cantidad_a_devolver = cantidad;
        
        actualizarTotalDevolucion();
    }

    function actualizarControlesDevolucion(index) {
        var rangeInput = document.getElementById('range_' + index);
        var valueInput = document.getElementById('value_' + index);
        var cantidad = productosDevolucionData[index].cantidad_a_devolver;
        
        if (rangeInput) rangeInput.value = cantidad;
        if (valueInput) valueInput.value = cantidad;
    }

    function actualizarTotalDevolucion() {
        var total = 0;
        for (var i = 0; i < productosDevolucionData.length; i++) {
            var p = productosDevolucionData[i];
            var checkbox = document.getElementById('chk_' + i);
            if (checkbox && checkbox.checked) {
                total += p.cantidad_a_devolver * p.precio_unitario;
            }
        }
        
        var totalSpan = document.getElementById('totalDevolucion');
        if (totalSpan) {
            totalSpan.innerText = '$' + total.toFixed(2);
        }
    }

    async function confirmarDevolucion() {
        var productosADevolver = [];
        var tieneProductos = false;
        
        for (var i = 0; i < productosDevolucionData.length; i++) {
            var p = productosDevolucionData[i];
            var checkbox = document.getElementById('chk_' + i);
            
            if (checkbox && checkbox.checked && p.cantidad_a_devolver > 0) {
                tieneProductos = true;
                productosADevolver.push({
                    id_detalle: p.id_detalle,
                    id_variante: p.id_variante,
                    cantidad: p.cantidad_a_devolver,
                    nombre: p.nombre
                });
            }
        }
        
        if (!tieneProductos) {
            mostrarAlertaSistema({
                titulo: 'Sin productos',
                mensaje: 'Seleccione al menos un producto para devolver',
                icono: 'warning',
                color: '#AB886D'
            });
            return;
        }
        
        const confirmacion = await Swal.fire({
            title: '¿Confirmar devolución?',
            html: 'Se reincorporará el stock y se ajustará el total de la venta.',
            icon: 'warning',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn-modal-primary',
                cancelButton: 'btn-modal-cancel'
            },
            showCancelButton: true,
            confirmButtonText: 'Sí, devolver',
            cancelButtonText: 'Cancelar'
        });
        
        if (!confirmacion.isConfirmed) return;
        
        try {
            var resp = await fetch('/ElZapato/src/api/procesar_devolucion.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_venta: ventaSeleccionadaDevolucion,
                    productos: productosADevolver
                })
            });
            
            var data = await resp.json();
            
            if (data.success) {
                var resumen = 'Devolución realizada:\n';
                for (var j = 0; j < data.productos_devueltos.length; j++) {
                    var prod = data.productos_devueltos[j];
                    resumen += '\n• ' + prod.nombre + ': ' + prod.cantidad + ' unidades ($' + prod.total.toFixed(2) + ')';
                }
                resumen += '\n\nTotal devuelto: $' + data.total_devuelto.toFixed(2);
                
                mostrarAlertaSistema({
                    titulo: 'Devolución exitosa',
                    mensaje: resumen,
                    icono: 'success',
                    color: '#AB886D'
                });
                
                cerrarModalDevolucion('modalSeleccionProductosDevolucion');
                
                setTimeout(function() {
                    location.reload();
                }, 2000);
                
            } else {
                mostrarAlertaSistema({
                    titulo: 'Error',
                    mensaje: data.error || 'No se pudo procesar la devolución',
                    icono: 'error',
                    color: '#772C24'
                });
            }
            
        } catch (error) {
            console.error('Error al procesar devolución:', error);
            mostrarAlertaSistema({
                titulo: 'Error',
                mensaje: 'Error al procesar la devolución',
                icono: 'error',
                color: '#772C24'
            });
        }
    }

    // Filtrado JS
    const searchVenta = document.getElementById('searchVenta');
    const filterMetodo = document.getElementById('filterVentaMetodo');
    const filterVentaFecha = document.getElementById('filterVentaFecha');

    function fechaFilaToISO(fechaTexto) {
        const raw = (fechaTexto || '').trim();
        if (!raw) return '';

        const parteFecha = raw.split(' ')[0] || '';
        const pedazos = parteFecha.split('/');
        if (pedazos.length !== 3) return '';

        const [dia, mes, anio] = pedazos;
        if (!dia || !mes || !anio) return '';

        return `${anio.padStart(4, '0')}-${mes.padStart(2, '0')}-${dia.padStart(2, '0')}`;
    }

    function applyVentasFilters() {
        const term = (searchVenta?.value || '').toLowerCase().trim();
        const metodo = (filterMetodo?.value || '').toLowerCase();
        const fecha = (filterVentaFecha?.value || '').trim();

        document.querySelectorAll('#ventasTable tbody tr').forEach(row => {
            if(row.cells.length < 2) return;
            const text = row.textContent.toLowerCase();
            const metodoCell = row.cells[3]?.textContent.toLowerCase() || '';
            const fechaCell = row.cells[4]?.textContent || '';
            const fechaIso = fechaFilaToISO(fechaCell);
            const passSearch = term === '' || text.includes(term);
            const passMetodo = metodo === '' || metodoCell.includes(metodo);
            const passFecha = fecha === '' || fechaIso === fecha;
            row.style.display = (passSearch && passMetodo && passFecha) ? '' : 'none';
        });

        actualizarStatsVentas();
    }

    if (searchVenta) searchVenta.addEventListener('input', applyVentasFilters);
    if (filterMetodo) filterMetodo.addEventListener('change', applyVentasFilters);
    if (filterVentaFecha) filterVentaFecha.addEventListener('change', applyVentasFilters);

    document.getElementById('btnResetVentaFiltros')?.addEventListener('click', function () {
        if (filterMetodo) filterMetodo.value = '';
        if (searchVenta) searchVenta.value = '';
        if (filterVentaFecha) filterVentaFecha.value = '';
        applyVentasFilters();
    });
    
    if (document.getElementById('btnDevoluciones')) {
        document.getElementById('btnDevoluciones').addEventListener('click', abrirModalDevoluciones);
    }

    document.addEventListener('DOMContentLoaded', function () {
        cargarVentasSiTablaVacia();
        actualizarStatsVentas();
        
        var buscarInput = document.getElementById('buscarVentaDevolucion');
        if (buscarInput) {
            buscarInput.addEventListener('input', function() {
                paginaActualDevolucion = 1;
                cargarVentasParaDevolucion();
            });
        }
    });
</script>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>
<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>