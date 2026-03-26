<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

require_once __DIR__ . '/../../../model/proveedor_model.php';
require_once __DIR__ . '/../../../controller/proveedor_controller.php';

$controlador = new ControladorProveedor();
$mensajeAlerta = "";

// Lógica de Registro y Edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["nuevoNombreEmpresa"])) {
        $respuesta = $controlador->ctrCrearProveedor();
        if($respuesta == "ok") $mensajeAlerta = "guardado";
    } else if (isset($_POST["editarIdProveedor"])) {
        $respuesta = $controlador->ctrEditarProveedor();
        if($respuesta == "ok") $mensajeAlerta = "editado";
    }
}

$proveedores = $controlador->ctrMostrarProveedores();
$stats = $controlador->ctrMostrarEstadisticas();

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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="proveedores-page">
    <div class="stats-grid stats-list">
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-truck"></i> Proveedores Totales</span>
            <span class="stats-list-value"><?php echo $stats['total_proveedores'] ?? 0; ?></span>
        </div>
        </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table" id="proveedoresTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Empresa</th>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Acciones</th> </tr>
                </thead>
                <tbody>
                    <?php foreach ($proveedores as $p): ?>
                    <tr>
                        <td><?php echo $p['id_proveedor']; ?></td>
                        <td><strong><?php echo htmlspecialchars($p['nombre_empresa']); ?></strong></td>
                        <td><?php echo htmlspecialchars($p['contacto_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($p['telefono']); ?></td>
                        <td><?php echo htmlspecialchars($p['email']); ?></td>
                        <td>
                            <div class="actions-wrapper" style="display: flex; gap: 8px;">
                                <button class="btn-icon small btnEditarProveedor" 
                                    title="Editar"
                                    style="background: #ffffff; color: #A67C52; border: 1px solid #A67C52; padding: 5px 10px; border-radius: 4px; cursor: pointer;"
                                    data-id="<?php echo $p['id_proveedor']; ?>"
                                    data-empresa="<?php echo $p['nombre_empresa']; ?>"
                                    data-contacto="<?php echo $p['contacto_nombre']; ?>"
                                    data-tel="<?php echo $p['telefono']; ?>"
                                    data-email="<?php echo $p['email']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <button class="btn-icon small" 
                                    title="Opciones"
                                    style="background: #ffffff; color: #555555; border: 1px solid #ddd; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal" id="modalEditarProveedor">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Editar Proveedor</h3>
            <button class="modal-close" type="button" onclick="closeEditModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form id="formEditarProveedor" method="POST">
                <input type="hidden" name="editarIdProveedor" id="editarIdProveedor">
                <div class="form-group">
                    <label>Nombre Empresa</label>
                    <input class="form-control" name="editarEmpresa" id="editarEmpresa" type="text" required>
                </div>
                <div class="form-group">
                    <label>Contacto</label>
                    <input class="form-control" name="editarContacto" id="editarContacto" type="text">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input class="form-control" name="editarTelefono" id="editarTelefono" type="text">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input class="form-control" name="editarEmail" id="editarEmail" type="email">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeEditModal()">Cancelar</button>
            <button class="btn-primary" onclick="updateProveedor()">Actualizar Cambios</button>
        </div>
    </div>
</div>

<script>
    // Alertas de SweetAlert
    <?php if($mensajeAlerta == "guardado"): ?>
        Swal.fire({ icon: 'success', title: '¡Guardado!', background: '#772c24', color: '#D6C0B3', showConfirmButton: false, timer: 1500 });
    <?php endif; ?>
    <?php if($mensajeAlerta == "editado"): ?>
        Swal.fire({ icon: 'success', title: '¡Actualizado!', text: 'Los datos se modificaron con éxito', background: '#772c24', color: '#D6C0B3', showConfirmButton: false, timer: 1500 });
    <?php endif; ?>
</script>

<script src="../../../assets/js/proveedores.js"></script>
<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>