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

// 1. PROCESAR ACCIONES
$controlador = new ProductosController();
$controlador->ctrCrearProducto(); 
$controlador->ctrActualizarProducto(); 

if (isset($_POST["id_eliminar"])) {
    $respuesta = $controlador->ctrEliminarProducto(); 
    if ($respuesta == "ok") {
        header("Location: productos.php");
        exit;
    }
}

// 2. CARGAR DATOS
$productos = ProductosController::ctrMostrarProductos(); 
$categorias = CategoriasController::ctrMostrarCategorias();
$marcas = MarcasController::ctrMostrarMarcas();

// 3. CÁLCULO DE ESTADÍSTICAS
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
            if($cant <= 5) $bajoStockCount++;
        }
    }
}

// Layout Setup
$activeMenu = 'productos';
$pageTitle = 'Inventario | ElZapato';
$pageStyles = ['/ElZapato/Assets/css/pages/admin-stats.css', '/ElZapato/Assets/css/pages/admin-productos.css'];
require __DIR__ . '/../layouts/admin-shell-start.php';

$pageHeading = 'Gestión de Inventario';
$searchInputId = 'searchProduct';
$searchPlaceholder = 'Buscar por nombre, color o SKU...';
$showSearch = true;
require __DIR__ . '/../layouts/admin-header.php';
?>

