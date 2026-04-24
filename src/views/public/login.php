<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zapatería POS - Iniciar Sesión</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ElZapato/Assets/css/login.css">
    <link rel="icon" type="image/jpeg" href="/ElZapato/Assets/img/zapa.jpeg">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-brand">
            <img src="/ElZapato/Assets/img/logo.png" alt="Logo" class="brand-logo">
            <h1>ElZapato</h1>
            <p>Sistema de punto de venta para calzado</p>
            <span class="brand-badge">POS &middot; v0.1</span>
        </div>

        <div class="auth-card">
            <div class="auth-header">
                <h2>Bienvenido</h2>
                <p>Ingresa tus credenciales para continuar</p>
            </div>

            <div class="error-message" id="error-msg">
                <i class="fas fa-exclamation-circle"></i>
                <span id="error-text"></span>
            </div>

            <form id="login-form" autocomplete="off">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user field-icon"></i>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Usuario" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                    </div>
                </div>
            
                <button type="submit" class="btn-login" id="btn-submit">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </form>
        </div>
    </div>

    <script>
        const form = document.getElementById('login-form');
        const btn = document.getElementById('btn-submit');
        const errBox = document.getElementById('error-msg');
        const errText = document.getElementById('error-text');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            errBox.classList.remove('show');
            btn.disabled = true;
            btn.innerHTML = 'Validando...';

            try {
                const formData = new FormData(form);
                const response = await fetch('/ElZapato/controller/LoginController.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) throw new Error('Error al conectar con el servidor');

                const data = await response.json();

                if (data.success) {
                    window.location.href = '../layouts/menu-general.php';
                } else {
                    errText.textContent = data.message;
                    errBox.classList.add('show');
                }
            } catch (error) {
                errText.textContent = "Error: " + error.message;
                errBox.classList.add('show');
            } finally {
                btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Iniciar Sesión';
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>