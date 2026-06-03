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
$stockBajoUmbral = defined('LOW_STOCK_THRESHOLD') ? (int)LOW_STOCK_THRESHOLD : 10;
$totalStock = 0;
$bajoStockCount = 0;
$totalVariantesCount = 0;
$totalActivosCount = 0;
$totalInactivosCount = 0;

if($productos){
    foreach($productos as $p){
        $info = $p['info_variantes'] ?? '';
        $vars = ($info != '') ? explode("||", $info) : [];
        foreach($vars as $v){
            $d = explode("|", $v);
            $cant = (int)($d[3] ?? 0);
            $estadoVariante = $d[5] ?? 'activo';

            if ($cant <= 0 && $estadoVariante === 'activo') {
                $estadoVariante = 'inactivo';
            }

            $totalStock += $cant;
            if($cant <= $stockBajoUmbral && $cant > 0) $bajoStockCount++;
            $totalVariantesCount++;

            if ($estadoVariante === 'activo') {
                $totalActivosCount++;
            } else {
                $totalInactivosCount++;
            }
        }
    }
}

$activeMenu = 'productos';
$pageTitle = 'Inventario | ElZapato';
$pageStyles = ['/ElZapato/Assets/css/pages/admin-stats.css?v=' . time(), '/ElZapato/Assets/css/pages/admin-productos.css?v=' . time()];
$imgProductosDir = $basePath . '/Assets/img/productos/';
$imgProductosUrl = '/ElZapato/Assets/img/productos/';
$imgDefault = '/ElZapato/Assets/img/zapa.jpeg';
$imgCacheBust = time();
require __DIR__ . '/../layouts/admin-shell-start.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>

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
    
    /* Estilos para el botón CSV */
    .btn-csv {
        background: #4D3B2E;
        color: white;
        border: none;
    }
    .btn-csv:hover {
        background: #3a2b21;
        color: white;
    }
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
            <span class="stats-list-value" id="totalVariantes"><?= $totalVariantesCount ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-check-circle"></i> Activos</span>
            <span class="stats-list-value" id="totalActivos"><?= $totalActivosCount ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-exclamation-triangle"></i> Stock Bajo</span>
            <span class="stats-list-value" id="totalBajoStock"><?= $bajoStockCount ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-ban"></i> Inactivos</span>
            <span class="stats-list-value" id="totalInactivos"><?= $totalInactivosCount ?></span>
        </div>
    </div>

    <div class="actions-bar" style="margin-bottom: 15px;">
        <button class="btn-outline-primary" id="btnNuevoProducto" type="button">
            <i class="fas fa-plus"></i> Nuevo Producto
        </button>
        <button class="btn-csv btn-outline-primary" id="btnCargarCSV" type="button">
            <i class="fas fa-file-csv"></i> Cargar CSV
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
                <option value="bajo_stock">Stock Bajo (≤<?= $stockBajoUmbral ?>)</option>
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

                            $img = $imgDefault;
                            foreach (['jpg', 'jpeg', 'png', 'webp'] as $extImagen) {
                                $fullPathServer = $imgProductosDir . $v_id_v . '.' . $extImagen;
                                if (file_exists($fullPathServer)) {
                                    $img = $imgProductosUrl . $v_id_v . '.' . $extImagen . '?t=' . $imgCacheBust;
                                    break;
                                }
                            }

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
                        <div class="acciones-admin">
                            <button type="button" class="btn-icon small" onclick="viewProduct(this)" title="Ver Detalle">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn-icon small" onclick="btnEditProduct(this)" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn-icon small" onclick="deleteVariant(<?= $v_id_v ?>)" title="Eliminar">
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
        <div class="modal-actions" style="margin-top:20px;">
            <button type="button" class="btn-modal-cancel" onclick="closeViewModal()">Cerrar</button>
        </div>
    </div>
</div>

