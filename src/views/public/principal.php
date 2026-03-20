<?php
session_start();
// Si viene el rol por la URL, lo guardamos en la sesión
if (isset($_GET['set_role'])) {
    $_SESSION['user_role'] = $_GET['set_role'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ElZapato - Dashboard Principal</title>
    
    <link rel="stylesheet" href="/ElZapato/Assets/css/principal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/ElZapato/src/views/layouts/header.php'; ?>

    <section class="hero">
        <h1>Bienvenido a ElZapato</h1>
        <p>Calzado de calidad para cada paso de tu vida</p>
    </section>

    <section class="promo-container">
        <div class="card">
            <i class="fas fa-shipping-fast"></i>
            <h3>Envíos Rápidos</h3>
            <p>Entregas en todo El Salvador en menos de 48 horas.</p>
        </div>
        <div class="card">
            <i class="fas fa-shield-halved"></i>
            <h3>Garantía Total</h3>
            <p>Cambios y devoluciones sin complicaciones por 30 días.</p>
        </div>
        <div class="card">
            <i class="fas fa-tags"></i>
            <h3>Ofertas de Temporada</h3>
            <p>Aprovecha hasta un 40% de descuento en calzado deportivo.</p>
        </div>
    </section>

    <section class="featured-section">
        <h2 class="section-title">Productos Destacados</h2>
        
        <div class="product-grid">
            <div class="product-item">
                <img src="https://images.unsplash.com/photo-1549298916-b41d501d3772" alt="Zapato">
                <h4>Casual Sport Brown</h4>
                <p class="price">$45.00</p>
            </div>
            <div class="product-item">
                <img src="https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77" alt="Zapato">
                <h4>Classic White Sneakers</h4>
                <p class="price">$38.99</p>
            </div>
            <div class="product-item">
                <img src="https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a" alt="Zapato">
                <h4>Neon Running Shoes</h4>
                <p class="price">$55.00</p>
            </div>
            <div class="product-item">
                <img src="https://images.unsplash.com/photo-1608231387042-66d1773070a5" alt="Zapato">
                <h4>Formal Business Black</h4>
                <p class="price">$60.00</p>
            </div>
        </div>
    </section>

    <footer style="background: var(--texto-negro); color: var(--bg-claro); text-align: center; padding: 20px; margin-top: 50px;">
        <p>&copy; 2026 ElZapato - Sistema de Gestión Escolar UNICAES</p>
    </footer>

</body>
</html>