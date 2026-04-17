<?php
// 1. Incluir auth.php para manejar la sesión
require_once __DIR__ . '/../../config/auth.php';

// 2. Seguridad: Si no hay sesión iniciada, redirigir al login
if (!is_authenticated()) {
    header("Location: /ElZapato/src/views/public/login.php");
    exit();
}

// 3. Capturar datos de la sesión
$nombreUsuario = $_SESSION['usuario'] ?? 'Usuario';
$rolUsuario    = $_SESSION['rol']     ?? 'Cajero'; // Por defecto cajero si no hay rol
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ElZapato - Panel</title>
    <link rel="stylesheet" href="/ElZapato/Assets/css/layout/menu-general.css?v=20260417b">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <nav class="top-bar">
        <div class="brand">
            <span class="dot"></span> ElZapato
        </div>
        <div class="user-section">
            <div class="user-info">
                <i class="fa-solid fa-circle-user"></i>
                <div class="user-text">
                    <span class="username"><?php echo htmlspecialchars($nombreUsuario); ?></span>
                    <span class="role"><?php echo strtoupper(htmlspecialchars($rolUsuario)); ?></span>
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
            <p>Estás a punto de salir del sistema de El Zapato. ¿Deseas continuar?</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="cerrarModalLogout()">CANCELAR</button>
                <a href="/ElZapato/src/views/public/logout.php" class="btn-confirm">SÍ, SALIR</a>
            </div>
        </div>
    </div>

    <script>
        function confirmarSalida() {
            document.getElementById('modalLogout').classList.add('active');
        }

        function cerrarModalLogout() {
            document.getElementById('modalLogout').classList.remove('active');
        }

        // Cerrar si hacen clic fuera de la tarjeta
        window.onclick = function(event) {
            let modal = document.getElementById('modalLogout');
            if (event.target == modal) {
                cerrarModalLogout();
            }
        }
    </script>
</body>
</html>

</body>
</html>