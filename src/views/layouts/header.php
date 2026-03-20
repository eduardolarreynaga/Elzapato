<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Capturar el rol si venimos del login a través de la URL
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
                <a href="/ElZapato/src/views/seller/pos.php" id="pos" class="nav-link">Punto venta</a>
            </li>
            <li><a href="/ElZapato/src/views/seller/punto_venta.php" class="nav-link">Contactos</a></li>
        </ul>

        <div class="header-utils">
            <a href="/ElZapato/src/views/public/index.php" id="boton-login">
                <button class="btn-waze">Iniciar Sesión</button>
            </a>
            
            <span id="user-greeting" style="display: none; font-weight: bold; color: #333; margin-left: 10px;"></span>
        </div>
    </nav>

    <script>
        // Pasamos el rol de PHP a JS
        const userRole = "<?php echo $rol; ?>";

        document.addEventListener("DOMContentLoaded", function() {
            const navMenu = document.querySelector(".nav-menu");
            const btnLogin = document.getElementById("boton-login");
            const greeting = document.getElementById("user-greeting");
            const liPos = document.getElementById("li-pos");

            if (userRole !== "guest") {
                // 1. Ocultamos el botón de login y mostramos el saludo
                if (btnLogin) btnLogin.style.display = "none";
                
                if (greeting) {
                    greeting.style.display = "inline-block";
                    greeting.textContent = "Hola " + userRole.charAt(0).toUpperCase() + userRole.slice(1);
                }

                // 2. Lógica de visibilidad por Rol
                if (userRole === "admin") {
                    // Admin ve POS y Dashboard
                    if (liPos) liPos.style.display = "block";
                    
                    const adminLi = document.createElement("li");
                    adminLi.innerHTML = '<a href="/ElZapato/src/views/admin/dashboard.php" class="nav-link" style="color: #e67e22; font-weight: bold;">Dashboard Admin</a>';
                    navMenu.appendChild(adminLi);

                } else if (userRole === "seller") {
                    // Seller solo ve el POS
                    if (liPos) liPos.style.display = "block";
                }
            }
        });
    </script>
</header>