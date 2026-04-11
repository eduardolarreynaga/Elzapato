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

// Ejecutar una SOLA función que maneja crear y actualizar
$controlador->ctrProcesarProducto(); 

if (isset($_POST["id_eliminar_v"])) {
    $respuesta = $controlador->ctrEliminarProducto(); 
    if ($respuesta == "ok") {
        header("Location: productos.php?res=eliminado");
        exit;
    }
}

// 2. CARGAR DATOS (Después de procesar acciones para ver cambios)
$productos = ProductosController::ctrMostrarProductos(); 
$categorias = CategoriasController::ctrMostrarCategorias();
$marcas = MarcasController::ctrMostrarMarcas();
$proveedores = ProductosController::ctrMostrarProveedores();

// 3. ESTADÍSTICAS RÁPIDAS
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
            if($cant <= 10) $bajoStockCount++;
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
    
    .sku-auto-hint {
        font-size: 0.7rem;
        color: #AB886D;
        margin-top: 4px;
        display: block;
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
    <div class="stats-container">
        <div class="stat-card"><h4>Modelos Totales</h4><p><?= $totalModelos ?></p></div>
        <div class="stat-card" style="border-left-color: #4D3B2E;"><h4>Stock Global</h4><p><?= $totalStock ?></p></div>
        <div class="stat-card" style="border-left-color: #772C24;"><h4>Alertas Stock</h4><p style="color: #772C24;"><?= $bajoStockCount ?></p></div>
    </div>

    <div class="actions-bar" style="margin-bottom: 15px;">
        <button class="btn-primary" id="btnNuevoProducto" style="background:#AB886D; border:none; padding:12px 24px; border-radius:8px; color:white; cursor:pointer; font-weight:bold; display:flex; align-items:center; gap:8px;">
            <i class="fas fa-plus"></i> Nuevo Producto
        </button>
    </div>

    <div class="table-scroll">
        <table class="products-table" id="tablaProductos">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Producto / SKU</th>
                    <th>Marca</th>
                    <th>Precio</th>
                    <th>Stock Actual</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if($productos): 
                    foreach ($productos as $p): 
                        $variantes = ($p['info_variantes'] != '') ? explode("||", $p['info_variantes']) : [];
                        foreach($variantes as $v):
                            $d = explode("|", $v);
                            $v_talla = $d[0]; $v_color = $d[1]; $v_precio = $d[2]; 
                            $v_stock = (int)$d[3]; $v_sku = $d[4] ?? 'N/A'; 
                            $v_estado = $d[5] ?? 'activo'; $v_id_v = $d[6] ?? '0'; 

                            // LÓGICA DE IMAGEN CORREGIDA
                            $imgDefault = "/ElZapato/Assets/img/zapa.jpeg";
                            $rutaImagen = "/ElZapato/Assets/img/productos/" . $v_id_v . ".jpg";
                            $fullPathServer = $_SERVER['DOCUMENT_ROOT'] . $rutaImagen;
                            
                            // Verificar si existe la imagen
                            $img = file_exists($fullPathServer) ? $rutaImagen . "?t=" . time() : $imgDefault;

                            $max = 50; 
                            $perc = min(($v_stock / $max) * 100, 100);
                            $bColor = ($v_stock <= 5) ? "bg-danger" : (($v_stock <= 15) ? "bg-warning" : "bg-success");
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
                    data-codigo="<?= htmlspecialchars($v_sku) ?>"
                    data-descripcion="<?= htmlspecialchars($p['descripcion'] ?? 'Sin descripción.') ?>">
                    
                    <td><img src="<?= $img ?>" width="60" height="60" style="object-fit:cover; border-radius:8px; border: 1px solid #ddd; display: block; margin: 0 auto;"></td>
                    <td style="padding-left: 15px;"><strong><?= $p['nombre_producto'] ?></strong><br><small style="color:#AB886D; font-weight: bold;"><?= $v_sku ?></small></td>
                    <td><?= $p['nombre_marca'] ?></td>
                    <td><strong style="color: #4D3B2E;">$<?= number_format($v_precio, 2) ?></strong></td>
                    <td>
                        <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-weight:bold;">
                            <span>Cant: <?= $v_stock ?></span>
                        </div>
                        <div class="progress-bg"><div class="progress-bar <?= $bColor ?>" style="width: <?= $perc ?>%;"></div></div>
                    </td>
                    <td>
                        <span style="font-size:0.65rem; font-weight:bold; color:white; background:<?= ($v_estado == 'activo') ? '#AB886D' : '#772C24' ?>; padding:4px 10px; border-radius:20px; text-transform:uppercase;">
                            <?= $v_estado ?>
                        </span>
                    </td>
                    <td>
                        <div style="display:flex; gap:15px; justify-content:center;">
                            <button type="button" onclick="viewProduct(this)" title="Ver Detalle" style="color:#4D3B2E; background:none; border:none; cursor:pointer; font-size:1.1rem;"><i class="fas fa-eye"></i></button>
                            <button type="button" onclick="btnEditProduct(this)" title="Editar" style="color:#AB886D; background:none; border:none; cursor:pointer; font-size:1.1rem;"><i class="fas fa-edit"></i></button>
                            <button type="button" onclick="deleteVariant(<?= $v_id_v ?>)" title="Eliminar" style="color:#772C24; background:none; border:none; cursor:pointer; font-size:1.1rem;"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

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

<div class="modal" id="productModal" style="display: none; align-items:center; justify-content:center; background: rgba(0,0,0,0.7); position:fixed; top:0; left:0; width:100%; height:100%; z-index:9998;">
    <div class="modal-content" style="background:white; padding:30px; border-radius:15px; width:750px; max-height:90vh; overflow-y:auto;">
        <form id="productForm" method="post" enctype="multipart/form-data">
            <h3 id="modalTitle" style="color: #AB886D; border-bottom: 2px solid #f4f1ef; padding-bottom:10px;">Editar Producto</h3>
            
            <!-- CAMPO OCULTO PARA DIFERENCIAR ACCIÓN -->
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
                            <option value="<?= $c['id_categoria'] ?>"><?= $c['nombre_categoria'] ?></option> 
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Marca *</label>
                    <select name="id_marca" id="id_marca" required>
                        <?php foreach($marcas as $m): ?> 
                            <option value="<?= $m['id_marca'] ?>"><?= $m['nombre_marca'] ?></option> 
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- CAMPO PROVEEDOR AGREGADO -->
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
                    <label>SKU (Código de Barras)</label>
                    <input type="text" name="codigo_barras" id="codigo_barras" placeholder="Déjalo en blanco para auto-generar">
                    <small class="sku-auto-hint">💡 Si lo dejas vacío, se generará automáticamente un número correlativo</small>
                </div>
                <div class="form-group"><label>Precio *</label><input type="number" step="0.01" name="precio_venta" id="precio_venta" required></div>
            </div>

            <div class="form-row">
                <div class="form-group"><label>Stock *</label><input type="number" name="stock" id="stock" required></div>
                <div class="form-group">
                    <label>Estado</label>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <label class="switch"><input type="checkbox" id="estado_switch" onchange="actualizarTxtEstado(this.checked)"><span class="slider"></span></label>
                        <input type="hidden" name="estado_v" id="estado_v" value="activo">
                        <span id="txtEstado" style="font-weight:bold;">ACTIVO</span>
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
// --- BUSCADOR ---
const searchInput = document.getElementById('searchProduct');
if(searchInput) {
    searchInput.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('#tablaProductos tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
        });
    });
}

