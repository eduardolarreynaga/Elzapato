<?php
require_once __DIR__ . '/../../config/auth.php';
if (!is_authenticated()) { header("Location: /ElZapato/src/views/public/login.php"); exit(); }

$rolActual = $_SESSION['rol'] ?? '';
if (!in_array($rolActual, ['cajero', 'admin'], true)) { header("Location: /ElZapato/src/views/layouts/menu-general.php"); exit(); }

require_once "../../../controller/productosController.php";
require_once "../../../model/ProductosModel.php";
require_once "../../../controller/categoriasController.php";
require_once "../../../model/CategoriasModel.php";

$productos = ProductosController::ctrMostrarProductos();
$categorias = CategoriasController::ctrMostrarCategorias();
$nombreUsuario = $_SESSION['usuario'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - El Zapato</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ElZapato/Assets/css/pos.css?v=<?php echo time(); ?>">
</head>
<body>

    <nav class="top-nav">
        <div class="user-info">
            <i class="fa-solid fa-circle-user"></i>
            <span><strong><?= htmlspecialchars($nombreUsuario) ?></strong> (<?= strtoupper($rolActual) ?>)</span>
        </div>
        <div class="brand-logo">SISTEMA DE VENTAS EL ZAPATO</div>
        <div class="nav-icons">
            <i class="fa-solid fa-house" title="Inicio" onclick="window.location.href='/ElZapato/src/views/layouts/menu-general.php'"></i>
            <i class="fa-solid fa-gear"></i>
            <i class="fa-solid fa-power-off" title="Salir" onclick="window.location.href='/ElZapato/Logout/salir.php'"></i>
        </div>
    </nav>

    <div class="main-layout">
        <aside class="sidebar-resumen">
            <h2>Resumen de venta</h2>
            
            <div class="tables-scroll-container">
                <div class="table-section">
                    <p class="table-title">Detalle de Venta</p>
                    <table class="ticket-table">
                        <thead>
                            <tr>
                                <th class="col-cant">Cant.</th>
                                <th class="col-prod">Producto</th>
                                <th class="col-subt">Subt.</th>
                            </tr>
                        </thead>
                        <tbody id="listaVenta"></tbody>
                    </table>
                </div>

                <div class="table-section discount-section">
                    <p class="table-title">Ahorro Aplicado</p>
                    <table class="ticket-table">
                        <thead>
                            <tr>
                                <th class="col-prod-desc">Producto</th>
                                <th class="col-icon-desc">Cant.</th>
                                <th class="col-price-desc">Ahorro</th>
                            </tr>
                        </thead>
                        <tbody id="listaDescuentos"></tbody>
                    </table>
                </div>
            </div>

            <div class="totals-section">
                <div class="total-row"><span>Subtotal:</span><span id="subTotal">$0.00</span></div>
                <div class="total-row"><span>Descuento:</span><span id="descuentoMonto" style="color:var(--nocolor)">-$0.00</span></div>
                <div class="total-row total-highlight">
                    <span class="total-big">Total:</span>
                    <span class="total-big" id="totalDisplay">$0.00</span>
                </div>
            </div>
            
            <div class="action-buttons-row">
                <button class="btn-action btn-discount" onclick="abrirModalDescuento()">
                    <i class="fa-solid fa-tag"></i> Descuento
                </button>
                <button class="btn-action btn-sell">
                    <i class="fa-solid fa-cart-shopping"></i> Vender
                </button>
            </div>
        </aside>

        <main class="content-area">
            <section class="filter-bar">
                <div class="search-container">
                    <i class="fa fa-search"></i>
                    <input type="text" id="productSearch" placeholder="¿Qué buscas hoy?">
                </div>
                
                <div class="select-group">
                    <div class="select-container">
                        <select id="categoryFilter">
                            <option value="all">Categorías</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nombre_categoria']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="select-container">
                        <select id="brandFilter">
                            <option value="all">Marcas</option>
                            <option value="nike">Nike</option>
                            <option value="adidas">Adidas</option>
                            <option value="puma">Puma</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="catalog-container">
                <div class="products-grid" id="productsGrid">
                    <?php foreach ($productos as $p): 
                        $info = $p['info_variantes'] ?? '';
                        $variantes = ($info != '') ? explode("||", $info) : [];

                        foreach ($variantes as $v):
                            $d = explode("|", $v);
                            
                            $v_talla  = $d[0] ?? '';
                            $v_color  = $d[1] ?? '';
                            $v_precio = (float)($d[2] ?? 0);
                            $v_stock  = (int)($d[3] ?? 0);
                            $v_id_v   = $d[6] ?? '0';
                            $v_estado = $d[5] ?? 'activo';

                            if($v_estado !== 'activo') continue;

                            // --- NUEVA LÓGICA DE ETIQUETAS (CONFIGURADA A 10) ---
                            if ($v_stock <= 0) {
                                $tagText = "Agotado";
                                $tagClass = "stock-agotado";
                            } elseif ($v_stock <= 10) {
                                $tagText = "Casi agotado";
                                $tagClass = "stock-agotandose";
                            } else {
                                $tagText = "Disponible";
                                $tagClass = "stock-disponible";
                            }
                            
                            $imagenFinal = "/ElZapato/Assets/img/zapa.jpeg"; 
                            $pathImg = "/Assets/img/productos/" . $v_id_v . ".jpg";
                            if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/ElZapato" . $pathImg)) {
                                $imagenFinal = "/ElZapato" . $pathImg;
                            }
                    ?>
                    <div class="product-card" 
                        data-id="<?= $v_id_v ?>" 
                        data-price="<?= $v_precio ?>" 
                        data-stock="<?= $v_stock ?>" 
                        data-nombre="<?= htmlspecialchars($p['nombre_producto']) ?>">
                        
                        <span class="stock-tag <?= $tagClass ?>">
                            <?= $tagText ?>: <?= $v_stock ?>
                        </span>
                        
                        <div class="switch-top">
                            <label class="switch">
                                <input type="checkbox" onchange="toggleProductoVenta(this, '<?= $v_id_v ?>')" <?= ($v_stock <= 0 ? 'disabled' : '') ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <div class="product-img" style="background-image: url('<?= $imagenFinal ?>');"></div>
                        
                        <div class="product-info">
                            <p class="product-name"><?= htmlspecialchars($p['nombre_producto']) ?></p>
                            <span class="product-price">$<?= number_format($v_precio, 2) ?></span>
                            
                            <div class="footer-controls">
                                <div class="quantity-controls">
                                    <button class="btn-qty" onclick="cambiarCantidad(this, -1)">-</button>
                                    <input type="number" class="qty-input" value="0" readonly>
                                    <button class="btn-qty" onclick="cambiarCantidad(this, 1)">+</button>
                                </div>
                                <div class="view-details-inline" onclick="abrirModalDetalle('<?= $v_id_v ?>', '<?= htmlspecialchars($p['nombre_producto']) ?>', '<?= $v_precio ?>', '<?= $v_stock ?>', '<?= $v_color ?>', '<?= $v_talla ?>')">
                                    <i class="fa-solid fa-eye"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; endforeach; ?>
                </div>
            </section>
        </main>
    </div>

    <div id="modalDescuento" class="modal">
        <div class="modal-content" style="width: 350px;">
            <span class="close-modal" onclick="cerrarModal('modalDescuento')">&times;</span>
            <h3 style="margin-bottom:20px; color:var(--primary-dark)">Aplicar Descuento</h3>
            <div class="form-group">
                <label><i class="fa-solid fa-box"></i> 1. Seleccionar Producto:</label>
                <select id="descProductoSelect" class="input-modal" onchange="actualizarMaxCantDesc()"></select>
            </div>
            <div class="form-group">
                <label><i class="fa-solid fa-layer-group"></i> 2. ¿A cuántos aplica?:</label>
                <input type="number" id="descCantAplicar" class="input-modal" value="1" min="1">
                <small id="maxCantDescInfo" style="font-size: 0.7rem; color: #888;"></small>
            </div>
            <div class="form-group">
                <label><i class="fa-solid fa-percent"></i> 3. Porcentaje de ahorro:</label>
                <input type="number" id="descPorcentajeInput" class="input-modal" placeholder="Ej: 10" min="1" max="100">
            </div>
            <button class="btn-action btn-sell" style="width:100%; margin-top:10px;" onclick="confirmarDescuento()">
                GUARDAR DESCUENTO
            </button>
        </div>
    </div>

    <div id="modalDetalle" class="modal">
        <div class="modal-content" style="width: 400px;">
            <span class="close-modal" onclick="cerrarModal('modalDetalle')">&times;</span>
            <h3 id="detNombre" style="color:var(--primary-dark); margin-bottom:15px; text-transform:uppercase; font-size:1.1rem;"></h3>
            <div style="display: flex; gap: 15px; align-items: center;">
                <div id="detImg" style="width: 150px; height: 150px; background-size: contain; background-repeat: no-repeat; background-position: center; border: 1px solid var(--primary-light); border-radius: 8px; background-color: #fff;"></div>
                <div style="flex: 1;">
                    <p style="margin: 5px 0;"><strong>Color:</strong> <span id="detColor"></span></p>
                    <p style="margin: 5px 0;"><strong>Talla:</strong> <span id="detTalla"></span></p>
                    <p style="margin: 5px 0;"><strong>Precio:</strong> <span id="detPrecio" style="color:var(--nocolor); font-weight:bold;"></span></p>
                    <p style="margin: 5px 0;"><strong>Stock:</strong> <span id="detStock"></span> unidades</p>
                    <p style="margin: 5px 0; font-size: 0.75rem; color: #888;">ID Variante: <span id="detId"></span></p>
                </div>
            </div>
        </div>
    </div>

    <div id="toast-container"></div>

    <script>
        let carrito = [];
        let descuentosAplicados = []; 

        function mostrarNotificacion(mensaje, tipo = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast show toast-${tipo}`;
            let icono = tipo === 'warning' ? 'fa-triangle-exclamation' : (tipo === 'success' ? 'fa-circle-check' : 'fa-circle-info');
            toast.innerHTML = `<i class="fa-solid ${icono}"></i> <span>${mensaje}</span>`;
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.transform = "translateX(120%)";
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function abrirModalDetalle(id, nombre, precio, stock, color, talla) {
            document.getElementById('detNombre').innerText = nombre;
            document.getElementById('detPrecio').innerText = `$${parseFloat(precio).toFixed(2)}`;
            document.getElementById('detStock').innerText = stock;
            document.getElementById('detId').innerText = id;
            document.getElementById('detColor').innerText = color;
            document.getElementById('detTalla').innerText = talla;

            const card = document.querySelector(`.product-card[data-id="${id}"]`);
            const imgDiv = card.querySelector('.product-img');
            const imgUrl = window.getComputedStyle(imgDiv).backgroundImage;
            document.getElementById('detImg').style.backgroundImage = imgUrl;

            const modal = document.getElementById('modalDetalle');
            modal.style.display = "flex"; 
            setTimeout(() => modal.classList.add('active'), 10);
        }

        function abrirModalDescuento() {
            if (carrito.length === 0) {
                mostrarNotificacion("No hay productos seleccionados.", "warning");
                return;
            }
            const select = document.getElementById('descProductoSelect');
            select.innerHTML = carrito.map(p => `<option value="${p.id}">${p.nombre}</option>`).join('');
            actualizarMaxCantDesc();
            const modal = document.getElementById('modalDescuento');
            modal.style.display = "flex"; 
            setTimeout(() => modal.classList.add('active'), 10);
        }

        function cerrarModal(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('active');
            setTimeout(() => modal.style.display = "none", 300);
        }

        function cambiarCantidad(btn, valor) {
            const card = btn.closest('.product-card');
            const input = card.querySelector('.qty-input');
            const stockMax = parseInt(card.dataset.stock);
            let actual = parseInt(input.value);
            
            if (valor > 0 && actual >= stockMax) {
                mostrarNotificacion("Stock insuficiente.", "warning"); return;
            }
            input.value = Math.max(0, actual + valor);
            if(card.querySelector('input[type="checkbox"]').checked) {
                actualizarItemCarrito(card.dataset.id, parseInt(input.value));
            }
        }

        function toggleProductoVenta(checkbox, id) {
            const card = checkbox.closest('.product-card');
            const cantidad = parseInt(card.querySelector('.qty-input').value);
            const nombre = card.dataset.nombre;

            if (checkbox.checked) {
                if (cantidad <= 0) {
                    mostrarNotificacion("Indique una cantidad.", "warning");
                    checkbox.checked = false; return;
                }
                carrito.push({ 
                    id, nombre, cantidad, 
                    precio: parseFloat(card.dataset.price), 
                    subtotal: cantidad * parseFloat(card.dataset.price) 
                });
                mostrarNotificacion(`${nombre} agregado`, "success");
            } else {
                carrito = carrito.filter(p => p.id !== id);
                descuentosAplicados = descuentosAplicados.filter(d => d.id !== id);
                mostrarNotificacion(`${nombre} quitado`, "info");
            }
            actualizarTablaResumen();
        }

        function actualizarItemCarrito(id, nuevaCant) {
            const index = carrito.findIndex(p => p.id === id);
            if (index !== -1) {
                if(nuevaCant <= 0) {
                    carrito.splice(index, 1);
                    descuentosAplicados = descuentosAplicados.filter(d => d.id !== id);
                    document.querySelector(`.product-card[data-id="${id}"] input[type="checkbox"]`).checked = false;
                } else {
                    carrito[index].cantidad = nuevaCant;
                    carrito[index].subtotal = nuevaCant * carrito[index].precio;
                    
                    let dIndex = descuentosAplicados.findIndex(d => d.id === id);
                    if(dIndex !== -1) {
                        if(descuentosAplicados[dIndex].cantAplicada > nuevaCant) {
                            descuentosAplicados[dIndex].cantAplicada = nuevaCant;
                        }
                        descuentosAplicados[dIndex].ahorroTotal = (carrito[index].precio * descuentosAplicados[dIndex].cantAplicada) * (descuentosAplicados[dIndex].porcentaje / 100);
                    }
                }
                actualizarTablaResumen();
            }
        }

        function actualizarTablaResumen() {
            const tVenta = document.getElementById('listaVenta');
            const tDesc = document.getElementById('listaDescuentos');
            tVenta.innerHTML = ""; tDesc.innerHTML = "";
            let subtotalGlobal = 0;
            let descuentoGlobal = 0;

            carrito.forEach(p => {
                subtotalGlobal += p.subtotal;
                tVenta.innerHTML += `<tr><td class="col-cant">${p.cantidad}</td><td class="col-prod">${p.nombre}</td><td class="col-subt">$${p.subtotal.toFixed(2)}</td></tr>`;
            });

            descuentosAplicados.forEach(d => {
                descuentoGlobal += d.ahorroTotal;
                tDesc.innerHTML += `<tr><td class="col-prod-desc">${d.nombre}</td><td class="col-icon-desc">${d.cantAplicada}</td><td class="col-price-desc">-$${d.ahorroTotal.toFixed(2)}</td></tr>`;
            });

            document.getElementById('subTotal').innerText = `$${subtotalGlobal.toFixed(2)}`;
            document.getElementById('descuentoMonto').innerText = `-$${descuentoGlobal.toFixed(2)}`;
            document.getElementById('totalDisplay').innerText = `$${(subtotalGlobal - descuentoGlobal).toFixed(2)}`;
        }

        function actualizarMaxCantDesc() {
            const id = document.getElementById('descProductoSelect').value;
            const producto = carrito.find(p => p.id === id);
            if(producto) {
                document.getElementById('descCantAplicar').max = producto.cantidad;
                document.getElementById('maxCantDescInfo').innerText = `Máximo: ${producto.cantidad} unidades.`;
            }
        }

        function confirmarDescuento() {
            const id = document.getElementById('descProductoSelect').value;
            const cantDesc = parseInt(document.getElementById('descCantAplicar').value);
            const porcentaje = parseFloat(document.getElementById('descPorcentajeInput').value);
            const producto = carrito.find(p => p.id === id);

            if (!porcentaje || porcentaje <= 0 || porcentaje > 100) {
                mostrarNotificacion("Porcentaje inválido.", "warning"); return;
            }
            if (cantDesc <= 0 || cantDesc > producto.cantidad) {
                mostrarNotificacion("Cantidad no válida.", "warning"); return;
            }

            const ahorro = (producto.precio * cantDesc) * (porcentaje / 100);
            descuentosAplicados = descuentosAplicados.filter(d => d.id !== id);
            descuentosAplicados.push({ id, nombre: producto.nombre, ahorroTotal: ahorro, porcentaje, cantAplicada: cantDesc });

            cerrarModal('modalDescuento');
            mostrarNotificacion("Descuento aplicado", "success");
            actualizarTablaResumen();
        }
    </script>
</body>
</html>