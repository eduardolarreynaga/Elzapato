<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

$activeMenu = 'caja';
$pageTitle = 'Caja';

$pageStyles = [
    '/ElZapato/Assets/css/pages/admin-stats.css', 
    '/ElZapato/Assets/css/pages/admin-ventas.css',
    '/ElZapato/Assets/css/caja.css'
];
require __DIR__ . '/../layouts/admin-shell-start.php';

$pageHeading = 'Caja';
$searchInputId = 'searchVenta';
$searchPlaceholder = 'Buscar por empleado o caja...';
$showSearch = true;
require __DIR__ . '/../layouts/admin-header.php';

require_once __DIR__ . '/../../../model/conexion.php';
$db = Conexion::conectar();

$mensaje = '';
$error = '';

// Verificar si la columna monto_caja existe en usuarios
try {
    $db->query("SELECT monto_caja FROM usuarios LIMIT 1");
} catch(PDOException $e) {
    $db->exec("ALTER TABLE `usuarios` ADD COLUMN `monto_caja` DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER `id_caja`");
    $mensaje = "Se ha inicializado la columna de dinero para los empleados";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'crear_caja':
                $nombre_caja = trim($_POST['nombre_caja']);
                $check = $db->prepare("SELECT id_caja FROM cajas WHERE nombre_caja = ?");
                $check->execute([$nombre_caja]);
                if ($check->fetch()) {
                    $error = "Ya existe una caja con ese nombre";
                } else {
                    $stmt = $db->prepare("INSERT INTO cajas (nombre_caja, estado, fecha_creacion) VALUES (?, 'activa', NOW())");
                    if ($stmt->execute([$nombre_caja])) {
                        $mensaje = "Caja física '$nombre_caja' creada exitosamente";
                    } else {
                        $error = "Error al crear la caja";
                    }
                }
                break;
                
            case 'asignar_dinero':
                $id_usuario = intval($_POST['id_usuario']);
                $monto = floatval($_POST['monto']);
                
                if ($monto <= 0) {
                    $error = "El monto debe ser mayor a 0";
                } else {
                    $check = $db->prepare("SELECT id_usuario, nombre_usuario, rol FROM usuarios WHERE id_usuario = ?");
                    $check->execute([$id_usuario]);
                    $usuario = $check->fetch();
                    
                    if (!$usuario) {
                        $error = "El usuario no existe";
                    } else {
                        $stmt = $db->prepare("UPDATE usuarios SET monto_caja = monto_caja + ? WHERE id_usuario = ?");
                        if ($stmt->execute([$monto, $id_usuario])) {
                            $stmt2 = $db->prepare("SELECT monto_caja FROM usuarios WHERE id_usuario = ?");
                            $stmt2->execute([$id_usuario]);
                            $nuevo_monto = $stmt2->fetchColumn();
                            
                            $mensaje = "Se han asignado $" . number_format($monto, 2) . " al empleado '{$usuario['nombre_usuario']}'.<br>
                                       Nuevo saldo en su caja: $" . number_format($nuevo_monto, 2);
                        } else {
                            $error = "Error al asignar el dinero";
                        }
                    }
                }
                break;
        }
    }
}

// Obtener SOLO CAJEROS con su caja asignada y su dinero individual
$sql = "SELECT 
            u.id_usuario,
            u.nombre_usuario,
            u.rol,
            u.monto_caja,
            c.id_caja as caja_id,
            c.nombre_caja
        FROM usuarios u
        LEFT JOIN cajas c ON u.id_caja = c.id_caja
        WHERE u.rol = 'cajero'
        ORDER BY c.nombre_caja, u.nombre_usuario";