<!-- Modal de Edición/Creación -->
<div class="modal" id="productModal" style="display: none; align-items:center; justify-content:center; background: rgba(0,0,0,0.7); position:fixed; top:0; left:0; width:100%; height:100%; z-index:9998;">
    <div class="modal-content product-modal-content" style="background:white; padding:30px; border-radius:15px; width:min(900px, 94vw); max-height:92vh; overflow-y:auto;">
        <form id="productForm" class="product-modal-form" method="post" enctype="multipart/form-data">
            <h3 id="modalTitle" style="color: #AB886D; border-bottom: 2px solid #f4f1ef; padding-bottom:10px;">Editar Producto</h3>
            
            <input type="hidden" name="accion" id="accion" value="">
            <input type="hidden" name="id_producto" id="id_producto">
            <input type="hidden" name="id_variante" id="id_variante">
            
            <div class="form-group">
                <label>Nombre del Calzado *</label>
                <div class="input-group">
                    <span class="input-group-icon"><i class="fas fa-shoe-prints"></i></span>
                    <input type="text" name="nombre_producto" id="nombre_producto" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Categoría *</label>
                    <div class="input-group">
                        <span class="input-group-icon"><i class="fas fa-layer-group"></i></span>
                        <select name="id_categoria" id="id_categoria" required>
                            <?php foreach($categorias as $c): ?> 
                                <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre_categoria']) ?></option> 
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Marca *</label>
                    <div class="input-group">
                        <span class="input-group-icon"><i class="fas fa-tags"></i></span>
                        <select name="id_marca" id="id_marca" required>
                            <?php foreach($marcas as $m): ?> 
                                <option value="<?= $m['id_marca'] ?>"><?= htmlspecialchars($m['nombre_marca']) ?></option> 
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Proveedor</label>
                <div class="input-group">
                    <span class="input-group-icon"><i class="fas fa-truck"></i></span>
                    <select name="id_proveedor" id="id_proveedor">
                        <option value="">Seleccione un proveedor</option>
                        <?php foreach($proveedores as $prov): ?> 
                            <option value="<?= $prov['id_proveedor'] ?>"><?= htmlspecialchars($prov['nombre_empresa']) ?></option> 
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row form-row-three">
                <div class="form-group">
                    <label>Talla *</label>
                    <div class="input-group">
                        <span class="input-group-icon"><i class="fas fa-ruler"></i></span>
                        <input type="text" name="talla" id="talla" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Color *</label>
                    <div class="input-group">
                        <span class="input-group-icon"><i class="fas fa-palette"></i></span>
                        <input type="text" name="color" id="color" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Stock *</label>
                    <div class="input-group">
                        <span class="input-group-icon"><i class="fas fa-boxes"></i></span>
                        <input type="number" name="stock" id="stock" required onchange="verificarStockEnFormulario()">
                    </div>
                    <small id="stockWarning" style="color:#772C24; display:none;">⚠️ Stock en 0 - El producto se desactivará automáticamente</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>SKU (Código de Barras) *</label>
                    <div class="input-group">
                        <span class="input-group-icon"><i class="fas fa-barcode"></i></span>
                        <input type="text" name="codigo_barras" id="codigo_barras" required maxlength="7" inputmode="numeric" pattern="\d{7}">
                    </div>
                    <small id="skuHelper" style="display:block; margin-top:6px; color:#666;">El SKU debe tener 7 números.</small>
                </div>
                <div class="form-group">
                    <label>Precio *</label>
                    <div class="input-group">
                        <span class="input-group-icon"><i class="fas fa-dollar-sign"></i></span>
                        <input type="number" step="0.01" name="precio_venta" id="precio_venta" required>
                    </div>
                </div>
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

            <div class="form-row">
                <div class="form-group">
                    <label>Imagen Actual</label>
                    <div class="image-box"><img id="currentImage" src="/ElZapato/Assets/img/zapa.jpeg"></div>
                </div>
                <div class="form-group">
                    <label>Cambiar Imagen</label>
                    <div class="image-box">
                        <div class="input-group">
                            <span class="input-group-icon"><i class="fas fa-image"></i></span>
                            <input type="file" name="imagen_producto" id="imagen_producto" accept="image/*">
                        </div>
                        <div id="newImagePreview" style="margin-top:10px;"></div>
                    </div>
                </div>
            </div>

            <div class="modal-actions" style="margin-top:20px;">
                <button type="button" class="btn-modal-cancel" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn-modal-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Carga CSV -->
