<?php
$activeMenu = $activeMenu ?? '';

$userName = $_SESSION['usuario'] ?? 'Invitado';
$userRole = $_SESSION['rol'] ?? 'Sin Rol';
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <button class="sidebar-close" type="button" aria-label="Cerrar menú lateral">
            <i class="fas fa-times"></i>
        </button>
        <div class="logo">
            <img src="/ElZapato/Assets/img/logo.png" alt="Logo" style="height: auto; width: 60px;">
            <h2>ElZapato</h2>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="/ElZapato/src/views/layouts/menu-general.php"><i class="fas fa-home"></i><span>Pagina principal</span></a>
            </li>
            <li class="<?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
                <a href="dashboard.php"><i class="fas fa-chart-pie"></i><span>Estadisticas</span></a>
            </li>
            <li class="<?= $activeMenu === 'productos' ? 'active' : '' ?>">
                <a href="productos.php"><i class="fas fa-boxes"></i><span>Inventario</span></a>
            </li>
            <li class="<?= $activeMenu === 'ventas' ? 'active' : '' ?>">
                <a href="ventas.php"><i class="fas fa-shopping-cart"></i><span>Ventas</span></a>
            </li>
            <li class="<?= $activeMenu === 'clientes' ? 'active' : '' ?>">
                <a href="clientes.php"><i class="fas fa-users"></i><span>Clientes</span></a>
            </li>
            <li class="<?= $activeMenu === 'empleados' ? 'active' : '' ?>">
                <a href="empleados.php"><i class="fas fa-user-tie"></i><span>Empleados</span></a>
            </li>
            <li class="<?= $activeMenu === 'proveedores' ? 'active' : '' ?>">
                <a href="proveedores.php"><i class="fas fa-truck"></i><span>Proveedores</span></a>
            </li>
            <li class="<?= $activeMenu === 'reportes' ? 'active' : '' ?>">
                <a href="reportes.php"><i class="fas fa-chart-line"></i><span>Reportes</span></a>
            </li>
            <li class="<?= $activeMenu === 'caja' ? 'active' : '' ?>">
                <a href="caja.php"><i class="fas fa-cash-register"></i><span>Caja</span></a>
            </li>
            <li class="<?= $activeMenu === 'configuracion' ? 'active' : '' ?>">
                <a href="configuracion.php"><i class="fas fa-cog"></i><span>Configuracion</span></a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info-container">
            <div class="user-info">
                <i class="fas <?= $userRole === 'admin' ? 'fa-user-shield' : 'fa-user' ?>"></i>
                <div>
                    <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                    <span class="user-role"><?php echo ucfirst(htmlspecialchars($userRole)); ?></span>
                </div>
            </div>
        </div>
    </div>
</aside>
