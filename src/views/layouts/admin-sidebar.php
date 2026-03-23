<?php
$activeMenu = $activeMenu ?? '';
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
                <a href="/ElZapato/index.php"><i class="fas fa-home"></i><span>Pagina principal</span></a>
            </li>
            <li class="<?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            </li>
            <li class="<?= $activeMenu === 'productos' ? 'active' : '' ?>">
                <a href="productos.php"><i class="fas fa-box"></i><span>Productos</span></a>
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
            <li class="separator logout-separator"></li>
            <li class="logout-item">
                <a href="/ElZapato/src/views/public/principal.php"><i class="fas fa-sign-out-alt"></i><span>Salir</span></a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <div>
                <span class="user-name">Admin</span>
                <span class="user-role">Administrador</span>
            </div>
        </div>
    </div>
</aside>