<div class="modal" id="csvModal" style="display: none; align-items:center; justify-content:center; background: rgba(0,0,0,0.7); position:fixed; top:0; left:0; width:100%; height:100%; z-index:9999;">
    <div class="modal-content" style="background:white; padding:30px; border-radius:15px; width:550px; max-width:94vw;">
        <form id="csvForm" method="post" enctype="multipart/form-data">
            <input type="hidden" name="accion_csv" value="cargar">
            <h3 style="color: #AB886D; border-bottom: 2px solid #f4f1ef; padding-bottom:10px; margin-bottom:20px;">
                <i class="fas fa-file-csv"></i> Carga Masiva de Productos
            </h3>
            
            <div class="form-group">
                <label>Archivo CSV (*.csv)</label>
                <div class="input-group">
                    <span class="input-group-icon"><i class="fas fa-upload"></i></span>
                    <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                </div>
                <small style="color:#666; display:block; margin-top:8px;">
                    El archivo debe tener las columnas: nombre_producto, descripcion, id_marca, id_categoria, id_proveedor, talla, color, codigo_barras, precio_venta, stock
                </small>
            </div>
            
            <div class="form-group">
                <label>Opciones de carga</label>
                <div style="display: flex; gap: 15px; margin-top: 8px;">
                    <label style="display: flex; align-items: center; gap: 5px;">
                        <input type="checkbox" name="sobrescribir_sku" value="1" checked>
                        <span>Saltar SKUs duplicados</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 5px;">
                        <input type="checkbox" name="modo_prueba" value="1">
                        <span>Modo prueba (solo validar)</span>
                    </label>
                </div>
            </div>
            
            <div id="csvPreview" style="display: none; margin-top: 15px; padding: 10px; background: #f5f5f5; border-radius: 8px; max-height: 200px; overflow-y: auto;">
                <strong>Vista previa de datos:</strong>
                <div id="csvPreviewContent" style="font-size: 12px; margin-top: 8px;"></div>
            </div>
            
            <div class="modal-actions" style="margin-top:20px;">
                <button type="button" class="btn-modal-cancel" onclick="closeCSVModal()">Cancelar</button>
                <button type="submit" class="btn-modal-primary" id="btnProcesarCSV">
                    <i class="fas fa-play"></i> Procesar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Actualizar estadísticas
function actualizarEstadisticas() {
    const filasVisibles = Array.from(document.querySelectorAll('#tablaProductos tbody tr'))
        .filter(row => row.style.display !== 'none');

    const totalFilas = filasVisibles.length;
    const activos = filasVisibles.filter(row => !row.classList.contains('fila-inactiva')).length;
    const inactivos = totalFilas - activos;
    const bajoStock = filasVisibles.filter(row => {
        const stock = parseInt(row.dataset.stock || '0', 10);
        return stock > 0 && stock <= STOCK_BAJO_UMBRAL;
    }).length;
    
    document.getElementById('totalVariantes').innerText = totalFilas;
    document.getElementById('totalActivos').innerText = activos;
    document.getElementById('totalInactivos').innerText = inactivos;
    document.getElementById('totalBajoStock').innerText = bajoStock > 0 ? bajoStock : '0';
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
            passStatus = rowStock <= STOCK_BAJO_UMBRAL && rowStock > 0;
        } else if (status === 'stock_cero') {
            passStatus = rowStock <= 0;
        }

        row.style.display = (passSearch && passCategory && passStatus) ? '' : 'none';
    });
    
    actualizarEstadisticas();
}

const SKU_LENGTH = 7;
const STOCK_BAJO_UMBRAL = <?= (int)$stockBajoUmbral ?>;
const skuInput = document.getElementById('codigo_barras');
const skuHelper = document.getElementById('skuHelper');

function obtenerSkusExistentes() {
    const skus = new Set();
    document.querySelectorAll('#tablaProductos tbody tr').forEach(row => {
        const sku = (row.dataset.sku || '').trim();
        if (sku !== '') {
            skus.add(sku);
        }
    });
    return skus;
}

