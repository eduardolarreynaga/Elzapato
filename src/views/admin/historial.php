<?php
// ==================== 1. LÓGICA DE DATOS ====================
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

$basePath = realpath(__DIR__ . '/../../../');
require_once $basePath . "/model/conexion.php";
require_once $basePath . "/helpers/LogHelper.php";

$db = Conexion::conectar();

// Obtener parámetros de filtro
$fechaDesde = $_GET['fecha_desde'] ?? date('Y-m-01');
$fechaHasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
$tipoAccion = $_GET['tipo_accion'] ?? 'todos';
$usuarioFiltro = $_GET['usuario_id'] ?? '0';
$tabActiva = $_GET['tab'] ?? 'panel1';

// Obtener lista de usuarios para el filtro
$sqlUsuarios = "SELECT id_usuario, nombre_usuario, rol FROM usuarios ORDER BY nombre_usuario";
$stmtUsuarios = $db->prepare($sqlUsuarios);
$stmtUsuarios->execute();
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

// ==================== CONSULTAS SEPARADAS ====================
$historial = [];

// 1. LOGS DE LA TABLA historial_logs (login, logout, crear, editar, eliminar)
if ($tipoAccion == 'todos' || in_array($tipoAccion, ['login', 'logout', 'login_fallido', 'crear', 'editar', 'eliminar', 'asignar_dinero'])) {
    $logs = LogHelper::obtenerLogs($fechaDesde, $fechaHasta, intval($usuarioFiltro), ($tipoAccion == 'todos' ? null : $tipoAccion), 500);
    foreach ($logs as $log) {
        $log['accion_display'] = $log['accion'];
        $historial[] = $log;
    }
}

