<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

require_once "../../../controller/clientesController.php";
require_once "../../../model/ClientesModel.php";

$controlador = new ClientesController();
$controlador->ctrCrearCliente();
$controlador->ctrActualizarCliente();


$clientesPorPagina = 5;
$paginaActual = isset($_GET["pagina"]) ? (int)$_GET["pagina"] : 1;
if ($paginaActual < 1) $paginaActual = 1;
$base = ($paginaActual - 1) * $clientesPorPagina;

// ELIMINAR CLIENTE
if (isset($_POST["id_eliminar_cliente"])) {
    $respuesta = $controlador->ctrEliminarCliente();

    if ($respuesta == "ok") {
       header("Location: clientes.php?pagina=" . $paginaActual);
exit();
    }
}


$todosLosClientesFull = ClientesController::ctrMostrarClientes();
$totalClientes = count($todosLosClientesFull);
$totalPaginas = ceil($totalClientes / $clientesPorPagina);

// Obtenemos solo los de la página actual
$clientesPaginados = ClientesController::ctrMostrarClientesPaginados(null, null, $base, $clientesPorPagina);

$conEmail = count(array_filter($todosLosClientesFull, fn($c) => !empty($c['email'])));
$nuevosMes = count(array_filter($todosLosClientesFull, fn($c) => (isset($c['fecha']) && strtotime($c['fecha']) > strtotime('-30 days'))));

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
            <span class="stats-list-value"><?= $totalClientes ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-user-plus"></i> Nuevos este Mes</span>
            <span class="stats-list-value"><?= $nuevosMes > 0 ? $nuevosMes : '0' ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-user-check"></i> Con Compras</span>
            <span class="stats-list-value">97</span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-envelope"></i> Con Email</span>
            <span class="stats-list-value"><?= $conEmail ?></span>
        </div>
    </div>

    <div class="actions-bar">
        <div class="actions-left">
            <button class="btn-outline-primary" id="btnNuevoCliente" onclick="openClienteModal()">
                <i class="fas fa-plus"></i> Nuevo Cliente
            </button>
            <div class="filters">
                <select class="filter-select" id="sortClienteNombre" onchange="applySort()">
                    <option value="">Ordenar por Nombre</option>
                    <option value="az">A - Z</option>
                    <option value="za">Z - A</option>
                </select>
                <button class="btn-outline-primary" onclick="window.location.href='clientes.php'">
                    <i class="fas fa-times"></i> Limpiar
                </button>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table" id="clientesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientesPaginados as $c): ?>
                    <tr>
                        <td><?= $c['id_cliente'] ?></td>
                        <td><strong><?= htmlspecialchars($c['nombre']) ?></strong></td>
                        <td><?= !empty($c['telefono']) ? $c['telefono'] : '-' ?></td>
                        <td><?= !empty($c['email']) ? $c['email'] : '-' ?></td>
                        <td>Activo</td>
                        <td>
    <!-- EDITAR -->
    <button class="btn-icon small" onclick="editCliente(<?= htmlspecialchars(json_encode($c)) ?>)">
        <i class="fas fa-edit"></i>
    </button>

    <!-- ELIMINAR -->
    <form method="post" style="display:inline;">
        <input type="hidden" name="id_eliminar_cliente" value="<?= $c['id_cliente'] ?>">

        <button type="submit" class="btn-icon small"
            style="color:#7a5c45; border:none; background:none; cursor:pointer;"
            onclick="return confirm('¿Eliminar este cliente?')">

            <i class="fas fa-trash"></i>
        </button>
    </form>
</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="pagination-simple" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
    <span style="font-size: 0.85rem; color: #666;">Página <?= $paginaActual ?> de <?= $totalPaginas ?></span>
    <div style="display: flex; gap: 5px;">
        <?php for ($i = 1; $i <= $totalPaginas; $i++): 
            $btnStyle = ($paginaActual == $i) ? 'background: #A67C52; color: white;' : 'background: #f4f4f4; color: #333;';
        ?>
        <a href="clientes.php?pagina=<?= $i ?>" style="padding: 5px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; <?= $btnStyle ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</div>

<!-- MODAL (IDEM ANTERIOR PERO LIMPIO) -->
<div class="modal" id="clienteModal" style="display: none; background: rgba(0,0,0,0.5); position:fixed; top:0; left:0; width:100%; height:100%; z-index:1000; align-items:center; justify-content:center;">
    <div class="modal-content" style="background:white; padding:20px; border-radius:8px; width:400px;">
        <h3 id="modalTitle">Cliente</h3>
        <form method="post" id="clienteForm">
            <input type="hidden" name="id_cliente" id="id_cliente">
            <div class="form-group"><label>Nombre</label><input class="form-control" name="nuevoNombre" id="clienteNombre" required style="width:100%"></div>
            <div class="form-group"><label>Teléfono</label><input class="form-control" name="nuevoTelefono" id="clienteTelefono" style="width:100%"></div>
            <div class="form-group"><label>Email</label><input class="form-control" name="nuevoEmail" id="clienteEmail" type="email" style="width:100%"></div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:15px;">
                <button class="btn-secondary" type="button" onclick="closeClienteModal()">Cancelar</button>
                <button class="btn-primary" type="submit">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
// BUSCADOR EN TIEMPO REAL
document.getElementById('searchCliente')?.addEventListener('keyup', function() {
    const term = this.value.toLowerCase();
    const rows = document.querySelectorAll('#clientesTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
});

// ORDENAR TABLA (A-Z / Z-A)
function applySort() {
    const order = document.getElementById('sortClienteNombre').value;
    const tbody = document.querySelector('#clientesTable tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        const nameA = a.cells[1].textContent.trim().toLowerCase();
        const nameB = b.cells[1].textContent.trim().toLowerCase();
        if (order === 'az') return nameA.localeCompare(nameB);
        if (order === 'za') return nameB.localeCompare(nameA);
        return 0;
    });

    rows.forEach(row => tbody.appendChild(row));
}

function openClienteModal() {
    document.getElementById('modalTitle').innerText = 'Nuevo Cliente';
    document.getElementById('clienteForm').reset();
    document.getElementById('id_cliente').value = "";
    document.getElementById('clienteNombre').name = "nuevoNombre";
    document.getElementById('clienteTelefono').name = "nuevoTelefono";
    document.getElementById('clienteEmail').name = "nuevoEmail";
    document.getElementById('clienteModal').style.display = 'flex';
}

function editCliente(data) {
    document.getElementById('modalTitle').innerText = 'Editar Cliente';
    document.getElementById('id_cliente').value = data.id_cliente;
    document.getElementById('clienteNombre').value = data.nombre;
    document.getElementById('clienteTelefono').value = data.telefono;
    document.getElementById('clienteEmail').value = data.email;
    
    document.getElementById('clienteNombre').name = "editarNombre";
    document.getElementById('clienteTelefono').name = "editarTelefono";
    document.getElementById('clienteEmail').name = "editarEmail";
    document.getElementById('clienteModal').style.display = 'flex';
}

function closeClienteModal() { document.getElementById('clienteModal').style.display = 'none'; }
</script>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>
