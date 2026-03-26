<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

$activeMenu = 'productos';
$pageTitle = 'Productos';
$pageStyles = [
    '/ElZapato/Assets/css/pages/admin-stats.css',
    '/ElZapato/Assets/css/pages/admin-productos.css'
];
require __DIR__ . '/../layouts/admin-shell-start.php';

$pageHeading = 'Productos';
$searchInputId = 'searchProduct';
$searchPlaceholder = 'Buscar productos...';
$showSearch = true;
require __DIR__ . '/../layouts/admin-header.php';

// --- CONTROLADORES Y MODELOS ---
require_once "../../../controller/productosController.php";
require_once "../../../model/ProductosModel.php";
require_once "../../../controller/categoriasController.php";
require_once "../../../model/CategoriasModel.php";
require_once "../../../controller/marcasController.php";
require_once "../../../model/MarcasModel.php";

// Procesar acciones POST
$controlador = new ProductosController();
$controlador->ctrCrearProducto();   
$controlador->ctrActualizarProducto(); 

// --- LÓGICA DE PAGINACIÓN ---
$productosPorPagina = 5;
$paginaActual = isset($_GET["pagina"]) ? (int)$_GET["pagina"] : 1;
$base = ($paginaActual - 1) * $productosPorPagina;

// Cargar datos
$todosLosProductos = ProductosController::ctrMostrarProductos(); // Para contar total
$totalProductos = count($todosLosProductos);
$totalPaginas = ceil($totalProductos / $productosPorPagina);

// Productos solo de la página actual
$productos = ProductosController::ctrMostrarProductosPaginados("productos", "producto_variante", $base, $productosPorPagina);
$categorias = CategoriasController::ctrMostrarCategorias();
$marcas = MarcasController::ctrMostrarMarcas();
?>

<div class="productos-page">
    <!-- Resumen de productos -->
    <div class="stats-grid stats-list">
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-boxes"></i> Total</span>
            <span class="stats-list-value"><?= $totalProductos ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-check-circle"></i> Activos</span>
            <span class="stats-list-value"><?= count(array_filter($todosLosProductos, fn($p)=>strtolower($p['estado'])=="activo")) ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-exclamation-triangle"></i> Stock Bajo</span>
            <span class="stats-list-value"><?= count(array_filter($todosLosProductos, fn($p)=>($p['stock'] ?? 0) <= 10)) ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-dollar-sign"></i> Valor</span>
            <span class="stats-list-value">$<?= number_format(array_sum(array_map(fn($p)=>($p['precio_venta'] ?? 0) * ($p['stock'] ?? 0), $todosLosProductos)) / 1000, 1) ?>k</span>
        </div>
    </div>

    <!-- Barra de acciones -->
    <div class="actions-bar">
        <div class="actions-left">
            <button class="btn-outline-primary" id="btnNuevoProducto">
                <i class="fas fa-plus"></i> Nuevo Producto
            </button>
            <div class="filters">
                <select class="filter-select" id="filterCategory">
                    <option value="">Categoría</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id_categoria'] ?>"><?= $cat['nombre_categoria'] ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn-outline-primary" id="btnResetProductoFiltros" type="button">
                    <i class="fas fa-times"></i> Limpiar
                </button>
            </div>
        </div>
        <div class="actions-right">
            <button class="btn-icon" title="Importar"><i class="fas fa-download"></i></button>
            <a href="../../files/exportar-productos.php" class="btn-icon" title="Exportar" target="_blank">
                <i class="fas fa-upload"></i>
            </a>
        </div>
    </div>

    <!-- Tabla de productos -->
    <div class="table-container">
        <table class="products-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Marca</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th style="width: 100px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $p): 
                    $stockClass = ($p['stock'] ?? 0) <= 10 ? 'stock-warning' : 'stock-ok';
                    $estadoClass = strtolower($p['estado'] ?? '') == 'activo' ? 'badge-active' : 'badge-inactive';
                ?>
                <tr data-id="<?= $p['id_producto'] ?>"
                    data-nombre="<?= htmlspecialchars($p['nombre_producto']) ?>"
                    data-categoria="<?= $p['id_categoria'] ?>"
                    data-marca="<?= $p['id_marca'] ?>"
                    data-precio="<?= $p['precio_venta'] ?>"
                    data-stock="<?= $p['stock'] ?>"
                    data-descripcion="<?= htmlspecialchars($p['descripcion']) ?>"
                    data-estado="<?= $p['estado'] ?>">
                    <td><span class="product-name"><?= htmlspecialchars($p['nombre_producto']) ?></span></td>
                    <td><span class="badge badge-category"><?= htmlspecialchars($p['nombre_categoria']) ?></span></td>
                    <td><?= htmlspecialchars($p['nombre_marca'] ?? 'N/A') ?></td>
                    <td>$<?= number_format($p['precio_venta'] ?? 0, 2) ?></td>
                    <td><span class="stock-badge <?= $stockClass ?>"><?= $p['stock'] ?? 0 ?></span></td>
                    <td><span class="badge <?= $estadoClass ?>"><?= ucfirst($p['estado'] ?? 'Inactivo') ?></span></td>
                    <td>
                        <button class="btn-icon small" title="Editar" onclick="editProduct(this)"><i class="fas fa-edit"></i></button>
                        <button class="btn-icon small">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación Simple Dinámica -->
    <div class="pagination-simple" style="display: flex; justify-content: space-between; align-items: center; margin-top: 25px; padding: 10px 8px;">
        
        <span class="pagination-info" style="color: #888; font-size: 0.9rem;">
            Mostrando <?= ($base + 1) ?>-<?= ($base + count($productos)) ?> de <?= $totalProductos ?> productos
        </span>

        <div class="pagination-pages" style="display: flex; gap: 8px; align-items: center;">
            <?php
            $rango = 2; // Número de páginas a mostrar alrededor de la actual

            for ($i = 1; $i <= $totalPaginas; $i++) {
                // Lógica para mostrar los botones o los puntos (...)
                if ($i == 1 || $i == $totalPaginas || ($i >= $paginaActual - $rango && $i <= $paginaActual + $rango)) {
                    $activeClass = ($paginaActual == $i) ? 'active' : '';
                    $style = ($paginaActual == $i) 
                        ? 'background: #A67C52; color: white; border: 1px solid #A67C52;' 
                        : 'background: white; color: #333; border: 1px solid #ddd;';
                    
                    echo '<a href="productos?pagina='.$i.'" class="page-btn '.$activeClass.'" 
                            style="padding: 6px 12px; border-radius: 4px; text-decoration: none; min-width: 35px; text-align: center; font-weight: 500; font-size: 0.9rem; '.$style.'">
                            '.$i.'
                        </a>';
                } 
                // Mostrar puntos suspensivos
                elseif ($i == $paginaActual - $rango - 1 || $i == $paginaActual + $rango + 1) {
                    echo '<span class="page-dots" style="color: #888; padding: 0 5px;">...</span>';
                }
            }
            ?>
        </div>
    </div>

