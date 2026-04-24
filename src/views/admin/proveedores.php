<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

require_once __DIR__ . '/../../../model/ProveedorModel.php';
require_once __DIR__ . '/../../../controller/proveedorController.php';

$controlador = new ControladorProveedor();
$mensajeAlerta = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["nuevoNombreEmpresa"])) {
        $respuesta = $controlador->ctrCrearProveedor();
        if($respuesta == "ok") $mensajeAlerta = "guardado";

    } else if (isset($_POST["editarIdProveedor"])) {
        $respuesta = $controlador->ctrEditarProveedor();
        if($respuesta == "ok") $mensajeAlerta = "editado";

    } elseif (isset($_POST["id_eliminar_proveedor"])) {
        $respuesta = $controlador->ctrEliminarProveedor();

        if($respuesta == "ok"){
            header("Location: proveedores.php");
            exit();
        }
    }
}

$proveedores = $controlador->ctrMostrarProveedores();
$stats = $controlador->ctrMostrarEstadisticas();

$activeMenu = 'proveedores';
$pageTitle = 'Proveedores';
// Asegúrate de que estas rutas de CSS existan en tu carpeta Assets
$pageStyles = ['/ElZapato/Assets/css/pages/admin-stats.css', '/ElZapato/Assets/css/pages/admin-proveedores.css'];

require __DIR__ . '/../layouts/admin-shell-start.php';

// Header del panel
$pageHeading = 'Gestión de Proveedores';
$searchInputId = 'searchProveedor';
$searchPlaceholder = 'Buscar proveedor...';
$showSearch = true;
require __DIR__ . '/../layouts/admin-header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="proveedores-page" style="padding: 1px;">

    <div class="stats-grid stats-list" style="margin-bottom: 25px;">
        <div class="stats-list-item">
            <span class="stats-list-label"><i class="fas fa-truck"></i> Proveedores Totales</span>
            <span class="stats-list-value"><?php echo $stats['total_proveedores'] ?? 0; ?></span>
        </div>
    </div>

    <div class="actions-bar" style="margin-bottom: 25px; display: flex; align-items: center; gap: 15px;">
        <button type="button" onclick="openAddModal()" 
            style="background: #ffffff; 
                   color: #A67C52; 
                   border: 1px solid #A67C52; 
                   padding: 10px 22px; 
                   border-radius: 8px; 
                   cursor: pointer; 
                   display: flex; 
                   align-items: center; 
                   gap: 10px; 
                   font-weight: 500;
                   font-size: 1rem;
                   transition: all 0.2s ease-in-out;">
            <i class="fas fa-plus"></i> Nuevo Proveedor
        </button>
    </div>

    <div class="table-card" style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden;">
        <div class="table-responsive">
            <table class="data-table" id="proveedoresTable" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #fdfaf8; text-align: left; border-bottom: 2px solid #eee;">
                        <th style="padding: 15px;">ID</th>
                        <th style="padding: 15px;">Empresa</th>
                        <th style="padding: 15px;">Contacto Directo</th>
                        <th style="padding: 15px;">Teléfono</th>
                        <th style="padding: 15px;">Correo Electrónico</th>
                        <th style="padding: 15px; text-align: center;">Acciones</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($proveedores)): foreach ($proveedores as $p): ?>
                    <tr style="border-bottom: 1px solid #f1f1f1;">
                        <td style="padding: 15px; color: #888;">#<?php echo $p['id_proveedor']; ?></td>
                        <td style="padding: 15px;"><strong style="color: #333;"><?php echo htmlspecialchars($p['nombre_empresa']); ?></strong></td>
                        <td style="padding: 15px;"><?php echo htmlspecialchars($p['contacto_nombre'] ?: 'No asignado'); ?></td>
                        <td style="padding: 15px;"><?php echo htmlspecialchars($p['telefono'] ?: '---'); ?></td>
                        <td style="padding: 15px; color: #A67C52;"><?php echo htmlspecialchars($p['email'] ?: '---'); ?></td>
                        <td style="padding: 15px; text-align: center;">

    <!-- EDITAR -->
    <button class="btn-icon btnEditarProveedor" 
        style="background: none; border: 1px solid #ddd; color: #A67C52; padding: 6px 10px; border-radius: 6px; cursor: pointer;"
        data-id="<?php echo $p['id_proveedor']; ?>"
        data-empresa="<?php echo $p['nombre_empresa']; ?>"
        data-contacto="<?php echo $p['contacto_nombre']; ?>"
        data-tel="<?php echo $p['telefono']; ?>"
        data-email="<?php echo $p['email']; ?>">
        <i class="fas fa-edit"></i>
    </button>

<!-- ELIMINAR -->
<form method="POST" style="display:inline;">
    <input type="hidden" name="id_eliminar_proveedor" value="<?php echo $p['id_proveedor']; ?>">

    <button type="submit"
        style="background:none; border:1px solid #ddd; color:#A67C52; padding:6px 10px; border-radius:6px; cursor:pointer;"
        onclick="return confirm('¿Eliminar este proveedor?')">

        <i class="fas fa-trash"></i>
        </button>
    </form>

    </td>