$stmt = $db->prepare($sql);
$stmt->execute();
$cajeros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .info-nota {
        background: #E4E0E1;
        border-left: 4px solid #AB886D;
        padding: 12px 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .info-nota i {
        color: #AB886D;
        font-size: 20px;
    }
    .btn-crear, .btn-asignar {
        background: #AB886D;
        color: white;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .btn-crear {
        padding: 10px 20px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    .btn-asignar {
        padding: 5px 15px;
        border-radius: 5px;
    }
    .btn-crear:hover, .btn-asignar:hover {
        background: #8B6B4F;
        transform: translateY(-2px);
    }
    .table-cajas {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        width: 100%;
        border-collapse: collapse;
    }
    .table-cajas thead th {
        background: #AB886D;
        color: white;
        font-weight: 600;
        padding: 12px;
        text-align: left;
    }
    .table-cajas tbody tr {
        border-bottom: 1px solid #eee;
        transition: all 0.3s ease;
    }
    .table-cajas tbody tr:hover {
        background: #E4E0E1;
    }
    .table-cajas tbody td {
        padding: 12px;
        vertical-align: middle;
    }
    .monto-usuario {
        font-weight: bold;
        color: #AB886D;
        font-size: 1.1em;
    }
    .alert-success {
        background: #d4edda;
        color: #155724;
        padding: 12px;
        border-radius: 5px;
        margin-bottom: 20px;
        border-left: 4px solid #28a745;
    }
    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        padding: 12px;
        border-radius: 5px;
        margin-bottom: 20px;
        border-left: 4px solid #dc3545;
    }
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
    }
    .modal-content {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        width: 500px;
        margin: 50px auto;
    }
    .modal-header {
        background: #AB886D;
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-header .close {
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
    }
    .modal-body {
        padding: 20px;
    }
    .modal-footer {
        padding: 15px 20px;
        background: #f9f9f9;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        font-weight: 600;
        margin-bottom: 5px;
        display: block;
    }
    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .form-control:focus {
        border-color: #AB886D;
        outline: none;
    }
    .form-control[readonly] {
        background: #f5f5f5;
        cursor: not-allowed;
    }
    .btn-primary {
        background: #AB886D;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn-primary:hover {
        background: #8B6B4F;
    }
    .btn-secondary {
        background: #6c757d;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 4px;
        cursor: pointer;
    }
    @media (max-width: 768px) {
        .table-cajas {
            font-size: 12px;
        }
        .modal-content {
            width: 90%;
            margin: 30px auto;
        }
    }
</style>

<div class="caja-container">
    <div class="info-nota">
        <i class="fas fa-info-circle"></i>
        <small>
            <strong>Nota:</strong> Cada empleado (cajero) tiene su propio fondo de dinero. 
            La caja física es solo una etiqueta que se asigna desde la gestión de usuarios.
        </small>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <div class="row mb-3">
        <div class="col-md-12">
            <button class="btn-crear" onclick="abrirModalCrear()">
                <i class="fas fa-plus"></i> Crear Nueva Caja Física
            </button>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table-cajas">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Empleado</th>
                    <th>Caja Física Asignada</th>
                    <th>💰 Dinero en su Caja</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cajeros)): ?>
                    <tr class="text-center">
                        <td colspan="5">No hay empleados cajeros registrados</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($cajeros as $cajero): ?>
                        <tr>
                            <td><?php echo $cajero['id_usuario']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($cajero['nombre_usuario']); ?></strong>
                            </td>
                            <td>
                                <?php if ($cajero['nombre_caja']): ?>
                                    <i class="fas fa-cash-register"></i> <?php echo htmlspecialchars($cajero['nombre_caja']); ?>
                                <?php else: ?>
                                    <span style="color: #999;">
                                        <i class="fas fa-user-slash"></i> Sin caja asignada
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="monto-usuario">
                                $<?php echo number_format($cajero['monto_caja'] ?? 0, 2); ?>
                            </td>
                            <td>
                                <button class="btn-asignar" 
                                        onclick="abrirModalAsignar(<?php echo $cajero['id_usuario']; ?>, '<?php echo htmlspecialchars($cajero['nombre_usuario']); ?>', <?php echo $cajero['monto_caja'] ?? 0; ?>)">
                                    <i class="fas fa-money-bill-wave"></i> Asignar Dinero
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Crear Caja Física -->
<div id="modalCrear" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-plus"></i> Crear Nueva Caja Física</h5>
            <button type="button" class="close" onclick="cerrarModal('modalCrear')">&times;</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" value="crear_caja">
                <div class="form-group">
                    <label>Nombre de la Caja *</label>
                    <input type="text" name="nombre_caja" class="form-control" required placeholder="Ej: Caja Principal">
                    <small>La caja física se asigna a los empleados desde la gestión de usuarios</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="cerrarModal('modalCrear')">Cancelar</button>
                <button type="submit" class="btn-primary">Crear Caja</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Asignar Dinero a Empleado -->
<div id="modalAsignar" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-money-bill-wave"></i> Asignar Dinero a Empleado</h5>
            <button type="button" class="close" onclick="cerrarModal('modalAsignar')">&times;</button>
        </div>
        <form method="POST" action="" id="formAsignar">
            <div class="modal-body">
                <input type="hidden" name="action" value="asignar_dinero">
                <input type="hidden" name="id_usuario" id="asignar_id_usuario">
                
                <div class="form-group">
                    <label>Empleado</label>
                    <input type="text" id="asignar_nombre_usuario" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label>💰 Dinero Actual en su Caja</label>
                    <input type="text" id="asignar_monto_actual" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label>Monto a Asignar *</label>
                    <input type="number" name="monto" id="monto_asignar" class="form-control" step="0.01" min="0.01" required>
                    <small>Este dinero se suma al fondo del empleado para que tenga cambio</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="cerrarModal('modalAsignar')">Cancelar</button>
                <button type="submit" class="btn-primary">Asignar Dinero</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalCrear() {
    document.getElementById('modalCrear').style.display = 'block';
}

function abrirModalAsignar(id, nombre, montoActual) {
    document.getElementById('asignar_id_usuario').value = id;
    document.getElementById('asignar_nombre_usuario').value = nombre;
    document.getElementById('asignar_monto_actual').value = '$' + parseFloat(montoActual).toFixed(2);
    document.getElementById('monto_asignar').value = '';
    document.getElementById('modalAsignar').style.display = 'block';
}

function cerrarModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Búsqueda
const searchInput = document.getElementById('searchVenta');
if (searchInput) {
    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.table-cajas tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
}

const formAsignar = document.getElementById('formAsignar');
if (formAsignar) {
    formAsignar.addEventListener('submit', function(e) {
        const monto = document.getElementById('monto_asignar').value;
        if (parseFloat(monto) <= 0) {
            e.preventDefault();
            alert('El monto debe ser mayor a 0');
        }
    });
}
</script>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>