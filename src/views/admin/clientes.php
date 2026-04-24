<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

// 1. CARGAR CONTROLADORES Y MODELOS
$basePath = realpath(__DIR__ . '/../../../');
require_once $basePath . "/controller/clientesController.php";
require_once $basePath . "/model/ClientesModel.php";
require_once $basePath . "/model/conexion.php";

$controlador = new ClientesController();
$controlador->ctrCrearCliente();
$controlador->ctrActualizarCliente();


$paginaActual = 1;

// ELIMINAR CLIENTE
if (isset($_POST["id_eliminar_cliente"])) {
    $respuesta = $controlador->ctrEliminarCliente();

    if ($respuesta == "ok") {
       header("Location: clientes.php");
exit();
    }
}

// ENVIAR EMAIL AL CLIENTE
if (isset($_POST["enviar_email_cliente"])) {
    $resultadoCorreo = $controlador->ctrEnviarCorreoCliente();
    header("Location: clientes.php?mail=" . $resultadoCorreo);
    exit();
}


$todosLosClientesFull = ClientesController::ctrMostrarClientes();
$totalClientes = count($todosLosClientesFull);
$clientesPaginados = $todosLosClientesFull;

$conEmail = count(array_filter($todosLosClientesFull, fn($c) => !empty($c['email'])));
$nuevosMes = 0;