</div>

<!-- Modal Crear / Editar Producto (Mismo código anterior) -->
<div class="modal" id="productModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fas fa-box"></i> Nuevo Producto</h3>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="productForm" method="post">
                <input type="hidden" name="id_producto" id="id_producto">
                <div class="form-group"><label>Nombre</label><input type="text" name="nombre_producto" id="nombre_producto" class="form-control" required></div>
                <div class="form-row">
                    <div class="form-group"><label>Categoría</label>
                        <select name="id_categoria" id="id_categoria" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($categorias as $cat): ?><option value="<?= $cat['id_categoria'] ?>"><?= $cat['nombre_categoria'] ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Marca</label>
                        <select name="id_marca" id="id_marca" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($marcas as $m): ?><option value="<?= $m['id_marca'] ?>"><?= $m['nombre_marca'] ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Precio</label><input type="number" step="0.01" name="precio_venta" id="precio_venta" class="form-control" required></div>
                    <div class="form-group"><label>Stock</label><input type="number" name="stock" id="stock" class="form-control" required></div>
                </div>
                <div class="form-group"><label>Descripción</label><textarea name="descripcion" id="descripcion" class="form-control"></textarea></div>
                <div class="form-group" id="groupEstado" style="display:none;"><label>Estado</label><select name="estado" id="estado" class="form-control"><option value="activo">Activo</option><option value="inactivo">Inactivo</option></select></div>
                <div class="modal-footer"><button type="button" class="btn-secondary" onclick="closeModal()">Cancelar</button><button type="submit" class="btn-primary">Guardar Cambios</button></div>
            </form>
        </div>
    </div>
</div>

<script>
const modal = document.getElementById('productModal');

// Búsqueda en tiempo real
document.getElementById('searchProduct').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    document.querySelectorAll('.products-table tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});

// Modal de Nuevo
document.getElementById('btnNuevoProducto').onclick = function() {
    document.getElementById('productForm').reset();
    document.getElementById('id_producto').value = "";
    document.getElementById('groupEstado').style.display = 'none';
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus"></i> Nuevo Producto';
    modal.style.display = 'flex';
};

// Función Editar
function editProduct(btn) {
    const tr = btn.closest('tr');
    document.getElementById('id_producto').value = tr.dataset.id;
    document.getElementById('nombre_producto').value = tr.dataset.nombre;
    document.getElementById('id_categoria').value = tr.dataset.categoria;
    document.getElementById('id_marca').value = tr.dataset.marca;
    document.getElementById('precio_venta').value = tr.dataset.precio;
    document.getElementById('stock').value = tr.dataset.stock;
    document.getElementById('descripcion').value = tr.dataset.descripcion;
    document.getElementById('estado').value = tr.dataset.estado;
    document.getElementById('groupEstado').style.display = 'block';
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Editar Producto';
    modal.style.display = 'flex';
}

function closeModal() { modal.style.display = 'none'; }
window.onclick = (e) => { if (e.target == modal) closeModal(); }
</script>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>
