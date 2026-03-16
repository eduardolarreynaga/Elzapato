<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zapatería POS - Iniciar Sesión</title>
    
    <!-- Font Awesome para iconos (opcional, para mejorar el diseño) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Fuente Roboto -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Tu archivo CSS principal -->
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="icon" type="image/x-icon" href="/zapallo/Assets/img/zapa.jpeg">
</head>
<body>
    <div class="auth-wrapper">

        <!-- Panel izquierdo: Marca -->
        <div class="auth-brand">
            <img src="/zapallo/Assets/img/logo1.jpg" alt="Logo ElZapato" class="brand-logo">
            <h1>ElZapato</h1>
            <p>Sistema de punto de venta para calzado</p>
            <span class="brand-badge">POS &middot; v0.1</span>
        </div>

        <!-- Panel derecho: Formulario -->
        <div class="auth-card">
            <div class="auth-header">
                <h2>Bienvenido</h2>
                <p>Ingresa tus credenciales para continuar</p>
            </div>

            <div class="error-message" id="error-msg">
                <i class="fas fa-exclamation-circle"></i>
                <span id="error-text">Usuario o contraseña incorrectos.</span>
            </div>

            <form id="login-form" action="" method="post" autocomplete="off">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user field-icon"></i>
                        <input type="text" class="form-control" id="username" name="username"
                               placeholder="Ingresa tu usuario" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Ingresa tu contraseña" required>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="btn-submit">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>

                <div class="auth-footer">
                    <a href="#" class="auth-link">¿Olvidaste tu contraseña?</a>
                    <a href="#" class="auth-link">Registrarse</a>
                </div>
            </form>
        </div>

    </div>

    <script src="assets/js/login.js"></script>
       
</body>
</html>