</tr>

                    <?php endforeach; else: ?>
                    <tr><td colspan="6" style="padding: 30px; text-align: center; color: #999;">No se encontraron proveedores registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal" id="modalAgregarProveedor" style="display:none; align-items:center; justify-content:center; background: rgba(0,0,0,0.5); position:fixed; top:0; left:0; width:100%; height:100%; z-index:9999;">
    <div class="modal-content" style="background:white; padding:35px; border-radius:18px; width:480px; box-shadow: 0 15px 40px rgba(0,0,0,0.2);">
        <h3 style="color: #A67C52; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-plus-circle"></i> Nuevo Proveedor
        </h3>
        <form method="POST">
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 600; font-size: 0.9rem;">Nombre de la Empresa *</label>
                <input class="form-control" name="nuevoNombreEmpresa" type="text" required style="width:100%; padding:10px; border: 1px solid #ddd; border-radius: 8px; margin-top: 5px;">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 600; font-size: 0.9rem;">Persona de Contacto</label>
                <input class="form-control" name="nuevoContacto" type="text" style="width:100%; padding:10px; border: 1px solid #ddd; border-radius: 8px; margin-top: 5px;">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label style="font-weight: 600; font-size: 0.9rem;">Teléfono</label>
                    <input class="form-control" name="nuevoTelefono" type="text" style="width:100%; padding:10px; border: 1px solid #ddd; border-radius: 8px; margin-top: 5px;">
                </div>
                <div class="form-group">
                    <label style="font-weight: 600; font-size: 0.9rem;">Email</label>
                    <input class="form-control" name="nuevoEmail" type="email" style="width:100%; padding:10px; border: 1px solid #ddd; border-radius: 8px; margin-top: 5px;">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <button type="button" onclick="closeAddModal()" style="background: #f5f5f5; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">Cancelar</button>
                <button type="submit" style="background: #A67C52; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600;">Guardar Proveedor</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="modalEditarProveedor" style="display:none; align-items:center; justify-content:center; background: rgba(0,0,0,0.5); position:fixed; top:0; left:0; width:100%; height:100%; z-index:9999;">
    <div class="modal-content" style="background:white; padding:35px; border-radius:18px; width:480px;">
        <h3 style="color: #A67C52; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-edit"></i> Editar Información
        </h3>
        <form id="formEditarProveedor" method="POST">
            <input type="hidden" name="editarIdProveedor" id="editarIdProveedor">
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 600;">Nombre de la Empresa</label>
                <input class="form-control" name="editarEmpresa" id="editarEmpresa" type="text" required style="width:100%; padding:10px; border: 1px solid #ddd; border-radius: 8px; margin-top: 5px;">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 600;">Contacto</label>
                <input class="form-control" name="editarContacto" id="editarContacto" type="text" style="width:100%; padding:10px; border: 1px solid #ddd; border-radius: 8px; margin-top: 5px;">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label style="font-weight: 600;">Teléfono</label>
                    <input class="form-control" name="editarTelefono" id="editarTelefono" type="text" style="width:100%; padding:10px; border: 1px solid #ddd; border-radius: 8px; margin-top: 5px;">
                </div>
                <div class="form-group">
                    <label style="font-weight: 600;">Email</label>
                    <input class="form-control" name="editarEmail" id="editarEmail" type="email" style="width:100%; padding:10px; border: 1px solid #ddd; border-radius: 8px; margin-top: 5px;">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <button type="button" onclick="closeEditModal()" style="background: #f5f5f5; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">Cancelar</button>
                <button type="submit" style="background: #A67C52; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600;">Actualizar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Lógica para abrir/cerrar modales
    function openAddModal() { document.getElementById('modalAgregarProveedor').style.display = 'flex'; }
    function closeAddModal() { document.getElementById('modalAgregarProveedor').style.display = 'none'; }
    function closeEditModal() { document.getElementById('modalEditarProveedor').style.display = 'none'; }

    // Notificaciones de SweetAlert
    <?php if($mensajeAlerta == "guardado"): ?>
        Swal.fire({ icon: 'success', title: '¡Guardado!', text: 'El proveedor ha sido registrado.', background: '#fff', confirmButtonColor: '#A67C52' });
    <?php endif; ?>
    <?php if($mensajeAlerta == "editado"): ?>
        Swal.fire({ icon: 'success', title: '¡Actualizado!', text: 'Cambios guardados con éxito.', background: '#fff', confirmButtonColor: '#A67C52' });
    <?php endif; ?>

    // Cargar datos en el modal de edición al hacer clic en el botón de la tabla
    document.querySelectorAll('.btnEditarProveedor').forEach(btn => {
        btn.onclick = function() {
            document.getElementById('editarIdProveedor').value = this.dataset.id;
            document.getElementById('editarEmpresa').value = this.dataset.empresa;
            document.getElementById('editarContacto').value = this.dataset.contacto;
            document.getElementById('editarTelefono').value = this.dataset.tel;
            document.getElementById('editarEmail').value = this.dataset.email;
            document.getElementById('modalEditarProveedor').style.display = 'flex';
        }
    });

    // Buscador en tiempo real
    document.getElementById('searchProveedor').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#proveedoresTable tbody tr');
        rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
        });
    });
</script>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>
<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>