<style>
    .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
    .stat-card { background: white; padding: 15px; border-radius: 12px; border-left: 5px solid #AB886D; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    .stat-card h4 { margin: 0; color: #888; font-size: 0.8rem; text-transform: uppercase; }
    .stat-card p { margin: 5px 0 0; font-size: 1.5rem; font-weight: bold; color: #4D3B2E; }

    /* --- TABLA CON ALTURA FIJA Y SCROLL --- */
    .table-scroll { 
        height: 350px; 
        overflow-y: scroll; 
        border: 1px solid #D6C0B3; 
        border-radius: 10px; 
        background: white;
        position: relative;
    }

    .products-table { width: 100%; border-collapse: collapse; table-layout: fixed; }

    .table-scroll::-webkit-scrollbar { width: 8px; }
    .table-scroll::-webkit-scrollbar-track { background: #f9f9f9; border-radius: 10px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #AB886D; border-radius: 10px; }
    .table-scroll::-webkit-scrollbar-thumb:hover { background: #4D3B2E; }

    .products-table thead th { 
        position: sticky; top: 0; background: #AB886D !important; color: white !important; 
        padding: 15px 12px; z-index: 20; box-shadow: 0 2px 2px rgba(0,0,0,0.1);
    }

    .products-table td { padding: 12px 10px; border-bottom: 1px solid #eee; vertical-align: middle; }

    /* Columnas fijas */
    .products-table th:nth-child(1), .products-table td:nth-child(1) { width: 70px; text-align: center; } 
    .products-table th:nth-child(6), .products-table td:nth-child(6) { width: 85px; } 
    .products-table th:nth-child(7), .products-table td:nth-child(7) { width: 75px; } 
    .products-table th:nth-child(9), .products-table td:nth-child(9) { width: 100px; } /* Ajuste acciones */

    /* ALINEACIÓN DE BOTONES EN ACCIONES */
    .acciones-flex {
        display: flex;
        gap: 12px;
        justify-content: flex-start;
        align-items: center;
    }

    .fila-inactiva { opacity: 0.6; background-color: #f9f9f9 !important; }
    
    .switch-container { display: flex; align-items: center; gap: 10px; background: #f4f4f4; padding: 10px; border-radius: 8px; }
    .switch { position: relative; display: inline-block; width: 40px; height: 20px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 20px; }
    .slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: #AB886D; }
    input:checked + .slider:before { transform: translateX(20px); }
</style>

<div class="productos-page">

    <div class="stats-container">
        <div class="stat-card"><h4>Modelos</h4><p><?= $totalModelos ?></p></div>
        <div class="stat-card" style="border-left-color: #4D3B2E;"><h4>Stock Total</h4><p><?= $totalStock ?></p></div>
        <div class="stat-card" style="border-left-color: #772C24;"><h4>Bajo Stock</h4><p style="color: #772C24;"><?= $bajoStockCount ?></p></div>
    </div>

    <div class="actions-bar" style="margin-bottom: 15px;">
        <button class="btn-primary" id="btnNuevoProducto" style="background:#AB886D; border:none; padding:10px 20px; border-radius:8px; color:white; cursor:pointer; font-weight:bold;">
            <i class="fas fa-plus"></i> Registrar Nuevo
        </button>
    </div>

    <div class="table-scroll">
        <table class="products-table" id="tablaProductos">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Producto</th>
                    <th>Color</th>
                    <th>Talla</th>
                    <th>Marca</th>
                    <th>Precio</th>
                    <th>Stock</th>
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
                            
                            $v_talla = $d[0]; 
                            $v_color = $d[1]; 
                            $v_precio = $d[2]; 
                            $v_stock = $d[3];
                            $v_sku = $d[4] ?? 'N/A'; 
                            $v_estado = $d[5] ?? 'activo';
                            $v_id_unico = $d[6] ?? '0'; 

                            $imgDefault = "/ElZapato/Assets/img/zapa.jpeg";
                            $pathImg = "/Assets/img/productos/" . $v_id_unico . ".jpg";
                            $img = file_exists($basePath . $pathImg) ? "/ElZapato" . $pathImg : $imgDefault;
                ?>
                <tr class="<?= ($v_estado == 'inactivo') ? 'fila-inactiva' : '' ?>" 
                    data-id-p="<?= $p['id_producto'] ?>" data-id-v="<?= $v_id_unico ?>"
                    data-nombre="<?= htmlspecialchars($p['nombre_producto']) ?>"
                    data-categoria="<?= $p['id_categoria'] ?>" data-marca="<?= $p['id_marca'] ?>"
                    data-precio="<?= $v_precio ?>" data-stock="<?= $v_stock ?>"
                    data-talla="<?= $v_talla ?>" data-color="<?= $v_color ?>" data-v-estado="<?= $v_estado ?>">
                    
                    <td><img src="<?= $img ?>" width="45" height="45" style="object-fit:cover; border-radius:8px; border: 1px solid #ddd;"></td>
                    <td><strong><?= $p['nombre_producto'] ?></strong><br><small><?= $v_sku ?></small></td>
                    <td><?= $v_color ?></td>
                    <td><strong><?= $v_talla ?></strong></td>
                    <td><?= $p['nombre_marca'] ?></td>
                    <td>$<?= number_format($v_precio, 2) ?></td>
                    <td><span style="font-weight:bold; color: <?= ($v_stock <= 5) ? '#772C24' : '#AB886D' ?>"><?= $v_stock ?></span></td>
                    <td><span style="font-size:0.7rem; font-weight:bold; color:white; background:<?= ($v_estado == 'activo') ? '#AB886D' : '#772C24' ?>; padding:2px 5px; border-radius:4px;"><?= strtoupper($v_estado) ?></span></td>
                    <td>
                        <div class="acciones-flex">
                            <button type="button" onclick="btnEditProduct(this)" style="color:#AB886D; border:none; background:none; cursor:pointer; font-size:1.1rem;"><i class="fas fa-edit"></i></button>
                            <button type="button" onclick="deleteProduct(<?= $p['id_producto'] ?>)" style="color:#772C24; border:none; background:none; cursor:pointer; font-size:1.1rem;"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal" id="productModal" style="display: none; align-items:center; justify-content:center; background: rgba(0,0,0,0.6); position:fixed; top:0; left:0; width:100%; height:100%; z-index:9999;">
    <div class="modal-content" style="background:white; padding:25px; border-radius:15px; width:480px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <form id="productForm" method="post" enctype="multipart/form-data">
            <h3 id="modalTitle" style="color: #AB886D; margin-bottom:15px;">Editar Detalle</h3>
            <input type="hidden" name="id_producto" id="id_producto">
            <input type="hidden" name="id_variante" id="id_variante"> 
            
            <div class="form-group" style="margin-bottom:12px;">
                <label>Nombre del Modelo</label>
                <input type="text" name="nombre_producto" id="nombre_producto" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;" required>
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label>Categoría</label>
                    <select name="id_categoria" id="id_categoria" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;">
                        <?php foreach($categorias as $c): ?> <option value="<?= $c['id_categoria'] ?>"><?= $c['nombre_categoria'] ?></option> <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Marca</label>
                    <select name="id_marca" id="id_marca" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;">
                        <?php foreach($marcas as $m): ?> <option value="<?= $m['id_marca'] ?>"><?= $m['nombre_marca'] ?></option> <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label>Color</label>
                    <input type="text" name="color" id="color" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;" required>
                </div>
                <div>
                    <label>Talla</label>
                    <input type="text" name="talla" id="talla" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:15px;">
                <div>
                    <label>Precio ($)</label>
                    <input type="number" step="0.01" name="precio_venta" id="precio_venta" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;" required>
                </div>
                <div>
                    <label>Stock</label>
                    <input type="number" name="stock" id="stock" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;" required>
                </div>
            </div>

            <div id="groupEstado" style="margin-bottom:15px;">
                <label>Estado de la Variante</label>
                <div class="switch-container">
                    <label class="switch">
                        <input type="checkbox" id="estado_switch" onchange="document.getElementById('estado_v').value = this.checked ? 'activo' : 'inactivo'">
                        <span class="slider"></span>
                    </label>
                    <input type="hidden" name="estado_v" id="estado_v" value="activo">
                    <span id="txtEstado" style="font-weight:bold; color:#AB886D;">ACTIVO</span>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" onclick="closeModal()" style="padding:10px 15px; border:none; background:#eee; border-radius:5px; cursor:pointer;">Cancelar</button>
                <button type="submit" style="padding:10px 25px; border:none; background:#AB886D; color:white; border-radius:5px; cursor:pointer; font-weight:bold;">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('searchProduct').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    document.querySelectorAll('#tablaProductos tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
    });
});

function btnEditProduct(btn) {
    const tr = btn.closest('tr');
    document.getElementById('id_producto').value = tr.dataset.idP;
    document.getElementById('id_variante').value = tr.dataset.idV; 
    document.getElementById('nombre_producto').value = tr.dataset.nombre;
    document.getElementById('id_categoria').value = tr.dataset.categoria;
    document.getElementById('id_marca').value = tr.dataset.marca;
    document.getElementById('precio_venta').value = tr.dataset.precio;
    document.getElementById('stock').value = tr.dataset.stock;
    document.getElementById('talla').value = tr.dataset.talla;
    document.getElementById('color').value = tr.dataset.color;
    
    const est = tr.dataset.vEstado;
    const isActivo = (est === 'activo');
    document.getElementById('estado_switch').checked = isActivo;
    document.getElementById('estado_v').value = est;
    const txt = document.getElementById('txtEstado');
    txt.innerText = est.toUpperCase();
    txt.style.color = isActivo ? '#AB886D' : '#772C24';
    document.getElementById('modalTitle').innerText = 'Editar Detalle (Variante #' + tr.dataset.idV + ')';
    document.getElementById('productModal').style.display = 'flex';
}

function closeModal() { document.getElementById('productModal').style.display = 'none'; }

document.getElementById('btnNuevoProducto').onclick = function() {
    document.getElementById('productForm').reset();
    document.getElementById('id_producto').value = "";
    document.getElementById('id_variante').value = "";
    document.getElementById('modalTitle').innerText = 'Nuevo Registro';
    document.getElementById('productModal').style.display = 'flex';
};

document.getElementById('estado_switch').addEventListener('change', function() {
    const txt = document.getElementById('txtEstado');
    if(this.checked) {
        txt.innerText = "ACTIVO";
        txt.style.color = "#AB886D";
    } else {
        txt.innerText = "INACTIVO";
        txt.style.color = "#772C24";
    }
});

function deleteProduct(id) {
    if(confirm('¿Está seguro de eliminar este producto y sus variantes?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'id_eliminar';
        input.value = id;
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php 
require __DIR__ . '/../layouts/admin-shell-end.php'; 
require __DIR__ . '/../layouts/admin-html-end.php'; 
?>