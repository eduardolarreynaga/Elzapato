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
$rolUsuario    = $_SESSION['rol'] ?? 'Cajero';
$idUsuario     = $_SESSION['id_usuario'] ?? 'No disponible';

// Intentar obtener email de la sesión o establecer valor por defecto
$email = $_SESSION['email'] ?? $_SESSION['correo'] ?? 'No registrado';

// Obtener fecha y hora actual
$fechaActual = date('d/m/Y');
$horaActual = date('H:i:s');
$fechaHoraCompleta = date('d/m/Y H:i:s');

// Obtener información adicional
$ip = $_SERVER['REMOTE_ADDR'] ?? 'No disponible';
$navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'No disponible';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - ElZapato</title>
    <link rel="stylesheet" href="/ElZapato/Assets/css/perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="perfil-container">
        <div class="perfil-card">
            <div class="perfil-header">
                <div class="perfil-avatar">
                    <i class="fa-solid fa-circle-user"></i>
                </div>
                <h1><?php echo htmlspecialchars($nombreUsuario); ?></h1>
                <div class="role-badge">
                    <i class="fa-solid fa-tag"></i> <?php echo strtoupper(htmlspecialchars($rolUsuario)); ?>
                </div>
            </div>

            <div class="perfil-body">
                <div class="seccion-info">
                    <h3><i class="fa-solid fa-user"></i> Información Personal</h3>
                    <div class="info-grupo">
                        <div class="info-etiqueta">Usuario:</div>
                        <div class="info-valor"><?php echo htmlspecialchars($nombreUsuario); ?></div>
                    </div>
                    <div class="info-grupo">
                        <div class="info-etiqueta">Rol:</div>
                        <div class="info-valor"><?php echo htmlspecialchars($rolUsuario); ?></div>
                    </div>
                </div>

                <div class="seccion-info">
                    <h3><i class="fa-solid fa-clock"></i> Información de Sesión</h3>
                    <div class="info-grupo">
                        <div class="info-etiqueta">Fecha:</div>
                        <div class="info-valor"><?php echo $fechaActual; ?></div>
                    </div>
                    <div class="info-grupo">
                        <div class="info-etiqueta">Hora:</div>
                        <div class="info-valor"><?php echo $horaActual; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function cerrarModal() {
            if (window.parent && window.parent.cerrarModalPerfil) {
                window.parent.cerrarModalPerfil();
            }
        }
    </script>
</body>
</html>