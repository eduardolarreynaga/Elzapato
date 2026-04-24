// ==========================================
// 1. ESTADO GLOBAL DEL CARRITO
// ==========================================
let carrito = [];

// ==========================================
// 2. LÓGICA DEL SISTEMA POS (VENTAS)
// ==========================================

/**
 * Agrega un producto al array del carrito o incrementa su cantidad
 */
function agregarAlCarrito(id, nombre, precio) {
    // Buscamos si el producto ya está en la lista actual
    const existente = carrito.find(item => item.id === id);

    if (existente) {
        existente.cantidad++;
        existente.subtotal = existente.cantidad * existente.precio;
    } else {
        // Si es nuevo, lo empujamos al array
        carrito.push({
            id: id,
            nombre: nombre,
            precio: precio,
            cantidad: 1,
            subtotal: precio
        });
    }
    // Refrescamos la tabla visualmente
    renderizarTablaPOS();
}

/**
 * Dibuja las filas en el tbody de la tabla del ticket
 */
function renderizarTablaPOS() {
    const tbody = document.querySelector('#ticketTable tbody');
    const totalDiv = document.getElementById('totalDisplay');

    if (!tbody) return; // Si no estamos en la vista POS, no hace nada

    // Limpiamos la tabla para que no se dupliquen las filas antiguas
    tbody.innerHTML = '';
    let totalGeneral = 0;

    carrito.forEach((item, index) => {
        totalGeneral += item.subtotal;

        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td class="ticket-qty" data-index="${index}" title="Clic para restar una unidad" style="cursor:pointer; user-select:none;">x${item.cantidad}</td>
            <td>${item.nombre}</td>
            <td>$${item.precio.toFixed(2)}</td>
            <td>$${item.subtotal.toFixed(2)}</td>
            <td style="width: 30px; text-align: center;">
                <i class="fas fa-trash" style="color:#AB886D; cursor:pointer;" onclick="eliminarDelCarrito(${index})"></i>
            </td>
        `;
        tbody.appendChild(fila);
    });

    // Actualizamos el texto del total en el cuadro azul/gris
    if (totalDiv) {
        totalDiv.innerText = `Total: $${totalGeneral.toFixed(2)}`;
    }
}

/**
 * Elimina una línea completa del carrito
 */
window.eliminarDelCarrito = function(index) {
    carrito.splice(index, 1);
    renderizarTablaPOS();
};

/**
 * Resta 1 unidad de una línea del carrito.
 * Si llega a 0, elimina el producto del ticket.
 */
window.restarCantidadDelCarrito = function(index) {
    const item = carrito[index];
    if (!item) return;

    if (item.cantidad > 1) {
        item.cantidad--;
        item.subtotal = item.cantidad * item.precio;
    } else {
        carrito.splice(index, 1);
    }

    renderizarTablaPOS();
};

/**
 * Limpia todo el carrito (Botón Reiniciar/Anular)
 */
window.resetVenta = function() {
    if (carrito.length > 0) {
        if (confirm('¿Desea vaciar el ticket de venta actual?')) {
            carrito = [];
            renderizarTablaPOS();
        }
    } else {
        alert("El ticket ya está vacío.");
    }
};

// ==========================================
// 3. EVENTOS Y MANEJO DE INTERFAZ
// ==========================================

document.addEventListener('DOMContentLoaded', () => {

    // --- CLIC EN CANTIDAD DEL TICKET (RESTA 1) ---
    const ticketBody = document.querySelector('#ticketTable tbody');
    if (ticketBody) {
        ticketBody.addEventListener('click', function(e) {
            const qtyCell = e.target.closest('.ticket-qty');
            if (!qtyCell) return;

            const index = parseInt(qtyCell.getAttribute('data-index'), 10);
            if (!Number.isNaN(index)) {
                window.restarCantidadDelCarrito(index);
            }
        });
    }

    // --- DETECTAR CLIC EN PRODUCTOS ---
    document.addEventListener('click', function(e) {
        const productCard = e.target.closest('.product-item');
        if (productCard) {
            const id = productCard.getAttribute('data-id');
            const nombre = productCard.querySelector('.item').innerText;
            const precio = parseFloat(productCard.getAttribute('data-price'));
            
            agregarAlCarrito(id, nombre, precio);
        }
    });

    // --- MANEJO DEL TECLADO (MOSTRAR/OCULTAR) ---
    const keyboardToggles = document.querySelectorAll('[data-toggle-keyboard]');
    if (keyboardToggles.length > 0) {
        const syncKeyboardToggleState = function() {
            const hidden = document.body.classList.contains('keyboard-hidden');
            keyboardToggles.forEach(t => t.classList.toggle('active', hidden));
        };

        const toggleKeyboard = function() {
            document.body.classList.toggle('keyboard-hidden');
            syncKeyboardToggleState();
        };

        keyboardToggles.forEach(toggle => {
            toggle.addEventListener('click', toggleKeyboard);
        });
        syncKeyboardToggleState();
    }

    // --- BOTÓN ANULAR ---
    const btnAnular = document.querySelector('.exit-btn');
    if (btnAnular) {
        btnAnular.addEventListener('click', window.resetVenta);
    }
});

// ==========================================
// 4. FUNCIONES DE MANTENIMIENTO (MODALES)
// ==========================================
function editProduct(button) {
    const row = button.closest('tr');
    const id = row.querySelector('.product-sku').textContent.replace('P','');
    const nombre = row.querySelector('.product-name').textContent;
    const precio = row.querySelector('td:nth-child(4)').textContent.replace('$','');
    const stock = row.querySelector('.stock-badge').textContent;

    if(document.getElementById('editId')){
        document.getElementById('editId').value = id;
        document.getElementById('editNombre').value = nombre;
        document.getElementById('editPrecio').value = parseFloat(precio);
        document.getElementById('editStock').value = stock;
        document.getElementById('editProductModal').style.display = 'block';
    }
}

function closeEditModal() {
    const modal = document.getElementById('editProductModal');
    if(modal) modal.style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('editProductModal');
    if (event.target == modal) modal.style.display = "none";
};