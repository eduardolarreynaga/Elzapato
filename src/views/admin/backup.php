<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

$basePath = realpath(__DIR__ . '/../../../');
require_once $basePath . "/controller/backupController.php";

$backupController = new BackupController();
$backupController->procesarBackup();
$backupController->procesarDescarga();

$backups = $backupController->obtenerBackups();
$estadisticas = $backupController->obtenerEstadisticas();
$tablas = $backupController->obtenerTablas();

$activeMenu = 'backup';
$pageTitle = 'Respaldos | ElZapato';
$pageStyles = [];
require __DIR__ . '/../layouts/admin-shell-start.php';
?>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-left: 4px solid #AB886D;
    }
    .stat-card h4 {
        margin: 0 0 10px 0;
        color: #666;
        font-size: 0.85rem;
        text-transform: uppercase;
    }
    .stat-card .value {
        font-size: 1.8rem;
        font-weight: bold;
        color: #4D3B2E;
    }
    .backup-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .backup-section h3 {
        color: #AB886D;
        margin-top: 0;
        margin-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 10px;
    }
    .backup-section h4 {
        color: #4D3B2E;
        margin: 20px 0 10px 0;
    }
    .backup-table {
        width: 100%;
        border-collapse: collapse;
    }
    .backup-table th,
    .backup-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    .backup-table th {
        background: #f8f5f2;
        color: #4D3B2E;
        font-weight: 600;
    }
    .btn-backup {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    .btn-primary {
        background: #AB886D;
        color: white;
    }
    .btn-primary:hover {
        background: #8a6b53;
    }
    .btn-success {
        background: #2e7d32;
        color: white;
    }
    .btn-success:hover {
        background: #1b5e20;
    }
    .btn-danger {
        background: #772C24;
        color: white;
    }
    .btn-danger:hover {
        background: #5a201a;
    }
    .btn-sm {
        padding: 5px 12px;
        font-size: 12px;
    }
    .info-text {
        background: #f8f5f2;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 15px;
        font-size: 0.9rem;
    }
    .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: bold;
    }
    .badge-completo {
        background: #AB886D;
        color: white;
    }
    .badge-tabla {
        background: #bc6e32;
        color: white;
    }
    .badge-auto {
        background: #2e7d32;
        color: white;
    }
    .form-row {
        display: flex;
        gap: 15px;
        align-items: flex-end;
        flex-wrap: wrap;
    }
    .form-group {
        min-width: 200px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #4D3B2E;
        font-weight: bold;
    }
    .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        min-width: 250px;
    }
    .empty-state {
        text-align: center;
        padding: 30px;
        color: #999;
    }
    code {
        background: #f0f0f0;
        padding: 2px 6px;
        border-radius: 4px;
        font-family: monospace;
    }
    .button-group {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-top: 10px;
    }
</style>

<?php
$pageHeading = 'Respaldos de Base de Datos';
$showSearch = false;
require __DIR__ . '/../layouts/admin-header.php';
?>

