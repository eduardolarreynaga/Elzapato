<?php
session_start();
// Si viene el rol por la URL, lo guardamos en la sesión (compatibilidad)
$incomingRole = $_GET['login_success'] ?? $_GET['set_role'] ?? null;

if ($incomingRole !== null) {
    $role = strtolower(trim((string) $incomingRole));

    if (in_array($role, ['admin', 'seller'], true)) {
        $_SESSION['user_role'] = $role;
        $_SESSION['rol'] = $role;

        if (!isset($_SESSION['usuario'])) {
            $_SESSION['usuario'] = $role;
        }

        if (!isset($_SESSION['id_usuario'])) {
            $_SESSION['id_usuario'] = $role === 'admin' ? 1 : 2;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nosotros | ElZapato</title>

    <link rel="stylesheet" href="/ElZapato/Assets/css/principal.css?v=20260323">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/ElZapato/src/views/layouts/header.php'; ?>

    <section class="about-section" id="nosotros">
        <div class="about-content">
            <h2 class="section-title">Nosotros</h2>
            <p>
                En <strong>ElZapato</strong> somos una zapatería salvadoreña dedicada a la venta de calzado
                nacional para empresas grandes y medianas.
            </p>
            <p>
                Nuestro enfoque está en el usuario: escuchamos sus necesidades, recomendamos el modelo ideal
                según su actividad y garantizamos una experiencia de compra cercana, rápida y confiable.
            </p>
            <p>
                Trabajamos con proveedores locales para ofrecer zapatos cómodos, resistentes y con estilos
                para uso diario, laboral, escolar y formal, impulsando también el crecimiento de la industria
                del calzado en El Salvador.
            </p>

            <div class="about-points">
                <div class="about-point"><i class="fas fa-shoe-prints"></i>Amplio catálogo de calzado nacional</div>
                <div class="about-point"><i class="fas fa-users"></i>Atención personalizada para cada cliente</div>
                <div class="about-point"><i class="fas fa-handshake"></i>Compromiso real con calidad y servicio</div>
            </div>

            <div class="about-grid">
                <article class="about-card">
                    <h3>Misión</h3>
                    <p>
                        Brindar calzado salvadoreño confiable, accesible y de calidad, enfocado en la comodidad,
                        seguridad y satisfacción de cada usuario y empresa que confía en nosotros.
                    </p>
                </article>
                <article class="about-card">
                    <h3>Visión</h3>
                    <p>
                        Ser la zapatería referente en El Salvador por nuestro compromiso con la calidad,
                        la atención al cliente y la mejora continua en nuestras soluciones de calzado.
                    </p>
                </article>
                <article class="about-card">
                    <h3>Valores</h3>
                    <p>
                        Cercanía con el cliente, calidad en cada producto, responsabilidad, honestidad,
                        trabajo en equipo y apoyo constante a la producción salvadoreña.
                    </p>
                </article>
            </div>

            <p class="about-closing">
                En ElZapato creemos que un buen par de zapatos mejora el día a día de las personas.
                Por eso, cada venta representa nuestro compromiso de ofrecer confianza en cada paso.
            </p>
        </div>
    </section>

    <footer style="background: var(--texto-negro); color: var(--bg-claro); text-align: center; padding: 20px; margin-top: 50px;">
        <p>&copy; 2026 ElZapato - Sistema de Gestión Escolar UNICAES</p>
    </footer>

</body>
</html>
