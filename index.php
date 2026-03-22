<?php
// Iniciamos sesión al principio para evitar errores de cabeceras
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "controller/UsuariosController.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zapatería POS - Iniciar Sesión</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com">
    <link href="https://fonts.googleapis.com" rel="stylesheet">
    <link rel="stylesheet" href="/ElZapato/Assets/css/login.css">
    <link rel="icon" type="image/x-icon" href="/ElZapato/Assets/img/logo.png">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-brand">
            <img src="/ElZapato/Assets/img/logo.png" alt="Logo ElZapato" class="brand-logo">
            <h1>ElZapato</h1>
            <p>Sistema de punto de venta para calzado</p>
            <span class="brand-badge">POS &middot; v0.1</span>
        </div>

        <div class="auth-card">
            <div class="auth-header">
                <h2>Bienvenido</h2>
                <p>Ingresa tus credenciales para continuar</p>
            </div>

            <?php
            // Ejecutamos el login UNA SOLA VEZ aquí arriba
            $login = new UsuariosController();
            $resultado = $login->login();

            if($resultado == "error"): 
            ?>
                <div class="error-message show" style="display: flex; background-color: #fee2e2; color: #dc2626; padding: 10px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #fecaca;">
                    <i class="fas fa-exclamation-circle" style="margin-right: 10px; margin-top: 3px;"></i>
                    <span>Usuario o contraseña incorrectos.</span>
                </div>
            <?php endif; ?>

            <form id="login-form" method="post" autocomplete="off">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user field-icon"></i>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Ingresa tu usuario" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="btn-submit">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </form>
        </div>
    </div>
</body>
</html>
