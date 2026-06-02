// ==================== VARIABLES GLOBALES ====================
let carrito = [];
let descuentosAplicados = [];
let cajaActual = null;
let descuentoFidelidad = 0;
let nivelFidelidad = '';
let clienteSeleccionado = null;

function goMenuGeneralTransition() {
    var transition = document.getElementById('pageTransitionPos');
    var targetUrl = '/ElZapato/src/views/layouts/menu-general.php';

    if (!transition) {
        window.location.href = targetUrl;
        return;
    }

    transition.classList.add('active');
    setTimeout(function() {
        window.location.href = targetUrl;
    }, 420);
}

// ==================== NOTIFICACIONES ====================
function mostrarNotificacion(mensaje, tipo = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = 'toast show toast-' + tipo;
    let icono = tipo === 'warning' ? 'fa-triangle-exclamation' : (tipo === 'success' ? 'fa-circle-check' : 'fa-circle-info');
    toast.innerHTML = '<i class="fa-solid ' + icono + '"></i> <span>' + mensaje + '</span>';
    container.appendChild(toast);
    
    setTimeout(function() {
        toast.style.transform = 'translateX(120%)';
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
}

// ==================== CAJA ====================
async function verificarEstadoCaja() {
    try {
        var resp = await fetch('/ElZapato/src/api/caja_api.php?action=verificar_estado');
        var data = await resp.json();
        
        var dropdownContent = document.getElementById('cajaEstadoContent');
        var cajaInfo = document.getElementById('cajaInfo');
        
        if (data.success && data.abierta) {
            cajaActual = data;
            document.getElementById('cajaMonto').innerText = '$' + parseFloat(data.saldo_actual).toFixed(2);
            cajaInfo.style.display = 'flex';
            
            dropdownContent.innerHTML = `
                <div style="padding: 10px;">
                    <div class="caja-dropdown-item">
                        <span class="label">Fecha Apertura:</span>
                        <span class="value">${new Date(data.fecha_apertura).toLocaleString()}</span>
                    </div>
                    <div class="caja-dropdown-item">
                        <span class="label">Monto Inicial:</span>
                        <span class="value">$${parseFloat(data.monto_inicial).toFixed(2)}</span>
                    </div>
                    <div class="caja-dropdown-item">
                        <span class="label">Saldo Actual:</span>
                        <span class="value caja-abierta">$${parseFloat(data.saldo_actual).toFixed(2)}</span>
                    </div>
                    <div class="caja-dropdown-footer">
                        <button class="btn-cerrar-caja" onclick="abrirModalCierreCaja()">
                            <i class="fa-solid fa-lock"></i> Cerrar Caja
                        </button>
                    </div>
                </div>
            `;
        } else {
            cajaInfo.style.display = 'none';
            var montoAsignado = data.monto_asignado || 0;
            dropdownContent.innerHTML = `
                <div style="padding: 10px;">
                    <div class="caja-dropdown-item">
                        <span class="label">Estado:</span>
                        <span class="value" style="color: #999;">Caja Cerrada</span>
                    </div>
                    <div class="caja-dropdown-item">
                        <span class="label">Dinero Asignado:</span>
                        <span class="value">$${parseFloat(montoAsignado).toFixed(2)}</span>
                    </div>
                    <div class="caja-dropdown-footer">
                        <button class="btn-abrir-caja" onclick="confirmarAbrirCaja()">
                            <i class="fa-solid fa-cash-register"></i> Abrir Caja
                        </button>
                    </div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error al verificar caja:', error);
    }
}

function toggleCajaDropdown() {
    var dropdown = document.getElementById('cajaDropdown');
    dropdown.classList.toggle('active');
}

async function confirmarAbrirCaja() {
    mostrarNotificacion('Abriendo caja...', 'info');
    
    var formData = new FormData();
    formData.append('action', 'abrir_caja');
    
    try {
        var resp = await fetch('/ElZapato/src/api/caja_api.php', {
            method: 'POST',
            body: formData
        });
        var data = await resp.json();
        
        if (data.success) {
            mostrarNotificacion('Caja abierta exitosamente', 'success');
            document.getElementById('cajaDropdown').classList.remove('active');
            verificarEstadoCaja();
        } else {
            mostrarNotificacion(data.message || data.error || 'Error al abrir caja', 'warning');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error al abrir caja', 'warning');
    }
}

async function abrirModalCierreCaja() {
    try {
        var resp = await fetch('/ElZapato/src/api/caja_api.php?action=obtener_estadisticas');
        var stats = await resp.json();
        
        if (stats.success) {
            document.getElementById('estMontoInicial').innerText = '$' + parseFloat(stats.monto_inicial).toFixed(2);
            document.getElementById('estTotalVentas').innerText = stats.total_ventas || 0;
            document.getElementById('estIngresos').innerText = '$' + parseFloat(stats.total_ingresos || 0).toFixed(2);
            document.getElementById('estVuelto').innerText = '$' + parseFloat(stats.total_vuelto || 0).toFixed(2);
            document.getElementById('estSaldoEsperado').innerText = '$' + parseFloat(stats.saldo_esperado || 0).toFixed(2);
        } else {
            mostrarNotificacion(stats.error || 'Error al obtener estadísticas', 'warning');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error al cargar estadísticas', 'warning');
    }
    
    var modal = document.getElementById('modalCerrarCaja');
    modal.style.display = 'flex';
    setTimeout(function() { modal.classList.add('active'); }, 10);
    document.getElementById('cajaDropdown').classList.remove('active');
}

async function confirmarCerrarCaja() {
    var formData = new FormData();
    formData.append('action', 'cerrar_caja');
    
    try {
        var resp = await fetch('/ElZapato/src/api/caja_api.php', {
            method: 'POST',
            body: formData
        });
        var data = await resp.json();
        
        if (data.success) {
            var mensaje = `✅ CAJA CERRADA EXITOSAMENTE\n\n`;
            mensaje += `💰 Monto Inicial: $${(data.stats?.monto_inicial || 0).toFixed(2)}\n`;
            mensaje += `📊 Número de Ventas: ${data.stats?.total_ventas || 0}\n`;
            mensaje += `💵 Ingresos por Ventas: $${(data.stats?.total_ingresos || 0).toFixed(2)}\n`;
            mensaje += `🔄 Vuelto Entregado: $${(data.stats?.total_vuelto || 0).toFixed(2)}\n`;
            mensaje += `💰 Saldo Esperado: $${data.saldo_esperado.toFixed(2)}\n`;
            mensaje += `📌 La caja ha sido cerrada automáticamente con el saldo calculado.\n`;
            
            alert(mensaje);
            mostrarNotificacion('Caja cerrada exitosamente', 'success');
            cerrarModal('modalCerrarCaja');
            verificarEstadoCaja();
        } else {
            mostrarNotificacion(data.message || data.error || 'Error al cerrar caja', 'warning');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error al cerrar caja', 'warning');
    }
}

// ==================== NOTIFICACIONES DE VENTAS ====================
function toggleNotifications() {
    var dropdown = document.getElementById('notificationDropdown');
    if (!dropdown) return;
    
    dropdown.classList.toggle('active');
    
    if (dropdown.classList.contains('active')) {
        cargarUltimasVentas();
    }
}

async function cargarUltimasVentas() {
    var container = document.getElementById('recentSalesList');
    if (!container) return;

    try {
        container.innerHTML = '<div class="loading-text"><i class="fa-solid fa-spinner fa-pulse"></i> Cargando ventas...</div>';
        
        var resp = await fetch('/ElZapato/src/api/obtener_ventas.php');
        
        if (!resp.ok) {
            throw new Error('HTTP error! status: ' + resp.status);
        }
        
        var response = await resp.json();

        if (response.error) {
            console.error('Error:', response.error);
            container.innerHTML = '<div class="loading-text">Error: ' + response.error + '</div>';
            return;
        }

        if (!response || response.length === 0) {
            container.innerHTML = '<div class="loading-text">No hay ventas registradas</div>';
            return;
        }

        var badge = document.getElementById('notificationBadge');
        if (badge) {
            badge.textContent = response.length;
        }

        var htmlContent = '';
        for (var i = 0; i < response.length; i++) {
            var venta = response[i];
            var fecha = new Date(venta.fecha_venta);
            var hora = fecha.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
            var fechaFormateada = fecha.toLocaleDateString('es-MX');
            
            htmlContent += `
                <div class="dropdown-item" data-venta-id="${venta.id_venta}">
                    <div class="sale-info">
                        <span class="sale-id">#${venta.id_venta}</span>
                        <span class="sale-date">${fechaFormateada} ${hora}</span>
                        <span class="sale-user">${venta.usuario || 'Usuario'}</span>
                    </div>
                    <div class="sale-actions">
                        <span class="sale-total">$${parseFloat(venta.total_venta).toFixed(2)}</span>
                        <button class="btn-view-sale" onclick="verDetalleVenta(${venta.id_venta}, event)" title="Ver Detalle">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        <button class="btn-view-sale" onclick="imprimirTicketVenta(${venta.id_venta}, event)" title="Imprimir Ticket">
                            <i class="fa-solid fa-print"></i>
                        </button>
                    </div>
                </div>
            `;
        }
        container.innerHTML = htmlContent;
        
    } catch (error) {
        console.error('Error al cargar ventas:', error);
        container.innerHTML = '<div class="loading-text">Error al conectar con el servidor</div>';
    }
}

function imprimirTicketVenta(idVenta, event) {
    if (event) {
        event.stopPropagation();
    }
    
    var dropdown = document.getElementById('notificationDropdown');
    if (dropdown) {
        dropdown.classList.remove('active');
    }
    
    window.open('/ElZapato/src/api/generar_ticket.php?id=' + idVenta, '_blank');
    mostrarNotificacion('Generando ticket de la venta #' + idVenta, 'info');
}

async function verDetalleVenta(idVenta, event) {
    if (event) {
        event.stopPropagation();
    }
    
    try {
        var dropdown = document.getElementById('notificationDropdown');
        if (dropdown) {
            dropdown.classList.remove('active');
        }
        
        mostrarNotificacion('Cargando detalles de la venta...', 'info');
        
        var resp = await fetch('/ElZapato/src/api/obtener_detalle_venta.php?id=' + idVenta);
        
        if (!resp.ok) {
            throw new Error('HTTP error! status: ' + resp.status);
        }
        
        var detalles = await resp.json();
        
        if (detalles.error) {
            mostrarNotificacion(detalles.error, 'warning');
            return;
        }
        
        if (!detalles || detalles.length === 0) {
            mostrarNotificacion('No se encontraron detalles para esta venta', 'warning');
            return;
        }
        
        var ventaResp = await fetch('/ElZapato/src/api/obtener_info_venta.php?id=' + idVenta);
        var infoVenta = await ventaResp.json();
        
        var ventaData = sessionStorage.getItem('ventaData_' + idVenta);
        var cambio = null;
        var dineroRecibido = null;
        var descuentosData = [];
        
        if (ventaData) {
            var data = JSON.parse(ventaData);
            cambio = data.cambio;
            dineroRecibido = data.dineroRecibido;
            descuentosData = data.descuentos || [];
        }
        
        mostrarModalDetalleVenta(idVenta, detalles, infoVenta, cambio, dineroRecibido, descuentosData);
        
    } catch (error) {
        console.error('Error al obtener detalles:', error);
        mostrarNotificacion('Error al cargar los detalles de la venta', 'warning');
    }
}

function mostrarModalDetalleVenta(idVenta, detalles, infoVenta, cambio = null, dineroRecibido = null, descuentosData = []) {
    var subtotalVenta = 0;
    
    for (var i = 0; i < detalles.length; i++) {
        var precioUnitario = parseFloat(detalles[i].precio_unitario);
        var cantidad = parseInt(detalles[i].cantidad);
        var subtotalOriginal = precioUnitario * cantidad;
        subtotalVenta += subtotalOriginal;
    }
    
    var totalDescuentos = 0;
    for (var j = 0; j < descuentosData.length; j++) {
        totalDescuentos += descuentosData[j].ahorroTotal;
    }
    
    var totalVenta = subtotalVenta - totalDescuentos;
    
    var fechaMostrar = 'N/A';
    var horaMostrar = 'N/A';
    var usuarioMostrar = 'N/A';
    var metodoPagoMostrar = 'N/A';
    
    if (infoVenta && !infoVenta.error) {
        var fecha = new Date(infoVenta.fecha_venta);
        fechaMostrar = fecha.toLocaleDateString('es-MX');
        horaMostrar = fecha.toLocaleTimeString('es-MX');
        usuarioMostrar = infoVenta.usuario || 'N/A';
        metodoPagoMostrar = infoVenta.metodo_pago || 'Efectivo';
    }
    
    var infoBoxHTML = `
        <div style="margin-bottom: 20px; padding: 15px; background: var(--primary-light); border-radius: 8px;">
            <div style="font-size: 1.2rem; font-weight: bold; margin-bottom: 15px; color: var(--primary-dark); border-bottom: 2px solid var(--primary-dark); padding-bottom: 8px;">
                <i class="fa-solid fa-receipt"></i> Venta #${idVenta}
            </div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-regular fa-calendar" style="color: var(--primary-dark); width: 20px;"></i>
                    <div>
                        <div style="font-size: 0.7rem; color: #888;">FECHA</div>
                        <div style="font-weight: 500;">${fechaMostrar}</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-regular fa-clock" style="color: var(--primary-dark); width: 20px;"></i>
                    <div>
                        <div style="font-size: 0.7rem; color: #888;">HORA</div>
                        <div style="font-weight: 500;">${horaMostrar}</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-user" style="color: var(--primary-dark); width: 20px;"></i>
                    <div>
                        <div style="font-size: 0.7rem; color: #888;">VENDEDOR</div>
                        <div style="font-weight: 500;">${usuarioMostrar}</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-regular fa-credit-card" style="color: var(--primary-dark); width: 20px;"></i>
                    <div>
                        <div style="font-size: 0.7rem; color: #888;">MÉTODO DE PAGO</div>
                        <div style="font-weight: 500;">${metodoPagoMostrar}</div>
                    </div>
                </div>`;
    
    if (totalDescuentos > 0) {
        infoBoxHTML += `
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-tag" style="color: var(--success); width: 20px;"></i>
                    <div>
                        <div style="font-size: 0.7rem; color: #888;">DESCUENTO TOTAL</div>
                        <div style="font-weight: 500; color: var(--success);">-$${totalDescuentos.toFixed(2)}</div>
                    </div>
                </div>`;
    }
    
    if (dineroRecibido !== null && cambio !== null) {
        infoBoxHTML += `
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-money-bill" style="color: var(--primary-dark); width: 20px;"></i>
                    <div>
                        <div style="font-size: 0.7rem; color: #888;">DINERO RECIBIDO</div>
                        <div style="font-weight: 500;">$${parseFloat(dineroRecibido).toFixed(2)}</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-coins" style="color: var(--success); width: 20px;"></i>
                    <div>
                        <div style="font-size: 0.7rem; color: #888;">CAMBIO/VUELTO</div>
                        <div style="font-weight: 500; color: var(--success);">$${parseFloat(cambio).toFixed(2)}</div>
                    </div>
                </div>`;
    }
    
    infoBoxHTML += `</div></div>`;
    
    var tablaHTML = '<table style="width:100%; border-collapse: collapse; margin-bottom: 15px;">';
    tablaHTML += '<thead><tr style="background: var(--primary-light); border-bottom: 2px solid var(--primary-dark);">';
    tablaHTML += '<th style="padding: 10px; text-align: left;">Cant.</th>';
    tablaHTML += '<th style="padding: 10px; text-align: left;">Producto</th>';
    tablaHTML += '<th style="padding: 10px; text-align: right;">Precio Unit.</th>';
    tablaHTML += '<th style="padding: 10px; text-align: right;">Descuento</th>';
    tablaHTML += '<th style="padding: 10px; text-align: right;">Subtotal</th>';
    tablaHTML += '</table></thead><tbody>';
    
    for (var i = 0; i < detalles.length; i++) {
        var item = detalles[i];
        var precioUnitario = parseFloat(item.precio_unitario).toFixed(2);
        var cantidad = parseInt(item.cantidad);
        var subtotalOriginal = (precioUnitario * cantidad).toFixed(2);
        var estiloFila = (i % 2 === 0) ? 'background: #fafafa;' : '';
        
        var descuentoItem = 0;
        var descuentoPorcentaje = 0;
        for (var j = 0; j < descuentosData.length; j++) {
            if (descuentosData[j].nombre === item.nombre_producto) {
                descuentoItem = descuentosData[j].ahorroTotal;
                descuentoPorcentaje = descuentosData[j].porcentaje;
                break;
            }
        }
        
        var descuentoHTML = '';
        if (descuentoItem > 0) {
            descuentoHTML = '<span style="color: var(--success);">-$' + descuentoItem.toFixed(2) + '</span>';
            if (descuentoPorcentaje > 0) {
                descuentoHTML += '<br><small style="color: #888;">(' + descuentoPorcentaje + '%)</small>';
            }
        } else {
            descuentoHTML = '—';
        }
        
        var subtotalConDescuento = (parseFloat(subtotalOriginal) - descuentoItem).toFixed(2);
        
        tablaHTML += '<tr style="border-bottom: 1px solid #eee; ' + estiloFila + '">';
        tablaHTML += '<td style="padding: 10px; text-align: left;">' + item.cantidad + '</td>';
        tablaHTML += '<td style="padding: 10px; text-align: left;"><strong>' + item.nombre_producto + '</strong>';
        if (item.talla) {
            tablaHTML += '<br><small style="color: #888;">Talla: ' + item.talla + '</small>';
        }
        if (item.color) {
            tablaHTML += '<br><small style="color: #888;">Color: ' + item.color + '</small>';
        }
        tablaHTML += '</td>';
        tablaHTML += '<td style="padding: 10px; text-align: right;">$' + precioUnitario + '</td>';
        tablaHTML += '<td style="padding: 10px; text-align: right;">' + descuentoHTML + '</td>';
        tablaHTML += '<td style="padding: 10px; text-align: right; font-weight: bold;">$' + subtotalConDescuento + '</td>';
        tablaHTML += '</tr>';
    }
    
    tablaHTML += '</tbody><tfoot>';
    tablaHTML += '<tr style="border-top: 1px solid #ddd;">';
    tablaHTML += '<td colspan="4" style="padding: 10px; text-align: right; font-weight: bold;">SUBTOTAL:</td>';
    tablaHTML += '<td style="padding: 10px; text-align: right; font-weight: bold;">$' + subtotalVenta.toFixed(2) + '</td>';
    tablaHTML += '</tr>';
    
    if (totalDescuentos > 0) {
        tablaHTML += '<tr style="background: #fff3e0;">';
        tablaHTML += '<td colspan="4" style="padding: 10px; text-align: right; font-weight: bold; color: var(--success);">DESCUENTO TOTAL:</td>';
        tablaHTML += '<td style="padding: 10px; text-align: right; font-weight: bold; color: var(--success);">-$' + totalDescuentos.toFixed(2) + '</td>';
        tablaHTML += '</tr>';
    }
    
    tablaHTML += '<tr style="border-top: 2px solid var(--primary-dark); background: var(--primary-light);">';
    tablaHTML += '<td colspan="4" style="padding: 12px 10px; text-align: right; font-weight: bold; font-size: 1rem;">TOTAL A PAGAR:</td>';
    tablaHTML += '<td style="padding: 12px 10px; text-align: right; font-weight: bold; font-size: 1.2rem; color: var(--nocolor);">$' + totalVenta.toFixed(2) + '</td>';
    tablaHTML += '</tr></tfoot></tr>';
    
    var botonesHTML = '<div style="display: flex; gap: 10px; margin-top: 20px;">';
    botonesHTML += '<button class="btn-action btn-discount" onclick="cerrarModal(\'modalVentaDetalle\')" style="flex: 1;"><i class="fa-solid fa-check"></i> Cerrar</button>';
    botonesHTML += '<button class="btn-action btn-sell" onclick="imprimirTicketVenta(' + idVenta + ', event); cerrarModal(\'modalVentaDetalle\')" style="flex: 1;"><i class="fa-solid fa-print"></i> Imprimir Ticket</button>';
    botonesHTML += '</div>';
    
    var detallesHTML = infoBoxHTML;
    detallesHTML += '<div style="margin-bottom: 15px;"><strong><i class="fa-solid fa-boxes"></i> Productos Vendidos:</strong></div>';
    detallesHTML += tablaHTML + botonesHTML;
    
    var modalVentaDetalle = document.getElementById('modalVentaDetalle');
    
    if (!modalVentaDetalle) {
        modalVentaDetalle = document.createElement('div');
        modalVentaDetalle.id = 'modalVentaDetalle';
        modalVentaDetalle.className = 'modal';
        modalVentaDetalle.innerHTML = '<div class="modal-content" style="width: 850px; max-width: 95%; max-height: 80vh; overflow-y: auto;">' +
            '<span class="close-modal" onclick="cerrarModal(\'modalVentaDetalle\')">&times;</span>' +
            '<h3 style="color: var(--primary-dark); margin-bottom: 15px; border-bottom: 2px solid var(--primary-light); padding-bottom: 10px;">' +
            '<i class="fa-solid fa-circle-info"></i> Detalle Completo de Venta</h3>' +
            '<div id="detalleVentaContent"></div></div>';
        document.body.appendChild(modalVentaDetalle);
    }
    
    var contentDiv = document.getElementById('detalleVentaContent');
    if (contentDiv) {
        contentDiv.innerHTML = detallesHTML;
    }
    
    modalVentaDetalle.style.display = 'flex';
    setTimeout(function() { modalVentaDetalle.classList.add('active'); }, 10);
}

// Cerrar dropdowns al hacer click fuera
document.addEventListener('click', function(event) {
    var container = document.querySelector('.notification-container');
    var dropdown = document.getElementById('notificationDropdown');
    
    if (container && !container.contains(event.target)) {
        if (dropdown && dropdown.classList.contains('active')) {
            dropdown.classList.remove('active');
        }
    }
    
    var cajaContainer = document.querySelector('.user-info');
    var cajaDropdown = document.getElementById('cajaDropdown');
    
    if (cajaContainer && !cajaContainer.contains(event.target)) {
        if (cajaDropdown && cajaDropdown.classList.contains('active')) {
            cajaDropdown.classList.remove('active');
        }
    }
});

document.addEventListener('click', function(event) {
    var target = event.target;
    if (!target || !target.classList || !target.classList.contains('modal')) return;

    target.classList.remove('active');
    setTimeout(function() {
        target.style.display = 'none';
    }, 300);
});

// ==================== MODALES ====================
function cerrarModal(id) {
    var modal = document.getElementById(id);
    if (!modal) return;
    
    modal.classList.remove('active');
    setTimeout(function() { modal.style.display = 'none'; }, 300);
}

function abrirModalDetalle(id, nombre, precio, stock, color, talla) {
    var modal = document.getElementById('modalDetalle');
    if (!modal) return;
    
    document.getElementById('detNombre').innerText = nombre;
    document.getElementById('detPrecio').innerText = '$' + parseFloat(precio).toFixed(2);
    document.getElementById('detStock').innerText = stock;
    document.getElementById('detId').innerText = id;
    document.getElementById('detColor').innerText = color;
    document.getElementById('detTalla').innerText = talla;

    var card = document.querySelector('.product-card[data-id="' + id + '"]');
    if (card) {
        var imgDiv = card.querySelector('.product-img');
        if (imgDiv) {
            var imgUrl = window.getComputedStyle(imgDiv).backgroundImage;
            document.getElementById('detImg').style.backgroundImage = imgUrl;
        }
    }

    modal.style.display = 'flex';
    setTimeout(function() { modal.classList.add('active'); }, 10);
}

function abrirModalDescuento() {
    if (carrito.length === 0) {
        mostrarNotificacion('No hay productos seleccionados.', 'warning');
        return;
    }
    
    var select = document.getElementById('descProductoSelect');
    if (!select) return;
    
    var options = '';
    for (var i = 0; i < carrito.length; i++) {
        options += '<option value="' + carrito[i].id + '">' + carrito[i].nombre + '</option>';
    }
    select.innerHTML = options;
    actualizarMaxCantDesc();
    
    var modal = document.getElementById('modalDescuento');
    if (!modal) return;
    
    modal.style.display = 'flex';
    setTimeout(function() { modal.classList.add('active'); }, 10);
}

function formatearNombreTicket(nombreBase, talla, color) {
    var nombre = (nombreBase || '').toString().trim();
    var tallaRaw = (talla || '').toString().trim();
    var colorRaw = (color || '').toString().trim();

    var tallaFmt = '';
    if (tallaRaw) {
        tallaFmt = /^t/i.test(tallaRaw) ? tallaRaw.toUpperCase() : ('T' + tallaRaw).toUpperCase();
    }

    var colorInicial = colorRaw ? colorRaw.charAt(0).toUpperCase() : '';
    var sufijo = [tallaFmt, colorInicial].filter(Boolean).join('-');

    return sufijo ? (nombre + ' - ' + sufijo) : nombre;
}

// ==================== CANTIDADES ====================
function cambiarCantidad(btn, valor) {
    var card = btn.closest('.product-card');
    if (!card) return;
    
    var input = card.querySelector('.qty-input');
    var stockMax = parseInt(card.dataset.stock);
    var actual = parseInt(input.value);
    
    if (stockMax <= 0) {
        mostrarNotificacion('Producto agotado, no se puede seleccionar.', 'warning');
        return;
    }
    
    if (valor > 0 && actual >= stockMax) {
        mostrarNotificacion('Stock insuficiente. Stock disponible: ' + stockMax, 'warning');
        return;
    }
    
    var nuevaCantidad = Math.max(0, actual + valor);
    
    if (nuevaCantidad > stockMax) {
        nuevaCantidad = stockMax;
    }
    
    input.value = nuevaCantidad;
    
    var checkbox = card.querySelector('input[type="checkbox"]');
    if (checkbox && checkbox.checked) {
        actualizarItemCarrito(card.dataset.id, nuevaCantidad);
    }
}

// ==================== CARRITO ====================
function toggleProductoVenta(checkbox, id) {
    var card = checkbox.closest('.product-card');
    if (!card) return;
    
    var cantidad = parseInt(card.querySelector('.qty-input').value);
    var stockMax = parseInt(card.dataset.stock);
    var nombreBase = card.dataset.nombre;
    var nombre = formatearNombreTicket(nombreBase, card.dataset.talla, card.dataset.color);

    if (stockMax <= 0) {
        mostrarNotificacion('Producto agotado, no se puede agregar al carrito.', 'warning');
        checkbox.checked = false;
        return;
    }

    if (checkbox.checked) {
        if (cantidad <= 0) {
            mostrarNotificacion('Indique una cantidad válida.', 'warning');
            checkbox.checked = false;
            return;
        }
        
        if (cantidad > stockMax) {
            mostrarNotificacion('La cantidad excede el stock disponible (' + stockMax + ')', 'warning');
            checkbox.checked = false;
            return;
        }
        
        carrito.push({ 
            id: id,
            nombre: nombre, 
            cantidad: cantidad, 
            precio: parseFloat(card.dataset.price), 
            subtotal: cantidad * parseFloat(card.dataset.price) 
        });
        mostrarNotificacion(nombre + ' agregado', 'success');
    } else {
        carrito = carrito.filter(function(p) { return p.id !== id; });
        descuentosAplicados = descuentosAplicados.filter(function(d) { return d.id !== id; });
        mostrarNotificacion(nombre + ' quitado', 'info');
    }
    actualizarTablaResumen();
}

function actualizarItemCarrito(id, nuevaCant) {
    var index = -1;
    for (var i = 0; i < carrito.length; i++) {
        if (carrito[i].id === id) {
            index = i;
            break;
        }
    }
    
    if (index !== -1) {
        var card = document.querySelector('.product-card[data-id="' + id + '"]');
        var stockMax = card ? parseInt(card.dataset.stock) : 0;
        
        if (nuevaCant > stockMax) {
            mostrarNotificacion('No puedes agregar más de ' + stockMax + ' unidades (stock disponible)', 'warning');
            nuevaCant = stockMax;
            if (card) {
                var input = card.querySelector('.qty-input');
                if (input) input.value = nuevaCant;
            }
        }
        
        if (nuevaCant <= 0) {
            carrito.splice(index, 1);
            descuentosAplicados = descuentosAplicados.filter(function(d) { return d.id !== id; });
            if (card) {
                var checkbox = card.querySelector('input[type="checkbox"]');
                if (checkbox) checkbox.checked = false;
                var input = card.querySelector('.qty-input');
                if (input) input.value = 0;
            }
        } else {
            carrito[index].cantidad = nuevaCant;
            carrito[index].subtotal = nuevaCant * carrito[index].precio;
            
            var dIndex = -1;
            for (var j = 0; j < descuentosAplicados.length; j++) {
                if (descuentosAplicados[j].id === id) {
                    dIndex = j;
                    break;
                }
            }
            if (dIndex !== -1) {
                if (descuentosAplicados[dIndex].cantAplicada > nuevaCant) {
                    descuentosAplicados[dIndex].cantAplicada = nuevaCant;
                }
                descuentosAplicados[dIndex].ahorroTotal = (carrito[index].precio * descuentosAplicados[dIndex].cantAplicada) * (descuentosAplicados[dIndex].porcentaje / 100);
            }
        }
        actualizarTablaResumen();
    }
}

// ==================== TABLA DE RESUMEN ====================
function actualizarTablaResumen() {
    var tVenta = document.getElementById('listaVenta');
    var tDesc = document.getElementById('listaDescuentos');
    
    if (!tVenta || !tDesc) return;
    
    tVenta.innerHTML = '';
    
    var subtotalGlobal = 0;
    var descuentoGlobal = 0;

    for (var i = 0; i < carrito.length; i++) {
        var p = carrito[i];
        subtotalGlobal += p.subtotal;
        tVenta.innerHTML += '<tr>' +
            '<td class="col-cant">' + p.cantidad + '</td>' +
            '<td class="col-prod">' + p.nombre + '<tr>' +
            '<td class="col-subt">$' + p.subtotal.toFixed(2) + '</td>' +
            '</tr>';
    }

    if (carrito.length === 0) {
        tVenta.innerHTML = '<tr class="empty-row"><td colspan="3" style="text-align: center; color: #999;">No hay productos seleccionados<\/td><\/tr>';
    }

    // Calcular descuentos de productos
    var descuentosExistentes = 0;
    var filasDescuentos = tDesc.querySelectorAll('tr:not([data-tipo="fidelidad"])');
    for (var j = 0; j < filasDescuentos.length; j++) {
        var fila = filasDescuentos[j];
        var texto = fila.querySelector('.col-price-desc')?.innerText || '';
        var monto = parseFloat(texto.replace('-$', '').replace(',', '')) || 0;
        descuentosExistentes += monto;
    }
    
    // Calcular descuento por fidelidad
    var descuentoFidelidadMonto = 0;
    if (descuentoFidelidad > 0 && clienteSeleccionado) {
        descuentoFidelidadMonto = subtotalGlobal * (descuentoFidelidad / 100);
    }
    
    descuentoGlobal = descuentosExistentes + descuentoFidelidadMonto;
    
    // Actualizar o crear la fila de fidelidad
    let filaFidelidad = tDesc.querySelector('tr[data-tipo="fidelidad"]');
    if (descuentoFidelidad > 0 && clienteSeleccionado) {
        if (filaFidelidad) {
            filaFidelidad.innerHTML = `
                <td class="col-prod-desc">FIDELIDAD (${descuentoFidelidad}%)</td>
                <td class="col-icon-desc">-</td>
                <td class="col-price-desc">-$${descuentoFidelidadMonto.toFixed(2)}</td>
            `;
        } else {
            const nuevaFila = document.createElement('tr');
            nuevaFila.setAttribute('data-tipo', 'fidelidad');
            nuevaFila.innerHTML = `
                <td class="col-prod-desc">FIDELIDAD (${descuentoFidelidad}%)</td>
                <td class="col-icon-desc">-</td>
                <td class="col-price-desc">-$${descuentoFidelidadMonto.toFixed(2)}</td>
            `;
            if (tDesc.firstChild) {
                tDesc.insertBefore(nuevaFila, tDesc.firstChild);
            } else {
                tDesc.appendChild(nuevaFila);
            }
        }
    } else if (filaFidelidad) {
        filaFidelidad.remove();
    }

    // Si no hay descuentos en la tabla, mostrar mensaje
    const otrasFilas = tDesc.querySelectorAll('tr:not([data-tipo="fidelidad"])');
    if (descuentosExistentes === 0 && (!clienteSeleccionado || descuentoFidelidad === 0)) {
        if (tDesc.querySelector('.empty-row') === null && tDesc.children.length === 0) {
            tDesc.innerHTML = '<tr class="empty-row"><td colspan="3" style="text-align: center; color: #999;">Sin descuentos aplicados<\/td><\/tr>';
        }
    } else if (tDesc.querySelector('.empty-row')) {
        tDesc.querySelector('.empty-row').remove();
    }

    var subTotalEl = document.getElementById('subTotal');
    var descuentoMontoEl = document.getElementById('descuentoMonto');
    var totalDisplayEl = document.getElementById('totalDisplay');
    
    if (subTotalEl) subTotalEl.innerText = '$' + subtotalGlobal.toFixed(2);
    if (descuentoMontoEl) descuentoMontoEl.innerText = '-$' + descuentoGlobal.toFixed(2);
    if (totalDisplayEl) totalDisplayEl.innerText = '$' + (subtotalGlobal - descuentoGlobal).toFixed(2);
}

// ==================== DESCUENTOS ====================
function actualizarMaxCantDesc() {
    var select = document.getElementById('descProductoSelect');
    if (!select) return;
    
    var id = select.value;
    var producto = null;
    for (var i = 0; i < carrito.length; i++) {
        if (carrito[i].id === id) {
            producto = carrito[i];
            break;
        }
    }
    
    if (producto) {
        var cantInput = document.getElementById('descCantAplicar');
        var maxInfo = document.getElementById('maxCantDescInfo');
        
        if (cantInput) cantInput.max = producto.cantidad;
        if (maxInfo) maxInfo.innerText = 'Máximo: ' + producto.cantidad + ' unidades.';
    }
}

function confirmarDescuento() {
    var select = document.getElementById('descProductoSelect');
    var cantDescInput = document.getElementById('descCantAplicar');
    var porcentajeInput = document.getElementById('descPorcentajeInput');
    
    if (!select || !cantDescInput || !porcentajeInput) return;
    
    var id = select.value;
    var cantDesc = parseInt(cantDescInput.value);
    var porcentaje = parseFloat(porcentajeInput.value);
    
    var producto = null;
    for (var i = 0; i < carrito.length; i++) {
        if (carrito[i].id === id) {
            producto = carrito[i];
            break;
        }
    }

    if (!porcentaje || porcentaje <= 0 || porcentaje > 100) {
        mostrarNotificacion('Porcentaje inválido.', 'warning');
        return;
    }
    
    if (!producto) {
        mostrarNotificacion('Producto no encontrado.', 'warning');
        return;
    }
    
    if (cantDesc <= 0 || cantDesc > producto.cantidad) {
        mostrarNotificacion('Cantidad no válida.', 'warning');
        return;
    }

    var ahorro = (producto.precio * cantDesc) * (porcentaje / 100);
    
    descuentosAplicados = descuentosAplicados.filter(function(d) { return d.id !== id; });
    
    descuentosAplicados.push({ 
        id: id, 
        nombre: producto.nombre, 
        ahorroTotal: ahorro, 
        porcentaje: porcentaje, 
        cantAplicada: cantDesc 
    });

    cerrarModal('modalDescuento');
    mostrarNotificacion('Descuento aplicado', 'success');
    actualizarTablaResumen();
}

// ==================== VENTAS ====================
function realizarVenta() {
    if (carrito.length === 0) {
        mostrarNotificacion('No hay productos en el carrito', 'warning');
        return;
    }
    
    var subtotalGlobal = 0;
    for (var i = 0; i < carrito.length; i++) {
        subtotalGlobal += carrito[i].subtotal;
    }
    
    var descuentoGlobal = 0;
    var descuentosHTML = '';
    for (var j = 0; j < descuentosAplicados.length; j++) {
        var d = descuentosAplicados[j];
        descuentoGlobal += d.ahorroTotal;
        descuentosHTML += '<div>' + d.nombre + ': -$' + d.ahorroTotal.toFixed(2) + ' (' + d.porcentaje + '%)</div>';
    }
    
    var total = subtotalGlobal - descuentoGlobal;
    
    document.getElementById('modalSubtotal').innerText = '$' + subtotalGlobal.toFixed(2);
    
    if (descuentoGlobal > 0) {
        document.getElementById('descuentosResumenContainer').style.display = 'block';
        document.getElementById('descuentosResumen').innerHTML = '<div style="color: var(--success);">-$' + descuentoGlobal.toFixed(2) + '</div>' + descuentosHTML;
    } else {
        document.getElementById('descuentosResumenContainer').style.display = 'none';
    }
    
    document.getElementById('modalTotalPago').innerText = '$' + total.toFixed(2);
    document.getElementById('dineroRecibido').value = '';
    document.getElementById('cambioDisplay').innerHTML = '<span style="color: var(--primary-dark);">$0.00</span>';
    document.getElementById('metodoPago').value = '1';
    
    toggleCamposPorMetodoPago();
    
    var modal = document.getElementById('modalPago');
    modal.style.display = 'flex';
    setTimeout(function() { modal.classList.add('active'); }, 10);
    
    var metodoPagoSelect = document.getElementById('metodoPago');
    var dineroInput = document.getElementById('dineroRecibido');
    dineroInput.oninput = function() {
        calcularCambio(total);
    };
    metodoPagoSelect.onchange = function() {
        toggleCamposPorMetodoPago();
        if (document.getElementById('metodoPago').value === '1') {
            calcularCambio(total);
        }
    };
}

function calcularCambio(total) {
    var metodoPago = document.getElementById('metodoPago').value;
    if (metodoPago !== '1') return;
    
    var dineroRecibido = parseFloat(document.getElementById('dineroRecibido').value) || 0;
    var cambio = dineroRecibido - total;
    var cambioDisplay = document.getElementById('cambioDisplay');
    
    if (cambio >= 0) {
        cambioDisplay.innerHTML = '<span style="color: var(--primary-dark);">$' + cambio.toFixed(2) + '</span>';
    } else {
        cambioDisplay.innerHTML = '<span style="color: var(--nocolor);">Falta $' + Math.abs(cambio).toFixed(2) + '</span>';
    }
}

async function confirmarPago() {
    // Verificar si la caja está abierta
    var respCaja = await fetch('/ElZapato/src/api/caja_api.php?action=verificar_estado');
    var dataCaja = await respCaja.json();
    
    if (!dataCaja.success || !dataCaja.abierta) {
        mostrarNotificacion('Debe abrir la caja antes de realizar ventas', 'warning');
        return;
    }
    
    var totalTexto = document.getElementById('modalTotalPago').innerText;
    var total = parseFloat(totalTexto.replace('$', ''));
    var metodoPago = document.getElementById('metodoPago').value;
    var esTarjeta = metodoPago === '2';
    var dineroRecibido = esTarjeta ? total : (parseFloat(document.getElementById('dineroRecibido').value) || 0);
    var metodoPagoTexto = document.getElementById('metodoPago').options[document.getElementById('metodoPago').selectedIndex].text;
    
    if (!esTarjeta && dineroRecibido < total) {
        mostrarNotificacion('El dinero recibido es insuficiente', 'warning');
        return;
    }
    
    mostrarNotificacion('Verificando stock disponible...', 'info');
    
    for (var i = 0; i < carrito.length; i++) {
        var productoCarrito = carrito[i];
        var card = document.querySelector('.product-card[data-id="' + productoCarrito.id + '"]');
        
        if (!card) {
            mostrarNotificacion('Error: Producto no encontrado en la vista', 'warning');
            return;
        }
        
        var stockActual = parseInt(card.dataset.stock);
        
        if (stockActual <= 0) {
            mostrarNotificacion('El producto ' + productoCarrito.nombre + ' está agotado', 'warning');
            return;
        }
        
        if (productoCarrito.cantidad > stockActual) {
            mostrarNotificacion('Stock insuficiente para ' + productoCarrito.nombre + '. Disponible: ' + stockActual, 'warning');
            return;
        }
    }
    
    var cambio = esTarjeta ? 0 : (dineroRecibido - total);
    
    mostrarNotificacion('Procesando venta...', 'info');
    
    var productosParaGuardar = [];
    for (var i = 0; i < carrito.length; i++) {
        productosParaGuardar.push({
            id: carrito[i].id,
            nombre: carrito[i].nombre,
            cantidad: carrito[i].cantidad,
            precio: carrito[i].precio
        });
    }
    
    var ventaData = {
        productos: productosParaGuardar,
        total: total,
        metodo_pago: parseInt(metodoPago),
        cambio: cambio,
        dinero_recibido: dineroRecibido,
        descuentos: descuentosAplicados,
        id_cliente: clienteSeleccionado ? clienteSeleccionado.id_cliente : null
    };
    
    try {
        var response = await fetch('/ElZapato/src/api/guardar_venta.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(ventaData)
        });
        
        var result = await response.json();
        
        if (result.success) {
            mostrarNotificacion('Venta #' + result.id_venta + ' realizada con éxito.', 'success');
            mostrarNotificacion('Imprimiendo ticket...', 'info');
            
            sessionStorage.setItem('ventaData_' + result.id_venta, JSON.stringify({
                cambio: cambio,
                dineroRecibido: dineroRecibido,
                metodoPago: metodoPagoTexto,
                total: total,
                descuentos: descuentosAplicados
            }));
            
            actualizarStocksLocales();
            
            carrito = [];
            descuentosAplicados = [];
            actualizarTablaResumen();
            
            var checkboxes = document.querySelectorAll('.product-card input[type="checkbox"]');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = false;
            }
            
            var inputs = document.querySelectorAll('.qty-input');
            for (var j = 0; j < inputs.length; j++) {
                inputs[j].value = 0;
            }
            
            cerrarModal('modalPago');
            await cargarUltimasVentas();
            verificarEstadoCaja();
            
        } else {
            mostrarNotificacion('Error: ' + (result.error || 'No se pudo guardar la venta'), 'warning');
        }
        
    } catch (error) {
        console.error('Error al guardar venta:', error);
        mostrarNotificacion('Error al procesar la venta', 'warning');
    }
}

function actualizarStocksLocales() {
    setTimeout(function() {
        location.reload();
    }, 1300);
}

function toggleCamposPorMetodoPago() {
    var metodoPago = document.getElementById('metodoPago').value;
    var efectivoFieldsContainer = document.getElementById('efectivoFieldsContainer');
    var dineroRecibido = document.getElementById('dineroRecibido');
    var cambioDisplay = document.getElementById('cambioDisplay');
    
    if (metodoPago === '2') {
        if (efectivoFieldsContainer) {
            efectivoFieldsContainer.style.display = 'none';
        }
        if (dineroRecibido) {
            dineroRecibido.value = '';
            dineroRecibido.disabled = true;
        }
        if (cambioDisplay) {
            cambioDisplay.innerHTML = '<span style="color: var(--primary-dark);">$0.00</span>';
        }
    } else {
        if (efectivoFieldsContainer) {
            efectivoFieldsContainer.style.display = 'block';
        }
        if (dineroRecibido) {
            dineroRecibido.disabled = false;
        }
    }
}

// ==================== VALIDACIONES DE BÚSQUEDA DE CLIENTE ====================
function cambiarCriterioBusqueda() {
    const criterio = document.getElementById('criterioBusqueda').value;
    const input = document.getElementById('valorBusqueda');
    
    input.value = '';
    input.classList.remove('input-telefono', 'input-nombre', 'input-error', 'input-valid');
    
    if (criterio === 'telefono') {
        input.placeholder = 'Ej: 23323453 (8 dígitos)';
        input.classList.add('input-telefono');
    } else {
        input.placeholder = 'Ej: Juan Pérez (solo letras)';
        input.classList.add('input-nombre');
    }
    
    const btnBuscar = document.getElementById('btnBuscarCliente');
    btnBuscar.disabled = true;
    btnBuscar.style.opacity = '0.6';
    btnBuscar.style.cursor = 'not-allowed';
    
    document.getElementById('resultadoBusqueda').style.display = 'none';
    document.getElementById('listaResultados').innerHTML = '';
}

function validarInputBusqueda() {
    const criterio = document.getElementById('criterioBusqueda').value;
    const input = document.getElementById('valorBusqueda');
    let valor = input.value;
    const btnBuscar = document.getElementById('btnBuscarCliente');
    let esValido = false;
    
    if (criterio === 'telefono') {
        let nuevoValor = valor.replace(/[^0-9]/g, '');
        if (nuevoValor.length > 8) nuevoValor = nuevoValor.substring(0, 8);
        
        if (valor !== nuevoValor) {
            input.value = nuevoValor;
            valor = nuevoValor;
        }
        
        let valorFormateado = '';
        if (valor.length > 0) {
            if (valor.length <= 4) {
                valorFormateado = valor;
            } else {
                valorFormateado = valor.substring(0, 4) + '-' + valor.substring(4);
            }
            input.value = valorFormateado;
        }
        
        const valorLimpio = valor.replace(/-/g, '');
        const longitudCorrecta = valorLimpio.length === 8;
        
        if (valorLimpio.length === 0) {
            input.classList.remove('input-error', 'input-valid');
            input.classList.add('input-telefono');
            esValido = false;
        } else if (longitudCorrecta) {
            input.classList.remove('input-error');
            input.classList.add('input-valid');
            esValido = true;
        } else {
            input.classList.remove('input-valid');
            input.classList.add('input-error');
            esValido = false;
        }
    } else {
        let nuevoValor = valor.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        if (nuevoValor.length > 50) nuevoValor = nuevoValor.substring(0, 50);
        
        if (valor !== nuevoValor) {
            input.value = nuevoValor;
            valor = nuevoValor;
        }
        
        let palabras = nuevoValor.toLowerCase().split(' ');
        for (let i = 0; i < palabras.length; i++) {
            if (palabras[i].length > 0) {
                palabras[i] = palabras[i].charAt(0).toUpperCase() + palabras[i].slice(1);
            }
        }
        let nombreFormateado = palabras.join(' ');
        if (valor !== nombreFormateado && valor.length > 0) {
            input.value = nombreFormateado;
            valor = nombreFormateado;
        }
        
        const tieneLetras = /[a-zA-ZáéíóúÁÉÍÓÚñÑ]/.test(valor);
        const longitudValida = valor.length >= 2;
        
        if (valor.length === 0) {
            input.classList.remove('input-error', 'input-valid');
            input.classList.add('input-nombre');
            esValido = false;
        } else if (tieneLetras && longitudValida) {
            input.classList.remove('input-error');
            input.classList.add('input-valid');
            esValido = true;
        } else {
            input.classList.remove('input-valid');
            input.classList.add('input-error');
            esValido = false;
        }
    }
    
    btnBuscar.disabled = !esValido;
    btnBuscar.style.opacity = esValido ? '1' : '0.6';
    btnBuscar.style.cursor = esValido ? 'pointer' : 'not-allowed';
}

function abrirModalBuscarCliente() {
    document.getElementById('modalBuscarCliente').style.display = 'flex';
    setTimeout(function() { document.getElementById('modalBuscarCliente').classList.add('active'); }, 10);
    
    const input = document.getElementById('valorBusqueda');
    const btnBuscar = document.getElementById('btnBuscarCliente');
    const criterioSelect = document.getElementById('criterioBusqueda');
    
    input.value = '';
    document.getElementById('resultadoBusqueda').style.display = 'none';
    document.getElementById('listaResultados').innerHTML = '';
    criterioSelect.value = 'telefono';
    
    input.classList.remove('input-error', 'input-valid', 'input-nombre');
    input.classList.add('input-telefono');
    input.placeholder = 'Ej: 23323453 (8 dígitos)';
    
    btnBuscar.disabled = true;
    btnBuscar.style.opacity = '0.6';
    btnBuscar.style.cursor = 'not-allowed';
}

async function buscarClienteModal() {
    const criterio = document.getElementById('criterioBusqueda').value;
    let valor = document.getElementById('valorBusqueda').value.trim();
    const btnBuscar = document.getElementById('btnBuscarCliente');
    
    if (criterio === 'telefono') {
        valor = valor.replace(/-/g, '');
        const soloNumeros = /^\d*$/.test(valor);
        if (!soloNumeros || valor.length !== 8) {
            mostrarNotificacion('Ingrese un número de teléfono válido (8 dígitos)', 'warning');
            return;
        }
    } else {
        const soloLetras = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/.test(valor);
        if (!soloLetras || valor.length < 2) {
            mostrarNotificacion('Ingrese un nombre válido (mínimo 2 letras)', 'warning');
            return;
        }
    }
    
    mostrarNotificacion('Buscando cliente...', 'info');
    
    const textoOriginal = btnBuscar.innerHTML;
    btnBuscar.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Buscando...';
    btnBuscar.disabled = true;
    
    try {
        const response = await fetch('/ElZapato/src/api/buscar_cliente.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ criterio: criterio, valor: valor })
        });
        
        const data = await response.json();
        const resultadoDiv = document.getElementById('resultadoBusqueda');
        const listaDiv = document.getElementById('listaResultados');
        
        if (data.success && data.clientes && data.clientes.length > 0) {
            let html = '';
            for (let i = 0; i < data.clientes.length; i++) {
                const c = data.clientes[i];
                const telefonoFormateado = c.telefono.replace(/(\d{4})(\d{4})/, '$1-$2');
                html += `
                    <div class="dropdown-item" style="cursor: pointer; margin: 5px; border-radius: 8px;" onclick="seleccionarCliente(${JSON.stringify(c).replace(/"/g, '&quot;')})">
                        <div style="flex:1;">
                            <div><strong>${c.nombre}</strong></div>
                            <div><small>Tel: ${telefonoFormateado} | ${c.nivel} (${c.descuento}% desc)</small></div>
                        </div>
                        <button class="btn-view-sale">Seleccionar</button>
                    </div>
                `;
            }
            listaDiv.innerHTML = html;
            resultadoDiv.style.display = 'block';
        } else {
            listaDiv.innerHTML = '<div style="padding: 15px; text-align: center; color: #999;">No se encontraron clientes</div>';
            resultadoDiv.style.display = 'block';
            abrirModalRegistrarCliente();
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error al buscar cliente', 'warning');
    } finally {
        btnBuscar.innerHTML = textoOriginal;
        btnBuscar.disabled = false;
    }
}

// ==================== MODAL REGISTRAR CLIENTE ====================
function abrirModalRegistrarCliente() {
    document.getElementById('modalRegistrarCliente').style.display = 'flex';
    setTimeout(function() { document.getElementById('modalRegistrarCliente').classList.add('active'); }, 10);
    
    document.getElementById('regNombre').value = '';
    document.getElementById('regTelefono').value = '';
    document.getElementById('regEmail').value = '';
    
    document.getElementById('regNombre').classList.remove('input-error', 'input-valid');
    document.getElementById('regTelefono').classList.remove('input-error', 'input-valid');
    
    const btnRegistrar = document.getElementById('btnRegistrarCliente');
    btnRegistrar.disabled = true;
    btnRegistrar.style.opacity = '0.6';
    btnRegistrar.style.cursor = 'not-allowed';
}

function validarRegistroCliente() {
    const inputNombre = document.getElementById('regNombre');
    const inputTelefono = document.getElementById('regTelefono');
    let nombre = inputNombre.value;
    let telefono = inputTelefono.value;
    const btnRegistrar = document.getElementById('btnRegistrarCliente');
    let esValido = false;
    
    let nuevoNombre = nombre.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
    if (nuevoNombre.length > 50) nuevoNombre = nuevoNombre.substring(0, 50);
    
    let palabras = nuevoNombre.toLowerCase().split(' ');
    for (let i = 0; i < palabras.length; i++) {
        if (palabras[i].length > 0) {
            palabras[i] = palabras[i].charAt(0).toUpperCase() + palabras[i].slice(1);
        }
    }
    let nombreFormateado = palabras.join(' ');
    if (nombre !== nombreFormateado && nombre.length > 0) {
        inputNombre.value = nombreFormateado;
        nombre = nombreFormateado;
    }
    
    const nombreValido = /[a-zA-ZáéíóúÁÉÍÓÚñÑ]/.test(nombre) && nombre.length >= 2;
    
    let nuevoTelefono = telefono.replace(/[^0-9]/g, '');
    if (nuevoTelefono.length > 8) nuevoTelefono = nuevoTelefono.substring(0, 8);
    
    let telefonoFormateado = '';
    if (nuevoTelefono.length > 0) {
        if (nuevoTelefono.length <= 4) {
            telefonoFormateado = nuevoTelefono;
        } else {
            telefonoFormateado = nuevoTelefono.substring(0, 4) + '-' + nuevoTelefono.substring(4);
        }
    }
    
    if (telefono !== telefonoFormateado) {
        inputTelefono.value = telefonoFormateado;
        telefono = telefonoFormateado;
    }
    
    const telefonoLimpio = nuevoTelefono;
    const telefonoValido = telefonoLimpio.length === 8;
    
    if (nombre.length === 0) {
        inputNombre.classList.remove('input-error', 'input-valid');
    } else if (nombreValido) {
        inputNombre.classList.add('input-valid');
        inputNombre.classList.remove('input-error');
    } else {
        inputNombre.classList.add('input-error');
        inputNombre.classList.remove('input-valid');
    }
    
    if (telefono.length === 0) {
        inputTelefono.classList.remove('input-error', 'input-valid');
    } else if (telefonoValido) {
        inputTelefono.classList.add('input-valid');
        inputTelefono.classList.remove('input-error');
    } else {
        inputTelefono.classList.add('input-error');
        inputTelefono.classList.remove('input-valid');
    }
    
    esValido = nombreValido && telefonoValido;
    
    btnRegistrar.disabled = !esValido;
    btnRegistrar.style.opacity = esValido ? '1' : '0.6';
    btnRegistrar.style.cursor = esValido ? 'pointer' : 'not-allowed';
}

async function confirmarRegistrarCliente() {
    let nombre = document.getElementById('regNombre').value.trim();
    let telefono = document.getElementById('regTelefono').value.trim();
    const email = document.getElementById('regEmail').value.trim();
    
    telefono = telefono.replace(/-/g, '');
    
    const soloLetras = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,}$/.test(nombre);
    const soloNumeros = /^\d{8}$/.test(telefono);
    
    if (!soloLetras) {
        mostrarNotificacion('Nombre inválido (mínimo 2 letras)', 'warning');
        return;
    }
    
    if (!soloNumeros) {
        mostrarNotificacion('Teléfono inválido (debe tener 8 dígitos)', 'warning');
        return;
    }
    
    const btnRegistrar = document.getElementById('btnRegistrarCliente');
    const textoOriginal = btnRegistrar.innerHTML;
    btnRegistrar.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Registrando...';
    btnRegistrar.disabled = true;
    
    try {
        const response = await fetch('/ElZapato/src/api/registrar_cliente.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre: nombre, telefono: telefono, email: email })
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarNotificacion('Cliente registrado exitosamente', 'success');
            cerrarModal('modalRegistrarCliente');
            cerrarModal('modalBuscarCliente');
            
            setTimeout(() => {
                abrirModalBuscarCliente();
                const telefonoFormateado = telefono.replace(/(\d{4})(\d{4})/, '$1-$2');
                document.getElementById('valorBusqueda').value = telefonoFormateado;
                validarInputBusqueda();
                setTimeout(() => buscarClienteModal(), 500);
            }, 500);
        } else {
            mostrarNotificacion(data.message || 'Error al registrar cliente', 'warning');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error al registrar cliente', 'warning');
    } finally {
        btnRegistrar.innerHTML = textoOriginal;
        btnRegistrar.disabled = false;
    }
}

function seleccionarCliente(cliente) {
    clienteSeleccionado = cliente;
    descuentoFidelidad = cliente.descuento;
    nivelFidelidad = cliente.nivel;
    
    const telefonoFormateado = cliente.telefono.replace(/(\d{4})(\d{4})/, '$1-$2');
    
    mostrarNotificacion(
        `Cliente: ${cliente.nombre} | Tel: ${telefonoFormateado} | Nivel: ${cliente.nivel} | Descuento: ${cliente.descuento}%`, 
        'success'
    );
    
    mostrarDescuentoFidelidadEnResumen();
    cerrarModal('modalBuscarCliente');
}

function mostrarDescuentoFidelidadEnResumen() {
    let container = document.getElementById('descuentoFidelidadContainer');
    
    const modalPago = document.getElementById('modalPago');
    if (!modalPago) {
        console.error('Modal de pago no encontrado');
        return;
    }
    
    const modalContent = modalPago.querySelector('.modal-content');
    if (!modalContent) {
        console.error('Modal content no encontrado');
        return;
    }
    
    const modalBody = modalContent.querySelector('.modal-body');
    if (!modalBody) {
        console.error('Modal body no encontrado');
        return;
    }
    
    if (!container) {
        const html = `
            <div id="descuentoFidelidadContainer" class="form-group" style="border-top: 1px solid #E4E0E1; padding-top: 15px; margin-top: 15px;">
                <label style="color: #AB886D; font-weight: bold;"><i class="fas fa-crown"></i> Descuento por Fidelidad:</label>
                <div style="background: #E4E0E1; padding: 12px; border-radius: 8px; margin-top: 5px;">
                    <div><strong>Cliente:</strong> <span id="clienteNombre">${clienteSeleccionado?.nombre || 'Ninguno'}</span></div>
                    <div><strong>Nivel:</strong> <span id="clienteNivel">${nivelFidelidad || 'Sin nivel'}</span></div>
                    <div><strong>Descuento:</strong> <span id="clienteDescuento">${descuentoFidelidad || 0}%</span></div>
                    <div><strong>Teléfono:</strong> <span id="clienteTelefono">${clienteSeleccionado?.telefono ? clienteSeleccionado.telefono.replace(/(\d{4})(\d{4})/, '$1-$2') : '-'}</span></div>
                    <div id="descuentoAplicadoInfo" style="color: #28a745; font-weight: bold; margin-top: 8px;"></div>
                </div>
                <button type="button" class="btn-action btn-discount" style="margin-top: 10px; width: 100%;" onclick="quitarDescuentoFidelidad()">
                    <i class="fas fa-times"></i> Quitar descuento
                </button>
            </div>
        `;
        
        modalBody.insertAdjacentHTML('beforeend', html);
        container = document.getElementById('descuentoFidelidadContainer');
    } else {
        document.getElementById('clienteNombre').innerText = clienteSeleccionado?.nombre || 'Ninguno';
        document.getElementById('clienteNivel').innerText = nivelFidelidad || 'Sin nivel';
        document.getElementById('clienteDescuento').innerText = descuentoFidelidad + '%';
        const telefonoSpan = document.getElementById('clienteTelefono');
        if (telefonoSpan) {
            telefonoSpan.innerText = clienteSeleccionado?.telefono ? clienteSeleccionado.telefono.replace(/(\d{4})(\d{4})/, '$1-$2') : '-';
        }
        container.style.display = 'block';
    }
    
    actualizarTablaResumen();
    actualizarTotalConDescuentoFidelidad();
}

function quitarDescuentoFidelidad() {
    clienteSeleccionado = null;
    descuentoFidelidad = 0;
    nivelFidelidad = '';
    
    const filaFidelidad = document.querySelector('#listaDescuentos tr[data-tipo="fidelidad"]');
    if (filaFidelidad) {
        filaFidelidad.remove();
    }
    
    const container = document.getElementById('descuentoFidelidadContainer');
    if (container) {
        container.style.display = 'none';
    }
    
    actualizarTablaResumen();
    
    const subtotalSpan = document.getElementById('modalSubtotal');
    if (subtotalSpan) {
        let subtotalGlobal = 0;
        for (var i = 0; i < carrito.length; i++) {
            subtotalGlobal += carrito[i].subtotal;
        }
        const totalSpan = document.getElementById('modalTotalPago');
        if (totalSpan) {
            totalSpan.innerText = '$' + subtotalGlobal.toFixed(2);
        }
    }
    
    mostrarNotificacion('Descuento por fidelidad eliminado', 'info');
}

function actualizarTotalConDescuentoFidelidad() {
    const subtotalSpan = document.getElementById('modalSubtotal');
    if (!subtotalSpan) return;
    
    const subtotalTexto = subtotalSpan.innerText;
    let subtotal = parseFloat(subtotalTexto.replace('$', ''));
    
    const totalSpan = document.getElementById('modalTotalPago');
    if (!totalSpan) return;
    
    let descuentoGlobal = 0;
    for (var i = 0; i < descuentosAplicados.length; i++) {
        descuentoGlobal += descuentosAplicados[i].ahorroTotal;
    }
    
    if (descuentoFidelidad > 0 && clienteSeleccionado) {
        const descuentoFidelidadMonto = subtotal * (descuentoFidelidad / 100);
        const nuevoTotal = subtotal - descuentoGlobal - descuentoFidelidadMonto;
        
        const descuentoInfo = document.getElementById('descuentoAplicadoInfo');
        if (descuentoInfo) {
            descuentoInfo.innerHTML = `Descuento fidelidad ${descuentoFidelidad}%: -$${descuentoFidelidadMonto.toFixed(2)}`;
        }
        
        totalSpan.innerText = '$' + nuevoTotal.toFixed(2);
    } else {
        totalSpan.innerText = '$' + (subtotal - descuentoGlobal).toFixed(2);
    }
}

// ==================== FILTROS ====================
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('productSearch');
    var categoryFilter = document.getElementById('categoryFilter');
    var brandFilter = document.getElementById('brandFilter');

    cargarUltimasVentas();
    verificarEstadoCaja();
    
    function filtrarProductos() {
        var searchTerm = searchInput.value.toLowerCase();
        var category = categoryFilter.value;
        var brand = brandFilter.value;
        
        var products = document.querySelectorAll('.product-card');
        
        for (var i = 0; i < products.length; i++) {
            var product = products[i];
            var nombre = product.dataset.nombre.toLowerCase();
            var productCategory = product.dataset.categoria;
            var productBrand = product.dataset.marca;
            
            var match = true;
            
            if (searchTerm && nombre.indexOf(searchTerm) === -1) {
                match = false;
            }
            
            if (category !== 'all' && productCategory !== category) {
                match = false;
            }
            
            if (brand !== 'all' && productBrand !== brand) {
                match = false;
            }
            
            product.style.display = match ? '' : 'none';
        }
    }
    
    if (searchInput) searchInput.addEventListener('keyup', filtrarProductos);
    if (categoryFilter) categoryFilter.addEventListener('change', filtrarProductos);
    if (brandFilter) brandFilter.addEventListener('change', filtrarProductos);
    
    var buscarInput = document.getElementById('buscarVentaDevolucion');
    if (buscarInput) {
        buscarInput.addEventListener('input', function() {
            paginaActualDevolucion = 1;
            cargarVentasParaDevolucion();
        });
    }
    
    console.log('POS JS inicializado correctamente');
});

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
                <div class="dropdown-item" style="margin-bottom: 10px; border: 1px solid #eee; border-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                        <div>
                            <div style="font-weight: bold; color: var(--primary-dark);">Venta #${venta.id_venta}</div>
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
        html += `<button class="btn-qty" onclick="irPaginaDevolucion(${i})" style="padding: 5px 10px; ${i === paginaActualDevolucion ? 'background: var(--primary-dark);' : ''}">${i}</button>`;
    }
    paginacionDiv.innerHTML = html;
}

function irPaginaDevolucion(pagina) {
    paginaActualDevolucion = pagina;
    cargarVentasParaDevolucion();
}

async function seleccionarVentaParaDevolucion(idVenta) {
    cerrarModalDevolucion('modalSeleccionVentaDevolucion');
    
    mostrarNotificacion('Cargando productos de la venta...', 'info');
    
    try {
        var resp = await fetch('/ElZapato/src/api/obtener_detalle_venta_devolucion.php?id=' + idVenta);
        var data = await resp.json();
        
        if (data.error) {
            mostrarNotificacion(data.error, 'warning');
            return;
        }
        
        if (!data.detalles || data.detalles.length === 0) {
            mostrarNotificacion('No se encontraron productos en esta venta', 'warning');
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
        
        mostrarModalProductosDevolucion();
        
    } catch (error) {
        console.error('Error al cargar detalles:', error);
        mostrarNotificacion('Error al cargar los productos', 'warning');
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
                            <span>Talla: ${p.talla || 'N/A'}</span>
                            <span>Color: ${p.color || 'N/A'}</span>
                        </div>
                        <div class="producto-devolucion-cantidad">
                            Vendido: ${p.cantidad_original} unidades | Precio: $${p.precio_unitario.toFixed(2)}
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
        mostrarNotificacion('Seleccione al menos un producto para devolver', 'warning');
        return;
    }
    
    var confirmacion = confirm('¿Está seguro de procesar esta devolución?\nSe reincorporará el stock y se ajustará el total de la venta.');
    
    if (!confirmacion) return;
    
    mostrarNotificacion('Procesando devolución...', 'info');
    
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
            mostrarNotificacion('Devolución procesada correctamente', 'success');
            
            var resumen = 'Devolución realizada:\n';
            for (var j = 0; j < data.productos_devueltos.length; j++) {
                var prod = data.productos_devueltos[j];
                resumen += '\n• ' + prod.nombre + ': ' + prod.cantidad + ' unidades ($' + prod.total.toFixed(2) + ')';
            }
            resumen += '\n\nTotal devuelto: $' + data.total_devuelto.toFixed(2);
            alert(resumen);
            
            cerrarModalDevolucion('modalSeleccionProductosDevolucion');
            
            setTimeout(function() {
                location.reload();
            }, 1500);
            
        } else {
            mostrarNotificacion('Error: ' + (data.error || 'No se pudo procesar la devolución'), 'warning');
        }
        
    } catch (error) {
        console.error('Error al procesar devolución:', error);
        mostrarNotificacion('Error al procesar la devolución', 'warning');
    }
}