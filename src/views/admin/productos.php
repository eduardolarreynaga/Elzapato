<?php
// --- CONFIGURACIÓN E INCLUDES ---
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin'); 
error_reporting(E_ALL & ~E_DEPRECATED);

$basePath = realpath(__DIR__ . '/../../../');
require_once $basePath . "/controller/productosController.php";
require_once $basePath . "/model/ProductosModel.php";
require_once $basePath . "/controller/categoriasController.php";
require_once $basePath . "/model/CategoriasModel.php";
require_once $basePath . "/controller/marcasController.php";
require_once $basePath . "/model/MarcasModel.php";

// 1. PROCESAR ACCIONES (CONTROLADOR)
$controlador = new ProductosController();
$controlador->ctrProcesarProducto(); 

if (isset($_POST["id_eliminar_v"])) {
    $respuesta = $controlador->ctrEliminarProducto(); 
    if ($respuesta == "ok") {
        header("Location: productos.php?res=eliminado");
        exit;
    }
}

// 2. CARGAR DATOS
$productos = ProductosController::ctrMostrarProductos(); 
$categorias = CategoriasController::ctrMostrarCategorias();
$marcas = MarcasController::ctrMostrarMarcas();
$proveedores = ProductosController::ctrMostrarProveedores();

// 3. ESTADÍSTICAS
$totalModelos = count($productos);
$totalStock = 0;
$bajoStockCount = 0;

if($productos){
    foreach($productos as $p){
        $info = $p['info_variantes'] ?? '';
        $vars = ($info != '') ? explode("||", $info) : [];
        foreach($vars as $v){
            $d = explode("|", $v);
            $cant = (int)($d[3] ?? 0);
            $totalStock += $cant;
            if($cant <= 10 && $cant > 0) $bajoStockCount++;
        }
    }
}

