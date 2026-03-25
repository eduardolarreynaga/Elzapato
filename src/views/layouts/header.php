<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lógica para cerrar sesión desde el menú desplegable
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

        if (!isset($_SESSION['usuario'])) {
            $_SESSION['usuario'] = $loginRole;
        }

        if (!isset($_SESSION['id_usuario'])) {
            $_SESSION['id_usuario'] = $loginRole === 'admin' ? 1 : 2;
        }
    }
}

$rol = strtolower((string) ($_SESSION['rol'] ?? $_SESSION['user_role'] ?? 'guest'));

$roleLabels = [
    'admin' => 'Administrador',
    'seller' => 'Vendedor',
];

$profilePaths = [
    'admin' => '/ElZapato/src/views/admin/perfil.php',
    'seller' => '/ElZapato/src/views/seller/perfil.php',
];

$panelConfig = [
    'admin' => [
        'label' => 'Dashboard',
        'href' => '/ElZapato/src/views/admin/dashboard.php',
        'icon' => 'fas fa-chart-line',
    ],
    'seller' => [
        'label' => 'Panel de ventas',
        'href' => '/ElZapato/src/views/seller/punto_venta.php',
        'icon' => 'fas fa-cash-register',
    ],
];

$displayRole = $roleLabels[$rol] ?? 'Usuario';
$profilePath = $profilePaths[$rol] ?? '/ElZapato/src/views/public/principal.php';
$panelLink = $panelConfig[$rol] ?? null;
$currentUrl = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/ElZapato/src/views/public/principal.php', ENT_QUOTES, 'UTF-8');
?>

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
            <li id="li-pos" style="display: none;">
                <a href="/ElZapato/src/views/seller/pos.php" class="nav-link">Punto de venta</a>
            </li>
            <li><a href="/ElZapato/src/views/public/punto_venta.php" class="nav-link">Contactos</a></li>
            <li><a href="/ElZapato/src/views/public/nosotros.php" class="nav-link">Nosotros</a></li>
            <li id="li-panel" style="display: none;">
            <?php if ($rol === 'admin' && $panelLink !== null): ?>
                <a class="nav-link" id="panel-link" href="<?php echo htmlspecialchars($panelLink['href'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($panelLink['label'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endif; ?>

            </li>
        </ul>

        <div class="header-utils">
            <a href="/ElZapato/src/views/public/index.php" id="boton-login" class="btn-waze"<?php echo $rol !== 'guest' ? ' style="display: none;"' : 'style="padding: 8px 0px;"'; ?>>Iniciar Sesión</a>
            
            <div id="user-info" class="nav-item dropdown user-dropdown"<?php echo $rol === 'guest' ? ' style="display: none;"' : ''; ?>>
                <button class="nav-link btn-waze dropdown-toggle user-dropdown-toggle" type="button" id="navbarDropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-user-circle mr-1"></i>
                    <span id="user-greeting"><?php echo htmlspecialchars($displayRole, ENT_QUOTES, 'UTF-8'); ?></span>
                </button>

                <div class="dropdown-menu dropdown-menu-right" id="userDropdownMenu" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item" id="profile-link" href="<?php echo htmlspecialchars($profilePath, ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="fas fa-user mr-2"></i>Mi Perfil
                    </a>



                    <div class="dropdown-divider"></div>

                        <button onclick="window.location.href='/ElZapato/src/views/public/logout.php'" class="dropdown-item text-danger logout-dropdown-btn" type="button">
                            <i class="fas fa-sign-out-alt mr-2"></i>Cerrar sesión
                        </button>
                </div>
            </div>
        </div>
    </nav>

    <script>
        const userRole = "<?php echo $rol; ?>";

        document.addEventListener("DOMContentLoaded", function() {
            const btnLogin = document.getElementById("boton-login");
            const userInfo = document.getElementById("user-info");
            const liPos = document.getElementById("li-pos");
            const liPanel = document.getElementById("li-panel");
            const dropdownToggle = document.getElementById("navbarDropdown");
            const dropdownMenu = document.getElementById("userDropdownMenu");

            if (userRole !== "guest") {
                if (btnLogin) btnLogin.style.display = "none";
                if (userInfo) userInfo.style.display = "block";

                if (userRole === "admin" || userRole === "seller") {
                    if (liPos) liPos.style.display = "block";
                }

                if (userRole === "admin") {
                    if (liPanel) liPanel.style.display = "block";
                }
            }

            if (!userInfo || !dropdownToggle || !dropdownMenu) {
                return;
            }

            const closeDropdown = function() {
                userInfo.classList.remove("open");
                dropdownMenu.classList.remove("show");
                dropdownToggle.setAttribute("aria-expanded", "false");
            };

            dropdownToggle.addEventListener("click", function(event) {
                event.preventDefault();

                const isOpen = dropdownMenu.classList.toggle("show");
                userInfo.classList.toggle("open", isOpen);
                dropdownToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
            });

            document.addEventListener("click", function(event) {
                if (!userInfo.contains(event.target)) {
                    closeDropdown();
                }
            });

            document.addEventListener("keydown", function(event) {
                if (event.key === "Escape") {
                    closeDropdown();
                }
            });
        });
    </script>
</header>