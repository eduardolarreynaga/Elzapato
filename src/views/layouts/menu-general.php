<?php
require_once __DIR__ . '/../../config/auth.php';

if (!is_authenticated()) {
    header("Location: /ElZapato/src/views/public/login.php");
    exit();
}

// --- NUEVO: Obtener nombre dinámico ---
$nombreSistema = defined('SYSTEM_NAME') ? SYSTEM_NAME : 'ElZapato';

$nombreUsuario = $_SESSION['usuario'] ?? 'Usuario';
$rolUsuario    = $_SESSION['rol']     ?? 'Cajero';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nombreSistema; ?> - Panel</title>
    <link rel="stylesheet" href="/ElZapato/Assets/css/layout/menu-general.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <div class="background-media" aria-hidden="true">
        <video class="background-video" autoplay muted loop playsinline>
            <source src="/ElZapato/Assets/video/model_edit.mp4" type="video/mp4">
        </video>
        <div class="background-overlay"></div>
    </div>

    <nav class="top-bar">
        <div class="brand">
            <span class="dot"></span> <?php echo $nombreSistema; ?>
        </div>
        <div class="user-section">
            <div onclick="abrirModalPerfil()" class="user-info-clickable">
                <div class="user-info">
                    <i class="fa-solid fa-circle-user"></i>
                    <div class="user-text">
                        <span class="username"><?php echo htmlspecialchars($nombreUsuario); ?></span>
                        <span class="role"><?php echo strtoupper(htmlspecialchars($rolUsuario)); ?></span>
                    </div>
                </div>
            </div>
            
            <a href="javascript:void(0)" onclick="confirmarSalida()" class="btn-exit">
                <i class="fa-solid fa-right-from-bracket"></i> Salir
            </a>
        </div>
    </nav>

    <div class="main-container">
        <header class="header">
            <h1>BIENVENIDO, <?php echo strtoupper(htmlspecialchars($nombreUsuario)); ?></h1>
            <p>Panel de administración — Selecciona una opción</p>
        </header>

        <div class="grid-container">
            <a href="/ElZapato/src/views/seller/pos.php" class="card">
                <i class="fa-solid fa-cart-shopping"></i>
                <h3>PUNTO DE VENTA</h3>
                <p>Registrar ventas</p>
            </a>

            <a href="/ElZapato/src/views/public/principal.php" class="card">
                <i class="fa-solid fa-globe"></i>
                <h3>PÁGINA WEB</h3>
                <p>Presentación del negocio</p>
            </a>

            <?php if (strtolower($rolUsuario) === 'admin'): ?>
                <a href="/ElZapato/src/views/admin/ventas.php" class="card">
                    <i class="fa-solid fa-tag"></i>
                    <h3>VENTAS</h3>
                    <p>Historial de ventas</p>
                </a>
                <a href="/ElZapato/src/views/admin/productos.php" class="card">
                    <i class="fa-solid fa-box-archive"></i>
                    <h3>INVENTARIO</h3>
                    <p>Productos y stock</p>
                </a>
                <a href="/ElZapato/src/views/admin/dashboard.php" class="card">
                    <i class="fa-solid fa-chart-pie"></i>
                    <h3>ESTADÍSTICAS</h3>
                    <p>Reportes y análisis</p>
                </a>
                <a href="/ElZapato/src/views/admin/empleados.php" class="card">
                    <i class="fa-solid fa-users"></i>
                    <h3>EMPLEADOS</h3>
                    <p>Gestión de empleados</p>
                </a>
                <a href="/ElZapato/src/views/admin/configuracion.php" class="card">
                    <i class="fa-solid fa-gear"></i>
                    <h3>AJUSTES</h3>
                    <p>Configuración</p>
                </a>
                <a href="/ElZapato/src/views/admin/proveedores.php" class="card">
                    <i class="fa-solid fa-truck-moving"></i>
                    <h3>PROVEEDORES</h3>
                    <p>Gestión de suministros</p>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div id="modalLogout" class="modal-overlay">
        <div class="modal-exit-card">
            <div class="modal-icon">
                <i class="fa-solid fa-right-from-bracket"></i>
            </div>
            <h3>¿Cerrar Sesión?</h3>
            <p>Estás a punto de salir del sistema de <?php echo $nombreSistema; ?>. ¿Deseas continuar?</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="cerrarModalLogout()">CANCELAR</button>
                <a href="/ElZapato/src/views/public/logout.php" class="btn-confirm">SÍ, SALIR</a>
            </div>
        </div>
    </div>

    <div id="modalPerfil" class="modal-overlay">
        <div class="modal-perfil-container">
            <button class="modal-close-btn" onclick="cerrarModalPerfil()">&times;</button>
            <iframe src="/ElZapato/src/views/seller/perfil.php" frameborder="0"></iframe>
        </div>
    </div>

    <div id="pageTransition" class="page-transition" aria-hidden="true">
        <div class="page-transition-loader"></div>
    </div>

    <script>
        function confirmarSalida() {
            document.getElementById('modalLogout').classList.add('active');
        }

        function cerrarModalLogout() {
            document.getElementById('modalLogout').classList.remove('active');
        }

        function abrirModalPerfil() {
            document.getElementById('modalPerfil').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function cerrarModalPerfil() {
            document.getElementById('modalPerfil').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                cerrarModalLogout();
                cerrarModalPerfil();
            }
        }

        const pageTransition = document.getElementById('pageTransition');

        function navigateWithTransition(targetUrl) {
            if (!pageTransition || !targetUrl) {
                window.location.href = targetUrl;
                return;
            }

            pageTransition.classList.add('active');
            setTimeout(() => {
                window.location.href = targetUrl;
            }, 420);
        }

        document.querySelectorAll('a[href]').forEach((link) => {
            const href = link.getAttribute('href') || '';
            if (!href || href.startsWith('javascript:') || href.startsWith('#')) return;

            link.addEventListener('click', function(event) {
                if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || link.target === '_blank') {
                    return;
                }
                event.preventDefault();
                navigateWithTransition(this.getAttribute('href'));
            });
        });
    </script>
</body>
</html>