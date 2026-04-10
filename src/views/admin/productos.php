<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

// --- CONTROLADORES Y MODELOS ---
require_once "../../../controller/productosController.php";
require_once "../../../model/ProductosModel.php";
require_once "../../../controller/categoriasController.php";
require_once "../../../model/CategoriasModel.php";
require_once "../../../controller/marcasController.php";
require_once "../../../model/MarcasModel.php";

// 1. PROCESAR ACCIONES
$controlador = new ProductosController();
$controlador->ctrCrearProducto(); 
$controlador->ctrActualizarProducto(); 

// 2. LÓGICA DE PAGINACIÓN
$paginaActual = isset($_GET["pagina"]) ? (int)$_GET["pagina"] : 1;
if ($paginaActual < 1) { $paginaActual = 1; }

// ELIMINAR
if (isset($_POST["id_eliminar"])) {
    $respuesta = $controlador->ctrEliminarProducto(); 
    if ($respuesta == "ok") {
        echo '<script>window.location = "productos.php?pagina=' . $paginaActual . '";</script>';
    }
}

// RESTO DE LA PAGINACIÓN
$productosPorPagina = 5;
$base = ($paginaActual - 1) * $productosPorPagina;

// Cargamos datos
$todosLosProductos = ProductosController::ctrMostrarProductos(); 
$totalProductos = count($todosLosProductos);
$totalPaginas = ceil($totalProductos / $productosPorPagina);

$productos = ProductosController::ctrMostrarProductosPaginados("productos", "producto_variante", max(0, $base), $productosPorPagina);
$categorias = CategoriasController::ctrMostrarCategorias();
$marcas = MarcasController::ctrMostrarMarcas();

// Configuración Layout
$activeMenu = 'productos';
$pageTitle = 'Productos';
$pageStyles = ['/ElZapato/Assets/css/pages/admin-stats.css', '/ElZapato/Assets/css/pages/admin-productos.css'];
require __DIR__ . '/../layouts/admin-shell-start.php';

// Header
$pageHeading = 'Inventario de Productos';
$searchInputId = 'searchProduct';
$searchPlaceholder = 'Buscar por nombre, categoría...';
$showSearch = true;
require __DIR__ . '/../layouts/admin-header.php';
?>

<style>
    .switch-container {
        display: flex;
        align-items: center;
        gap: 12px;
        background: var(--primary-light); /* #E4E0E1 */
        padding: 12px;
        border-radius: 10px;
        margin-top: 5px;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 22px;
    }

    .switch input { opacity: 0; width: 0; height: 0; }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: var(--primary-soft); /* #D6C0B3 */
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 16px; width: 16px;
        left: 3px; bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    /* Color cuando está Activo: --primary-dark (#AB886D) */
    input:checked + .slider { background-color: var(--primary-dark); }
    input:checked + .slider:before { transform: translateX(24px); }

    #estado_label {
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
    }
</style>