// --- VER DETALLE ---
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
    badge.innerText = d.vEstado;
    badge.style.background = (d.vEstado === 'activo') ? '#AB886D' : '#772C24';
    document.getElementById('viewModal').style.display = 'flex';
}

function closeViewModal() { document.getElementById('viewModal').style.display = 'none'; }

// --- EDITAR (UPDATE) ---
function btnEditProduct(btn) {
    const d = btn.closest('tr').dataset;
    
    // VALIDAR que tenemos el ID de la variante
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
    
    const isActivo = (d.vEstado === 'activo');
    document.getElementById('estado_switch').checked = isActivo;
    actualizarTxtEstado(isActivo);
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
}

// --- NUEVO PRODUCTO (INSERT) ---
document.getElementById('btnNuevoProducto').onclick = function() {
    document.getElementById('productForm').reset();
    document.getElementById('accion').value = 'crear';
    document.getElementById('id_producto').value = "";
    document.getElementById('id_variante').value = "";
    document.getElementById('id_proveedor').value = "";
    document.getElementById('codigo_barras').value = ""; // Limpiar SKU para que se auto-genere
    document.getElementById('modalTitle').innerText = 'Nuevo Producto';
    document.getElementById('currentImage').src = "/ElZapato/Assets/img/zapa.jpeg";
    document.getElementById('productModal').style.display = 'flex';
};

// Preview de imagen seleccionada
document.getElementById('imagen_producto').onchange = function(e) {
    const preview = document.getElementById('newImagePreview');
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => preview.innerHTML = `<img src="${e.target.result}" style="max-width:80px; border-radius:8px; border:2px solid #AB886D;">`;
        reader.readAsDataURL(this.files[0]);
    }
};

// --- ELIMINAR ---
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

// Mensajes SweetAlert de respuesta del controlador
<?php if(isset($_GET['res'])): ?>
    <?php if($_GET['res'] == 'duplicado'): ?>
    Swal.fire({ 
        title: '¡Código de Barras Duplicado!', 
        html: 'El SKU/Código de barras <strong><?= htmlspecialchars($_GET['sku'] ?? '') ?></strong> ya está en uso.<br><br>Por favor, utiliza un código diferente o déjalo en blanco para que se genere automáticamente.',
        icon: 'error', 
        confirmButtonColor: '#772C24',
        confirmButtonText: 'Entendido'
    });
    <?php elseif($_GET['res'] == 'error_sku'): ?>
    Swal.fire({ 
        title: '¡Error al generar SKU!', 
        text: 'No se pudo generar un SKU automáticamente. Por favor, contacta al administrador.', 
        icon: 'error', 
        confirmButtonColor: '#772C24' 
    });
    <?php elseif($_GET['res'] == 'error'): ?>
    Swal.fire({ 
        title: '¡Error!', 
        text: 'Ocurrió un error al procesar la operación. Por favor, intenta de nuevo.', 
        icon: 'error', 
        confirmButtonColor: '#772C24' 
    });
    <?php else: ?>
    const msjs = {
        creado: '✅ Producto registrado correctamente', 
        actualizado: '✅ Cambios guardados con éxito', 
        eliminado: '✅ Variante eliminada'
    };
    Swal.fire({ 
        title: '¡Operación Exitosa!', 
        text: msjs['<?= $_GET['res'] ?>'] || 'Proceso completado', 
        icon: 'success', 
        confirmButtonColor: '#AB886D' 
    });
    <?php endif; ?>
    // Limpiar la URL para evitar que el mensaje salga al recargar
    window.history.replaceState({}, document.title, "productos.php");
<?php endif; ?>
</script>

<?php 
require __DIR__ . '/../layouts/admin-shell-end.php'; 
require __DIR__ . '/../layouts/admin-html-end.php'; 
?>