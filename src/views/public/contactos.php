<?php 
require_once __DIR__ . '/../../config/auth.php'; 
$nombreSistema = defined('SYSTEM_NAME') ? SYSTEM_NAME : 'ElZapato';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contáctanos en <?php echo $nombreSistema; ?>. Información de sucursal, teléfono y ubicación para visitarnos fácilmente.">
    <title>Contactos | <?php echo $nombreSistema; ?></title>

    <link rel="stylesheet" href="/ElZapato/Assets/css/contactos.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/ElZapato/Assets/css/components/dev-modal.css?v=20260423">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/ElZapato/src/views/layouts/header.php'; ?>

    <main class="contact-page">
        <section class="contact-hero reveal-up">
            <div class="contact-hero-content">
                <span class="contact-kicker">Atención cercana • Ubicación fácil • Respuesta rápida</span>
                <h1>Estamos listos para <span>ayudarte</span></h1>
                <p>
                    Visítanos en sucursal, llámanos o abre navegación directa en <strong><?php echo $nombreSistema; ?></strong>.
                    Diseñamos esta sección para que encuentres la información más importante en segundos.
                </p>

                <div class="contact-hero-actions">
                    <a href="tel:+50323781500" class="contact-btn contact-btn-primary">
                        <i class="fas fa-phone"></i>
                        Llamar ahora
                    </a>
                    <a href="#contactMap" class="contact-btn contact-btn-secondary">
                        <i class="fas fa-map-location-dot"></i>
                        Ver mapa
                    </a>
                </div>
            </div>

            <div class="contact-bento reveal-up delay-1">
                <div class="bento-head">
                    <span class="bento-status"><span class="live-dot"></span> Disponible ahora</span>
                    <span class="bento-pill waze"><i class="fab fa-waze"></i> Colaboración Waze</span>
                </div>

                <h3>Asistencia instantánea en Waze</h3>
                <p>Estamos priorizando una experiencia de navegación colaborativa con Waze para que llegues más rápido a <?php echo $nombreSistema; ?>.</p>

                <div class="bento-grid">
                    <a href="https://waze.com/ul?ll=13.8152576,-88.8626189&navigate=yes" target="_blank" rel="noopener noreferrer" class="bento-chip full waze">
                        <i class="fab fa-waze"></i>
                        Abrir navegación colaborativa en Waze
                    </a>
                </div>

                <div class="bento-foot">
                    <span><i class="fas fa-bolt"></i> Integración activa con Waze para rutas optimizadas en tiempo real.</span>
                </div>
            </div>

            <div class="contact-shape shape-a"></div>
            <div class="contact-shape shape-b"></div>
        </section>

        <section class="contact-main" id="contactMap">
            <article class="contact-info-card reveal-up">
                <div class="contact-info-head">
                    <h2>Datos de contacto</h2>
                    <p>Elige la forma más rápida para llegar o comunicarte con nosotros.</p>
                </div>

                <div class="contact-info-list">
                    <div class="contact-item">
                        <i class="fas fa-location-dot"></i>
                        <div>
                            <h4>Dirección</h4>
                            <p>Km 51, Cantón Agua Zarca, Ilobasco.</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h4>Teléfono</h4>
                            <p>(503) 2378-1500</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>Email</h4>
                            <p>contacto@<?php echo strtolower(str_replace(' ', '', $nombreSistema)); ?>.com</p>
                        </div>
                    </div>
                </div>

                <div class="contact-actions">
                    <a class="contact-action-btn" href="https://waze.com/ul?ll=13.8152576,-88.8626189&navigate=yes" target="_blank" rel="noopener noreferrer">
                        <i class="fab fa-waze"></i>
                        Navegar en Waze
                    </a>
                    <a class="contact-action-btn secondary" href="https://maps.google.com/?q=UNICAES+Ilobasco" target="_blank" rel="noopener noreferrer">
                        <i class="fas fa-map"></i>
                        Abrir en Google Maps
                    </a>
                </div>
            </article>

            <article class="contact-map-card reveal-up delay-1">
                <div class="map-head">
                    <h3>Ubicación en tiempo real</h3>
                </div>
                <div class="map-wrap">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3873.3444648782346!2d-88.86519382413554!3d13.815257595759714!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8f33663a8323602d%3A0xc39f972b90494488!2sUNICAES%20Ilobasco!5e0!3m2!1ses!2ssv!4v1711200000000!5m2!1ses!2ssv"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </article>
        </section>
    </main>

    <footer class="contact-footer">
        <p>© <?php echo date('Y'); ?> <?php echo $nombreSistema; ?> - Contactos</p>
        <button type="button" onclick="openDevModal()" class="btn-devs">Desarrollado por</button>
    </footer>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/ElZapato/src/views/layouts/dev-team-modal.php'; ?>
    <script src="/ElZapato/Assets/js/dev-modal.js?v=20260423"></script>

    <script>
        const revealItems = document.querySelectorAll('.reveal-up');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                }
            });
        }, { threshold: 0.14 });

        revealItems.forEach(item => revealObserver.observe(item));
    </script>
</body>
</html>