<div class="backup-page">
    <!-- Estadisticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <h4><i class="fas fa-database"></i> Total Backups</h4>
            <div class="value"><?php echo $estadisticas['total_backups']; ?></div>
        </div>
        <div class="stat-card">
            <h4><i class="fas fa-hdd"></i> Espacio Total</h4>
            <div class="value"><?php echo $estadisticas['tamano_total_formateado']; ?></div>
        </div>
    </div>

    <!-- ========== BACKUPS MANUALES ========== -->
    
    <!-- Backup Completo Manual -->
    <div class="backup-section">
        <h3><i class="fas fa-database"></i> Backup Completo (Manual)</h3>
        <div class="info-text">
            <i class="fas fa-info-circle"></i> Respaldar toda la base de datos <strong>pos_zapateria</strong><br>
            Archivo: <code>pos_zapateria.sql.gz</code>
        </div>
        <form method="post">
            <input type="hidden" name="accion_backup" value="crear_completo">
            <button type="submit" class="btn-backup btn-primary">
                <i class="fas fa-database"></i> Generar Backup Completo
            </button>
        </form>
    </div>

    <!-- Backup por Tabla Manual -->
    <div class="backup-section">
        <h3><i class="fas fa-table"></i> Backup por Tabla (Manual)</h3>
        <div class="info-text">
            <i class="fas fa-info-circle"></i> Selecciona una tabla especifica para respaldar solo esa tabla<br>
            Archivo: <code>nombre_tabla.sql.gz</code>
        </div>
        <form method="post" class="form-row">
            <input type="hidden" name="accion_backup_tabla" value="crear_backup_tabla">
            <div class="form-group">
                <label>Seleccionar Tabla</label>
                <select name="tabla_seleccionada" required>
                    <option value="">-- Seleccione una tabla --</option>
                    <?php foreach ($tablas as $tabla): ?>
                    <option value="<?php echo $tabla['nombre']; ?>">
                        <?php echo $tabla['nombre']; ?> (<?php echo number_format($tabla['registros']); ?> registros)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="btn-backup btn-primary" style="background: #bc6e32;">
                    <i class="fas fa-table"></i> Generar Backup de Tabla
                </button>
            </div>
        </form>
    </div>

    <!-- ========== BACKUPS AUTOMATICOS (SOLO INFORMACION) ========== -->
    
    <div class="backup-section">
        <h3><i class="fas fa-robot"></i> Backups Automaticos</h3>
        <div class="info-text">
            <i class="fas fa-info-circle"></i> 
            <strong><i class="fas fa-calendar-alt"></i> Backup Diario:</strong> Se ejecuta automaticamente todos los dias a las <strong>6:00 PM</strong><br>
            Respalda TODA la base de datos - Archivo: <code>pos_zapateria.sql.gz</code><br><br>
            <strong><i class="fas fa-clock"></i> Backup por Hora (POS):</strong> Se ejecuta automaticamente <strong>CADA HORA</strong><br>
            Respalda las tablas esenciales del punto de venta:
            <code>productos.sql.gz</code>, <code>ventas.sql.gz</code>, <code>clientes.sql.gz</code>, etc.
        </div>
    </div>

    <!-- ========== MANTENIMIENTO ========== -->
    
    <div class="backup-section">
        <h3><i class="fas fa-tools"></i> Mantenimiento</h3>
        <div class="info-text">
            <i class="fas fa-exclamation-triangle"></i> <strong>Precaución:</strong> Esta acción eliminará TODOS los backups.<br>
            Útil para limpiar antes de hacer pruebas en vivo.
        </div>
        <div class="button-group">
            <form method="post" onsubmit="return confirmarLimpieza(event)">
                <input type="hidden" name="accion_backup" value="limpiar_todo">
                <button type="submit" class="btn-backup btn-danger">
                    <i class="fas fa-trash-alt"></i> Limpiar Todos los Backups
                </button>
            </form>
        </div>
    </div>

    <!-- ========== LISTA DE BACKUPS DISPONIBLES ========== -->

    <div class="backup-section">
        <h3><i class="fas fa-archive"></i> Backups Disponibles</h3>
        
        <!-- Backups Completos -->
        <h4><i class="fas fa-database"></i> Backups Completos</h4>
        <table class="backup-table">
            <thead>
                <tr>
                    <th>Archivo</th>
                    <th>Fecha</th>
                    <th>Tamaño</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($backups['completo']) > 0): ?>
                    <?php foreach ($backups['completo'] as $b): ?>
                    <tr>
                        <td><i class="fas fa-file-archive"></i> <?php echo $b['nombre']; ?> <span class="badge badge-completo">Completo</span></td>
                        <td><?php echo $b['fecha']; ?></td>
                        <td><?php echo $b['tamano']; ?></td>
                        <td>
                            <a href="backup.php?accion_backup=descargar&archivo=<?php echo urlencode($b['nombre']); ?>&tipo=completo" class="btn-backup btn-success btn-sm">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="empty-state"><td colspan="4">No hay backups completos disponibles</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Backups por Tabla -->
        <h4><i class="fas fa-table"></i> Backups por Tabla</h4>
        <table class="backup-table">
            <thead>
                <tr>
                    <th>Archivo</th>
                    <th>Fecha</th>
                    <th>Tamaño</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($backups['tablas']) > 0): ?>
                    <?php foreach ($backups['tablas'] as $b): ?>
                    <tr>
                        <td><i class="fas fa-file-archive"></i> <?php echo $b['nombre']; ?> <span class="badge badge-tabla">Tabla</span></td>
                        <td><?php echo $b['fecha']; ?></td>
                        <td><?php echo $b['tamano']; ?></td>
                        <td>
                            <a href="backup.php?accion_backup=descargar&archivo=<?php echo urlencode($b['nombre']); ?>&tipo=tablas" class="btn-backup btn-success btn-sm">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="empty-state"><td colspan="4">No hay backups por tabla disponibles</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Backups por Hora (Automaticos) -->
        <h4><i class="fas fa-clock"></i> Backups por Hora (Automaticos)</h4>
        <table class="backup-table">
            <thead>
                <tr>
                    <th>Archivo</th>
                    <th>Fecha</th>
                    <th>Tamaño</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($backups['hora']) > 0): ?>
                    <?php foreach ($backups['hora'] as $b): ?>
                    <tr>
                        <td><i class="fas fa-file-archive"></i> <?php echo $b['nombre']; ?> <span class="badge badge-auto">Hora</span></td>
                        <td><?php echo $b['fecha']; ?></td>
                        <td><?php echo $b['tamano']; ?></td>
                        <td>
                            <a href="backup.php?accion_backup=descargar&archivo=<?php echo urlencode($b['nombre']); ?>&tipo=hora" class="btn-backup btn-success btn-sm">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="empty-state"><td colspan="4">No hay backups por hora disponibles</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmarLimpieza(event) {
    event.preventDefault();
    Swal.fire({
        title: '¿Eliminar todos los backups?',
        text: 'Esta acción eliminará TODOS los archivos de backup. No se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#772C24',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Sí, eliminar todo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="accion_backup" value="limpiar_todo">';
            document.body.appendChild(form);
            form.submit();
        }
    });
    return false;
}
</script>

