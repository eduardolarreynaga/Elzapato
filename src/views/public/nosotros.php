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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="ElZapato - Calzado salvadoreño de calidad. Conoce nuestra historia, misión y valores.">
    <title>Nosotros | ElZapato</title>

    <!-- CSS Principal -->
    <link rel="stylesheet" href="/ElZapato/assets/css/principal.css?v=20260323">
    <!-- CSS específico para la página Nosotros (carpeta pages) -->
    <link rel="stylesheet" href="/ElZapato/assets/css/pages/nosotros.css?v=20260325">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/ElZapato/src/views/layouts/header.php'; ?>

    <main>
        <section class="about-section" id="nosotros">
            <div class="about-content">
                <h2 class="section-title">Nosotros</h2>
                
                <div class="about-intro">
                    <p>
                        <i class="fas fa-crown"></i> 
                        En <strong>ElZapato</strong> no solo vendemos zapatos, <strong>creamos experiencias</strong> 
                        que te llevan a donde necesitas ir. Somos una zapatería salvadoreña con alma, 
                        dedicada a calzar los sueños de empresas grandes, medianas y de cada salvadoreño 
                        que busca calidad y estilo.
                    </p>
                    <p>
                        🎯 <strong>Nuestro superpoder:</strong> escuchamos tus necesidades hasta entender 
                        cada paso que das. Recomendamos el modelo ideal según tu actividad y garantizamos 
                        que cada par que elijas sea <strong>tu aliado perfecto</strong> en el día a día.
                    </p>
                    <p>
                        🤝 Trabajamos con <strong>maestros zapateros salvadoreños</strong> que ponen 
                        corazón en cada puntada. Calzado cómodo, resistente y con estilo para uso diario, 
                        laboral, escolar y formal. Porque cuando apoyas lo nuestro, <strong>todos ganamos</strong>.
                    </p>
                </div>

                <div class="about-points">
                    <div class="about-point">
                        <i class="fas fa-shoe-prints"></i>
                        <span>📦 +500 modelos de calzado 100% nacional</span>
                    </div>
                    <div class="about-point">
                        <i class="fas fa-users"></i>
                        <span>💚 Atención que te hace sentir en familia</span>
                    </div>
                    <div class="about-point">
                        <i class="fas fa-handshake"></i>
                        <span>🏆 Compromiso real: calidad que se nota</span>
                    </div>
                </div>

                <div class="about-grid">
                    <article class="about-card">
                        <i class="fas fa-bullseye"></i>
                        <h3>Misión</h3>
                        <p>
                            Calzar a El Salvador con orgullo, ofreciendo calzado nacional de calidad 
                            que combine comodidad, seguridad y estilo, mientras creamos relaciones 
                            duraderas con cada cliente que confía en nosotros.
                        </p>
                    </article>
                    
                    <article class="about-card">
                        <i class="fas fa-eye"></i>
                        <h3>Visión</h3>
                        <p>
                            Ser el corazón de la industria del calzado en Centroamérica, donde cada 
                            salvadoreño se sienta orgulloso de usar zapatos hechos en casa, con 
                            estándares internacionales de calidad.
                        </p>
                    </article>
                    
                    <article class="about-card">
                        <i class="fas fa-gem"></i>
                        <h3>Valores</h3>
                        <p>
                            ✨ Pasión por el calzado · 🤝 Honestidad radical · 🚀 Innovación constante · 
                            💚 Compromiso social · 👥 Trabajo en equipo · 🌱 Apoyo al talento local
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
                    <p>
                        <i class="fas fa-heart"></i> 
                        En ElZapato creemos que cada paso cuenta una historia
                    </p>
                    <p>
                        "Un buen par de zapatos no solo te lleva a donde quieres ir, 
                        te recuerda de dónde vienes y quién te apoya en el camino."
                    </p>
                  
                </div>
            </div>
        </section>
    </main>

    <!-- Botón flotante para volver arriba -->
    <div class="floating-shoe" onclick="window.scrollTo({top: 0, behavior: 'smooth'});">
        <i class="fas fa-arrow-up"></i>
    </div>

       <footer style="background: var(--texto-negro); color: var(--bg-claro); text-align: center; padding: 20px; margin-top: 50px;">
        <p>&copy; 2026 ElZapato - Sistema de Gestión Escolar UNICAES</p>
    </footer>

    <script>
        // Animación de conteo para estadísticas
        function animateNumber(element, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                element.textContent = Math.floor(progress * (end - start) + start);
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }
        
        // Intersection Observer para animar números al hacer scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const yearsEl = document.getElementById('yearsCount');
                    const clientsEl = document.getElementById('clientsCount');
                    const artisansEl = document.getElementById('artisansCount');
                    
                    if (yearsEl && yearsEl.textContent === '0') {
                        animateNumber(yearsEl, 0, 12, 2000);
                        animateNumber(clientsEl, 0, 5847, 2500);
                        animateNumber(artisansEl, 0, 28, 1500);
                    }
                    
                    observer.disconnect();
                }
            });
        }, { threshold: 0.3 });
        
        const statsSection = document.querySelector('.about-stats');
        if (statsSection) {
            observer.observe(statsSection);
        }
        
        // Mensaje de bienvenida en consola
        console.log('%c👞 ElZapato - Calzado con orgullo salvadoreño 👞', 'color: #AB886D; font-size: 16px; font-weight: bold;');
    </script>

</body>
</html>