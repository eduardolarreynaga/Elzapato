// Datos de ejemplo basados en la estructura de la base de datos
const productos = [
    {
        id_producto: 1,
        nombre_producto: "Tenis Deportivo",
        descripcion: "Tenis para correr, color blanco",
        marca: "Nike",
        categoria: "Deportivo",
        variantes: [
            { talla: "40", color: "Blanco", codigo_barras: "123456789", precio_venta: 60.00, stock: 15 },
            { talla: "41", color: "Blanco", codigo_barras: "123456790", precio_venta: 60.00, stock: 10 },
            { talla: "42", color: "Negro", codigo_barras: "123456791", precio_venta: 62.00, stock: 8 }
        ]
    },
    {
        id_producto: 2,
        nombre_producto: "Zapato Casual",
        descripcion: "Zapato cómodo para diario, color negro",
        marca: "Flexi",
        categoria: "Casual",
        variantes: [
            { talla: "42", color: "Negro", codigo_barras: "223456789", precio_venta: 45.00, stock: 20 },
            { talla: "43", color: "Marrón", codigo_barras: "223456790", precio_venta: 47.00, stock: 12 }
        ]
    },
    {
        id_producto: 3,
        nombre_producto: "Botín Cuero",
        descripcion: "Botín de cuero genuino, color marrón",
        marca: "Cat",
        categoria: "Botas",
        variantes: [
            { talla: "39", color: "Marrón", codigo_barras: "323456789", precio_venta: 75.00, stock: 5 }
        ]
    },
    {
        id_producto: 4,
        nombre_producto: "Sandalia Playa",
        descripcion: "Sandalia cómoda para verano, color azul",
        marca: "Havaianas",
        categoria: "Sandalias",
        variantes: [
            { talla: "36", color: "Azul", codigo_barras: "423456789", precio_venta: 25.00, stock: 30 },
            { talla: "37", color: "Rosa", codigo_barras: "423456790", precio_venta: 25.00, stock: 25 }
        ]
    }
];

// Ejemplo de venta actual
const ventaActual = {
    id_usuario: 1,
    id_cliente: null,
    id_metodo_pago: 1,
    total_venta: 265.00,
    detalles: [
        { id_variante: 3, cantidad: 1, precio_unitario: 45.00, subtotal: 45.00 },
        { id_variante: 1, cantidad: 2, precio_unitario: 60.00, subtotal: 120.00 },
        { id_variante: 4, cantidad: 1, precio_unitario: 75.00, subtotal: 75.00 },
        { id_variante: 5, cantidad: 1, precio_unitario: 25.00, subtotal: 25.00 }
    ]
};

console.log("Datos de productos cargados:", productos);
console.log("Venta actual:", ventaActual);

const keyboardToggles = document.querySelectorAll('[data-toggle-keyboard]');

if (keyboardToggles.length > 0) {
    const syncKeyboardToggleState = function() {
        const hidden = document.body.classList.contains('keyboard-hidden');
        keyboardToggles.forEach(function(toggle) {
            toggle.classList.toggle('active', hidden);
        });
    };

    const toggleKeyboard = function() {
        document.body.classList.toggle('keyboard-hidden');
        syncKeyboardToggleState();
    };

    keyboardToggles.forEach(function(toggle) {
        toggle.addEventListener('click', toggleKeyboard);

        toggle.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                toggleKeyboard();
            }
        });
    });

    syncKeyboardToggleState();
}

function editProduct(button) {
    const row = button.closest('tr');
    const id = row.querySelector('.product-sku').textContent.replace('P','');
    const nombre = row.querySelector('.product-name').textContent;
    const precio = row.querySelector('td:nth-child(4)').textContent.replace('$','');
    const stock = row.querySelector('.stock-badge').textContent;

    // Llenar formulario
    document.getElementById('editId').value = id;
    document.getElementById('editNombre').value = nombre;
    document.getElementById('editPrecio').value = parseFloat(precio);
    document.getElementById('editStock').value = stock;

    // Mostrar modal
    document.getElementById('editProductModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editProductModal').style.display = 'none';
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('editProductModal');
    if (event.target == modal) modal.style.display = "none";
};

// Aquí podrías agregar funciones para:
// - Cargar productos en la interfaz
// - Agregar productos al ticket
// - Calcular totales
// - Procesar pagos