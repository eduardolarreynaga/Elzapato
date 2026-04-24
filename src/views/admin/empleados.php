<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

require_once __DIR__ . '/../../../model/conexion.php';

$empleadoFeedback = ['type' => '', 'message' => ''];
$empleadoLastAction = '';

function asegurarEstructuraCajas(PDO $db): void {
    $stmtTablaCajas = $db->query("SHOW TABLES LIKE 'cajas'");
    if (!($stmtTablaCajas && $stmtTablaCajas->fetch(PDO::FETCH_ASSOC))) {
        $db->exec("CREATE TABLE cajas (
            id_caja INT AUTO_INCREMENT PRIMARY KEY,
            nombre_caja VARCHAR(80) NOT NULL UNIQUE,
            estado ENUM('activa','inactiva') DEFAULT 'activa',
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    $stmtColCaja = $db->query("SHOW COLUMNS FROM usuarios LIKE 'id_caja'");
    if (!($stmtColCaja && $stmtColCaja->fetch(PDO::FETCH_ASSOC))) {
        $db->exec("ALTER TABLE usuarios ADD COLUMN id_caja INT NULL AFTER rol");
    }

    try {
        $stmtFk = $db->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'id_caja' AND REFERENCED_TABLE_NAME = 'cajas' LIMIT 1");
        $fkExiste = $stmtFk && $stmtFk->fetch(PDO::FETCH_ASSOC);
        if (!$fkExiste) {
            $db->exec("ALTER TABLE usuarios ADD CONSTRAINT fk_usuarios_caja FOREIGN KEY (id_caja) REFERENCES cajas(id_caja)");
        }
    } catch (Throwable $e) {
    }

    $stmtCountCajas = $db->query("SELECT COUNT(*) AS total FROM cajas");
    $totalCajas = (int)($stmtCountCajas->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    if ($totalCajas === 0) {
        $db->exec("INSERT INTO cajas (nombre_caja, estado) VALUES ('Caja Principal', 'activa')");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_empleado'])) {
    $empleadoLastAction = 'crear';
    $usuario = trim($_POST['empleadoUsuario'] ?? '');
    $password = (string)($_POST['empleadoPassword'] ?? '');
    $rol = trim($_POST['empleadoRol'] ?? '');
    $idCaja = (int)($_POST['empleadoCaja'] ?? 0);

    if ($usuario === '' || $password === '' || !in_array($rol, ['admin', 'cajero'], true)) {
        $empleadoFeedback = [
            'type' => 'error',
            'message' => 'Completa todos los campos correctamente.'
        ];
    } elseif ($rol === 'cajero' && $idCaja <= 0) {
        $empleadoFeedback = [
            'type' => 'warning',
            'message' => 'Para un cajero debes asignar una caja.'
        ];
    } else {
        try {
            $db = Conexion::conectar();
            asegurarEstructuraCajas($db);

            $stmtExiste = $db->prepare('SELECT id_usuario FROM usuarios WHERE nombre_usuario = :usuario LIMIT 1');
            $stmtExiste->execute([':usuario' => $usuario]);

            if ($stmtExiste->fetch(PDO::FETCH_ASSOC)) {
                $empleadoFeedback = [
                    'type' => 'warning',
                    'message' => 'El nombre de usuario ya existe. Intenta con otro.'
                ];
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $idCajaFinal = $rol === 'cajero' ? $idCaja : null;

                $stmtInsert = $db->prepare('INSERT INTO usuarios (nombre_usuario, password_hash, rol, id_caja) VALUES (:usuario, :password_hash, :rol, :id_caja)');
                $stmtInsert->execute([
                    ':usuario' => $usuario,
                    ':password_hash' => $passwordHash,
                    ':rol' => $rol,
                    ':id_caja' => $idCajaFinal
                ]);

                $empleadoFeedback = [
                    'type' => 'success',
                    'message' => 'Empleado guardado correctamente.'
                ];
            }
        } catch (PDOException $e) {
            $empleadoFeedback = [
                'type' => 'error',
                'message' => 'No se pudo guardar el empleado.'
            ];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_empleado'])) {
    $empleadoLastAction = 'editar';
    $idUsuario = (int)($_POST['empleadoIdEdit'] ?? 0);
    $usuario = trim($_POST['empleadoUsuarioEdit'] ?? '');
    $password = (string)($_POST['empleadoPasswordEdit'] ?? '');
    $rol = trim($_POST['empleadoRolEdit'] ?? '');
    $idCaja = (int)($_POST['empleadoCajaEdit'] ?? 0);

    if ($idUsuario <= 0 || $usuario === '' || !in_array($rol, ['admin', 'cajero'], true)) {
        $empleadoFeedback = [
            'type' => 'error',
            'message' => 'Completa los datos de edición correctamente.'
        ];
    } elseif ($rol === 'cajero' && $idCaja <= 0) {
        $empleadoFeedback = [
            'type' => 'warning',
            'message' => 'Para un cajero debes asignar una caja.'
        ];
    } else {
        try {
            $db = Conexion::conectar();
            asegurarEstructuraCajas($db);

            $stmtExiste = $db->prepare('SELECT id_usuario FROM usuarios WHERE nombre_usuario = :usuario AND id_usuario <> :id_usuario LIMIT 1');
            $stmtExiste->execute([
                ':usuario' => $usuario,
                ':id_usuario' => $idUsuario
            ]);

            if ($stmtExiste->fetch(PDO::FETCH_ASSOC)) {
                $empleadoFeedback = [
                    'type' => 'warning',
                    'message' => 'El nombre de usuario ya existe. Intenta con otro.'
                ];
            } else {
                $idCajaFinal = $rol === 'cajero' ? $idCaja : null;

                if (trim($password) !== '') {
                    if (strlen($password) < 4) {
                        $empleadoFeedback = [
                            'type' => 'warning',
                            'message' => 'La contraseña debe tener al menos 4 caracteres.'
                        ];
                    } else {
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        $stmtUpdate = $db->prepare('UPDATE usuarios SET nombre_usuario = :usuario, rol = :rol, id_caja = :id_caja, password_hash = :password_hash WHERE id_usuario = :id_usuario');
                        $stmtUpdate->execute([
                            ':usuario' => $usuario,
                            ':rol' => $rol,
                            ':id_caja' => $idCajaFinal,
                            ':password_hash' => $passwordHash,
                            ':id_usuario' => $idUsuario
                        ]);

                        $empleadoFeedback = [
                            'type' => 'success',
                            'message' => 'Empleado actualizado correctamente.'
                        ];
                    }
                } else {
                    $stmtUpdate = $db->prepare('UPDATE usuarios SET nombre_usuario = :usuario, rol = :rol, id_caja = :id_caja WHERE id_usuario = :id_usuario');
                    $stmtUpdate->execute([
                        ':usuario' => $usuario,
                        ':rol' => $rol,
                        ':id_caja' => $idCajaFinal,
                        ':id_usuario' => $idUsuario
                    ]);

                    $empleadoFeedback = [
                        'type' => 'success',
                        'message' => 'Empleado actualizado correctamente.'
                    ];
                }
            }
        } catch (PDOException $e) {
            $empleadoFeedback = [
                'type' => 'error',
                'message' => 'No se pudo actualizar el empleado.'
            ];
        }
    }
}

$empleados = [];
$cajasDisponibles = [];
try {
    $db = Conexion::conectar();
    asegurarEstructuraCajas($db);

    $stmtCajas = $db->query("SELECT id_caja, nombre_caja FROM cajas WHERE estado = 'activa' ORDER BY nombre_caja ASC");
    $cajasDisponibles = $stmtCajas ? ($stmtCajas->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

    $stmtEmpleados = $db->query('SELECT u.id_usuario, u.nombre_usuario, u.rol, u.id_caja, u.fecha_creacion, c.nombre_caja FROM usuarios u LEFT JOIN cajas c ON u.id_caja = c.id_caja ORDER BY u.id_usuario DESC');
    $empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    $empleados = [];
    if ($empleadoFeedback['message'] === '') {
        $empleadoFeedback = [
            'type' => 'error',
            'message' => 'No se pudo cargar el listado de empleados.'
        ];
    }
}

$totalEmpleados = count($empleados);
$totalAdmins = count(array_filter($empleados, static fn($emp) => ($emp['rol'] ?? '') === 'admin'));
$totalCajeros = count(array_filter($empleados, static fn($emp) => ($emp['rol'] ?? '') === 'cajero'));
$totalActivos = $totalEmpleados;

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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="empleados-page">
    <div class="stats-grid stats-list">
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-user-tie"></i> Empleados Totales</span>
            <span class="stats-list-value" id="statsEmpleadosTotal"><?= $totalEmpleados ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-user-shield"></i> Administradores</span>
            <span class="stats-list-value" id="statsEmpleadosAdmin"><?= $totalAdmins ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-cash-register"></i> Cajeros</span>
            <span class="stats-list-value" id="statsEmpleadosCajeros"><?= $totalCajeros ?></span>
        </div>
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-user-check"></i> Activos</span>
            <span class="stats-list-value" id="statsEmpleadosActivos"><?= $totalActivos ?></span>
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
                        <th>Caja</th>
                        <th>Fecha Creación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($empleados)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; color:#999;">No hay empleados registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($empleados as $emp): ?>
                            <tr>
                                <td><?= (int)$emp['id_usuario'] ?></td>
                                <td><?= htmlspecialchars($emp['nombre_usuario']) ?></td>
                                <td><?= htmlspecialchars($emp['rol']) ?></td>
                                <td><?= htmlspecialchars($emp['rol'] === 'admin' ? 'No aplica' : ($emp['nombre_caja'] ?? 'Sin asignar')) ?></td>
                                <td><?= !empty($emp['fecha_creacion']) ? date('d/m/Y H:i', strtotime($emp['fecha_creacion'])) : '-' ?></td>
                                <td><span class="status-badge completed">Activo</span></td>
                                <td class="acciones-admin">
                                    <button class="btn-icon small btnEditarEmpleado"
                                        data-id="<?= (int)$emp['id_usuario'] ?>"
                                        data-usuario="<?= htmlspecialchars($emp['nombre_usuario'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-rol="<?= htmlspecialchars($emp['rol'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-id-caja="<?= (int)($emp['id_caja'] ?? 0) ?>"
                                        title="Editar empleado">
                                        <i class="fas fa-edit"></i>
                                    </button>
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

<div class="modal" id="empleadoModal">
    <div class="modal-content empleados-modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Nuevo Empleado</h3>
            <button class="modal-close" type="button" onclick="closeEmpleadoModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="empleadoForm" method="post">
                <div class="form-group">
                    <label>Nombre Usuario</label>
                    <input class="form-control" id="empleadoUsuario" name="empleadoUsuario" type="text" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Password</label>
                        <input class="form-control" id="empleadoPassword" name="empleadoPassword" type="password" minlength="4" required>
                    </div>
                    <div class="form-group">
                        <label>Rol</label>
                        <select class="form-control" id="empleadoRol" name="empleadoRol" required>
                            <option value="">Seleccionar rol</option>
                            <option value="admin">Admin</option>
                            <option value="cajero">Cajero</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Caja asignada</label>
                    <select class="form-control" id="empleadoCaja" name="empleadoCaja">
                        <option value="">Seleccionar caja</option>
                        <?php foreach ($cajasDisponibles as $caja): ?>
                            <option value="<?= (int)$caja['id_caja'] ?>"><?= htmlspecialchars($caja['nombre_caja']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small id="empleadoCajaHint" style="display:block; margin-top:4px; color:#666;">Solo aplica para cajeros.</small>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-modal-cancel" type="button" onclick="closeEmpleadoModal()">Cancelar</button>
            <button class="btn-modal-primary" type="button" onclick="saveEmpleado()">Guardar</button>
        </div>
    </div>
</div>

<div class="modal" id="empleadoEditModal">
    <div class="modal-content empleados-modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-edit"></i> Editar Empleado</h3>
            <button class="modal-close" type="button" onclick="closeEmpleadoEditModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="empleadoEditForm" method="post">
                <input type="hidden" id="empleadoIdEdit" name="empleadoIdEdit">
                <div class="form-group">
                    <label>Nombre Usuario</label>
                    <input class="form-control" id="empleadoUsuarioEdit" name="empleadoUsuarioEdit" type="text" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nueva Password (opcional)</label>
                        <input class="form-control" id="empleadoPasswordEdit" name="empleadoPasswordEdit" type="password" minlength="4" placeholder="Dejar vacío para conservar">
                    </div>
                    <div class="form-group">
                        <label>Rol</label>
                        <select class="form-control" id="empleadoRolEdit" name="empleadoRolEdit" required>
                            <option value="admin">Admin</option>
                            <option value="cajero">Cajero</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Caja asignada</label>
                    <select class="form-control" id="empleadoCajaEdit" name="empleadoCajaEdit">
                        <option value="">Seleccionar caja</option>
                        <?php foreach ($cajasDisponibles as $caja): ?>
                            <option value="<?= (int)$caja['id_caja'] ?>"><?= htmlspecialchars($caja['nombre_caja']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small id="empleadoCajaEditHint" style="display:block; margin-top:4px; color:#666;">Solo aplica para cajeros.</small>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-modal-cancel" type="button" onclick="closeEmpleadoEditModal()">Cancelar</button>
            <button class="btn-modal-primary" type="button" onclick="saveEmpleadoEdit()">Guardar cambios</button>
        </div>
    </div>
</div>

<script>
    const empleadoFeedbackType = <?= json_encode((string)($empleadoFeedback['type'] ?? ''), JSON_UNESCAPED_UNICODE) ?>;
    const empleadoFeedbackMessage = <?= json_encode((string)($empleadoFeedback['message'] ?? ''), JSON_UNESCAPED_UNICODE) ?>;
    const empleadoLastAction = <?= json_encode((string)$empleadoLastAction, JSON_UNESCAPED_UNICODE) ?>;

    const empleadosTableBody = document.querySelector('#empleadosTable tbody');
    const searchEmpleado = document.getElementById('searchEmpleado');
    const filterEmpleadoNombre = document.getElementById('filterEmpleadoNombre');
    const filterEmpleadoRol = document.getElementById('filterEmpleadoRol');
    const filterEmpleadoEstado = document.getElementById('filterEmpleadoEstado');
    const empleadoRol = document.getElementById('empleadoRol');
    const empleadoCaja = document.getElementById('empleadoCaja');
    const empleadoRolEdit = document.getElementById('empleadoRolEdit');
    const empleadoCajaEdit = document.getElementById('empleadoCajaEdit');

    function applyEmpleadosFilters() {
        const term = (searchEmpleado?.value || '').toLowerCase().trim();
        const rol = (filterEmpleadoRol?.value || '').toLowerCase();
        const estado = (filterEmpleadoEstado?.value || '').toLowerCase();
        const rows = Array.from(empleadosTableBody.querySelectorAll('tr'));

        rows.forEach((row) => {
            const rowText = row.textContent.toLowerCase();
            const rolText = row.children[2]?.textContent.toLowerCase().trim() || '';
            const estadoText = row.children[5]?.textContent.toLowerCase().trim() || '';

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

        actualizarStatsEmpleados();
    }

    function actualizarStatsEmpleados() {
        const rows = Array.from(empleadosTableBody.querySelectorAll('tr'));
        const visibles = rows.filter(row => row.style.display !== 'none' && row.children.length >= 5);

        const total = visibles.length;
        const admins = visibles.filter(row => (row.children[2]?.textContent || '').toLowerCase().includes('admin')).length;
        const cajeros = visibles.filter(row => (row.children[2]?.textContent || '').toLowerCase().includes('cajero')).length;

        const totalEl = document.getElementById('statsEmpleadosTotal');
        const adminsEl = document.getElementById('statsEmpleadosAdmin');
        const cajerosEl = document.getElementById('statsEmpleadosCajeros');
        const activosEl = document.getElementById('statsEmpleadosActivos');

        if (totalEl) totalEl.textContent = String(total);
        if (adminsEl) adminsEl.textContent = String(admins);
        if (cajerosEl) cajerosEl.textContent = String(cajeros);
        if (activosEl) activosEl.textContent = String(total);
    }

    function toggleCajaCreate() {
        if (!empleadoRol || !empleadoCaja) return;
        const esCajero = empleadoRol.value === 'cajero';
        empleadoCaja.disabled = !esCajero;
        empleadoCaja.required = esCajero;
        if (!esCajero) empleadoCaja.value = '';
    }

    function toggleCajaEdit() {
        if (!empleadoRolEdit || !empleadoCajaEdit) return;
        const esCajero = empleadoRolEdit.value === 'cajero';
        empleadoCajaEdit.disabled = !esCajero;
        empleadoCajaEdit.required = esCajero;
        if (!esCajero) empleadoCajaEdit.value = '';
    }

    function openEmpleadoModal() {
        const form = document.getElementById('empleadoForm');
        form?.reset();
        toggleCajaCreate();
        document.getElementById('empleadoModal').classList.add('active');
    }

    function closeEmpleadoModal() {
        document.getElementById('empleadoModal').classList.remove('active');
    }

    function openEmpleadoEditModal() {
        document.getElementById('empleadoEditModal').classList.add('active');
    }

    function closeEmpleadoEditModal() {
        document.getElementById('empleadoEditModal').classList.remove('active');
    }

    function saveEmpleado() {
        const form = document.getElementById('empleadoForm');
        if (!form) return;

        toggleCajaCreate();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (empleadoRol?.value === 'cajero' && (!empleadoCaja?.value || parseInt(empleadoCaja.value, 10) <= 0)) {
            Swal.fire({
                icon: 'warning',
                title: 'Caja requerida',
                text: 'Selecciona una caja para el cajero.',
                confirmButtonColor: '#A67C52'
            });
            return;
        }

        form.querySelector('input[name="guardar_empleado"]')?.remove();

        const hiddenAction = document.createElement('input');
        hiddenAction.type = 'hidden';
        hiddenAction.name = 'guardar_empleado';
        hiddenAction.value = '1';
        form.appendChild(hiddenAction);
        form.submit();
    }

    function saveEmpleadoEdit() {
        const form = document.getElementById('empleadoEditForm');
        if (!form) return;

        toggleCajaEdit();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (empleadoRolEdit?.value === 'cajero' && (!empleadoCajaEdit?.value || parseInt(empleadoCajaEdit.value, 10) <= 0)) {
            Swal.fire({
                icon: 'warning',
                title: 'Caja requerida',
                text: 'Selecciona una caja para el cajero.',
                confirmButtonColor: '#A67C52'
            });
            return;
        }

        form.querySelector('input[name="editar_empleado"]')?.remove();

        const hiddenAction = document.createElement('input');
        hiddenAction.type = 'hidden';
        hiddenAction.name = 'editar_empleado';
        hiddenAction.value = '1';
        form.appendChild(hiddenAction);
        form.submit();
    }

    document.querySelectorAll('.btnEditarEmpleado').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id || '';
            const usuario = this.dataset.usuario || '';
            const rol = this.dataset.rol || 'cajero';
            const idCaja = this.dataset.idCaja || '';

            document.getElementById('empleadoIdEdit').value = id;
            document.getElementById('empleadoUsuarioEdit').value = usuario;
            document.getElementById('empleadoPasswordEdit').value = '';
            if (empleadoRolEdit) empleadoRolEdit.value = rol;
            if (empleadoCajaEdit) empleadoCajaEdit.value = idCaja && idCaja !== '0' ? idCaja : '';

            toggleCajaEdit();
            openEmpleadoEditModal();
        });
    });

    searchEmpleado?.addEventListener('input', applyEmpleadosFilters);
    filterEmpleadoNombre?.addEventListener('change', applyEmpleadosFilters);
    filterEmpleadoRol?.addEventListener('change', applyEmpleadosFilters);
    filterEmpleadoEstado?.addEventListener('change', applyEmpleadosFilters);
    empleadoRol?.addEventListener('change', toggleCajaCreate);
    empleadoRolEdit?.addEventListener('change', toggleCajaEdit);
    document.getElementById('btnResetEmpleadoFiltros')?.addEventListener('click', function () {
        if (filterEmpleadoNombre) filterEmpleadoNombre.value = '';
        if (filterEmpleadoRol)    filterEmpleadoRol.value    = '';
        if (filterEmpleadoEstado) filterEmpleadoEstado.value = '';
        applyEmpleadosFilters();
    });
    document.getElementById('btnNuevoEmpleado')?.addEventListener('click', openEmpleadoModal);
    document.addEventListener('DOMContentLoaded', function () {
        actualizarStatsEmpleados();
        toggleCajaCreate();
        toggleCajaEdit();
    });

    if (empleadoFeedbackMessage !== '') {
        const iconByType = {
            success: 'success',
            warning: 'warning',
            error: 'error'
        };

        const titleByType = {
            success: empleadoFeedbackMessage.toLowerCase().includes('actualizado') ? 'Empleado actualizado' : 'Empleado guardado',
            warning: 'Revisión requerida',
            error: 'No se pudo guardar'
        };

        Swal.fire({
            icon: iconByType[empleadoFeedbackType] || 'info',
            title: titleByType[empleadoFeedbackType] || 'Aviso',
            text: empleadoFeedbackMessage,
            confirmButtonColor: '#A67C52'
        });
    }

    <?php if ($empleadoFeedback['type'] === 'warning' || $empleadoFeedback['type'] === 'error'): ?>
    if (empleadoLastAction === 'editar') {
        openEmpleadoEditModal();
    } else {
        openEmpleadoModal();
    }
    <?php endif; ?>
</script>

<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>