$activeMenu = 'productos';
$pageTitle = 'Inventario | ElZapato';
$pageStyles = ['/ElZapato/Assets/css/pages/admin-stats.css', '/ElZapato/Assets/css/pages/admin-productos.css'];
require __DIR__ . '/../layouts/admin-shell-start.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
    .stat-card { background: white; padding: 15px; border-radius: 12px; border-left: 5px solid #AB886D; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    .stat-card h4 { margin: 0; color: #888; font-size: 0.8rem; text-transform: uppercase; }
    .stat-card p { margin: 5px 0 0; font-size: 1.5rem; font-weight: bold; color: #4D3B2E; }

    .table-scroll { height: 388px; overflow-y: auto; border: 1px solid #D6C0B3; border-radius: 10px; background: white; }
    .products-table { width: 100%; border-collapse: collapse; }
    .products-table thead th { position: sticky; top: 0; background: #AB886D !important; color: white !important; padding: 15px 12px; z-index: 20; text-align: left; }
    .products-table td { padding: 12px 10px; border-bottom: 1px solid #eee; vertical-align: middle; font-size: 0.9rem; }
    
    .progress-bg { background: #eee; border-radius: 10px; height: 8px; width: 100%; overflow: hidden; border: 1px solid #eee; margin-top: 5px; }
    .progress-bar { height: 100%; transition: width 0.5s ease; border-radius: 10px; }
    .bg-danger { background-color: #772C24; }   
    .bg-warning { background-color: #bc6e32; }  
    .bg-success { background-color: #AB886D; }  

    .switch { position: relative; display: inline-block; width: 44px; height: 22px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
    .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: #AB886D; }
    input:checked + .slider:before { transform: translateX(22px); }
    input:disabled + .slider { opacity: 0.5; cursor: not-allowed; }

    .fila-inactiva { opacity: 0.5; background-color: #f2f2f2 !important; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 15px; }
    .info-item { background: #f9f9f9; padding: 10px; border-radius: 8px; border: 1px solid #eee; }
    .info-item label { display: block; font-size: 0.7rem; color: #999; font-weight: bold; text-transform: uppercase; }
    
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #4D3B2E; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    
    .image-box { background: white; padding: 10px; border-radius: 8px; border: 1px solid #eee; text-align: center; min-height: 140px; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .image-box img { max-width: 100px; max-height: 100px; border-radius: 8px; object-fit: cover; }
    
    .stock-cero { background-color: #772C24 !important; color: white !important; }
    .warning-text { color: #bc6e32; font-weight: bold; }
    .info-tooltip { cursor: help; border-bottom: 1px dashed #999; }
</style>

<?php
$pageHeading = 'Gestión de Inventario';
$searchInputId = 'searchProduct';
$searchPlaceholder = 'Buscar modelo, SKU, marca...';
$showSearch = true;
require __DIR__ . '/../layouts/admin-header.php';
?>

<div class="productos-page">
    <div class="stats-grid stats-list" aria-label="Resumen de productos">
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-boxes"></i> Total Variantes</span>
            <span class="stats-list-value" id="totalVariantes">0</span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-check-circle"></i> Activos</span>
            <span class="stats-list-value" id="totalActivos">0</span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-exclamation-triangle"></i> Stock Bajo</span>
            <span class="stats-list-value" id="totalBajoStock"><?= $bajoStockCount ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-ban"></i> Inactivos</span>
            <span class="stats-list-value" id="totalInactivos">0</span>
        </div>
    </div>

    <div class="actions-bar" style="margin-bottom: 15px;">
        <button class="btn-primary" id="btnNuevoProducto" style="background:#AB886D; border:none; padding:12px 24px; border-radius:8px; color:white; cursor:pointer; font-weight:bold; display:flex; align-items:center; gap:8px;">
            <i class="fas fa-plus"></i> Nuevo Producto
        </button>
        <div class="filters">
            <select class="filter-select" id="filterCategory">
                <option value="">Categoría</option>
                <?php foreach($categorias as $cat): ?>
                    <option value="<?= strtolower($cat['nombre_categoria']) ?>"><?= htmlspecialchars($cat['nombre_categoria']) ?></option>
                <?php endforeach; ?>
            </select>
            <select class="filter-select" id="filterStatus">
                <option value="">Estado</option>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
                <option value="bajo_stock">Stock Bajo (≤10)</option>
                <option value="stock_cero">Stock Cero</option>
            </select>
            <button class="btn-outline-primary" id="btnResetProductoFiltros" type="button" title="Limpiar filtros">
                <i class="fas fa-times"></i> Limpiar
            </button>
        </div>
    </div>

    <div class="table-scroll">
        <table class="products-table" id="tablaProductos">
            <thead>
                <tr>
                    <th style="text-align: center;">Imagen</th>
                    <th>Producto / SKU</th>
                    <th>Marca</th>
                    <th>Precio</th>
                    <th>Stock Actual</th>
                    <th>Estado</th>
                    <th style="text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalVariantes = 0;
                $totalActivos = 0;
                $totalInactivos = 0;
                
                if($productos): 
                    foreach ($productos as $p): 
                        $variantes = ($p['info_variantes'] != '') ? explode("||", $p['info_variantes']) : [];
                        foreach($variantes as $v):
                            $d = explode("|", $v);
                            $v_talla = $d[0]; $v_color = $d[1]; $v_precio = $d[2]; 
                            $v_stock = (int)$d[3]; $v_sku = $d[4] ?? 'N/A'; 
                            $v_estado = $d[5] ?? 'activo'; $v_id_v = $d[6] ?? '0';
                            
                            // Actualizar estado si el stock es 0
                            if($v_stock <= 0 && $v_estado == 'activo') {
                                $v_estado = 'inactivo';
                            }
                            
                            $totalVariantes++;
                            if($v_estado == 'activo') $totalActivos++;
                            if($v_estado == 'inactivo') $totalInactivos++;

                            $imgDefault = "/ElZapato/Assets/img/zapa.jpeg";
                            $rutaImagen = "/ElZapato/Assets/img/productos/" . $v_id_v . ".jpg";
                            $fullPathServer = $_SERVER['DOCUMENT_ROOT'] . $rutaImagen;
                            $img = file_exists($fullPathServer) ? $rutaImagen . "?t=" . time() : $imgDefault;

                            $max = 50; 
                            $perc = min(($v_stock / $max) * 100, 100);
                            if($v_stock <= 0) {
                                $bColor = "stock-cero";
                            } elseif($v_stock <= 5) {
                                $bColor = "bg-danger";
                            } elseif($v_stock <= 15) {
                                $bColor = "bg-warning";
                            } else {
                                $bColor = "bg-success";
                            }
                ?>
                <tr class="<?= ($v_estado == 'inactivo') ? 'fila-inactiva' : '' ?>" 
                    data-id-p="<?= $p['id_producto'] ?>" data-id-v="<?= $v_id_v ?>"
                    data-nombre="<?= htmlspecialchars($p['nombre_producto']) ?>"
                    data-categoria="<?= $p['id_categoria'] ?>" data-marca="<?= $p['id_marca'] ?>"
                    data-categoria-nom="<?= htmlspecialchars($p['nombre_categoria']) ?>"
                    data-marca-nom="<?= htmlspecialchars($p['nombre_marca']) ?>"
                    data-precio="<?= $v_precio ?>" data-stock="<?= $v_stock ?>"
                    data-talla="<?= $v_talla ?>" data-color="<?= $v_color ?>" 
                    data-v-estado="<?= $v_estado ?>" data-sku="<?= $v_sku ?>" data-img="<?= $img ?>"
                    data-proveedor="<?= htmlspecialchars($p['nombre_empresa'] ?? 'Distribuidor General') ?>"
                    data-id-proveedor="<?= $p['id_proveedor'] ?? '' ?>"
                    data-descripcion="<?= htmlspecialchars($p['descripcion'] ?? 'Sin descripción.') ?>">
                    
                    <td><img src="<?= $img ?>" width="60" height="60" style="object-fit:cover; border-radius:8px; border: 1px solid #ddd; display: block; margin: 0 auto;"></td>
                    <td style="padding-left: 15px;">
                        <strong><?= htmlspecialchars($p['nombre_producto']) ?></strong>
                        <br><small style="color:#AB886D; font-weight: bold;"><?= $v_sku ?></small>
                    </td>
                    <td><?= htmlspecialchars($p['nombre_marca']) ?></td>
                    <td><strong style="color: #4D3B2E;">$<?= number_format($v_precio, 2) ?></strong></td>
                    <td>
                        <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-weight:bold;">
                            <span>Cant: <?= $v_stock ?></span>
                        </div>
                        <div class="progress-bg">
                            <div class="progress-bar <?= $bColor ?>" style="width: <?= $perc ?>%;"></div>
                        </div>
                     </td>
                    <td>
                        <span style="font-size:0.65rem; font-weight:bold; color:white; background:<?= ($v_estado == 'activo') ? '#AB886D' : '#772C24' ?>; padding:4px 10px; border-radius:20px; text-transform:uppercase;">
                            <?= $v_estado ?>
                        </span>
                     </td>
                    <td>
                        <div style="display:flex; gap:15px; justify-content:center;">
                            <button type="button" onclick="viewProduct(this)" title="Ver Detalle" style="color:#4D3B2E; background:none; border:none; cursor:pointer; font-size:1.1rem;">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" onclick="btnEditProduct(this)" title="Editar" style="color:#AB886D; background:none; border:none; cursor:pointer; font-size:1.1rem;">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" onclick="deleteVariant(<?= $v_id_v ?>)" title="Eliminar" style="color:#772C24; background:none; border:none; cursor:pointer; font-size:1.1rem;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                     </td>
                 </tr>
                <?php endforeach; endforeach; endif; ?>
            </tbody>
         </table>
    </div>
</div>

<!-- Modal de Vista -->
<div class="modal" id="viewModal" style="display: none; align-items:center; justify-content:center; background: rgba(0,0,0,0.7); position:fixed; top:0; left:0; width:100%; height:100%; z-index:9999;">
    <div class="modal-content" style="background:white; padding:30px; border-radius:15px; width:580px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
        <div style="display:flex; gap:20px; margin-bottom:20px;">
            <img id="v_img" src="" style="width:160px; height:160px; object-fit:cover; border-radius:12px; border:1px solid #ddd;">
            <div style="flex:1;">
                <h2 id="v_nombre" style="margin:0; color:#4D3B2E;"></h2>
                <p style="color:#888;">SKU: <span id="v_sku_p" style="font-weight:bold; color:#AB886D;"></span></p>
                <div id="v_estado_badge" style="display:inline-block; padding:4px 12px; border-radius:20px; color:white; font-size:0.7rem; font-weight:bold;"></div>
            </div>
        </div>
        <div class="info-grid">
            <div class="info-item"><label>Proveedor</label><span id="v_proveedor"></span></div>
            <div class="info-item"><label>Marca / Cat</label><span id="v_marca_cat"></span></div>
            <div class="info-item"><label>Variante</label><span id="v_variante_txt"></span></div>
            <div class="info-item" style="background:#f4f1ef;"><label>Precio</label><span id="v_precio_txt" style="font-size:1.2rem; font-weight:bold;"></span></div>
        </div>
        <div style="margin-top:20px; text-align:right;">
            <button onclick="closeViewModal()" style="padding:12px 35px; background:#4D3B2E; color:white; border:none; border-radius:8px; cursor:pointer;">Cerrar</button>
        </div>
    </div>
</div>

<!-- Modal de Edición/Creación -->
<div class="modal" id="productModal" style="display: none; align-items:center; justify-content:center; background: rgba(0,0,0,0.7); position:fixed; top:0; left:0; width:100%; height:100%; z-index:9998;">
    <div class="modal-content" style="background:white; padding:30px; border-radius:15px; width:750px; max-height:90vh; overflow-y:auto;">
        <form id="productForm" method="post" enctype="multipart/form-data">
            <h3 id="modalTitle" style="color: #AB886D; border-bottom: 2px solid #f4f1ef; padding-bottom:10px;">Editar Producto</h3>
            
            <input type="hidden" name="accion" id="accion" value="">
            <input type="hidden" name="id_producto" id="id_producto">
            <input type="hidden" name="id_variante" id="id_variante">
            
            <div class="form-group">
                <label>Nombre del Calzado *</label>
                <input type="text" name="nombre_producto" id="nombre_producto" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Categoría *</label>
                    <select name="id_categoria" id="id_categoria" required>
                        <?php foreach($categorias as $c): ?> 
                            <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre_categoria']) ?></option> 
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Marca *</label>
                    <select name="id_marca" id="id_marca" required>
                        <?php foreach($marcas as $m): ?> 
                            <option value="<?= $m['id_marca'] ?>"><?= htmlspecialchars($m['nombre_marca']) ?></option> 
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Proveedor</label>
                <select name="id_proveedor" id="id_proveedor">
                    <option value="">Seleccione un proveedor</option>
                    <?php foreach($proveedores as $prov): ?> 
                        <option value="<?= $prov['id_proveedor'] ?>"><?= htmlspecialchars($prov['nombre_empresa']) ?></option> 
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group"><label>Talla *</label><input type="text" name="talla" id="talla" required></div>
                <div class="form-group"><label>Color *</label><input type="text" name="color" id="color" required></div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>SKU (Código de Barras) *</label>
                    <input type="text" name="codigo_barras" id="codigo_barras" required>
                </div>
                <div class="form-group"><label>Precio *</label><input type="number" step="0.01" name="precio_venta" id="precio_venta" required></div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Stock *</label>
                    <input type="number" name="stock" id="stock" required onchange="verificarStockEnFormulario()">
                    <small id="stockWarning" style="color:#772C24; display:none;">⚠️ Stock en 0 - El producto se desactivará automáticamente</small>
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <label class="switch">
                            <input type="checkbox" id="estado_switch" onchange="actualizarTxtEstado(this.checked)">
                            <span class="slider"></span>
                        </label>
                        <input type="hidden" name="estado_v" id="estado_v" value="activo">
                        <span id="txtEstado" style="font-weight:bold;">ACTIVO</span>
                        <i id="estadoInfoIcon" class="fas fa-info-circle info-tooltip" style="color:#AB886D; display:none;" title="Producto desactivado automáticamente por falta de stock"></i>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Imagen Actual</label>
                    <div class="image-box"><img id="currentImage" src="/ElZapato/Assets/img/zapa.jpeg"></div>
                </div>
                <div class="form-group">
                    <label>Cambiar Imagen</label>
                    <div class="image-box">
                        <input type="file" name="imagen_producto" id="imagen_producto" accept="image/*">
                        <div id="newImagePreview" style="margin-top:10px;"></div>
                    </div>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
                <button type="button" onclick="closeModal()" style="padding:10px 20px; background:#eee; border:none; border-radius:8px; cursor:pointer;">Cancelar</button>
                <button type="submit" style="padding:10px 25px; background:#AB886D; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold;">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
// Actualizar estadísticas
function actualizarEstadisticas() {
    const totalFilas = document.querySelectorAll('#tablaProductos tbody tr').length;
    const activos = document.querySelectorAll('#tablaProductos tbody tr:not(.fila-inactiva)').length;
    const inactivos = totalFilas - activos;
    const stockCero = document.querySelectorAll('#tablaProductos tbody tr[data-stock="0"]').length;
    
    document.getElementById('totalVariantes').innerText = totalFilas;
    document.getElementById('totalActivos').innerText = activos;
    document.getElementById('totalInactivos').innerText = inactivos;
    document.getElementById('totalBajoStock').innerText = stockCero > 0 ? stockCero : '0';
}

// Función para verificar stock en el formulario
function verificarStockEnFormulario() {
    const stockInput = document.getElementById('stock');
    const stockValue = parseInt(stockInput.value);
    const estadoSwitch = document.getElementById('estado_switch');
    const estadoHidden = document.getElementById('estado_v');
    const stockWarning = document.getElementById('stockWarning');
    const estadoInfoIcon = document.getElementById('estadoInfoIcon');
    
    if (stockValue <= 0) {
        // Forzar estado inactivo
        estadoSwitch.checked = false;
        estadoHidden.value = 'inactivo';
        actualizarTxtEstado(false);
        estadoSwitch.disabled = true;
        stockWarning.style.display = 'block';
        estadoInfoIcon.style.display = 'inline-block';
        
        // Mostrar tooltip
        estadoInfoIcon.title = 'Producto desactivado automáticamente por falta de stock';
    } else {
        estadoSwitch.disabled = false;
        stockWarning.style.display = 'none';
        estadoInfoIcon.style.display = 'none';
    }
}

// Función de búsqueda y filtros
const searchInput = document.getElementById('searchProduct');
const filterCategory = document.getElementById('filterCategory');
const filterStatus = document.getElementById('filterStatus');
const resetFiltersBtn = document.getElementById('btnResetProductoFiltros');

function normalizeText(value) {
    return (value || '')
        .toString()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

function applyProductFilters() {
    const term = normalizeText(searchInput?.value || '');
    const category = normalizeText(filterCategory?.value || '');
    const status = filterStatus?.value || '';

    document.querySelectorAll('#tablaProductos tbody tr').forEach(row => {
        const rowText = normalizeText(row.innerText);
        const rowCategory = normalizeText(row.dataset.categoriaNom || '');
        const rowStatus = row.dataset.vEstado || '';
        const rowStock = parseInt(row.dataset.stock || '0', 10);

        const passSearch = !term || rowText.includes(term);
        const passCategory = !category || rowCategory.includes(category);

        let passStatus = true;
        if (status === 'activo') {
            passStatus = rowStatus === 'activo' && rowStock > 0;
        } else if (status === 'inactivo') {
            passStatus = rowStatus === 'inactivo' || rowStock <= 0;
        } else if (status === 'bajo_stock') {
            passStatus = rowStock <= 10 && rowStock > 0;
        } else if (status === 'stock_cero') {
            passStatus = rowStock <= 0;
        }

        row.style.display = (passSearch && passCategory && passStatus) ? '' : 'none';
    });
    
    actualizarEstadisticas();
}

searchInput?.addEventListener('input', applyProductFilters);
filterCategory?.addEventListener('change', applyProductFilters);
filterStatus?.addEventListener('change', applyProductFilters);
resetFiltersBtn?.addEventListener('click', function () {
    if (filterCategory) filterCategory.value = '';
    if (filterStatus) filterStatus.value = '';
    if (searchInput) searchInput.value = '';
    applyProductFilters();
});

// Ver detalle del producto
function viewProduct(btn) {
    const d = btn.closest('tr').dataset;
    document.getElementById('v_img').src = d.img;
    document.getElementById('v_nombre').innerText = d.nombre;
    document.getElementById('v_sku_p').innerText = d.sku;
    document.getElementById('v_proveedor').innerText = d.proveedor;
    document.getElementById('v_marca_cat').innerText = d.marcaNom + " / " + d.categoriaNom;
    document.getElementById('v_variante_txt').innerText = d.color + " | Talla: " + d.talla;
    document.getElementById('v_precio_txt').innerText = "$" + parseFloat(d.precio).toFixed(2);
    const badge = document.getElementById('v_estado_badge');
    const stockActual = parseInt(d.stock);
    
    if(stockActual <= 0) {
        badge.innerText = 'INACTIVO';
        badge.style.background = '#772C24';
    } else {
        badge.innerText = d.vEstado;
        badge.style.background = (d.vEstado === 'activo') ? '#AB886D' : '#772C24';
    }
    
    document.getElementById('viewModal').style.display = 'flex';
}

function closeViewModal() { 
    document.getElementById('viewModal').style.display = 'none'; 
}

// Editar producto
function btnEditProduct(btn) {
    const d = btn.closest('tr').dataset;
    
    if(!d.idV || d.idV === '0') {
        Swal.fire('Error', 'No se pudo identificar la variante a editar', 'error');
        return;
    }
    
    document.getElementById('accion').value = 'actualizar';
    document.getElementById('id_producto').value = d.idP;
    document.getElementById('id_variante').value = d.idV;
    document.getElementById('nombre_producto').value = d.nombre;
    document.getElementById('id_categoria').value = d.categoria;
    document.getElementById('id_marca').value = d.marca;
    document.getElementById('id_proveedor').value = d.idProveedor || '';
    document.getElementById('talla').value = d.talla;
    document.getElementById('color').value = d.color;
    document.getElementById('codigo_barras').value = d.sku;
    document.getElementById('precio_venta').value = d.precio;
    document.getElementById('stock').value = d.stock;
    
    const stockActual = parseInt(d.stock);
    const estadoActual = d.vEstado;
    const estadoSwitch = document.getElementById('estado_switch');
    const estadoHidden = document.getElementById('estado_v');
    const stockWarning = document.getElementById('stockWarning');
    const estadoInfoIcon = document.getElementById('estadoInfoIcon');
    
    // Limpiar marca de cambio manual
    estadoSwitch.removeAttribute('data-manual-change');
    
    if (stockActual <= 0) {
        // Forzar estado inactivo
        estadoSwitch.checked = false;
        estadoHidden.value = 'inactivo';
        actualizarTxtEstado(false);
        estadoSwitch.disabled = true;
        stockWarning.style.display = 'block';
        estadoInfoIcon.style.display = 'inline-block';
        estadoInfoIcon.title = 'Producto desactivado automáticamente por falta de stock';
    } else {
        estadoSwitch.disabled = false;
        stockWarning.style.display = 'none';
        estadoInfoIcon.style.display = 'none';
        
        // Si hay stock, permitir editar el estado manualmente
        const isActivo = (estadoActual === 'activo');
        estadoSwitch.checked = isActivo;
        actualizarTxtEstado(isActivo);
        estadoHidden.value = isActivo ? 'activo' : 'inactivo';
    }
    
    document.getElementById('currentImage').src = d.img;
    document.getElementById('modalTitle').innerText = 'Editar Variante';
    document.getElementById('productModal').style.display = 'flex';
}

function actualizarTxtEstado(checked) {
    const txt = document.getElementById('txtEstado');
    const input = document.getElementById('estado_v');
    txt.innerText = checked ? "ACTIVO" : "INACTIVO";
    txt.style.color = checked ? "#AB886D" : "#772C24";
    input.value = checked ? "activo" : "inactivo";
}

function closeModal() { 
    document.getElementById('productModal').style.display = 'none';
    document.getElementById('newImagePreview').innerHTML = '';
    // Resetear el switch
    const estadoSwitch = document.getElementById('estado_switch');
    estadoSwitch.disabled = false;
    estadoSwitch.removeAttribute('data-manual-change');
}

// Nuevo producto
document.getElementById('btnNuevoProducto').onclick = function() {
    document.getElementById('productForm').reset();
    document.getElementById('accion').value = 'crear';
    document.getElementById('id_producto').value = "";
    document.getElementById('id_variante').value = "";
    document.getElementById('id_proveedor').value = "";
    document.getElementById('modalTitle').innerText = 'Nuevo Producto';
    document.getElementById('currentImage').src = "/ElZapato/Assets/img/zapa.jpeg";
    
    // Resetear estado del switch
    const estadoSwitch = document.getElementById('estado_switch');
    estadoSwitch.checked = true;
    estadoSwitch.disabled = false;
    estadoSwitch.removeAttribute('data-manual-change');
    actualizarTxtEstado(true);
    
    document.getElementById('stockWarning').style.display = 'none';
    document.getElementById('estadoInfoIcon').style.display = 'none';
    
    document.getElementById('productModal').style.display = 'flex';
};

// Preview de imagen
document.getElementById('imagen_producto').onchange = function(e) {
    const preview = document.getElementById('newImagePreview');
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => preview.innerHTML = `<img src="${e.target.result}" style="max-width:80px; border-radius:8px; border:2px solid #AB886D;">`;
        reader.readAsDataURL(this.files[0]);
    }
};

// Eliminar variante
function deleteVariant(idV) {
    Swal.fire({
        title: '¿Eliminar?',
        text: "Se borrará la variante y su imagen.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#772C24',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input name="id_eliminar_v" type="hidden" value="${idV}">`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Validar antes de enviar el formulario
document.getElementById('productForm').addEventListener('submit', function(e) {
    const stockInput = document.getElementById('stock');
    const stockValue = parseInt(stockInput.value);
    const estadoHidden = document.getElementById('estado_v');
    
    if (stockValue <= 0) {
        // Forzar estado inactivo
        estadoHidden.value = 'inactivo';
        
        // Mostrar confirmación
        Swal.fire({
            title: 'Stock en cero',
            text: 'El producto se guardará como INACTIVO debido a que el stock es 0',
            icon: 'info',
            confirmButtonColor: '#AB886D',
            timer: 2000,
            showConfirmButton: true
        });
    }
});

// Escuchar cambios manuales en el switch
document.getElementById('estado_switch').addEventListener('change', function() {
    this.setAttribute('data-manual-change', 'true');
});

// Inicializar estadísticas
document.addEventListener('DOMContentLoaded', function() {
    actualizarEstadisticas();
    console.log('Inventario inicializado - Control de stock automático activo');
});
</script>

<?php if(isset($_GET['res'])): ?>
    <?php if($_GET['res'] == 'duplicado'): ?>
    <script>
    Swal.fire({ 
        title: '¡Código de Barras Duplicado!', 
        html: 'El SKU <strong><?= htmlspecialchars($_GET['sku'] ?? '') ?></strong> ya está en uso.',
        icon: 'error', 
        confirmButtonColor: '#772C24'
    });
    </script>
    <?php elseif($_GET['res'] == 'error'): ?>
    <script>
    Swal.fire({ 
        title: '¡Error!', 
        text: 'Ocurrió un error. Verifica que el SKU no esté duplicado.', 
        icon: 'error', 
        confirmButtonColor: '#772C24' 
    });
    </script>
    <?php else: ?>
    <script>
    const msjs = {
        creado: 'Producto registrado correctamente', 
        actualizado: 'Cambios guardados con éxito', 
        eliminado: 'Variante eliminada'
    };
    Swal.fire({ 
        title: '¡Operación Exitosa!', 
        text: msjs['<?= $_GET['res'] ?>'] || 'Proceso completado', 
        icon: 'success', 
        confirmButtonColor: '#AB886D' 
    });
    </script>
    <?php endif; ?>
    <script>window.history.replaceState({}, document.title, "productos.php");</script>
<?php endif; ?>

<?php 
require __DIR__ . '/../layouts/admin-shell-end.php'; 
require __DIR__ . '/../layouts/admin-html-end.php'; 
?>