<?php if (isset($_SESSION['backup_resultado'])): ?>
<script>
Swal.fire({
    title: '<?php echo $_SESSION['backup_resultado']['success'] ? 'Backup Generado' : 'Error'; ?>',
    text: '<?php echo $_SESSION['backup_resultado']['success'] ? 'Archivo: ' . $_SESSION['backup_resultado']['archivo'] . ' - Tamaño: ' . $_SESSION['backup_resultado']['tamano'] : addslashes($_SESSION['backup_resultado']['error']); ?>',
    icon: '<?php echo $_SESSION['backup_resultado']['success'] ? "success" : "error"; ?>',
    confirmButtonColor: '#AB886D'
});
</script>
<?php unset($_SESSION['backup_resultado']); endif; ?>

<?php if (isset($_SESSION['backup_hora_resultado'])): ?>
<script>
Swal.fire({
    title: 'Backups por Hora',
    text: 'Se generaron <?php echo $_SESSION['backup_hora_resultado']['total']; ?> backups de tablas POS',
    icon: 'success',
    confirmButtonColor: '#AB886D'
});
</script>
<?php unset($_SESSION['backup_hora_resultado']); endif; ?>

<?php if (isset($_SESSION['limpieza_resultado'])): ?>
<script>
Swal.fire({
    title: 'Limpieza Completada',
    text: 'Se eliminaron <?php echo $_SESSION['limpieza_resultado']['eliminados']; ?> archivos de backup',
    icon: 'success',
    confirmButtonColor: '#AB886D'
});
</script>
<?php unset($_SESSION['limpieza_resultado']); endif; ?>

<?php 
require __DIR__ . '/../layouts/admin-shell-end.php'; 
require __DIR__ . '/../layouts/admin-html-end.php'; 
?>