$clientesConCompras = 0;
try {
    $dbClientesStats = Conexion::conectar();

    $stmtColFecha = $dbClientesStats->query("SHOW COLUMNS FROM clientes LIKE 'fecha_registro'");
    $tieneFechaRegistro = $stmtColFecha && $stmtColFecha->fetch(PDO::FETCH_ASSOC);

    if (!$tieneFechaRegistro) {
        $dbClientesStats->exec("ALTER TABLE clientes ADD COLUMN fecha_registro TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER email");
        $tieneFechaRegistro = true;
    }

    $stmtTotal = $dbClientesStats->query("SELECT COUNT(*) AS total, SUM(CASE WHEN email IS NOT NULL AND TRIM(email) <> '' THEN 1 ELSE 0 END) AS con_email FROM clientes");
    $statsClientes = $stmtTotal->fetch(PDO::FETCH_ASSOC) ?: [];
    $totalClientes = (int)($statsClientes['total'] ?? 0);
    $conEmail = (int)($statsClientes['con_email'] ?? 0);

    if ($tieneFechaRegistro) {
        $stmtNuevosMes = $dbClientesStats->query("SELECT COUNT(*) AS total FROM clientes WHERE fecha_registro >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND fecha_registro < DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH)");
        $nuevosMes = (int)($stmtNuevosMes->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    }

    $stmtCompras = $dbClientesStats->query("SELECT COUNT(DISTINCT id_cliente) AS total FROM ventas WHERE id_cliente IS NOT NULL");
    $clientesConCompras = (int)($stmtCompras->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
} catch (Throwable $e) {
    $clientesConCompras = 0;
}

$activeMenu = 'clientes';
$pageTitle = 'Clientes';
$pageStyles = ['/ElZapato/Assets/css/pages/admin-stats.css', '/ElZapato/Assets/css/pages/admin-clientes.css'];
require __DIR__ . '/../layouts/admin-shell-start.php';

$pageHeading = 'Clientes';
$searchInputId = 'searchCliente';
$searchPlaceholder = 'Buscar cliente...';
$showSearch = true;
require __DIR__ . '/../layouts/admin-header.php';

$mailStatus = $_GET['mail'] ?? '';
$resultStatus = $_GET['res'] ?? '';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="clientes-page">
    <div class="stats-grid stats-list">
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-users"></i> Clientes Totales</span>
            <span class="stats-list-value" id="statsClientesTotal"><?= $totalClientes ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-user-plus"></i> Nuevos este Mes</span>
            <span class="stats-list-value" id="statsClientesNuevos"><?= $nuevosMes > 0 ? $nuevosMes : '0' ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-user-check"></i> Con Compras</span>
            <span class="stats-list-value" id="statsClientesCompras"><?= $clientesConCompras ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-envelope"></i> Con Email</span>
            <span class="stats-list-value" id="statsClientesEmail"><?= $conEmail ?></span>
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
                        <td class="acciones-admin">
    <!-- EDITAR -->
    <button class="btn-icon small" onclick="editCliente(<?= htmlspecialchars(json_encode($c)) ?>)">
        <i class="fas fa-edit"></i>
    </button>

    <!-- ENVIAR EMAIL -->
    <?php if (!empty($c['email'])): ?>
    <form method="post">
        <input type="hidden" name="enviar_email_cliente" value="1">
        <input type="hidden" name="email_cliente" value="<?= htmlspecialchars($c['email']) ?>">
        <input type="hidden" name="nombre_cliente" value="<?= htmlspecialchars($c['nombre']) ?>">
        <button type="submit" class="btn-icon small" title="Enviar email">
            <i class="fas fa-envelope"></i>
        </button>
    </form>
    <?php else: ?>
    <button type="button" class="btn-icon small" title="Cliente sin email" style="opacity:0.45; cursor:not-allowed;" disabled>
        <i class="fas fa-envelope"></i>
    </button>
    <?php endif; ?>

    <!-- ELIMINAR -->
    <form method="post">
        <input type="hidden" name="id_eliminar_cliente" value="<?= $c['id_cliente'] ?>">

        <button type="submit" class="btn-icon small"
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

<!-- MODAL (IDEM ANTERIOR PERO LIMPIO) -->
<div class="modal" id="clienteModal" style="display: none; background: rgba(0,0,0,0.5); position:fixed; top:0; left:0; width:100%; height:100%; z-index:1000; align-items:center; justify-content:center;">
    <div class="modal-content" style="background:white; padding:20px; border-radius:8px; width:400px;">
        <h3 id="modalTitle">Cliente</h3>
        <form method="post" id="clienteForm">
            <input type="hidden" name="id_cliente" id="id_cliente">
            <div class="form-group"><label>Nombre</label><input class="form-control" name="nuevoNombre" id="clienteNombre" required style="width:100%"></div>
            <div class="form-group">
                <label>Teléfono</label>
                <input class="form-control" name="nuevoTelefono" id="clienteTelefono" type="text" inputmode="numeric" maxlength="8" pattern="^\d{8}$" style="width:100%">
                <small style="display:block; margin-top:4px; color:#666;">Ingresa 8 dígitos numéricos (opcional).</small>
                <small id="clienteTelefonoError" style="display:none; margin-top:4px; color:#772c24;">El teléfono debe tener exactamente 8 dígitos numéricos.</small>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input class="form-control" name="nuevoEmail" id="clienteEmail" type="email" style="width:100%">
                <small style="display:block; margin-top:4px; color:#666;">Opcional. Si lo ingresas, debe tener formato válido.</small>
                <small id="clienteEmailError" style="display:none; margin-top:4px; color:#772c24;">Ingresa un correo con formato válido.</small>
            </div>
            <div class="modal-actions" style="margin-top:15px;">
                <button class="btn-modal-cancel" type="button" onclick="closeClienteModal()">Cancelar</button>
                <button class="btn-modal-primary" type="submit">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div id="emailToast" style="position: fixed; right: 20px; bottom: 20px; min-width: 280px; max-width: 360px; padding: 12px 14px; border-radius: 10px; color: #fff; font-size: 0.9rem; box-shadow: 0 10px 25px rgba(0,0,0,.18); display: none; z-index: 99999; opacity: 0; transform: translateY(8px); transition: opacity .25s ease, transform .25s ease;"></div>

<script>
const mailStatus = <?= json_encode($mailStatus, JSON_UNESCAPED_UNICODE) ?>;
const resultStatus = <?= json_encode($resultStatus, JSON_UNESCAPED_UNICODE) ?>;

function showEmailToast(message, type = 'info') {
    const toast = document.getElementById('emailToast');
    if (!toast) return;

    const palette = {
        success: '#E4E0E1',
        warning: '#AB886D',
        error: '#772c24',
        info: '#4a5568'
    };

    toast.textContent = message;
    toast.style.background = palette[type] || palette.info;
    toast.style.display = 'block';

    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });

    clearTimeout(window.__emailToastTimer);
    window.__emailToastTimer = setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(8px)';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 250);
    }, 3200);
}