<div class="productos-page">
    
    <div class="stats-grid stats-list">
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-boxes"></i> Total</span>
            <span class="stats-list-value"><?= $totalProductos ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-check-circle"></i> Activos</span>
            <span class="stats-list-value">
                <?= count(array_filter($todosLosProductos, fn($p) => strtolower($p['estado'] ?? "") == "activo")) ?>
            </span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-exclamation-triangle"></i> Stock Bajo</span>
            <span class="stats-list-value">
                <?= count(array_filter($todosLosProductos, fn($p) => ($p['stock'] ?? 0) <= 10)) ?>
            </span>
        </div>
    </div>

    <div class="actions-bar">
        <div class="actions-left">
            <button class="btn-outline-primary" id="btnNuevoProducto">
                <i class="fas fa-plus"></i> Registrar Calzado
            </button>
            <div class="filters">
                <select class="filter-select" id="filterCategory">
                    <option value="">Todas las Categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id_categoria'] ?>"><?= $cat['nombre_categoria'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="products-table">
            <thead>
                <tr>
                    <th>Vista</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Marca</th>
                    <th>Precio Venta</th>
                    <th>Existencias</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if($productos): foreach ($productos as $p): 
                    $stockClass = ($p['stock'] ?? 0) <= 10 ? 'stock-warning' : 'stock-ok';
                    $estadoClass = (strtolower($p['estado'] ?? '') == 'activo') ? 'badge-active' : 'badge-inactive';
                    
                    $ruta_img = "/ElZapato/Assets/img/zapa.jpeg"; 
                    $formatos = ['jpg', 'jpeg', 'png', 'webp'];
                    foreach($formatos as $f){
                        if(file_exists(__DIR__ . "/../../../Assets/img/productos/{$p['id_producto']}.$f")){
                            $ruta_img = "/ElZapato/Assets/img/productos/{$p['id_producto']}.$f";
                            break;
                        }
                    }
                ?>
                <tr data-id="<?= $p['id_producto'] ?>"
                    data-nombre="<?= htmlspecialchars($p['nombre_producto'] ?? "") ?>"
                    data-categoria="<?= $p['id_categoria'] ?>"
                    data-marca="<?= $p['id_marca'] ?>"
                    data-precio="<?= $p['precio_venta'] ?>"
                    data-stock="<?= $p['stock'] ?>"
                    data-descripcion="<?= htmlspecialchars($p['descripcion'] ?? "") ?>"
                    data-estado="<?= strtolower($p['estado'] ?? "activo") ?>">
                    
                    <td><img src="<?= $ruta_img ?>?v=<?= time() ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;"></td>
                    <td><strong><?= htmlspecialchars($p['nombre_producto'] ?? "") ?></strong></td>
                    <td><span class="badge badge-category"><?= htmlspecialchars($p['nombre_categoria'] ?? "Sin categoría") ?></span></td>
                    <td><?= htmlspecialchars($p['nombre_marca'] ?? 'Genérica') ?></td>
                    <td>$<?= number_format($p['precio_venta'] ?? 0, 2) ?></td>
                    <td><span class="stock-badge <?= $stockClass ?>"><?= $p['stock'] ?? 0 ?></span></td>
                    <td><span class="badge <?= $estadoClass ?>"><?= ucfirst($p['estado'] ?? 'Inactivo') ?></span></td>
                    <td>
                        <button class="btn-icon small" onclick="editProduct(this)" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon small btn-delete" onclick="deleteProduct(<?= $p['id_producto'] ?>)" style="color: var(--nocolor)" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-simple" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
        <span style="font-size: 0.85rem; color: #666;">Página <?= $paginaActual ?> de <?= $totalPaginas ?></span>
        <div style="display: flex; gap: 5px;">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): 
                $btnStyle = ($paginaActual == $i) ? 'background: var(--primary-dark); color: white;' : 'background: var(--primary-light); color: var(--text-dark);';
            ?>
                <a href="productos.php?pagina=<?= $i ?>" style="padding: 5px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; <?= $btnStyle ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
</div>

