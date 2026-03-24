<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('seller');

$_SESSION['user_role'] = $_SESSION['rol'] ?? $_SESSION['user_role'] ?? 'seller';

$username = $_SESSION['usuario'] ?? 'Vendedor';
$email = $_SESSION['email'] ?? 'No disponible';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | Vendedor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f5f2;
            color: #1f1f1f;
        }

        .profile-page {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .profile-card {
            background: #fff;
            border-radius: 18px;
            padding: 32px;
            box-shadow: 0 14px 35px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(214, 192, 179, 0.7);
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 28px;
        }

        .profile-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #E4E0E1;
            color: #AB886D;
            font-size: 2rem;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        .info-box {
            background: #faf7f4;
            border-radius: 12px;
            padding: 18px;
            border: 1px solid rgba(214, 192, 179, 0.65);
        }

        .info-box span {
            display: block;
            color: #7b6a60;
            font-size: 0.88rem;
            margin-bottom: 6px;
        }

        .profile-actions {
            margin-top: 24px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .profile-actions a {
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 10px;
            font-weight: 600;
        }

        .btn-primary {
            background: #AB886D;
            color: #fff;
        }

        .btn-secondary {
            background: #E4E0E1;
            color: #1f1f1f;
        }
    </style>
</head>
<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/ElZapato/src/views/layouts/header.php'; ?>

    <main class="profile-page">
        <section class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div>
                    <h1>Mi Perfil</h1>
                    <p>Información general del vendedor.</p>
                </div>
            </div>

            <div class="profile-grid">
                <div class="info-box">
                    <span>Usuario</span>
                    <strong><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="info-box">
                    <span>Rol</span>
                    <strong>Vendedor</strong>
                </div>
                <div class="info-box">
                    <span>Correo</span>
                    <strong><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
            </div>

            <div class="profile-actions">
                <a class="btn-primary" href="/ElZapato/src/views/seller/punto_venta.php">Ir al panel</a>
                <a class="btn-secondary" href="/ElZapato/src/views/public/principal.php">Volver al inicio</a>
            </div>
        </section>
    </main>
</body>
</html>