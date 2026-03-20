<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lógica para cerrar sesión si se presiona el botón
if (isset($_GET['logout'])) {
    session_destroy(); // Destruye la sesión en el servidor
    header("Location: /ElZapato/src/views/public/principal.php"); // Redirige al principal
    exit();
}

// Capturar el rol
if (isset($_GET['login_success'])) {
    $_SESSION['user_role'] = $_GET['login_success'];
}
$rol = $_SESSION['user_role'] ?? 'guest';
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
            <li id="li-pos" style="display: none;">
                <a href="/ElZapato/src/views/seller/pos.php" class="nav-link">Punto venta</a>
            </li>
            <li><a href="/ElZapato/src/views/seller/punto_venta.php" class="nav-link">Contactos</a></li>
        </ul>

        <div class="header-utils" style="display: flex; align-items: center; gap: 15px;">
            <a href="/ElZapato/src/views/public/index.php" id="boton-login">
                <button class="btn-waze">Iniciar Sesión</button>
            </a>
            
            <div id="user-info" style="display: none; align-items: center; gap: 10px;">
                <span id="user-greeting" style="font-weight: bold; color: #333;"></span>
                <a href="?logout=true" style="color: #d9534f; text-decoration: none; font-size: 0.9em; font-weight: bold; border: 1px solid #d9534f; padding: 2px 8px; border-radius: 4px;">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <script>
        const userRole = "<?php echo $rol; ?>";

        document.addEventListener("DOMContentLoaded", function() {
            const navMenu = document.querySelector(".nav-menu");
            const btnLogin = document.getElementById("boton-login");
            const userInfo = document.getElementById("user-info");
            const greeting = document.getElementById("user-greeting");
            const liPos = document.getElementById("li-pos");

            if (userRole !== "guest") {
                // 1. Ocultar Login y mostrar Info de Usuario
                if (btnLogin) btnLogin.style.display = "none";
                if (userInfo) userInfo.style.display = "flex";
                
                greeting.textContent = "Hola " + userRole.charAt(0).toUpperCase() + userRole.slice(1);

                // 2. Opciones según Rol
                if (userRole === "admin") {
                    if (liPos) liPos.style.display = "block";
                    
                    const adminLi = document.createElement("li");
                    adminLi.innerHTML = '<a href="/ElZapato/src/views/admin/dashboard.php" class="nav-link" style="color: #e67e22; font-weight: bold;">Dashboard Admin</a>';
                    navMenu.appendChild(adminLi);
                } else if (userRole === "seller") {
                    if (liPos) liPos.style.display = "block";
                }
            }
        });
    </script>
</header>