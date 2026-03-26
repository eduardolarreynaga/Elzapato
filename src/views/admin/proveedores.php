<?php
// 1. Cargar dependencias con rutas corregidas (3 niveles hacia arriba)
require_once __DIR__ . '/../../config/auth.php'; // Este asumo que sigue en src/config
require_auth('admin');

// 2. Cargar Modelos y Controladores desde la RAÍZ (../../../)
require_once __DIR__ . '/../../../model/proveedor_model.php';
require_once __DIR__ . '/../../../controller/proveedor_controller.php';

// 3. Inicializar controlador y procesar lógica
$controlador = new ControladorProveedor();

// Si se envió el formulario de nuevo proveedor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["nuevoNombreEmpresa"])) {
    $controlador->ctrCrearProveedor();
}

// Obtener datos para la vista
$proveedores = $controlador->ctrMostrarProveedores();
$stats = $controlador->ctrMostrarEstadisticas();

// 4. Configuración de cabeceras de la página
$activeMenu = 'proveedores';
$pageTitle = 'Proveedores';
$pageStyles = ['/ElZapato/Assets/css/pages/admin-stats.css', '/ElZapato/Assets/css/pages/admin-proveedores.css'];
require __DIR__ . '/../layouts/admin-shell-start.php';

$pageHeading = 'Proveedores';
$searchInputId = 'searchProveedor';
$searchPlaceholder = 'Buscar proveedor...';
$showSearch = true;
require __DIR__ . '/../layouts/admin-header.php';
?>

