// ==================== VARIABLES GLOBALES ====================
let carrito = [];
let descuentosAplicados = [];

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
        
        var resp = await fetch('/Elzapato/src/api/obtener_ventas.php');
        
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

// FUNCIÓN MODIFICADA: Imprimir ticket de venta
function imprimirTicketVenta(idVenta, event) {
    if (event) {
        event.stopPropagation();
    }
    
    // Cerrar el dropdown de notificaciones
    var dropdown = document.getElementById('notificationDropdown');
    if (dropdown) {
        dropdown.classList.remove('active');
    }
    
    // Abrir el ticket en una nueva pestaña
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
        
        var resp = await fetch('/Elzapato/src/api/obtener_detalle_venta.php?id=' + idVenta);
        
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
        
        var ventaResp = await fetch('/Elzapato/src/api/obtener_info_venta.php?id=' + idVenta);
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
    
    infoBoxHTML += `
            </div>
        </div>
    `;
    
    var tablaHTML = '<table style="width:100%; border-collapse: collapse; margin-bottom: 15px;">';
    tablaHTML += '<thead><tr style="background: var(--primary-light); border-bottom: 2px solid var(--primary-dark);">';
    tablaHTML += '<th style="padding: 10px; text-align: left;">Cant.</th>';
    tablaHTML += '<th style="padding: 10px; text-align: left;">Producto</th>';
    tablaHTML += '<th style="padding: 10px; text-align: right;">Precio Unit.</th>';
    tablaHTML += '<th style="padding: 10px; text-align: right;">Descuento</th>';
    tablaHTML += '<th style="padding: 10px; text-align: right;">Subtotal</th>';
    tablaHTML += '</tr></thead><tbody>';
    
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
    tablaHTML += '</tr></tfoot></table>';
    
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

document.addEventListener('click', function(event) {
    var container = document.querySelector('.notification-container');
    var dropdown = document.getElementById('notificationDropdown');
    
    if (container && !container.contains(event.target)) {
        if (dropdown && dropdown.classList.contains('active')) {
            dropdown.classList.remove('active');
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

function cerrarModal(id) {
    var modal = document.getElementById(id);
    if (!modal) return;
    
    modal.classList.remove('active');
    setTimeout(function() { modal.style.display = 'none'; }, 300);
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

// ==================== CANTIDADES (MODIFICADA) ====================
function cambiarCantidad(btn, valor) {
    var card = btn.closest('.product-card');
    if (!card) return;
    
    var input = card.querySelector('.qty-input');
    var stockMax = parseInt(card.dataset.stock);
    var actual = parseInt(input.value);
    
    // Verificar si el producto está agotado (stock <= 0)
    if (stockMax <= 0) {
        mostrarNotificacion('Producto agotado, no se puede seleccionar.', 'warning');
        return;
    }
    
    if (valor > 0 && actual >= stockMax) {
        mostrarNotificacion('Stock insuficiente. Stock disponible: ' + stockMax, 'warning');
        return;
    }
    
    var nuevaCantidad = Math.max(0, actual + valor);
    
    // No permitir superar el stock máximo
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

    // Verificar si el producto está agotado
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
        
        // Verificar que no exceda el stock disponible
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
        
        // Validar que no exceda el stock
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
    tDesc.innerHTML = '';
    
    var subtotalGlobal = 0;
    var descuentoGlobal = 0;

    for (var i = 0; i < carrito.length; i++) {
        var p = carrito[i];
        subtotalGlobal += p.subtotal;
        tVenta.innerHTML += '<tr>' +
            '<td class="col-cant">' + p.cantidad + '</td>' +
            '<td class="col-prod">' + p.nombre + '</td>' +
            '<td class="col-subt">$' + p.subtotal.toFixed(2) + '</td>' +
            '</tr>';
    }

    if (carrito.length === 0) {
        tVenta.innerHTML = '<tr class="empty-row"><td colspan="3" style="text-align: center; color: #999;">No hay productos seleccionados</td></tr>';
    }

    for (var j = 0; j < descuentosAplicados.length; j++) {
        var d = descuentosAplicados[j];
        descuentoGlobal += d.ahorroTotal;
        tDesc.innerHTML += '<tr>' +
            '<td class="col-prod-desc">' + d.nombre + '</td>' +
            '<td class="col-icon-desc">' + d.cantAplicada + '</td>' +
            '<td class="col-price-desc">-$' + d.ahorroTotal.toFixed(2) + '</td>' +
            '</tr>';
    }

    if (descuentosAplicados.length === 0) {
        tDesc.innerHTML = '<tr class="empty-row"><td colspan="3" style="text-align: center; color: #999;">Sin descuentos aplicados</td></tr>';
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
    document.getElementById('cambioDisplay').innerHTML = '<span style="color: var(--success);">$0.00</span>';
    document.getElementById('metodoPago').value = '1';
    
    var modal = document.getElementById('modalPago');
    modal.style.display = 'flex';
    setTimeout(function() { modal.classList.add('active'); }, 10);
    
    var dineroInput = document.getElementById('dineroRecibido');
    dineroInput.oninput = function() {
        calcularCambio(total);
    };
}

function calcularCambio(total) {
    var dineroRecibido = parseFloat(document.getElementById('dineroRecibido').value) || 0;
    var cambio = dineroRecibido - total;
    var cambioDisplay = document.getElementById('cambioDisplay');
    
    if (cambio >= 0) {
        cambioDisplay.innerHTML = '<span style="color: var(--success);">$' + cambio.toFixed(2) + '</span>';
    } else {
        cambioDisplay.innerHTML = '<span style="color: var(--nocolor);">Falta $' + Math.abs(cambio).toFixed(2) + '</span>';
    }
}

async function confirmarPago() {
    var totalTexto = document.getElementById('modalTotalPago').innerText;
    var total = parseFloat(totalTexto.replace('$', ''));
    var dineroRecibido = parseFloat(document.getElementById('dineroRecibido').value) || 0;
    var metodoPago = document.getElementById('metodoPago').value;
    var metodoPagoTexto = document.getElementById('metodoPago').options[document.getElementById('metodoPago').selectedIndex].text;
    
    if (dineroRecibido < total) {
        mostrarNotificacion('El dinero recibido es insuficiente', 'warning');
        return;
    }
    
    // VERIFICAR STOCK ANTES DE PROCESAR LA VENTA
    mostrarNotificacion('Verificando stock disponible...', 'info');
    
    // Verificar cada producto en el carrito contra el stock actual
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
    
    var cambio = dineroRecibido - total;
    
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
        descuentos: descuentosAplicados
    };
    
    try {
        var response = await fetch('/Elzapato/src/api/guardar_venta.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(ventaData)
        });
        
        var result = await response.json();
        
        if (result.success) {
            mostrarNotificacion('Venta #' + result.id_venta + ' realizada con éxito. Cambio: $' + cambio.toFixed(2), 'success');
            
            sessionStorage.setItem('ventaData_' + result.id_venta, JSON.stringify({
                cambio: cambio,
                dineroRecibido: dineroRecibido,
                metodoPago: metodoPagoTexto,
                total: total,
                descuentos: descuentosAplicados
            }));
            
            // Actualizar los stocks en las tarjetas de productos
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
            
        } else {
            mostrarNotificacion('Error: ' + (result.error || 'No se pudo guardar la venta'), 'warning');
        }
        
    } catch (error) {
        console.error('Error al guardar venta:', error);
        mostrarNotificacion('Error al procesar la venta', 'warning');
    }
}

// ==================== ACTUALIZAR STOCKS LOCALMENTE (NUEVA FUNCIÓN) ====================
function actualizarStocksLocales() {
    // Recargar la página para reflejar los cambios de stock
    // Esto asegura que los productos agotados se marquen correctamente
    location.reload();
}

// ==================== FILTROS ====================
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('productSearch');
    var categoryFilter = document.getElementById('categoryFilter');
    var brandFilter = document.getElementById('brandFilter');
    
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
    
    console.log('POS JS inicializado correctamente');
});