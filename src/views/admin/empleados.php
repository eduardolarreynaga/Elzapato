<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

$activeMenu = 'empleados';
$pageTitle = 'Empleados';
$pageStyles = ['/ElZapato/Assets/css/pages/admin-stats.css', '/ElZapato/Assets/css/pages/admin-empleados.css'];
require __DIR__ . '/../layouts/admin-shell-start.php';

$pageHeading = 'Empleados';
$searchInputId = 'searchEmpleado';
$searchPlaceholder = 'Buscar por usuario o rol...';
$showSearch = true;
require __DIR__ . '/../layouts/admin-header.php';
?>

<div class="empleados-page">
    <div class="stats-grid stats-list">
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-user-tie"></i> Empleados Totales</span>
            <span class="stats-list-value">8</span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-user-shield"></i> Administradores</span>
            <span class="stats-list-value">2</span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-cash-register"></i> Cajeros</span>
            <span class="stats-list-value">6</span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-user-check"></i> Activos</span>
            <span class="stats-list-value">7</span>
        </div>
    </div>

    <div class="actions-bar">
        <div class="actions-left">
            <button class="btn-outline-primary" id="btnNuevoEmpleado" type="button">
                <i class="fas fa-plus"></i> Nuevo Empleado
            </button>
            <div class="filters">
                <select class="filter-select" id="filterEmpleadoNombre">
                    <option value="">Nombre (A-Z / Z-A)</option>
                    <option value="az">Nombre A-Z</option>
                    <option value="za">Nombre Z-A</option>
                </select>
                <select class="filter-select" id="filterEmpleadoRol">
                    <option value="">Rol</option>
                    <option value="admin">Admin</option>
                    <option value="cajero">Cajero</option>
                </select>
                <select class="filter-select" id="filterEmpleadoEstado">
                    <option value="">Estado</option>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
                <button class="btn-outline-primary" id="btnResetEmpleadoFiltros" type="button" title="Limpiar filtros">
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
            <h3>Listado de Empleados (Tabla usuarios)</h3>
            <a href="#" class="view-all"><i class="fas fa-sync"></i> Actualizar</a>
        </div>
        <div class="table-responsive">
            <table class="data-table" id="empleadosTable">
                <thead>
                    <tr>
                        <th>ID Usuario</th>
                        <th>Nombre Usuario</th>
                        <th>Rol</th>
                        <th>Fecha Creación</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>admin</td>
                        <td>admin</td>
                        <td>18/03/2026 09:10</td>
                        <td><span class="status-badge completed">Activo</span></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>cajero01</td>
                        <td>cajero</td>
                        <td>18/03/2026 09:15</td>
                        <td><span class="status-badge completed">Activo</span></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>cajero02</td>
                        <td>cajero</td>
                        <td>18/03/2026 09:18</td>
                        <td><span class="status-badge completed">Activo</span></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>cajero03</td>
                        <td>cajero</td>
                        <td>18/03/2026 09:22</td>
                        <td><span class="status-badge pending">Inactivo</span></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>admin2</td>
                        <td>admin</td>
                        <td>18/03/2026 09:27</td>
                        <td><span class="status-badge completed">Activo</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>

<div class="modal" id="empleadoModal">
    <div class="modal-content empleados-modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Nuevo Empleado</h3>
            <button class="modal-close" type="button" onclick="closeEmpleadoModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="empleadoForm">
                <div class="form-group">
                    <label>Nombre Usuario</label>
                    <input class="form-control" id="empleadoUsuario" type="text" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Password</label>
                        <input class="form-control" id="empleadoPassword" type="password" required>
                    </div>
                    <div class="form-group">
                        <label>Rol</label>
                        <select class="form-control" id="empleadoRol" required>
                            <option value="">Seleccionar rol</option>
                            <option value="admin">Admin</option>
                            <option value="cajero">Cajero</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" type="button" onclick="closeEmpleadoModal()">Cancelar</button>
            <button class="btn-primary" type="button" onclick="saveEmpleado()">Guardar</button>
        </div>
    </div>
</div>

<script>
    const empleadosTableBody = document.querySelector('#empleadosTable tbody');
    const searchEmpleado = document.getElementById('searchEmpleado');
    const filterEmpleadoNombre = document.getElementById('filterEmpleadoNombre');
    const filterEmpleadoRol = document.getElementById('filterEmpleadoRol');
    const filterEmpleadoEstado = document.getElementById('filterEmpleadoEstado');

    function applyEmpleadosFilters() {
        const term = (searchEmpleado?.value || '').toLowerCase().trim();
        const rol = (filterEmpleadoRol?.value || '').toLowerCase();
        const estado = (filterEmpleadoEstado?.value || '').toLowerCase();
        const rows = Array.from(empleadosTableBody.querySelectorAll('tr'));

        rows.forEach((row) => {
            const rowText = row.textContent.toLowerCase();
            const rolText = row.children[2]?.textContent.toLowerCase().trim() || '';
            const estadoText = row.children[4]?.textContent.toLowerCase().trim() || '';

            const passSearch = term === '' || rowText.includes(term);
            const passRol = rol === '' || rolText.includes(rol);
            const passEstado = estado === '' || estadoText.includes(estado);

            row.style.display = passSearch && passRol && passEstado ? '' : 'none';
        });

        const order = filterEmpleadoNombre?.value || '';
        if (order === 'az' || order === 'za') {
            const sorted = rows.sort((a, b) => {
                const nameA = a.children[1].textContent.trim().toLowerCase();
                const nameB = b.children[1].textContent.trim().toLowerCase();
                return order === 'az' ? nameA.localeCompare(nameB) : nameB.localeCompare(nameA);
            });
            sorted.forEach((row) => empleadosTableBody.appendChild(row));
        }
    }

    function openEmpleadoModal() {
        document.getElementById('empleadoModal').classList.add('active');
    }

    function closeEmpleadoModal() {
        document.getElementById('empleadoModal').classList.remove('active');
    }

    function saveEmpleado() {
        alert('Empleado guardado correctamente');
        closeEmpleadoModal();
    }

    searchEmpleado?.addEventListener('input', applyEmpleadosFilters);
    filterEmpleadoNombre?.addEventListener('change', applyEmpleadosFilters);
    filterEmpleadoRol?.addEventListener('change', applyEmpleadosFilters);
    filterEmpleadoEstado?.addEventListener('change', applyEmpleadosFilters);
    document.getElementById('btnResetEmpleadoFiltros')?.addEventListener('click', function () {
        if (filterEmpleadoNombre) filterEmpleadoNombre.value = '';
        if (filterEmpleadoRol)    filterEmpleadoRol.value    = '';
        if (filterEmpleadoEstado) filterEmpleadoEstado.value = '';
        applyEmpleadosFilters();
    });
    document.getElementById('btnNuevoEmpleado')?.addEventListener('click', openEmpleadoModal);
</script>

<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>
