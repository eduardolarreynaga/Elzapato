<?php
require_once __DIR__ . '/../../config/auth.php';
if (!is_authenticated()) { header("Location: /ElZapato/src/views/public/login.php"); exit(); }

// Obtener el nombre del sistema desde la configuración
$nombreSistema = defined('SYSTEM_NAME') ? SYSTEM_NAME : 'ElZapato';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
   <title>POS - <?= htmlspecialchars($nombreSistema) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ElZapato/Assets/css/pos.css?v=<?php echo time(); ?>">
</head>
<body>

    <nav class="top-nav">
        <div class="user-info">
            <i class="fa-solid fa-circle-user"></i>
            <span><strong><?= htmlspecialchars($nombreUsuario) ?></strong> (<?= strtoupper($rolActual) ?>)</span>
        </div>
        <div class="brand-logo">SISTEMA DE VENTAS <?= strtoupper(htmlspecialchars($nombreSistema)) ?></div>
        <div class="nav-icons">
            <i class="fa-solid fa-house" title="Inicio" onclick="goMenuGeneralTransition()"></i>
            <div class="notification-container">
                <i class="fa-solid fa-bell" id="bellIcon" onclick="toggleNotifications()"></i>
                <span class="badge" id="notificationBadge">0</span>
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="dropdown-header">
                        <i class="fa-solid fa-clock-rotate-left"></i> Últimas Ventas
                    </div>
                    <div class="dropdown-body" id="recentSalesList">
                        <div class="loading-text">
                            <i class="fa-solid fa-spinner fa-pulse"></i> Cargando ventas...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="main-layout">
        <aside class="sidebar-resumen">
            <h2>
                <i class="fa-solid fa-receipt"></i> Resumen de venta
            </h2>
            
            <div class="tables-scroll-container">
                <div class="table-section">
                    <p class="table-title">
                        <i class="fa-solid fa-cart-shopping"></i> Detalle de Venta
                    </p>
                    <table class="ticket-table">
                        <thead>
                            <tr>
                                <th class="col-cant">Cant.</th>
                                <th class="col-prod">Producto</th>
                                <th class="col-subt">Subt.</th>
                            </tr>
                        </thead>
                        <tbody id="listaVenta">
                            <tr class="empty-row">
                                <td colspan="3" style="text-align: center; color: #999;">
                                    No hay productos seleccionados
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-section discount-section">
                    <p class="table-title">
                        <i class="fa-solid fa-tag"></i> Ahorro Aplicado
                    </p>
                    <table class="ticket-table">
                        <thead>
                            <tr>
                                <th class="col-prod-desc">Producto</th>
                                <th class="col-icon-desc">Cant.</th>
                                <th class="col-price-desc">Ahorro</th>
                            </tr>
                        </thead>
                        <tbody id="listaDescuentos">
                            <tr class="empty-row">
                                <td colspan="3" style="text-align: center; color: #999;">
                                    Sin descuentos aplicados
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="totals-section">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span id="subTotal">$0.00</span>
                </div>
                <div class="total-row">
                    <span>Descuento:</span>
                    <span id="descuentoMonto" style="color:var(--nocolor)">-$0.00</span>
                </div>
                <div class="total-row total-highlight">
                    <span class="total-big">
                        <i class="fa-solid fa-calculator"></i> Total:
                    </span>
                    <span class="total-big" id="totalDisplay">$0.00</span>
                </div>
            </div>
            
            <div class="action-buttons-row">
                <button class="btn-action btn-discount" onclick="abrirModalDescuento()">
                    <i class="fa-solid fa-tag"></i> Descuento
                </button>
                <button class="btn-action btn-sell" onclick="realizarVenta()">
                    <i class="fa-solid fa-cart-shopping"></i> Vender
                </button>
            </div>
        </aside>

        <main class="content-area">
            <section class="filter-bar">
                <div class="search-container">
                    <i class="fa fa-search"></i>
                    <input type="text" id="productSearch" placeholder="Buscar por nombre, categoría o marca...">
                </div>
                
                <div class="select-group">
                    <div class="select-container">
                        <select id="categoryFilter">
                            <option value="all">Todas las categorías</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nombre_categoria']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="select-container">
                        <select id="brandFilter">
                            <option value="all">Todas las marcas</option>
                            <option value="nike">Nike</option>
                            <option value="adidas">Adidas</option>
                            <option value="puma">Puma</option>
                            <option value="reebok">Reebok</option>
                            <option value="converse">Converse</option>
                            <option value="vans">Vans</option>
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

                            if ($v_stock <= 0) {
                                $tagText = "Agotado";
                                $tagClass = "stock-agotado";
                            } elseif ($v_stock <= 10) {
                                $tagText = "¡Últimas unidades!";
                                $tagClass = "stock-agotandose";
                            } else {
                                $tagText = "Disponible";
                                $tagClass = "stock-disponible";
                            }
                            
                            $imagenFinal = "/ElZapato/Assets/img/zapa.jpeg"; 
                            $pathImg = "/Assets/img/productos/" . $v_id_v . ".jpg";
                            $fullPath = __DIR__ . '/../../../Assets/img/productos/' . $v_id_v . '.jpg';
                            if (file_exists($fullPath)) {
                                $imagenFinal = "/ElZapato" . $pathImg;
                            }

                            $tallaTexto = trim((string)$v_talla);
                            $tallaEtiqueta = ($tallaTexto !== '')
                                ? (preg_match('/^t/i', $tallaTexto) ? strtoupper($tallaTexto) : ('T' . strtoupper($tallaTexto)))
                                : '';
                    ?>
                    <div class="product-card <?= ($v_stock <= 0) ? 'agotado' : '' ?>" 
                        data-id="<?= $v_id_v ?>" 
                        data-price="<?= $v_precio ?>" 
                        data-stock="<?= $v_stock ?>" 
                        data-nombre="<?= htmlspecialchars($p['nombre_producto']) ?>"
                        data-talla="<?= htmlspecialchars($v_talla) ?>"
                        data-color="<?= htmlspecialchars($v_color) ?>"
                        data-categoria="<?= $p['id_categoria'] ?? '' ?>"
                        data-marca="<?= strtolower($p['marca'] ?? '') ?>">
                        
                        <span class="stock-tag <?= $tagClass ?>">
                            <?= $tagText ?> (<?= $v_stock ?>)
                        </span>
                        
                        <div class="switch-top">
                            <label class="switch">
                                <input type="checkbox" onchange="toggleProductoVenta(this, '<?= $v_id_v ?>')" <?= ($v_stock <= 0 ? 'disabled' : '') ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <div class="product-img" style="background-image: url('<?= $imagenFinal ?>');">
                            <?php if ($tallaEtiqueta !== ''): ?>
                                <span class="talla-badge"><?= htmlspecialchars($tallaEtiqueta) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-info">
                            <p class="product-name"><?= htmlspecialchars($p['nombre_producto']) ?></p>
                            <span class="product-price">$<?= number_format($v_precio, 2) ?></span>
                            
                            <div class="footer-controls">
                                <div class="quantity-controls">
                                    <button class="btn-qty" onclick="cambiarCantidad(this, -1)">-</button>
                                    <input type="number" class="qty-input" value="0" readonly>
                                    <button class="btn-qty" onclick="cambiarCantidad(this, 1)">+</button>
                                </div>
                                <div class="view-details-inline" onclick="abrirModalDetalle('<?= $v_id_v ?>', '<?= htmlspecialchars($p['nombre_producto']) ?>', '<?= $v_precio ?>', '<?= $v_stock ?>', '<?= htmlspecialchars($v_color) ?>', '<?= $v_talla ?>')">
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

    <!-- Modal de Descuento -->
    <div id="modalDescuento" class="modal">
        <div class="modal-content" style="width: 350px;">
            <span class="close-modal" onclick="cerrarModal('modalDescuento')">&times;</span>
            <h3 style="margin-bottom:20px; color:var(--primary-dark)">
                <i class="fa-solid fa-percent"></i> Aplicar Descuento
            </h3>
            <div class="form-group">
                <label><i class="fa-solid fa-box"></i> 1. Seleccionar Producto:</label>
                <select id="descProductoSelect" class="input-modal" onchange="actualizarMaxCantDesc()">
                    <option value="">Cargando productos...</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fa-solid fa-layer-group"></i> 2. ¿A cuántos aplica?:</label>
                <input type="number" id="descCantAplicar" class="input-modal" value="1" min="1">
                <small id="maxCantDescInfo" style="font-size: 0.7rem; color: #888; display: block; margin-top: 5px;"></small>
            </div>
            <div class="form-group">
                <label><i class="fa-solid fa-percent"></i> 3. Porcentaje de ahorro:</label>
                <select id="descPorcentajeInput" class="input-modal">
                    <option value="">Seleccione porcentaje</option>
                    <option value="5">5%</option>
                    <option value="10">10%</option>
                    <option value="15">15%</option>
                    <option value="20">20%</option>
                    <option value="25">25%</option>
                    <option value="30">30%</option>
                    <option value="35">35%</option>
                    <option value="40">40%</option>
                    <option value="45">45%</option>
                    <option value="50">50%</option>
                </select>
            </div>
            <button class="btn-action btn-sell" style="width:100%; margin-top:10px;" onclick="confirmarDescuento()">
                <i class="fa-solid fa-check"></i> GUARDAR DESCUENTO
            </button>
        </div>
    </div>

    <!-- Modal de Detalle de Producto -->
    <div id="modalDetalle" class="modal">
        <div class="modal-content" style="width: 450px;">
            <span class="close-modal" onclick="cerrarModal('modalDetalle')">&times;</span>
            <h3 id="detNombre" style="color:var(--primary-dark); margin-bottom:15px; text-transform:uppercase; font-size:1.1rem;"></h3>
            <div style="display: flex; gap: 20px; align-items: flex-start;">
                <div id="detImg" style="width: 150px; height: 150px; background-size: contain; background-repeat: no-repeat; background-position: center; border: 1px solid var(--primary-light); border-radius: 8px; background-color: #fff;"></div>
                <div style="flex: 1;">
                    <p style="margin: 8px 0;">
                        <strong><i class="fa-solid fa-palette"></i> Color:</strong> 
                        <span id="detColor"></span>
                    </p>
                    <p style="margin: 8px 0;">
                        <strong><i class="fa-solid fa-ruler"></i> Talla:</strong> 
                        <span id="detTalla"></span>
                    </p>
                    <p style="margin: 8px 0;">
                        <strong><i class="fa-solid fa-dollar-sign"></i> Precio:</strong> 
                        <span id="detPrecio" style="color:var(--nocolor); font-weight:bold;"></span>
                    </p>
                    <p style="margin: 8px 0;">
                        <strong><i class="fa-solid fa-boxes"></i> Stock:</strong> 
                        <span id="detStock"></span> unidades
                    </p>
                    <p style="margin: 8px 0; font-size: 0.7rem; color: #888;">
                        <strong>ID Variante:</strong> 
                        <span id="detId"></span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Pago -->
    <div id="modalPago" class="modal">
        <div class="modal-content" style="width: 450px;">
            <span class="close-modal" onclick="cerrarModal('modalPago')">&times;</span>
            <h3 style="margin-bottom:20px; color:var(--primary-dark)">
                <i class="fa-solid fa-cash-register"></i> Finalizar Venta
            </h3>
            
            <div class="form-group">
                <label><i class="fa-solid fa-receipt"></i> Subtotal:</label>
                <div style="font-size: 1.2rem; text-align: right; padding: 5px; background: #f5f5f5; border-radius: 8px;" id="modalSubtotal">
                    $0.00
                </div>
            </div>
            
            <div class="form-group" id="descuentosResumenContainer" style="display: none;">
                <label><i class="fa-solid fa-tag"></i> Descuentos Aplicados:</label>
                <div id="descuentosResumen" style="font-size: 0.9rem; text-align: right; padding: 5px; background: #fff3e0; border-radius: 8px; color: var(--success);">
                </div>
            </div>
            
            <div class="form-group">
                <label><i class="fa-solid fa-calculator"></i> Total a Pagar:</label>
                <div style="font-size: 2rem; font-weight: bold; color: var(--nocolor); text-align: center; padding: 10px; background: var(--primary-light); border-radius: 8px;" id="modalTotalPago">
                    $0.00
                </div>
            </div>
            
            <div class="form-group">
                <label><i class="fa-solid fa-money-bill"></i> Dinero Recibido:</label>
                <input type="number" id="dineroRecibido" class="input-modal" step="0.01" min="0" placeholder="Ingrese el monto recibido" style="font-size: 1.2rem; text-align: right;">
            </div>
            
            <div class="form-group">
                <label><i class="fa-solid fa-coins"></i> Cambio:</label>
                <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary-dark); text-align: center; padding: 10px; background: var(--primary-light); border-radius: 8px;" id="cambioDisplay">
                    $0.00
                </div>
            </div>
            
            <div class="form-group">
                <label><i class="fa-solid fa-credit-card"></i> Método de Pago:</label>
                <select id="metodoPago" class="input-modal">
                    <option value="1">Efectivo</option>
                    <option value="2">Tarjeta</option>
                    <option value="3">Transferencia</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="btn-action btn-discount" onclick="cerrarModal('modalPago')" style="flex: 1;">
                    <i class="fa-solid fa-times"></i> Cancelar
                </button>
                <button class="btn-action btn-sell" onclick="confirmarPago()" style="flex: 1;">
                    <i class="fa-solid fa-check"></i> Cobrar
                </button>
            </div>
        </div>
    </div>

    <div id="toast-container"></div>

    <div id="pageTransitionPos" class="page-transition" aria-hidden="true">
        <div class="page-transition-loader"></div>
    </div>

    <script src="/ElZapato/Assets/js/pos.js?v=<?php echo time(); ?>"></script>
</body>
</html>