function validarSkuFormulario() {
    if (!skuInput || !skuHelper) return true;

    const valorOriginal = (skuInput.value || '').trim();
    const soloDigitos = valorOriginal.replace(/\D/g, '');
    if (valorOriginal !== soloDigitos) {
        skuInput.value = soloDigitos;
    }

    const sku = skuInput.value.trim();
    const faltan = Math.max(0, SKU_LENGTH - sku.length);

    const idVarianteActual = (document.getElementById('id_variante')?.value || '').trim();
    const skusExistentes = obtenerSkusExistentes();

    if (idVarianteActual !== '') {
        const filaActual = document.querySelector(`#tablaProductos tbody tr[data-id-v="${idVarianteActual}"]`);
        const skuActualFila = (filaActual?.dataset?.sku || '').trim();
        if (skuActualFila !== '') {
            skusExistentes.delete(skuActualFila);
        }
    }

    const existe = sku !== '' && skusExistentes.has(sku);

    if (sku.length === 0) {
        skuHelper.textContent = `El SKU debe tener ${SKU_LENGTH} números.`;
        skuHelper.style.color = '#666';
        return false;
    }

    if (faltan > 0) {
        skuHelper.textContent = `Faltan ${faltan} número${faltan === 1 ? '' : 's'} para completar el SKU (${SKU_LENGTH}).`;
        skuHelper.style.color = '#bc6e32';
        return false;
    }

    if (sku.length > SKU_LENGTH) {
        skuHelper.textContent = `El SKU no puede superar ${SKU_LENGTH} números.`;
        skuHelper.style.color = '#772C24';
        return false;
    }

    if (existe) {
        skuHelper.textContent = 'SKU ya detectado en la lista. Al guardar, el sistema validará si realmente está duplicado.';
        skuHelper.style.color = '#bc6e32';
        return true;
    }

    skuHelper.textContent = 'SKU válido y disponible.';
    skuHelper.style.color = '#2e7d32';
    return true;
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
    validarSkuFormulario();
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
    if (skuHelper) {
        skuHelper.textContent = `El SKU debe tener ${SKU_LENGTH} números.`;
        skuHelper.style.color = '#666';
    }
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
    validarSkuFormulario();
    
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

    if (!validarSkuFormulario()) {
        e.preventDefault();
        Swal.fire({
            title: '¡Revisa el SKU!',
            text: 'El SKU debe contener exactamente 7 números.',
            icon: 'error',
            confirmButtonColor: '#772C24'
        });
    }
});

skuInput?.addEventListener('input', validarSkuFormulario);

// Escuchar cambios manuales en el switch
document.getElementById('estado_switch').addEventListener('change', function() {
    this.setAttribute('data-manual-change', 'true');
});

// ===================== FUNCIONES PARA CSV =====================

// Modal CSV
const btnCargarCSV = document.getElementById('btnCargarCSV');
const csvModal = document.getElementById('csvModal');
const csvFile = document.getElementById('csv_file');

function openCSVModal() {
    csvModal.style.display = 'flex';
}

function closeCSVModal() {
    csvModal.style.display = 'none';
    document.getElementById('csvForm').reset();
    document.getElementById('csvPreview').style.display = 'none';
}

btnCargarCSV?.addEventListener('click', openCSVModal);

// Vista previa del CSV
csvFile?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    const previewDiv = document.getElementById('csvPreview');
    const previewContent = document.getElementById('csvPreviewContent');
    
    Papa.parse(file, {
        header: true,
        preview: 5,
        complete: function(results) {
            if (results.data && results.data.length > 0) {
                let html = '<table style="width:100%; font-size:11px; border-collapse:collapse;">';
                html += '<thead><tr>';
                Object.keys(results.data[0]).forEach(key => {
                    html += `<th style="border:1px solid #ddd; padding:4px;">${key}</th>`;
                });
                html += '</tr></thead><tbody>';
                
                results.data.forEach(row => {
                    html += '<tr>';
                    Object.values(row).forEach(val => {
                        html += `<td style="border:1px solid #ddd; padding:4px;">${val || '-'}</td>`;
                    });
                    html += '</tr>';
                });
                html += '</tbody></td>';
                html += `<p style="margin-top:8px;"><strong>Total registros detectados:</strong> ${results.meta.rows} líneas</p>`;
                previewContent.innerHTML = html;
                previewDiv.style.display = 'block';
            }
        },
        error: function(error) {
            previewContent.innerHTML = '<span style="color:red;">Error al leer el archivo: ' + error.message + '</span>';
            previewDiv.style.display = 'block';
        }
    });
});