// 2. VENTAS
if ($tipoAccion == 'todos' || $tipoAccion == 'venta') {
    $sql = "SELECT 
               'venta' as accion,
               v.id_venta as registro_id,
               v.fecha_venta as fecha,
               u.nombre_usuario as nombre_usuario,
               u.rol as rol_usuario,
               CONCAT('Venta #', v.id_venta, ' - $', FORMAT(v.total_venta, 2)) as detalle,
               v.total_venta as monto,
               mp.nombre_metodo as metodo_pago,
               v.estado,
               'ventas' as tabla_afectada
            FROM ventas v
            INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
            LEFT JOIN metodos_pago mp ON v.id_metodo_pago = mp.id_metodo_pago
            WHERE DATE(v.fecha_venta) BETWEEN :fecha_desde AND :fecha_hasta";
    if ($usuarioFiltro > 0) {
        $sql .= " AND u.id_usuario = :usuario_id";
    }
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':fecha_desde', $fechaDesde);
    $stmt->bindParam(':fecha_hasta', $fechaHasta);
    if ($usuarioFiltro > 0) {
        $stmt->bindParam(':usuario_id', $usuarioFiltro);
    }
    $stmt->execute();
    $historial = array_merge($historial, $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// 3. DEVOLUCIONES
if ($tipoAccion == 'todos' || $tipoAccion == 'devolucion') {
    $sql = "SELECT 
               'devolucion' as accion,
               dv.id_devolucion as registro_id,
               dv.fecha_devolucion as fecha,
               u.nombre_usuario as nombre_usuario,
               u.rol as rol_usuario,
               CONCAT('Devolución de venta #', dv.id_venta, ' - ', dv.cantidad_devuelta, ' unidades - $', FORMAT(dv.total_devuelto, 2)) as detalle,
               dv.total_devuelto as monto,
               NULL as metodo_pago,
               'completada' as estado,
               'devoluciones_venta' as tabla_afectada
            FROM devoluciones_venta dv
            INNER JOIN usuarios u ON dv.id_usuario = u.id_usuario
            WHERE DATE(dv.fecha_devolucion) BETWEEN :fecha_desde AND :fecha_hasta";
    if ($usuarioFiltro > 0) {
        $sql .= " AND u.id_usuario = :usuario_id";
    }
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':fecha_desde', $fechaDesde);
    $stmt->bindParam(':fecha_hasta', $fechaHasta);
    if ($usuarioFiltro > 0) {
        $stmt->bindParam(':usuario_id', $usuarioFiltro);
    }
    $stmt->execute();
    $historial = array_merge($historial, $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// 4. APERTURAS DE CAJA
if ($tipoAccion == 'todos' || $tipoAccion == 'apertura_caja') {
    $sql = "SELECT 
               'apertura_caja' as accion,
               ca.id_apertura as registro_id,
               ca.fecha_apertura as fecha,
               u.nombre_usuario as nombre_usuario,
               u.rol as rol_usuario,
               CONCAT('Apertura de caja - Caja: ', COALESCE(c.nombre_caja, 'N/A'), ' - Monto inicial: $', FORMAT(ca.monto_inicial, 2)) as detalle,
               ca.monto_inicial as monto,
               NULL as metodo_pago,
               ca.estado,
               'caja_aperturas' as tabla_afectada
            FROM caja_aperturas ca
            INNER JOIN usuarios u ON ca.id_usuario = u.id_usuario
            LEFT JOIN cajas c ON ca.id_caja = c.id_caja
            WHERE DATE(ca.fecha_apertura) BETWEEN :fecha_desde AND :fecha_hasta";
    if ($usuarioFiltro > 0) {
        $sql .= " AND u.id_usuario = :usuario_id";
    }
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':fecha_desde', $fechaDesde);
    $stmt->bindParam(':fecha_hasta', $fechaHasta);
    if ($usuarioFiltro > 0) {
        $stmt->bindParam(':usuario_id', $usuarioFiltro);
    }
    $stmt->execute();
    $historial = array_merge($historial, $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// 5. CIERRES DE CAJA
if ($tipoAccion == 'todos' || $tipoAccion == 'cierre_caja') {
    $sql = "SELECT 
               'cierre_caja' as accion,
               ca.id_apertura as registro_id,
               ca.fecha_cierre as fecha,
               u.nombre_usuario as nombre_usuario,
               u.rol as rol_usuario,
               CONCAT('Cierre de caja - Caja: ', COALESCE(c.nombre_caja, 'N/A'), ' - Ventas: ', ca.total_ventas, ' - Ingresos: $', FORMAT(ca.total_ingresos, 2)) as detalle,
               ca.monto_cierre as monto,
               NULL as metodo_pago,
               ca.estado,
               'caja_aperturas' as tabla_afectada
            FROM caja_aperturas ca
            INNER JOIN usuarios u ON ca.id_usuario = u.id_usuario
            LEFT JOIN cajas c ON ca.id_caja = c.id_caja
            WHERE ca.fecha_cierre IS NOT NULL 
            AND DATE(ca.fecha_cierre) BETWEEN :fecha_desde AND :fecha_hasta";
    if ($usuarioFiltro > 0) {
        $sql .= " AND u.id_usuario = :usuario_id";
    }
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':fecha_desde', $fechaDesde);
    $stmt->bindParam(':fecha_hasta', $fechaHasta);
    if ($usuarioFiltro > 0) {
        $stmt->bindParam(':usuario_id', $usuarioFiltro);
    }
    $stmt->execute();
    $historial = array_merge($historial, $stmt->fetchAll(PDO::FETCH_ASSOC));
}

// Ordenar por fecha descendente
usort($historial, function($a, $b) {
    return strtotime($b['fecha']) - strtotime($a['fecha']);
});

// Limitar a 500 registros
$historial = array_slice($historial, 0, 500);

// Calcular estadísticas
$totalRegistros = count($historial);
$usuariosUnicos = count(array_unique(array_filter(array_column($historial, 'nombre_usuario'))));
$totalVentas = count(array_filter($historial, function($item) { return $item['accion'] == 'venta'; }));
$totalDevoluciones = count(array_filter($historial, function($item) { return $item['accion'] == 'devolucion'; }));
$totalAperturas = count(array_filter($historial, function($item) { return $item['accion'] == 'apertura_caja'; }));
$totalCierres = count(array_filter($historial, function($item) { return $item['accion'] == 'cierre_caja'; }));
$totalLogins = count(array_filter($historial, function($item) { return $item['accion'] == 'login'; }));
$totalLogouts = count(array_filter($historial, function($item) { return $item['accion'] == 'logout'; }));
$totalCreaciones = count(array_filter($historial, function($item) { return $item['accion'] == 'crear'; }));
$totalEdiciones = count(array_filter($historial, function($item) { return $item['accion'] == 'editar'; }));
$totalEliminaciones = count(array_filter($historial, function($item) { return $item['accion'] == 'eliminar'; }));

// Tipos de acción para el filtro
$tiposAccion = [
    'todos' => 'Todos',
    'venta' => 'Ventas',
    'devolucion' => 'Devoluciones',
    'apertura_caja' => 'Aperturas de Caja',
    'cierre_caja' => 'Cierres de Caja',
    'login' => 'Inicios de Sesión',
    'logout' => 'Cierres de Sesión',
    'login_fallido' => 'Intentos Fallidos',
    'crear' => 'Creaciones',
    'editar' => 'Ediciones',
    'eliminar' => 'Eliminaciones'
];

// Función para obtener el badge de tipo
function getTipoBadge($accion) {
    $badges = [
        'venta' => '<span class="badge badge-venta"><i class="fas fa-shopping-cart"></i> Venta</span>',
        'devolucion' => '<span class="badge badge-devolucion"><i class="fas fa-undo-alt"></i> Devolución</span>',
        'apertura_caja' => '<span class="badge badge-apertura"><i class="fas fa-unlock-alt"></i> Apertura Caja</span>',
        'cierre_caja' => '<span class="badge badge-cierre"><i class="fas fa-lock"></i> Cierre Caja</span>',
        'login' => '<span class="badge badge-login"><i class="fas fa-sign-in-alt"></i> Login</span>',
        'logout' => '<span class="badge badge-logout"><i class="fas fa-sign-out-alt"></i> Logout</span>',
        'login_fallido' => '<span class="badge badge-login-fallido"><i class="fas fa-exclamation-triangle"></i> Login Fallido</span>',
        'crear' => '<span class="badge badge-crear"><i class="fas fa-plus-circle"></i> Creación</span>',
        'editar' => '<span class="badge badge-editar"><i class="fas fa-edit"></i> Edición</span>',
        'eliminar' => '<span class="badge badge-eliminar"><i class="fas fa-trash-alt"></i> Eliminación</span>'
    ];
    return $badges[$accion] ?? '<span class="badge badge-otro"><i class="fas fa-info-circle"></i> ' . ucfirst($accion) . '</span>';
}

// ==================== CONFIGURACIÓN ====================
$activeMenu = 'historial';
$pageTitle = 'Historial';
$pageStyles = [
    '/ElZapato/Assets/css/pages/admin-stats.css',
    '/ElZapato/Assets/css/pages/admin-ventas.css',
    '/ElZapato/Assets/css/pages/admin-reportes.css'
];

require __DIR__ . '/../layouts/admin-shell-start.php';

$pageHeading = 'Historial del Sistema';
$searchInputId = 'searchHistorial';
$searchPlaceholder = 'Buscar en el historial...';
$showSearch = true;

require __DIR__ . '/../layouts/admin-header.php';
?>

<style>
    :root {
        --primary-light: #E4E0E1;
        --primary-soft: #D6C0B3;
        --primary-dark: #AB886D;
        --text-dark: #000000;
        --font-family: "Roboto", sans-serif;
        --nocolor: #772c24;
    }

    * {
        font-family: var(--font-family);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(9, 1fr);
        gap: 12px;
        margin-bottom: 25px;
    }
    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 12px 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-left: 4px solid var(--primary-dark);
        text-align: center;
    }
    .stats-card .stats-number {
        font-size: 22px;
        font-weight: 700;
        color: var(--primary-dark);
    }
    .stats-card .stats-label {
        font-size: 11px;
        color: #666;
        margin-top: 5px;
    }
    .stats-card .stats-label i {
        margin-right: 4px;
        color: var(--primary-dark);
    }
    .filtros-bar {
        background: white;
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: flex-end;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .filtros-bar .filtro-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .filtros-bar label {
        font-size: 12px;
        font-weight: 600;
        color: #666;
        text-transform: uppercase;
    }
    .filtros-bar label i {
        margin-right: 4px;
        color: var(--primary-dark);
    }
    .filtros-bar input, .filtros-bar select {
        padding: 8px 12px;
        border: 1px solid var(--primary-soft);
        border-radius: 8px;
        font-size: 14px;
        min-width: 150px;
    }
    .filtros-bar input:focus, .filtros-bar select:focus {
        border-color: var(--primary-dark);
        outline: none;
        box-shadow: 0 0 0 2px rgba(171,136,109,0.2);
    }
    .filtros-bar button {
        background: var(--primary-dark);
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 8px;
        cursor: pointer;
        transition: 0.3s;
    }
    .filtros-bar button:hover {
        background: #8B6B4F;
    }
    
    /* Botón enviar correo */
    .btn-enviar-correo {
        background: var(--primary-dark);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        transition: 0.3s;
        font-weight: 600;
        margin-bottom: 20px;
    }
    .btn-enviar-correo:hover {
        background: #8B6B4F;
        transform: translateY(-2px);
    }
    
    /* Badges */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .badge i {
        font-size: 11px;
    }
    .badge-venta { background: var(--primary-dark); color: white; }
    .badge-devolucion { background: var(--nocolor); color: white; }
    .badge-apertura { background: var(--primary-dark); color: white; }
    .badge-cierre { background: var(--primary-soft); color: var(--text-dark); }
    .badge-login { background: var(--primary-dark); color: white; }
    .badge-logout { background: var(--primary-soft); color: var(--text-dark); }
    .badge-login-fallido { background: var(--nocolor); color: white; }
    .badge-crear { background: var(--primary-dark); color: white; }
    .badge-editar { background: var(--primary-dark); color: white; }
    .badge-eliminar { background: var(--nocolor); color: white; }
    .badge-otro { background: var(--primary-soft); color: var(--text-dark); }
    
    /* Tablas */
    .table-container {
        background: white;
        border-radius: 12px;
        overflow-x: auto;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .data-table thead th {
        background: var(--primary-dark);
        color: white;
        padding: 14px 12px;
        text-align: left;
        font-weight: 600;
        white-space: nowrap;
    }
    .data-table thead th i {
        margin-right: 6px;
    }
    .data-table tbody td {
        padding: 12px;
        border-bottom: 1px solid var(--primary-light);
        vertical-align: middle;
        color: var(--text-dark);
    }
    .data-table tbody tr:hover {
        background: var(--primary-light);
    }
    .data-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .text-center {
        text-align: center;
    }
    .text-success { color: var(--primary-dark); font-weight: bold; }
    .text-danger { color: var(--nocolor); font-weight: bold; }
    .text-warning { color: var(--nocolor); font-weight: bold; }
    .text-info { color: var(--primary-dark); font-weight: bold; }
    
    .rol-badge {
        display: inline-block;
        background: var(--primary-light);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        color: var(--primary-dark);
        margin-top: 4px;
    }
    
    /* Tabs */
    .tablist {
        display: flex;
        gap: 5px;
        margin-bottom: 20px;
        border-bottom: 2px solid var(--primary-soft);
        padding-bottom: 0;
        list-style: none;
    }
    .tab {
        margin: 0;
    }
    .tab a {
        display: block;
        padding: 10px 18px;
        text-decoration: none;
        color: var(--text-dark);
        font-weight: 500;
        border-radius: 8px 8px 0 0;
        transition: 0.3s;
    }
    .tab a i {
        margin-right: 6px;
    }
    .tab a:hover {
        background: var(--primary-light);
        color: var(--primary-dark);
    }
    .tab-active a {
        background: var(--primary-dark);
        color: white;
    }
    .tab-active a:hover {
        background: var(--primary-dark);
        color: white;
    }
    
    .tabpanel {
        display: none;
    }
    .tabpanel.show {
        display: block;
        animation: fadeIn 0.3s ease;
    }
    
    /* Modal */
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
    }
    .modal-content {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        width: 450px;
        margin: 0 auto;
    }
    .modal-header {
        background: var(--primary-dark);
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
    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid var(--primary-soft);
        border-radius: 8px;
    }
    .form-control:focus {
        border-color: var(--primary-dark);
        outline: none;
    }
    .btn-primary {
        background: var(--primary-dark);
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 5px;
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
        border-radius: 5px;
        cursor: pointer;
    }
    .btn-secondary:hover {
        background: #5a6268;
    }
    .text-muted {
        font-size: 11px;
        color: #666;
        margin-top: 5px;
        display: block;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Toast notifications */
    .toast {
        background: #333;
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        transform: translateX(120%);
        transition: 0.3s;
    }
    .toast.show {
        transform: translateX(0);
    }
    .toast-success { border-left: 4px solid var(--primary-dark); }
    .toast-warning { border-left: 4px solid var(--nocolor); }
    .toast-info { border-left: 4px solid var(--primary-soft); }
    
    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(5, 1fr);
        }
    }
    @media (max-width: 992px) {
        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .filtros-bar {
            flex-direction: column;
        }
        .filtros-bar .filtro-group {
            width: 100%;
        }
        .filtros-bar input, .filtros-bar select {
            width: 100%;
        }
        .data-table {
            font-size: 11px;
        }
        .data-table thead th,
        .data-table tbody td {
            padding: 8px;
        }
        .badge {
            padding: 3px 8px;
            font-size: 9px;
        }
        .tab a {
            padding: 8px 12px;
            font-size: 12px;
        }
        .modal-content {
            width: 90%;
            margin: 30px auto;
        }
    }
</style>

<!-- STATS -->
<div class="stats-grid">
    <div class="stats-card">
        <div class="stats-number"><?= number_format($totalRegistros) ?></div>
        <div class="stats-label"><i class="fas fa-database"></i> Total</div>
    </div>
    <div class="stats-card">
        <div class="stats-number"><?= number_format($usuariosUnicos) ?></div>
        <div class="stats-label"><i class="fas fa-users"></i> Usuarios</div>
    </div>
    <div class="stats-card">
        <div class="stats-number"><?= number_format($totalVentas) ?></div>
        <div class="stats-label"><i class="fas fa-shopping-cart"></i> Ventas</div>
    </div>
    <div class="stats-card">
        <div class="stats-number"><?= number_format($totalDevoluciones) ?></div>
        <div class="stats-label"><i class="fas fa-undo-alt"></i> Devoluciones</div>
    </div>
    <div class="stats-card">
        <div class="stats-number"><?= number_format($totalLogins) ?></div>
        <div class="stats-label"><i class="fas fa-sign-in-alt"></i> Logins</div>
    </div>
    <div class="stats-card">
        <div class="stats-number"><?= number_format($totalLogouts) ?></div>
        <div class="stats-label"><i class="fas fa-sign-out-alt"></i> Logouts</div>
    </div>
    <div class="stats-card">
        <div class="stats-number"><?= number_format($totalCreaciones) ?></div>
        <div class="stats-label"><i class="fas fa-plus-circle"></i> Creaciones</div>
    </div>
    <div class="stats-card">
        <div class="stats-number"><?= number_format($totalEdiciones) ?></div>
        <div class="stats-label"><i class="fas fa-edit"></i> Ediciones</div>
    </div>
    <div class="stats-card">
        <div class="stats-number"><?= number_format($totalEliminaciones) ?></div>
        <div class="stats-label"><i class="fas fa-trash-alt"></i> Eliminaciones</div>
    </div>
</div>

<!-- FILTROS -->
<div class="filtros-bar">
    <div class="filtro-group">
        <label><i class="fas fa-calendar-alt"></i> Desde</label>
        <input type="date" id="fecha_desde" value="<?= htmlspecialchars($fechaDesde) ?>">
    </div>
    <div class="filtro-group">
        <label><i class="fas fa-calendar-alt"></i> Hasta</label>
        <input type="date" id="fecha_hasta" value="<?= htmlspecialchars($fechaHasta) ?>">
    </div>
    <div class="filtro-group">
        <label><i class="fas fa-user"></i> Usuario</label>
        <select id="usuario_id">
            <option value="0">Todos los usuarios</option>
            <?php foreach ($usuarios as $usuario): ?>
                <option value="<?= $usuario['id_usuario'] ?>" <?= ($usuarioFiltro == $usuario['id_usuario']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($usuario['nombre_usuario']) ?> (<?= ucfirst($usuario['rol']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filtro-group">
        <label><i class="fas fa-tag"></i> Tipo</label>
        <select id="tipo_accion">
            <?php foreach ($tiposAccion as $key => $label): ?>
                <option value="<?= $key ?>" <?= ($tipoAccion == $key) ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filtro-group">
        <button id="btnFiltrar"><i class="fas fa-filter"></i> Aplicar</button>
    </div>
</div>

<!-- Botón para enviar por correo -->
<div style="margin-bottom: 20px; text-align: right;">
    <button class="btn-enviar-correo" onclick="abrirModalEnviarCorreo()">
        <i class="fas fa-envelope"></i> Enviar Historial por Correo
    </button>
</div>

<!-- Modal para enviar correo -->
<div id="modalEnviarCorreo" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="margin: 0;"><i class="fas fa-envelope"></i> Enviar Historial por Correo</h3>
            <button type="button" class="close" onclick="cerrarModalEnviarCorreo()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Correo del Administrador:</label>
                <input type="email" id="email_destino" class="form-control" placeholder="admin@ejemplo.com" required>
                <small class="text-muted">Se enviará el historial con los filtros actuales</small>
            </div>
            <div class="form-group">
                <label><i class="fas fa-chart-line"></i> Resumen a enviar:</label>
                <div style="background: var(--primary-light); padding: 10px; border-radius: 8px; font-size: 13px;">
                    <div><strong>📅 Período:</strong> <span id="resumen_fecha_desde"><?= $fechaDesde ?></span> - <span id="resumen_fecha_hasta"><?= $fechaHasta ?></span></div>
                    <div><strong>👤 Usuario filtrado:</strong> <span id="resumen_usuario"><?= $usuarioFiltro > 0 ? htmlspecialchars($usuarios[array_search($usuarioFiltro, array_column($usuarios, 'id_usuario'))]['nombre_usuario'] ?? 'Todos') : 'Todos' ?></span></div>
                    <div><strong>🏷️ Tipo:</strong> <span id="resumen_tipo"><?= $tiposAccion[$tipoAccion] ?? 'Todos' ?></span></div>
                    <div><strong>📊 Registros:</strong> <span id="resumen_total"><?= $totalRegistros ?></span></div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="cerrarModalEnviarCorreo()">Cancelar</button>
            <button type="button" class="btn-primary" onclick="enviarHistorialCorreo()">Enviar Correo</button>
        </div>
    </div>
</div>

<!-- TABS -->
<ul class="tablist">
    <li class="tab <?= ($tabActiva == 'panel1') ? 'tab-active' : '' ?>"><a href="#panel1"><i class="fas fa-list"></i> TODOS</a></li>
    <li class="tab <?= ($tabActiva == 'panel2') ? 'tab-active' : '' ?>"><a href="#panel2"><i class="fas fa-shopping-cart"></i> VENTAS</a></li>
    <li class="tab <?= ($tabActiva == 'panel3') ? 'tab-active' : '' ?>"><a href="#panel3"><i class="fas fa-undo-alt"></i> DEVOLUCIONES</a></li>
    <li class="tab <?= ($tabActiva == 'panel4') ? 'tab-active' : '' ?>"><a href="#panel4"><i class="fas fa-cash-register"></i> CAJA</a></li>
    <li class="tab <?= ($tabActiva == 'panel5') ? 'tab-active' : '' ?>"><a href="#panel5"><i class="fas fa-sign-in-alt"></i> LOGIN/LOGOUT</a></li>
    <li class="tab <?= ($tabActiva == 'panel6') ? 'tab-active' : '' ?>"><a href="#panel6"><i class="fas fa-tools"></i> CRUD</a></li>
</ul>

<div id="historialContent">

<!-- PANEL 1: TODOS -->
<div class="tabpanel <?= ($tabActiva == 'panel1') ? 'show' : '' ?>" id="panel1">
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th><i class="fas fa-clock"></i> Fecha/Hora</th>
                    <th><i class="fas fa-user"></i> Usuario</th>
                    <th><i class="fas fa-tag"></i> Tipo</th>
                    <th><i class="fas fa-info-circle"></i> Detalle</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($historial)): ?>
                    <tr class="text-center"><td colspan="4">No hay registros en el período seleccionado</td></tr>
                <?php else: ?>
                    <?php foreach ($historial as $item): ?>
                        <tr>
                            <td style="white-space: nowrap;"><?= date('d/m/Y H:i:s', strtotime($item['fecha'])) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($item['nombre_usuario'] ?? 'Sistema') ?></strong>
                                <?php if (!empty($item['rol_usuario'])): ?>
                                    <div class="rol-badge"><?= ucfirst($item['rol_usuario']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= getTipoBadge($item['accion']) ?></td>
                            <td><?= htmlspecialchars($item['detalle'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- PANEL 2: VENTAS -->
<div class="tabpanel <?= ($tabActiva == 'panel2') ? 'show' : '' ?>" id="panel2">
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th><i class="fas fa-clock"></i> Fecha/Hora</th>
                    <th><i class="fas fa-user"></i> Usuario</th>
                    <th><i class="fas fa-hashtag"></i> # Venta</th>
                    <th><i class="fas fa-credit-card"></i> Método</th>
                    <th><i class="fas fa-dollar-sign"></i> Total</th>
                    <th><i class="fas fa-check-circle"></i> Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $ventasFiltradas = array_filter($historial, function($item) {
                    return $item['accion'] == 'venta';
                });
                ?>
                <?php if (empty($ventasFiltradas)): ?>
                    <tr class="text-center"><td colspan="6">No hay ventas en el período seleccionado</td></tr>
                <?php else: ?>
                    <?php foreach ($ventasFiltradas as $item): ?>
                        <tr>
                            <td style="white-space: nowrap;"><?= date('d/m/Y H:i:s', strtotime($item['fecha'])) ?></td>
                            <td><strong><?= htmlspecialchars($item['nombre_usuario']) ?></strong></td>
                            <td>#<?= $item['registro_id'] ?></td>
                            <td><?= htmlspecialchars($item['metodo_pago'] ?? 'N/A') ?></td>
                            <td class="text-success">$<?= number_format($item['monto'] ?? 0, 2) ?></td>
                            <td class="text-success"><?= ucfirst($item['estado'] ?? 'completada') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- PANEL 3: DEVOLUCIONES -->
<div class="tabpanel <?= ($tabActiva == 'panel3') ? 'show' : '' ?>" id="panel3">
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th><i class="fas fa-clock"></i> Fecha/Hora</th>
                    <th><i class="fas fa-user"></i> Usuario</th>
                    <th><i class="fas fa-info-circle"></i> Detalle</th>
                    <th><i class="fas fa-dollar-sign"></i> Total Devuelto</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $devolucionesFiltradas = array_filter($historial, function($item) {
                    return $item['accion'] == 'devolucion';
                });
                ?>
                <?php if (empty($devolucionesFiltradas)): ?>
                    <tr class="text-center"><td colspan="4">No hay devoluciones en el período seleccionado</td></tr>
                <?php else: ?>
                    <?php foreach ($devolucionesFiltradas as $item): ?>
                        <tr>
                            <td style="white-space: nowrap;"><?= date('d/m/Y H:i:s', strtotime($item['fecha'])) ?></td>
                            <td><strong><?= htmlspecialchars($item['nombre_usuario']) ?></strong></td>
                            <td><?= htmlspecialchars($item['detalle']) ?></td>
                            <td class="text-success">$<?= number_format($item['monto'] ?? 0, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- PANEL 4: CAJA -->
<div class="tabpanel <?= ($tabActiva == 'panel4') ? 'show' : '' ?>" id="panel4">
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th><i class="fas fa-clock"></i> Fecha/Hora</th>
                    <th><i class="fas fa-user"></i> Usuario</th>
                    <th><i class="fas fa-tag"></i> Tipo</th>
                    <th><i class="fas fa-info-circle"></i> Detalle</th>
                    <th><i class="fas fa-dollar-sign"></i> Monto</th>
                    <th><i class="fas fa-check-circle"></i> Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $cajaFiltradas = array_filter($historial, function($item) {
                    return $item['accion'] == 'apertura_caja' || $item['accion'] == 'cierre_caja';
                });
                ?>
                <?php if (empty($cajaFiltradas)): ?>
                    <tr class="text-center"><td colspan="6">No hay movimientos de caja en el período seleccionado</td></tr>
                <?php else: ?>
                    <?php foreach ($cajaFiltradas as $item): ?>
                        <tr>
                            <td style="white-space: nowrap;"><?= date('d/m/Y H:i:s', strtotime($item['fecha'])) ?></td>
                            <td><strong><?= htmlspecialchars($item['nombre_usuario']) ?></strong></td>
                            <td><?= getTipoBadge($item['accion']) ?></td>
                            <td><?= htmlspecialchars($item['detalle']) ?></td>
                            <td class="text-success">$<?= number_format($item['monto'] ?? 0, 2) ?></td>
                            <td class="text-info"><?= ucfirst($item['estado'] ?? 'completada') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- PANEL 5: LOGIN/LOGOUT -->
<div class="tabpanel <?= ($tabActiva == 'panel5') ? 'show' : '' ?>" id="panel5">
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th><i class="fas fa-clock"></i> Fecha/Hora</th>
                    <th><i class="fas fa-user"></i> Usuario</th>
                    <th><i class="fas fa-tag"></i> Tipo</th>
                    <th><i class="fas fa-info-circle"></i> Detalle</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $loginFiltrados = array_filter($historial, function($item) {
                    return $item['accion'] == 'login' || $item['accion'] == 'logout' || $item['accion'] == 'login_fallido';
                });
                ?>
                <?php if (empty($loginFiltrados)): ?>
                    <tr class="text-center"><td colspan="4">No hay registros de login/logout en el período seleccionado</td></tr>
                <?php else: ?>
                    <?php foreach ($loginFiltrados as $item): ?>
                        <tr>
                            <td style="white-space: nowrap;"><?= date('d/m/Y H:i:s', strtotime($item['fecha'])) ?></td>
                            <td><strong><?= htmlspecialchars($item['nombre_usuario'] ?? 'Desconocido') ?></strong></td>
                            <td><?= getTipoBadge($item['accion']) ?></td>
                            <td><?= htmlspecialchars($item['detalle'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- PANEL 6: CRUD -->
<div class="tabpanel <?= ($tabActiva == 'panel6') ? 'show' : '' ?>" id="panel6">
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th><i class="fas fa-clock"></i> Fecha/Hora</th>
                    <th><i class="fas fa-user"></i> Usuario</th>
                    <th><i class="fas fa-tag"></i> Acción</th>
                    <th><i class="fas fa-table"></i> Tabla</th>
                    <th><i class="fas fa-info-circle"></i> Detalle</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $crudFiltrados = array_filter($historial, function($item) {
                    return in_array($item['accion'], ['crear', 'editar', 'eliminar']);
                });
                ?>
                <?php if (empty($crudFiltrados)): ?>
                    <tr class="text-center"><td colspan="5">No hay registros de CRUD en el período seleccionado</td></tr>
                <?php else: ?>
                    <?php foreach ($crudFiltrados as $item): ?>
                        <tr>
                            <td style="white-space: nowrap;"><?= date('d/m/Y H:i:s', strtotime($item['fecha'])) ?></td>
                            <td><strong><?= htmlspecialchars($item['nombre_usuario']) ?></strong></td>
                            <td><?= getTipoBadge($item['accion']) ?></td>
                            <td><?= htmlspecialchars($item['tabla_afectada'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($item['detalle'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div>

<script>
(function(){
    // Tabs
    const tabs = document.querySelectorAll('.tab a');
    const panels = document.querySelectorAll('.tabpanel');
    const defaultTab = '<?= htmlspecialchars($tabActiva, ENT_QUOTES, 'UTF-8') ?>';

    function activarTab(targetId, updateUrl = true) {
        const id = (targetId || '').replace('#', '');
        const panel = document.getElementById(id);
        if (!panel) return;

        tabs.forEach(link => {
            const li = link.parentNode;
            const active = link.getAttribute('href') === '#' + id;
            li.classList.toggle('tab-active', active);
            link.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        panels.forEach(p => p.classList.toggle('show', p.id === id));

        if (updateUrl) {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', id);
            history.replaceState({}, '', url.toString());
        }
    }

    tabs.forEach(link => {
        link.setAttribute('role', 'tab');
        link.addEventListener('click', function(e) {
            e.preventDefault();
            activarTab(this.getAttribute('href'), true);
        });
    });

    const tabFromHash = window.location.hash ? window.location.hash.substring(1) : '';
    activarTab(tabFromHash || defaultTab || 'panel1', false);

    // Filtrar
    const btnFiltrar = document.getElementById('btnFiltrar');
    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', function() {
            const fechaDesde = document.getElementById('fecha_desde').value;
            const fechaHasta = document.getElementById('fecha_hasta').value;
            const usuarioId = document.getElementById('usuario_id').value;
            const tipoAccion = document.getElementById('tipo_accion').value;
            const tabActiva = document.querySelector('.tab-active a')?.getAttribute('href')?.replace('#', '') || 'panel1';
            
            let url = window.location.pathname + '?';
            url += 'fecha_desde=' + fechaDesde;
            url += '&fecha_hasta=' + fechaHasta;
            url += '&usuario_id=' + usuarioId;
            url += '&tipo_accion=' + tipoAccion;
            url += '&tab=' + tabActiva;
            
            window.location.href = url;
        });
    }

    // Búsqueda en tiempo real
    const searchInput = document.getElementById('searchHistorial');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.data-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (searchTerm === '' || text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
})();

// ==================== FUNCIONES PARA ENVÍO DE CORREO ====================
function abrirModalEnviarCorreo() {
    document.getElementById('modalEnviarCorreo').style.display = 'flex';
}

function cerrarModalEnviarCorreo() {
    document.getElementById('modalEnviarCorreo').style.display = 'none';
}

async function enviarHistorialCorreo() {
    const email = document.getElementById('email_destino').value;
    
    if (!email) {
        mostrarNotificacion('Por favor ingrese un correo electrónico', 'warning');
        return;
    }
    
    if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        mostrarNotificacion('Correo electrónico inválido', 'warning');
        return;
    }
    
    const fechaDesde = document.getElementById('fecha_desde').value;
    const fechaHasta = document.getElementById('fecha_hasta').value;
    const usuarioId = document.getElementById('usuario_id').value;
    const tipoAccion = document.getElementById('tipo_accion').value;
    const nombreUsuario = '<?= $_SESSION['usuario'] ?>';
    
    // Mostrar loading
    const btnEnviar = document.querySelector('#modalEnviarCorreo .btn-primary');
    const textoOriginal = btnEnviar.innerHTML;
    btnEnviar.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Enviando...';
    btnEnviar.disabled = true;
    
    const formData = new FormData();
    formData.append('email', email);
    formData.append('nombre', nombreUsuario);
    formData.append('fecha_desde', fechaDesde);
    formData.append('fecha_hasta', fechaHasta);
    formData.append('usuario_id', usuarioId);
    formData.append('tipo_accion', tipoAccion);
    
    try {
        const response = await fetch('/ElZapato/src/api/enviar_historial.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarNotificacion('Correo enviado exitosamente', 'success');
            cerrarModalEnviarCorreo();
            document.getElementById('email_destino').value = '';
        } else {
            mostrarNotificacion(data.message || 'Error al enviar el correo', 'warning');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error al enviar el correo', 'warning');
    } finally {
        btnEnviar.innerHTML = textoOriginal;
        btnEnviar.disabled = false;
    }
}

// Función para mostrar notificaciones
function mostrarNotificacion(mensaje, tipo) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 9999;';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = 'toast show toast-' + tipo;
    
    let icono = '';
    if (tipo === 'warning') icono = '⚠️ ';
    else if (tipo === 'success') icono = '✅ ';
    else icono = 'ℹ️ ';
    
    toast.innerHTML = icono + mensaje;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 10);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(120%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>