<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

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
            <span class="stats-list-value">14</span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-shopping-basket"></i> Compras del Mes</span>
            <span class="stats-list-value">27</span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-box-open"></i> Unidades Compradas</span>
            <span class="stats-list-value">1,640</span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-dollar-sign"></i> Monto Comprado</span>
            <span class="stats-list-value">$26,480</span>
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
            <h3>Listado de Proveedores (Tabla proveedores)</h3>
            <a href="#" class="view-all"><i class="fas fa-sync"></i> Actualizar</a>
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
                    <tr>
                        <td>1</td>
                        <td>Calzado Andino SAC</td>
                        <td>Rosa Medina</td>
                        <td>+51 998 442 120</td>
                        <td>ventas@andino.com</td>
                        <td data-order="2026-03-10">10/03/2026</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Distribuidora Nova</td>
                        <td>Joel Díaz</td>
                        <td>+51 977 320 111</td>
                        <td>contacto@nova.pe</td>
                        <td data-order="2026-03-11">11/03/2026</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Importaciones Lima Shoes</td>
                        <td>Carla Torres</td>
                        <td>+51 945 100 320</td>
                        <td>compras@limashoes.pe</td>
                        <td data-order="2026-03-12">12/03/2026</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Mayorista del Pacífico</td>
                        <td>Marco Ruiz</td>
                        <td>+51 966 500 088</td>
                        <td>marco@pacifico.com</td>
                        <td data-order="2026-03-14">14/03/2026</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Textiles y Calzado Sur</td>
                        <td>Lucía Salas</td>
                        <td>+51 922 381 403</td>
                        <td>l.salas@calzadosur.pe</td>
                        <td data-order="2026-03-18">18/03/2026</td>
                    </tr>
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
            <form id="proveedorForm">
                <div class="form-group">
                    <label>Nombre Empresa</label>
                    <input class="form-control" id="proveedorEmpresa" type="text" required>
                </div>
                <div class="form-group">
                    <label>Contacto</label>
                    <input class="form-control" id="proveedorContacto" type="text">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input class="form-control" id="proveedorTelefono" type="text">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input class="form-control" id="proveedorEmail" type="email">
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
        const rows = Array.from(proveedoresTableBody.querySelectorAll('tr'));

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
    }

    function saveProveedor() {
        alert('Proveedor guardado correctamente');
        closeProveedorModal();
    }

    searchProveedor?.addEventListener('input', applyProveedoresFilters);
    filterProveedorNombre?.addEventListener('change', applyProveedoresFilters);
    filterProveedorFecha?.addEventListener('change', applyProveedoresFilters);
    document.getElementById('btnResetProveedorFiltros')?.addEventListener('click', function () {
        if (filterProveedorNombre) filterProveedorNombre.value = '';
        if (filterProveedorFecha)  filterProveedorFecha.value  = '';
        applyProveedoresFilters();
    });
    document.getElementById('btnNuevoProveedor')?.addEventListener('click', openProveedorModal);
</script>

<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>