if (mailStatus) {
    if (mailStatus === 'ok') {
        showEmailToast('Correo enviado correctamente al cliente.', 'success');
    } else if (mailStatus === 'sin_email') {
        showEmailToast('El cliente no tiene correo registrado.', 'warning');
    } else if (mailStatus === 'email_invalido') {
        showEmailToast('El correo del cliente no es válido.', 'warning');
    } else {
        showEmailToast('No se pudo enviar el correo. Revisa la configuración SMTP del servidor.', 'error');
    }

    const url = new URL(window.location.href);
    url.searchParams.delete('mail');
    window.history.replaceState({}, document.title, url.pathname + (url.search ? url.search : ''));
}

if (resultStatus === 'creado') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Cliente guardado',
            text: 'El cliente se registró correctamente.',
            confirmButtonColor: '#A67C52'
        });
    } else {
        showEmailToast('El cliente se registró correctamente.', 'success');
    }
} else if (resultStatus === 'actualizado') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Cliente actualizado',
            text: 'Los cambios se guardaron correctamente.',
            confirmButtonColor: '#A67C52'
        });
    } else {
        showEmailToast('Los cambios se guardaron correctamente.', 'success');
    }
}

if (resultStatus) {
    const urlRes = new URL(window.location.href);
    urlRes.searchParams.delete('res');
    window.history.replaceState({}, document.title, urlRes.pathname + (urlRes.search ? urlRes.search : ''));
}

// BUSCADOR EN TIEMPO REAL
const searchClienteHeader = document.getElementById('searchCliente');

function aplicarBusquedaClientes() {
    const term = (searchClienteHeader?.value || '').toLowerCase();
    const rows = document.querySelectorAll('#clientesTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
    actualizarStatsClientes();
}

searchClienteHeader?.addEventListener('keyup', function() {
    aplicarBusquedaClientes();
});

function actualizarStatsClientes() {
    return;
}

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
    actualizarStatsClientes();
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

function limpiarErroresClienteModal() {
    const telError = document.getElementById('clienteTelefonoError');
    const emailError = document.getElementById('clienteEmailError');
    const telInput = document.getElementById('clienteTelefono');
    const emailInput = document.getElementById('clienteEmail');

    if (telError) telError.style.display = 'none';
    if (emailError) emailError.style.display = 'none';
    if (telInput) telInput.style.borderColor = '';
    if (emailInput) emailInput.style.borderColor = '';
}

function validarClienteModal() {
    limpiarErroresClienteModal();

    const telInput = document.getElementById('clienteTelefono');
    const emailInput = document.getElementById('clienteEmail');
    const telError = document.getElementById('clienteTelefonoError');
    const emailError = document.getElementById('clienteEmailError');

    const telefono = (telInput?.value || '').trim();
    const email = (emailInput?.value || '').trim();
    const telValido = telefono === '' || /^\d{8}$/.test(telefono);
    const emailValido = email === '' || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

    let esValido = true;

    if (!telValido) {
        esValido = false;
        if (telError) telError.style.display = 'block';
        if (telInput) telInput.style.borderColor = '#772c24';
    }

    if (!emailValido) {
        esValido = false;
        if (emailError) emailError.style.display = 'block';
        if (emailInput) emailInput.style.borderColor = '#772c24';
    }

    if (!esValido && !telValido && telInput) {
        telInput.focus();
    } else if (!esValido && !emailValido && emailInput) {
        emailInput.focus();
    }

    return esValido;
}

document.getElementById('clienteForm')?.addEventListener('submit', function(event) {
    if (!validarClienteModal()) {
        event.preventDefault();
    }
});

document.getElementById('clienteTelefono')?.addEventListener('input', limpiarErroresClienteModal);
document.getElementById('clienteEmail')?.addEventListener('input', limpiarErroresClienteModal);

document.addEventListener('DOMContentLoaded', actualizarStatsClientes);
</script>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>
<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>