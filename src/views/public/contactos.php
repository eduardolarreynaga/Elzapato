<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contáctanos en ElZapato. Información de sucursal, teléfono y ubicación para visitarnos fácilmente.">
    <title>Contactos | ElZapato</title>

    <link rel="stylesheet" href="/ElZapato/Assets/css/contactos.css?v=20260417">
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
                    Visítanos en sucursal, llámanos o abre navegación directa.
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
                <p>Estamos priorizando una experiencia de navegación colaborativa con Waze para que llegues más rápido y con mejor ruta.</p>

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
                            <p>contacto@elzapato.com</p>
                        </div>
                    </div>
                </div>

                <div class="contact-actions">
                    <a class="contact-action-btn" href="https://waze.com/ul?ll=13.8152576,-88.8626189&navigate=yes" target="_blank" rel="noopener noreferrer">
                        <i class="fab fa-waze"></i>
                        Navegar en Waze
                    </a>
                    <a class="contact-action-btn secondary" href="https://maps.google.com/?q=13.8152576,-88.8626189" target="_blank" rel="noopener noreferrer">
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
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3874.395769311833!2d-88.86261889135724!3d13.81525760853133!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8f635957920bc5d9%3A0x6b2b5d9001cbf3bc!2sUniversidad%20Cat%C3%B3lica%20de%20El%20Salvador%20%7C%20Ilobasco!5e0!3m2!1ses-419!2ssv!4v1774908318365!5m2!1ses-419!2ssv"
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
        <p>&copy; 2026 ElZapato - Contactos</p>
    </footer>

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