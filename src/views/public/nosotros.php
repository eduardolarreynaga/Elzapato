<?php
require_once __DIR__ . '/../../config/auth.php';
// Mantenemos tu lógica de compatibilidad de roles
$incomingRole = $_GET['login_success'] ?? $_GET['set_role'] ?? null;

if ($incomingRole !== null) {
    $role = strtolower(trim((string) $incomingRole));
    if (in_array($role, ['admin', 'seller'], true)) {
        $_SESSION['user_role'] = $role;
        $_SESSION['rol'] = $role;
        if (!isset($_SESSION['usuario'])) { $_SESSION['usuario'] = $role; }
        if (!isset($_SESSION['id_usuario'])) { $_SESSION['id_usuario'] = $role === 'admin' ? 1 : 2; }
    }
}

$nombreSistema = defined('SYSTEM_NAME') ? SYSTEM_NAME : 'ElZapato';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="<?php echo $nombreSistema; ?> - Calzado salvadoreño de calidad. Conoce nuestra historia, misión y valores.">
    <title>Nosotros | <?php echo $nombreSistema; ?></title>

    <link rel="stylesheet" href="/ElZapato/Assets/css/nosotros.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/ElZapato/Assets/css/components/dev-modal.css?v=20260423">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/ElZapato/src/views/layouts/header.php'; ?>

    <main class="about-page">
        <section class="about-hero">
            <div class="about-hero-overlay"></div>
            <div class="about-hero-particles"></div>

            <div class="about-hero-content">
                <span class="hero-kicker">Orgullo salvadoreño • Estilo • Calidad</span>
                <h1>Más que zapatos, <span>creamos experiencias</span></h1>
                <p>
                    En <strong><?php echo $nombreSistema; ?></strong> caminamos junto a cada cliente con una propuesta moderna,
                    cercana y auténtica, uniendo diseño, comodidad y el talento local.
                </p>

                <div class="about-hero-actions">
                    <a href="#nosotros" class="about-btn about-btn-primary">
                        <i class="fas fa-shoe-prints"></i>
                        Conocernos
                    </a>
                    <a href="#valores" class="about-btn about-btn-secondary">
                        <i class="fas fa-heart"></i>
                        Nuestros valores
                    </a>
                </div>
            </div>

            <div class="hero-glass-card">
                <div class="mini-badge">
                    <i class="fas fa-award"></i>
                    <span>Hecho con identidad</span>
                </div>
                <h3>Calzado con alma</h3>
                <p>Diseños pensados para el día a día, el trabajo, la escuela y cada momento importante.</p>
            </div>

            <div class="floating-shape shape-1"></div>
            <div class="floating-shape shape-2"></div>
            <div class="floating-icon-bg">
                <i class="fas fa-shoe-prints"></i>
            </div>
        </section>

        <section class="about-section" id="nosotros">
            <div class="about-content">
                <div class="section-top">
                    <span class="section-mini">Nuestra esencia</span>
                    <h2 class="section-title">Nosotros</h2>
                    <p class="section-subtitle">
                        Una zapatería salvadoreña que busca que cada paso tenga respaldo, estilo y confianza.
                    </p>
                </div>
                
                <div class="about-intro">
                    <article class="intro-card wide intro-person-1">
                        <div class="intro-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div>
                            <h3>Quiénes somos</h3>
                            <p>
                                En <strong><?php echo $nombreSistema; ?></strong> no solo vendemos zapatos, <strong>creamos experiencias</strong>
                                que te llevan a donde necesitas ir. Somos una zapatería salvadoreña con alma,
                                dedicada a calzar los sueños de empresas grandes, medianas y de cada salvadoreño
                                que busca calidad y estilo.
                            </p>
                        </div>
                    </article>

                    <article class="intro-card intro-person-2">
                        <div class="intro-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div>
                            <h3>Nuestro superpoder</h3>
                            <p>
                                Escuchamos tus necesidades hasta entender cada paso que das en <strong><?php echo $nombreSistema; ?></strong>.
                                Recomendamos el modelo ideal según tu actividad y buscamos que cada par
                                sea tu aliado perfecto.
                            </p>
                        </div>
                    </article>

                    <article class="intro-card intro-person-3">
                        <div class="intro-icon">
                            <i class="fas fa-handshake-angle"></i>
                        </div>
                        <div>
                            <h3>Compromiso local</h3>
                            <p>
                                Trabajamos con maestros zapateros salvadoreños que ponen corazón en cada puntada,
                                apostando por calidad, resistencia y apoyo al talento nacional.
                            </p>
                        </div>
                    </article>
                </div>

                <div class="about-points">
                    <div class="about-point">
                        <i class="fas fa-box-open"></i>
                        <span>+500 modelos de calzado 100% nacional</span>
                    </div>
                    <div class="about-point">
                        <i class="fas fa-users"></i>
                        <span>Atención cercana que te hace sentir en familia</span>
                    </div>
                    <div class="about-point">
                        <i class="fas fa-medal"></i>
                        <span>Compromiso real con calidad y confianza</span>
                    </div>
                </div>

                <div class="about-grid" id="valores">
                    <article class="about-card">
                        <div class="about-card-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3>Misión</h3>
                        <p>
                            Ofrecer calzado cómodo, duradero y con estilo, brindando una experiencia
                            de atención cercana y una solución real para cada necesidad del cliente.
                        </p>
                    </article>

                    <article class="about-card">
                        <div class="about-card-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3>Visión</h3>
                        <p>
                            Ser una zapatería referente en El Salvador por su calidad, identidad,
                            servicio humano e impulso al talento local.
                        </p>
                    </article>

                    <article class="about-card">
                        <div class="about-card-icon">
                            <i class="fas fa-gem"></i>
                        </div>
                        <h3>Valores</h3>
                        <p>
                            Calidad, honestidad, compromiso social, trabajo en equipo y orgullo por lo nuestro
                            en cada producto y en cada atención.
                        </p>
                    </article>
                </div>

                <div class="about-stats">
                    <div class="stat-item">
                        <span class="stat-number" id="yearsCount">0</span>
                        <span class="stat-label">Años calzando sueños</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="clientsCount">0</span>
                        <span class="stat-label">Clientes que confían</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="artisansCount">0</span>
                        <span class="stat-label">Artesanos locales</span>
                    </div>
                </div>

                <div class="about-closing">
                    <div class="closing-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <p class="closing-main">
                        En <?php echo $nombreSistema; ?> creemos que cada paso cuenta una historia.
                    </p>
                    <p class="closing-quote">
                        “Un buen par de zapatos no solo te lleva a donde quieres ir,
                        te recuerda de dónde vienes y quién te apoya en el camino.”
                    </p>
                </div>
            </div>
        </section>
    </main>

    <div class="floating-shoe" onclick="window.scrollTo({top: 0, behavior: 'smooth'});">
        <i class="fas fa-arrow-up"></i>
    </div>

    <footer class="about-footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo $nombreSistema; ?> - Sistema de Gestión Escolar UNICAES</p>
        <button type="button" onclick="openDevModal()" class="btn-devs">Desarrollado por</button>
    </footer>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/ElZapato/src/views/layouts/dev-team-modal.php'; ?>
    <script src="/ElZapato/Assets/js/dev-modal.js?v=20260423"></script>

    <script>
        // Mantengo tus funciones de animación JS intactas
        function animateNumber(element, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                element.textContent = Math.floor(progress * (end - start) + start);
                if (progress < 1) window.requestAnimationFrame(step);
            };
            window.requestAnimationFrame(step);
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const yearsEl = document.getElementById('yearsCount');
                    const clientsEl = document.getElementById('clientsCount');
                    const artisansEl = document.getElementById('artisansCount');

                    if (yearsEl && yearsEl.textContent === '0') {
                        animateNumber(yearsEl, 0, 12, 1800);
                        animateNumber(clientsEl, 0, 5847, 2300);
                        animateNumber(artisansEl, 0, 28, 1500);
                    }
                    observer.disconnect();
                }
            });
        }, { threshold: 0.35 });

        const statsSection = document.querySelector('.about-stats');
        if (statsSection) observer.observe(statsSection);

        const revealItems = document.querySelectorAll('.intro-card, .about-point, .about-card, .stat-item, .about-closing');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                }
            });
        }, { threshold: 0.16 });

        revealItems.forEach(item => revealObserver.observe(item));
    </script>
</body>
</html>