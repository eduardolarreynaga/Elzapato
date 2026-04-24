<?php 
require_once __DIR__ . '/../../config/auth.php'; 
$nombreSistema = defined('SYSTEM_NAME') ? SYSTEM_NAME : 'ElZapato';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nombreSistema; ?> - Dashboard Principal</title>
    <link rel="icon" type="image/jpeg" href="/ElZapato/Assets/img/zapa.jpeg">
    
    <link rel="stylesheet" href="/ElZapato/Assets/css/principal.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/ElZapato/Assets/css/components/dev-modal.css?v=20260423">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <main class="landing-page">

        <section class="hero">
            <div class="hero-overlay"></div>
            <div class="hero-particles"></div>

            <div class="hero-content">
                <span class="hero-badge">Estilo • Calidad • Comodidad</span>
                <h1>Bienvenido a <span><?php echo $nombreSistema; ?></span></h1>
                <p>
                    Descubre una experiencia moderna en calzado con modelos elegantes,
                    deportivos y casuales pensados para cada paso de tu vida.
                </p>

                <div class="hero-actions">
                    <a href="#productos" class="btn-primary">
                        <i class="fas fa-shoe-prints"></i>
                        Ver productos
                    </a>
                    <a href="#beneficios" class="btn-secondary">
                        <i class="fas fa-store"></i>
                        Conocer más
                    </a>
                </div>
            </div>

            <div class="hero-floating-card">
                <div class="floating-icon">
                    <i class="fas fa-shoe-prints"></i>
                </div>
                <h3>Diseño con identidad</h3>
                <p>Calzado para todos los estilos con una imagen elegante, moderna y funcional.</p>
            </div>

            <div class="hero-shoe hero-shoe-1">
                <i class="fas fa-shoe-prints"></i>
            </div>

            <div class="hero-shape hero-shape-1"></div>
            <div class="hero-shape hero-shape-2"></div>
            <div class="hero-line"></div>
        </section>

        <section class="promo-section" id="beneficios">
            <div class="section-heading">
                <span>Nuestros beneficios</span>
                <h2>¿Por qué elegir <?php echo $nombreSistema; ?>?</h2>
                <p>Una tienda pensada para ofrecer variedad, comodidad y atención de calidad.</p>
            </div>

            <div class="promo-container">
                <div class="card promo-card promo-hombre">
                    <h3>Para Caballero</h3>
                    <p>Modelos elegantes y casuales para cualquier ocasión.</p>
                </div>
                
                <div class="card promo-card promo-mujer">
                    <h3>Para Dama</h3>
                    <p>Bonitos y versátiles que combinan personalidad y estilo.</p>
                </div>

                <div class="card promo-card promo-nino">
                    <h3>Para Niño</h3>
                    <p>Opciones cómodas, resistentes y diseños atractivos para uso diario.</p>
                </div>

                <div class="card promo-card promo-nina">
                    <h3>Para Niña</h3>
                    <p>Diseños coloridos, cómodos y bonitos para cada etapa y actividad.</p>
                </div>


            </div>
        </section>

        <section class="featured-section" id="productos">
            <div class="section-heading left">
                <span>Colección destacada</span>
                <h2 class="section-title">Productos Destacados</h2>
                <p>Una selección especial con modelos modernos, elegantes y funcionales.</p>
            </div>
            
            <div class="product-grid">
                <div class="product-item">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1549298916-b41d501d3772" alt="Zapato">
                    </div>
                    <div class="product-info">
                        <h4>Casual Sport Brown</h4>
                        <p class="product-desc">Diseño casual con acabado moderno y elegante.</p>
                        <p class="price">$45.00</p>
                    </div>
                </div>

                <div class="product-item">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77" alt="Zapato">
                    </div>
                    <div class="product-info">
                        <h4>Classic White Sneakers</h4>
                        <p class="product-desc">Estilo limpio, cómodo y perfecto para diario.</p>
                        <p class="price">$38.99</p>
                    </div>
                </div>

                <div class="product-item">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a" alt="Zapato">
                    </div>
                    <div class="product-info">
                        <h4>Neon Running Shoes</h4>
                        <p class="product-desc">Ideal para un look deportivo con personalidad.</p>
                        <p class="price">$55.00</p>
                    </div>
                </div>

                <div class="product-item">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1608231387042-66d1773070a5" alt="Zapato">
                    </div>
                    <div class="product-info">
                        <h4>Formal Business Black</h4>
                        <p class="product-desc">Elegancia formal para ambientes profesionales.</p>
                        <p class="price">$60.00</p>
                    </div>
                </div>

                <div class="product-item">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1600185365483-26d7a4cc7519" alt="Zapato">
                    </div>
                    <div class="product-info">
                        <h4>Urban Street Gray</h4>
                        <p class="product-desc">Una propuesta urbana con imagen auténtica.</p>
                        <p class="price">$49.99</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <div class="cta-content">
                <span class="cta-mini"><?php echo $nombreSistema; ?></span>
                <h2>Camina con estilo, seguridad y personalidad</h2>
                <p>
                    Nuestra colección está pensada para ofrecer una experiencia visual atractiva
                    y una mejor presentación del catálogo de productos.
                </p>
                <a href="catalogo.php" class="btn-primary dark">
                    <i class="fas fa-arrow-right"></i>
                    Explorar catálogo
                </a>
            </div>
        </section>

    </main>

    <footer class="main-footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo $nombreSistema; ?> - Sistema de Gestión Escolar UNICAES</p>
        <button type="button" onclick="openDevModal()" class="btn-devs">Desarrollado por</button>
    </footer>

    <?php include __DIR__ . '/../layouts/dev-team-modal.php'; ?>
    <script src="/ElZapato/Assets/js/dev-modal.js?v=20260423"></script>

</body>
</html>