<div class="proveedores-page">
    <div class="stats-grid stats-list">
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-truck"></i> Proveedores Totales</span>
            <span class="stats-list-value"><?php echo $stats['total_proveedores'] ?? 0; ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-shopping-basket"></i> Compras del Mes</span>
            <span class="stats-list-value"><?php echo $stats['compras_mes'] ?? 0; ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-box-open"></i> Unidades Compradas</span>
            <span class="stats-list-value"><?php echo number_format($stats['unidades_compradas'] ?? 0); ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-dollar-sign"></i> Monto Comprado</span>
            <span class="stats-list-value">$<?php echo number_format($stats['monto_comprado'] ?? 0, 2); ?></span>
        </div>
    </div>

    <div class="actions-bar">
        <div class="actions-left">
            <button class="btn-outline-primary" id="btnNuevoProveedor" type="button">
                <i class="fas fa-plus"></i> Nuevo Proveedor
            </button>
            <div class="filters">
                <select class="filter-select" id="filterProveedorNombre">
                    <option value="">Nombre (A-Z / Z-A)</option>
                    <option value="az">Nombre A-Z</option>
                    <option value="za">Nombre Z-A</option>
                </select>
                <select class="filter-select" id="filterProveedorFecha">
                    <option value="">Fecha agregado</option>
                    <option value="nuevos">Más recientes</option>
                    <option value="antiguos">Más antiguos</option>
                </select>
                <button class="btn-outline-primary" id="btnResetProveedorFiltros" type="button" title="Limpiar filtros">
                    <i class="fas fa-times"></i> Limpiar
                </button>
            </div>
        </div>
        <div class="actions-right">
            <button class="btn-icon" title="Importar" type="button"><i class="fas fa-download"></i></button>
            <button class="btn-icon" title="Exportar" type="button"><i class="fas fa-upload"></i></button>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Listado de Proveedores</h3>
            <a href="proveedores.php" class="view-all"><i class="fas fa-sync"></i> Actualizar</a>
        </div>
        <div class="table-responsive">
            <table class="data-table" id="proveedoresTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Empresa</th>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($proveedores)): ?>
                        <tr><td colspan="6" style="text-align:center;">No se encontraron proveedores registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($proveedores as $p): ?>
                        <tr>
                            <td><?php echo $p['id_proveedor']; ?></td>
                            <td><strong><?php echo htmlspecialchars($p['nombre_empresa']); ?></strong></td>
                            <td><?php echo htmlspecialchars($p['contacto_nombre'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($p['telefono'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($p['email'] ?? '-'); ?></td>
                            <td data-order="<?php echo $p['fecha_registro']; ?>">
                                <?php echo date('d/m/Y', strtotime($p['fecha_registro'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>

<div class="modal" id="proveedorModal">
    <div class="modal-content proveedores-modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-truck-loading"></i> Nuevo Proveedor</h3>
            <button class="modal-close" type="button" onclick="closeProveedorModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="proveedorForm" method="POST" action="proveedores.php">
                <div class="form-group">
                    <label>Nombre Empresa</label>
                    <input class="form-control" name="nuevoNombreEmpresa" id="proveedorEmpresa" type="text" required>
                </div>
                <div class="form-group">
                    <label>Contacto (Nombre)</label>
                    <input class="form-control" name="nuevoContacto" id="proveedorContacto" type="text">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input class="form-control" name="nuevoTelefono" id="proveedorTelefono" type="text">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input class="form-control" name="nuevoEmail" id="proveedorEmail" type="email">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" type="button" onclick="closeProveedorModal()">Cancelar</button>
            <button class="btn-primary" type="button" onclick="saveProveedor()">Guardar</button>
        </div>
    </div>
</div>

<script>
    const proveedoresTableBody = document.querySelector('#proveedoresTable tbody');
    const searchProveedor = document.getElementById('searchProveedor');
    const filterProveedorNombre = document.getElementById('filterProveedorNombre');
    const filterProveedorFecha = document.getElementById('filterProveedorFecha');

    function applyProveedoresFilters() {
        const term = (searchProveedor?.value || '').toLowerCase().trim();
        const rows = Array.from(proveedoresTableBody.querySelectorAll('tr:not(.no-data)'));

        rows.forEach((row) => {
            const rowText = row.textContent.toLowerCase();
            row.style.display = term === '' || rowText.includes(term) ? '' : 'none';
        });

        const nameOrder = filterProveedorNombre?.value || '';
        if (nameOrder === 'az' || nameOrder === 'za') {
            const sortedByName = rows.sort((a, b) => {
                const nameA = a.children[1].textContent.trim().toLowerCase();
                const nameB = b.children[1].textContent.trim().toLowerCase();
                return nameOrder === 'az' ? nameA.localeCompare(nameB) : nameB.localeCompare(nameA);
            });
            sortedByName.forEach((row) => proveedoresTableBody.appendChild(row));
        }

        const dateOrder = filterProveedorFecha?.value || '';
        if (dateOrder === 'nuevos' || dateOrder === 'antiguos') {
            const sortedByDate = rows.sort((a, b) => {
                const dateA = new Date(a.children[5].dataset.order || '1970-01-01').getTime();
                const dateB = new Date(b.children[5].dataset.order || '1970-01-01').getTime();
                return dateOrder === 'nuevos' ? dateB - dateA : dateA - dateB;
            });
            sortedByDate.forEach((row) => proveedoresTableBody.appendChild(row));
        }
    }

    function openProveedorModal() {
        document.getElementById('proveedorModal').classList.add('active');
    }

    function closeProveedorModal() {
        document.getElementById('proveedorModal').classList.remove('active');
        document.getElementById('proveedorForm').reset();
    }

    function saveProveedor() {
        const form = document.getElementById('proveedorForm');
        if(form.checkValidity()) {
            form.submit();
        } else {
            form.reportValidity();
        }
    }

    searchProveedor?.addEventListener('input', applyProveedoresFilters);
    filterProveedorNombre?.addEventListener('change', applyProveedoresFilters);
    filterProveedorFecha?.addEventListener('change', applyProveedoresFilters);
    
    document.getElementById('btnResetProveedorFiltros')?.addEventListener('click', function () {
        if (filterProveedorNombre) filterProveedorNombre.value = '';
        if (filterProveedorFecha)  filterProveedorFecha.value  = '';
        if (searchProveedor) searchProveedor.value = '';
        applyProveedoresFilters();
    });

    document.getElementById('btnNuevoProveedor')?.addEventListener('click', openProveedorModal);
</script>

<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>