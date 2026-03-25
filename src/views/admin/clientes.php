<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

$activeMenu = 'clientes';
$pageTitle = 'Clientes';
$pageStyles = ['/ElZapato/Assets/css/pages/admin-stats.css', '/ElZapato/Assets/css/pages/admin-clientes.css'];
require __DIR__ . '/../layouts/admin-shell-start.php';

$pageHeading = 'Clientes';
$searchInputId = 'searchCliente';
$searchPlaceholder = 'Buscar cliente...';
$showSearch = true;
require __DIR__ . '/../layouts/admin-header.php';
?>
<div class="clientes-page">
    <div class="stats-grid stats-list">
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-users"></i> Clientes Totales</span>
            <span class="stats-list-value">132</span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-user-plus"></i> Nuevos este Mes</span>
            <span class="stats-list-value">18</span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-user-check"></i> Con Compras</span>
            <span class="stats-list-value">97</span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-envelope"></i> Con Email</span>
            <span class="stats-list-value">124</span>
        </div>
    </div>

    <div class="actions-bar">
        <div class="actions-left">
            <button class="btn-outline-primary" id="btnNuevoCliente" type="button">
                <i class="fas fa-plus"></i> Nuevo Cliente
            </button>
            <div class="filters">
                <select class="filter-select" id="filterClienteNombre">
                    <option value="">Nombre (A-Z / Z-A)</option>
                    <option value="az">Nombre A-Z</option>
                    <option value="za">Nombre Z-A</option>
                </select>
                <select class="filter-select" id="filterClienteEstado">
                    <option value="">Estado</option>
                    <option value="activo">Activo</option>
                    <option value="incompleto">Incompleto</option>
                    <option value="sin datos">Sin datos</option>
                </select>
                <button class="btn-outline-primary" id="btnResetClienteFiltros" type="button" title="Limpiar filtros">
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
            <h3>Listado de Clientes (Tabla clientes)</h3>
            <a href="#" class="view-all"><i class="fas fa-sync"></i> Actualizar</a>
        </div>
        <div class="table-responsive">
            <table class="data-table" id="clientesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Juan Pérez</td>
                        <td>+51 999 111 222</td>
                        <td>juan.perez@email.com</td>
                        <td><span class="status-badge completed">Activo</span></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>María García</td>
                        <td>+51 988 210 300</td>
                        <td>maria.garcia@email.com</td>
                        <td><span class="status-badge completed">Activo</span></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Carlos López</td>
                        <td>+51 977 654 910</td>
                        <td>carlos.lopez@email.com</td>
                        <td><span class="status-badge completed">Activo</span></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Ana Martínez</td>
                        <td>+51 966 445 120</td>
                        <td>ana.martinez@email.com</td>
                        <td><span class="status-badge pending">Incompleto</span></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Cliente Mostrador</td>
                        <td>-</td>
                        <td>-</td>
                        <td><span class="status-badge pending">Sin datos</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>

<div class="modal" id="clienteModal">
    <div class="modal-content clientes-modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Nuevo Cliente</h3>
            <button class="modal-close" type="button" onclick="closeClienteModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="clienteForm">
                <div class="form-group">
                    <label>Nombre</label>
                    <input class="form-control" id="clienteNombre" type="text" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input class="form-control" id="clienteTelefono" type="text">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input class="form-control" id="clienteEmail" type="email">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" type="button" onclick="closeClienteModal()">Cancelar</button>
            <button class="btn-primary" type="button" onclick="saveCliente()">Guardar</button>
        </div>
    </div>
</div>

<script>
    const clientesTableBody = document.querySelector('#clientesTable tbody');
    const searchCliente = document.getElementById('searchCliente');
    const filterClienteNombre = document.getElementById('filterClienteNombre');
    const filterClienteEstado = document.getElementById('filterClienteEstado');

    function applyClientesFilters() {
        const term = (searchCliente?.value || '').toLowerCase().trim();
        const estado = (filterClienteEstado?.value || '').toLowerCase();
        const rows = Array.from(clientesTableBody.querySelectorAll('tr'));

        rows.forEach((row) => {
            const rowText = row.textContent.toLowerCase();
            const estadoText = row.querySelector('td:last-child')?.textContent.toLowerCase().trim() || '';
            const passSearch = term === '' || rowText.includes(term);
            const passEstado = estado === '' || estadoText.includes(estado);
            row.style.display = passSearch && passEstado ? '' : 'none';
        });

        const order = filterClienteNombre?.value || '';
        if (order === 'az' || order === 'za') {
            const sorted = rows.sort((a, b) => {
                const nameA = a.children[1].textContent.trim().toLowerCase();
                const nameB = b.children[1].textContent.trim().toLowerCase();
                return order === 'az' ? nameA.localeCompare(nameB) : nameB.localeCompare(nameA);
            });
            sorted.forEach((row) => clientesTableBody.appendChild(row));
        }
    }

    function openClienteModal() {
        document.getElementById('clienteModal').classList.add('active');
    }

    function closeClienteModal() {
        document.getElementById('clienteModal').classList.remove('active');
    }

    function saveCliente() {
        alert('Cliente guardado correctamente');
        closeClienteModal();
    }

    searchCliente?.addEventListener('input', applyClientesFilters);
    filterClienteNombre?.addEventListener('change', applyClientesFilters);
    filterClienteEstado?.addEventListener('change', applyClientesFilters);
    document.getElementById('btnResetClienteFiltros')?.addEventListener('click', function () {
        if (filterClienteNombre) filterClienteNombre.value = '';
        if (filterClienteEstado) filterClienteEstado.value = '';
        applyClientesFilters();
    });
    document.getElementById('btnNuevoCliente')?.addEventListener('click', openClienteModal);
</script>

<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>
