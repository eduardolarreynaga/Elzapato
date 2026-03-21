<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('seller');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de Venta | ElZapato</title>
    
    <link rel="stylesheet" href="/ElZapato/Assets/css/punto_venta.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/ElZapato/src/views/layouts/header.php'; ?>

    <main class="pv-container">
        
        <section class="pv-map-area">
            <div class="map-placeholder">
                <i class="fas fa-map-marked-alt fa-3x"></i>
                <p>Cargando mapa interactivo...</p>
                <img src="https://via.placeholder.com/800x600?text=Simulacion+de+Mapa+ElZapato" alt="Mapa">
            </div>
        </section>

        <section class="pv-info-area">
            <div class="info-card">
                <div class="info-card-header">
                    SUCURSAL CENTRAL - SAN SALVADOR
                </div>
                <div class="info-card-body">
                    <div class="info-item">
                        <i class="fas fa-location-dot"></i>
                        <span>C. Rubén Darío y 7a Av. Sur Edif. Emicar, local 2 y 3</span>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <span>Lunes a Domingo: 8:00am a 6:00pm</span>
                    </div>

                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <span>(503) 2222-0811</span>
                    </div>

                    <div class="info-item">
                        <i class="fab fa-whatsapp"></i>
                        <span>(503) 7275-2074</span>
                    </div>

                    <button class="btn-waze">
                        <i class="fas fa-location-arrow"></i> NAVEGAR EN WAZE
                    </button>
                </div>
            </div>

            </section>

    </main>

</body>
</html>