<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lógica para cerrar sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: /ElZapato/src/views/public/principal.php");
    exit();
}

// Capturar el rol
if (isset($_GET['login_success'])) {
    $loginRole = strtolower(trim((string) $_GET['login_success']));
    if (in_array($loginRole, ['admin', 'seller'], true)) {
        $_SESSION['user_role'] = $loginRole;
        $_SESSION['rol'] = $loginRole;
        if (!isset($_SESSION['usuario'])) $_SESSION['usuario'] = $loginRole;
        if (!isset($_SESSION['id_usuario'])) $_SESSION['id_usuario'] = $loginRole === 'admin' ? 1 : 2;
    }
}

$rol = strtolower((string) ($_SESSION['rol'] ?? $_SESSION['user_role'] ?? 'guest'));

$roleLabels = ['admin' => 'Administrador', 'seller' => 'Vendedor'];
$profilePaths = [
    'admin' => '/ElZapato/src/views/admin/perfil.php',
    'seller' => '/ElZapato/src/views/seller/perfil.php',
];

$panelConfig = [
    'admin' => ['label' => 'Dashboard', 'href' => '/ElZapato/src/views/admin/dashboard.php'],
    'seller' => ['label' => 'Panel de ventas', 'href' => '/ElZapato/src/views/seller/punto_venta.php'],
];

$displayRole = $roleLabels[$rol] ?? 'Usuario';
$profilePath = $profilePaths[$rol] ?? '/ElZapato/src/views/public/principal.php';
$panelLink = $panelConfig[$rol] ?? null;
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="/ElZapato/Assets/css/header.css">

<header class="main-header">
    <nav class="nav-container">
        <div class="logo">
            <a href="/ElZapato/src/views/public/principal.php">
                <img src="/ElZapato/Assets/img/logo.png" alt="Logo" style="height: 45px;">
            </a>
        </div>

        <ul class="nav-menu">
            <li><a href="/ElZapato/src/views/public/principal.php" class="nav-link">Inicio</a></li>
            <li><a href="/ElZapato/src/views/public/punto_venta.php" class="nav-link">Contactos</a></li>
            <li><a href="/ElZapato/src/views/public/nosotros.php" class="nav-link">Nosotros</a></li>
            <li id="li-panel" style="display: none;">
                <?php if ($rol === 'admin' && $panelLink !== null): ?>
                    <a class="nav-link" href="<?php echo htmlspecialchars($panelLink['href'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($panelLink['label'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endif; ?>
            </li>
        </ul>

        <div class="header-utils">
            <a href="/ElZapato/src/views/seller/pos.php" id="li-pos" class="pos-icon-btn" title="Punto de Venta">
                <i class="fas fa-cash-register"></i>
            </a>

            <a href="/ElZapato/src/views/public/index.php" id="boton-login" class="btn-waze" <?php echo $rol !== 'guest' ? 'style="display: none;"' : 'style="padding: 8px 0px;"'; ?>>
                Iniciar Sesión
            </a>
            
            <div id="user-info" class="nav-item dropdown user-dropdown" <?php echo $rol === 'guest' ? 'style="display: none;"' : ''; ?>>
                <button class="nav-link btn-waze dropdown-toggle user-dropdown-toggle" type="button" id="navbarDropdown">
                    <i class="fas fa-user-circle mr-1"></i>
                    <span id="user-greeting"><?php echo htmlspecialchars($displayRole, ENT_QUOTES, 'UTF-8'); ?></span>
                </button>

                <div class="dropdown-menu dropdown-menu-right" id="userDropdownMenu">
                    <a class="dropdown-item" href="<?php echo htmlspecialchars($profilePath, ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="fas fa-user mr-2"></i>Mi Perfil
                    </a>
                    <div class="dropdown-divider"></div>
                    <button onclick="window.location.href='/ElZapato/src/views/public/logout.php'" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt mr-2"></i>Cerrar sesión
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

   <div id="loader-wrapper">
        <div class="loader-content">
            <img 
                id="loader-gif" 
                src="/ElZapato/Assets/img/animaciones/caminado.gif" 
                style="width: 160px; height: 120px; display: block; margin: 0 auto;" 
                alt="Cargando...">
        </div>
    </div>

    <script>
        const userRole = "<?php echo $rol; ?>";

        // Bloqueo inmediato del scroll al empezar a leer el script
        document.body.style.overflow = "hidden";

        document.addEventListener("DOMContentLoaded", function() {
            const liPos = document.getElementById("li-pos");
            const liPanel = document.getElementById("li-panel");
            const userInfo = document.getElementById("user-info");
            const dropdownToggle = document.getElementById("navbarDropdown");
            const dropdownMenu = document.getElementById("userDropdownMenu");
            const loader = document.getElementById("loader-wrapper");

            // 1. VISIBILIDAD SEGÚN ROL
            if (userRole !== "guest") {
                if (userRole === "admin" || userRole === "seller") {
                    if (liPos) liPos.style.display = "flex"; 
                }
                if (userRole === "admin" && liPanel) {
                    liPanel.style.display = "block";
                }
            }

            // 2. LÓGICA DEL DROPDOWN
            if (dropdownToggle && dropdownMenu) {
                dropdownToggle.addEventListener("click", function(e) {
                    e.preventDefault();
                    const isShowing = dropdownMenu.classList.toggle("show");
                    userInfo.classList.toggle("open", isShowing);
                });

                document.addEventListener("click", function(e) {
                    if (userInfo && !userInfo.contains(e.target)) {
                        dropdownMenu.classList.remove("show");
                        userInfo.classList.remove("open");
                    }
                });
            }

            // 3. OCULTAR LOADER (Lógica para Lottie JSON)
            // Usamos un tiempo suficiente para que MacPato se vea nítido
            setTimeout(() => {
                if(loader) {
                    loader.style.opacity = "0"; // Inicia desvanecimiento suave
                    setTimeout(() => {
                        loader.style.display = "none"; // Quita de la vista
                        document.body.style.overflow = "auto"; // Habilita scroll
                    }, 500); // Duración de la transición
                }
            }, 1200); // Tiempo que permanece MacPato en pantalla
        });

        // 4. MOSTRAR LOADER AL NAVEGAR (Para que no haya pantallas blancas)
        document.addEventListener("click", function(e) {
            const link = e.target.closest("a");
            
            if (link && 
                link.href.includes(window.location.origin) && 
                !link.hash && 
                link.target !== "_blank" &&
                !link.classList.contains('logout-dropdown-btn')) {
                
                const loader = document.getElementById("loader-wrapper");
                if (loader) {
                    document.body.style.overflow = "hidden";
                    loader.style.display = "flex";
                    // Pequeño delay para que el navegador procese el display:flex antes del opacity
                    setTimeout(() => { loader.style.opacity = "1"; }, 10);
                }
            }
        });
    </script>
</header>   