// Botón descargar plantilla
document.getElementById('btnDescargarPlantilla')?.addEventListener('click', function() {
    const plantilla = "nombre_producto,descripcion,id_marca,id_categoria,id_proveedor,talla,color,codigo_barras,precio_venta,stock\n" +
        "Nike Air Max 90,Zapatilla deportiva,1,1,1,39,Negro,1000001,89.99,15\n" +
        "Adidas Superstar,Zapatilla casual,2,2,2,38,Blanco,1000002,79.99,20\n" +
        "Puma Suede Classic,Zapatilla urbana,3,2,3,40,Azul,1000003,69.99,10\n" +
        "Vans Old Skool,Sneaker skater,5,6,3,38,Negro,1000004,59.99,25\n" +
        "Converse Chuck Taylor,Zapatilla de lona,4,6,2,40,Negro,1000005,54.99,30";
    
    const blob = new Blob([plantilla], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'plantilla_productos.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
});

// Cerrar modales con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCSVModal();
        closeViewModal();
        closeModal();
    }
});

// Cerrar al hacer clic fuera del modal
window.onclick = function(event) {
    if (event.target === csvModal) closeCSVModal();
    if (event.target === document.getElementById('viewModal')) closeViewModal();
    if (event.target === document.getElementById('productModal')) closeModal();
}

// Inicializar estadísticas
document.addEventListener('DOMContentLoaded', function() {
    actualizarEstadisticas();
    validarSkuFormulario();
    console.log('Inventario inicializado - Control de stock automático activo');
});
</script>