<div class="modal" id="productModal" style="display: none; align-items:center; justify-content:center; background: rgba(0,0,0,0.6); position:fixed; top:0; left:0; width:100%; height:100%; z-index:9999;">
    <div class="modal-content" style="background:white; padding:30px; border-radius:15px; width:480px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <form id="productForm" method="post" enctype="multipart/form-data">
            <h3 id="modalTitle" style="color: var(--primary-dark); margin-bottom:20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Producto</h3>
            <input type="hidden" name="id_producto" id="id_producto">
            
            <div style="text-align:center; margin-bottom:20px;">
                <img id="previewImg" src="/ElZapato/Assets/img/zapa.jpeg" style="width:120px; height:120px; object-fit:cover; border:2px dashed var(--primary-soft); border-radius:12px; cursor:pointer;" onclick="document.getElementById('imagen_producto').click()">
                <input type="file" name="imagen_producto" id="imagen_producto" style="display:none" accept="image/*" onchange="previewImage(this)">
            </div>

            <div class="form-group"><label>Nombre del Calzado</label><input type="text" name="nombre_producto" id="nombre_producto" class="form-control" required></div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <div class="form-group"><label>Categoría</label>
                    <select name="id_categoria" id="id_categoria" class="form-control">
                        <?php foreach($categorias as $c): ?> <option value="<?= $c['id_categoria'] ?>"><?= $c['nombre_categoria'] ?></option> <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Marca</label>
                    <select name="id_marca" id="id_marca" class="form-control">
                        <?php foreach($marcas as $m): ?> <option value="<?= $m['id_marca'] ?>"><?= $m['nombre_marca'] ?></option> <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <div class="form-group"><label>Precio Venta ($)</label><input type="number" step="0.01" name="precio_venta" id="precio_venta" class="form-control" required></div>
                <div class="form-group"><label>Stock Inicial</label><input type="number" name="stock" id="stock" class="form-control" required></div>
            </div>

            <div class="form-group" id="groupEstado" style="display:none; margin-top:10px;">
                <label>Estado del Producto</label>
                <div class="switch-container">
                    <label class="switch">
                        <input type="checkbox" id="estado_switch" onchange="syncEstado(this)">
                        <span class="slider"></span>
                    </label>
                    <span id="estado_label">Activo</span>
                    <input type="hidden" name="estado" id="estado" value="activo">
                </div>
            </div>

            <div style="margin-top:25px; display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" onclick="closeModal()" class="btn-secondary" style="padding: 10px 20px; border-radius: 8px;">Cerrar</button>
                <button type="submit" class="btn-primary" style="padding: 10px 20px; border-radius: 8px; background: var(--primary-dark); border:none; color:white;">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) { document.getElementById('previewImg').src = e.target.result; }
        reader.readAsDataURL(input.files[0]);
    }
}

function closeModal() { document.getElementById('productModal').style.display = 'none'; }

// --- LÓGICA DEL SWITCH CON TUS COLORES ---
function syncEstado(checkbox) {
    const hiddenInput = document.getElementById('estado');
    const label = document.getElementById('estado_label');
    
    if (checkbox.checked) {
        hiddenInput.value = "activo";
        label.innerText = "Activo";
        label.style.color = "var(--primary-dark)"; // #AB886D
    } else {
        hiddenInput.value = "inactivo";
        label.innerText = "Inactivo";
        label.style.color = "var(--nocolor)";    // #772C24
    }
}

function editProduct(btn) {
    const tr = btn.closest('tr');
    document.getElementById('id_producto').value = tr.dataset.id;
    document.getElementById('nombre_producto').value = tr.dataset.nombre;
    document.getElementById('id_categoria').value = tr.dataset.categoria;
    document.getElementById('id_marca').value = tr.dataset.marca;
    document.getElementById('precio_venta').value = tr.dataset.precio;
    document.getElementById('stock').value = tr.dataset.stock;
    document.getElementById('previewImg').src = tr.querySelector('img').src;
    
    // Sincronizar Switch
    const estado = tr.dataset.estado;
    const switchEl = document.getElementById('estado_switch');
    switchEl.checked = (estado === 'activo');
    syncEstado(switchEl);

    document.getElementById('groupEstado').style.display = 'block';
    document.getElementById('modalTitle').innerText = 'Editar Calzado';
    document.getElementById('productModal').style.display = 'flex';
}

document.getElementById('btnNuevoProducto').onclick = function() {
    document.getElementById('productForm').reset();
    document.getElementById('id_producto').value = "";
    document.getElementById('groupEstado').style.display = 'none';
    document.getElementById('previewImg').src = "/ElZapato/Assets/img/zapa.jpeg";
    document.getElementById('modalTitle').innerText = 'Nuevo Registro de Calzado';
    document.getElementById('estado').value = "activo"; // Reset valor oculto
    document.getElementById('productModal').style.display = 'flex';
};

function deleteProduct(id) {
    if (confirm('¿Deseas eliminar este producto permanentemente?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="id_eliminar" value="${id}">`;
        document.body.appendChild(form);
        form.submit();
    }
}

document.getElementById('searchProduct').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    document.querySelectorAll('.products-table tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>
<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>