<?php if(isset($_GET['res'])): ?>
    <?php if($_GET['res'] == 'duplicado'): ?>
    <script>
    Swal.fire({ 
        title: 'No se pudo guardar', 
        html: 'El SKU <strong><?= htmlspecialchars($_GET['sku'] ?? '') ?></strong> ya está registrado. Intenta con uno diferente.',
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
    <?php elseif($_GET['res'] == 'creado_img_error' || $_GET['res'] == 'actualizado_img_error'): ?>
    <script>
    const estadoImagen = '<?= htmlspecialchars($_GET['img_status'] ?? '') ?>';
    const mensajesImagen = {
        upload_1: 'La imagen supera el tamaño máximo permitido por el servidor (upload_max_filesize).',
        upload_2: 'La imagen supera el tamaño máximo permitido por el formulario.',
        upload_3: 'La imagen se subió parcialmente. Intenta nuevamente.',
        upload_4: 'No se recibió ningún archivo de imagen.',
        upload_6: 'Falta la carpeta temporal del servidor para subir archivos.',
        upload_7: 'El servidor no pudo escribir la imagen en disco.',
        upload_8: 'La subida fue detenida por una extensión de PHP.',
        archivo_invalido: 'El archivo de imagen recibido no es válido.',
        archivo_vacio: 'La imagen está vacía o dañada.',
        tamano: 'La imagen supera el máximo permitido de 5MB.',
        formato: 'Formato no permitido. Usa JPG, JPEG, PNG o WEBP.',
        directorio: 'No se pudo crear la carpeta de imágenes.',
        sin_permisos: 'Sin permisos para guardar imágenes en el servidor.',
        error: 'No se pudo mover la imagen al directorio final.'
    };

    const accion = '<?= $_GET['res'] === 'creado_img_error' ? 'registró' : 'actualizó' ?>';
    Swal.fire({ 
        title: 'Guardado con advertencia', 
        html: `El producto se ${accion}, pero la imagen no se pudo guardar.<br><br><strong>Detalle:</strong> ${mensajesImagen[estadoImagen] || 'Error desconocido al guardar la imagen.'}`,
        icon: 'warning', 
        confirmButtonColor: '#AB886D' 
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

<?php if(isset($_GET['res_csv'])): ?>
    <?php 
    $resultados = $_SESSION['csv_resultados'] ?? null;
    unset($_SESSION['csv_resultados']);
    ?>
    <?php if($_GET['res_csv'] == 'completado' && $resultados): ?>
    <script>
    (function() {
        var detallesHtml = '<div style="text-align:left; max-height:300px; overflow-y:auto;">';
        <?php foreach($resultados['detalles'] as $detalle): ?>
            detallesHtml += '<div style="font-size:12px; padding:4px 0;"><?= htmlspecialchars($detalle) ?></div>';
        <?php endforeach; ?>
        detallesHtml += '</div>';
        
        Swal.fire({
            title: 'Carga Masiva Completada',
            html: '<div style="text-align:center;">' +
                '<p><strong>Total procesados:</strong> <?= $resultados['total'] ?></p>' +
                '<p style="color:#2e7d32;"><strong>✓ Creados:</strong> <?= $resultados['creados'] ?></p>' +
                '<p style="color:#bc6e32;"><strong>↻ Actualizados:</strong> <?= $resultados['actualizados'] ?></p>' +
                '<p style="color:#772C24;"><strong>✗ Errores:</strong> <?= $resultados['errores'] ?></p>' +
                '<?php if($resultados['skus_duplicados'] > 0): ?>' +
                '<p><strong>⚠ SKUs duplicados omitidos:</strong> <?= $resultados['skus_duplicados'] ?></p>' +
                '<?php endif; ?>' +
                '<hr>' +
                '<details>' +
                '<summary style="cursor:pointer; color:#AB886D;">Ver detalles</summary>' +
                detallesHtml +
                '</details>' +
                '</div>',
            icon: 'success',
            confirmButtonColor: '#AB886D',
            width: '600px'
        });
    })();
    </script>
    <?php elseif($_GET['res_csv'] == 'prueba' && $resultados): ?>
    <script>
    (function() {
        var detallesHtml = '<div style="text-align:left; max-height:300px; overflow-y:auto;">';
        <?php foreach($resultados['detalles'] as $detalle): ?>
            detallesHtml += '<div style="font-size:12px; padding:4px 0;"><?= htmlspecialchars($detalle) ?></div>';
        <?php endforeach; ?>
        detallesHtml += '</div>';
        
        Swal.fire({
            title: '📋 Modo Prueba',
            html: '<div style="text-align:center;">' +
                '<p><strong>Archivo validado correctamente</strong></p>' +
                '<p>Total registros a procesar: <?= $resultados['total'] ?></p>' +
                '<p style="color:#bc6e32;"><strong>⚠ Sin cambios en la base de datos</strong></p>' +
                '<hr>' +
                '<details>' +
                '<summary style="cursor:pointer; color:#AB886D;">Ver validaciones</summary>' +
                detallesHtml +
                '</details>' +
                '</div>',
            icon: 'info',
            confirmButtonColor: '#AB886D',
            width: '600px'
        });
    })();
    </script>
    <?php elseif($_GET['res_csv'] == 'error_archivo'): ?>
    <script>
    Swal.fire({ title: 'Error', text: 'No se pudo subir el archivo. Intenta nuevamente.', icon: 'error', confirmButtonColor: '#772C24' });
    </script>
    <?php elseif($_GET['res_csv'] == 'error_lectura'): ?>
    <script>
    Swal.fire({ title: 'Error', text: 'No se pudo leer el archivo CSV. Verifica el formato.', icon: 'error', confirmButtonColor: '#772C24' });
    </script>
    <?php elseif($_GET['res_csv'] == 'faltan_columnas'): ?>
    <script>
    Swal.fire({ 
        title: 'Formato incorrecto', 
        html: 'El archivo no tiene la columna requerida: <strong><?= htmlspecialchars($_GET['columna'] ?? '') ?></strong><br><br>Asegúrate de que el CSV tenga las columnas: nombre_producto, descripcion, id_marca, id_categoria, id_proveedor, talla, color, codigo_barras, precio_venta, stock',
        icon: 'error', 
        confirmButtonColor: '#772C24' 
    });
    </script>
    <?php endif; ?>
    <script>window.history.replaceState({}, document.title, "productos.php");</script>
<?php endif; ?>

<?php 
require __DIR__ . '/../layouts/admin-shell-end.php'; 
require __DIR__ . '/../layouts/admin-html-end.php'; 
?>