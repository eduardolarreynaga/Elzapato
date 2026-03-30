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
        </section>

        <section class="pv-info-area">
            <div class="info-card">
                <div class="info-card-header" style="background: #A67C52; color: white; padding: 15px; text-align: center; border-radius: 12px 12px 0 0;">
                    SUCURSAL UNICAES - ILOBASCO
                </div>
                <div class="info-card-body" style="padding: 20px; background: white; border: 1px solid #ddd; border-radius: 0 0 12px 12px;">
                    <div class="info-item" style="margin-bottom: 12px;">
                        <i class="fas fa-location-dot" style="color: #A67C52;"></i>
                        <span>Km 51, Cantón Agua Zarca, Ilobasco.</span>
                    </div>
                    <div class="info-item" style="margin-bottom: 12px;">
                        <i class="fas fa-phone" style="color: #A67C52;"></i>
                        <span>(503) 2378-1500</span>
                    </div>

                    <button class="btn-waze" onclick="window.open('https://waze.com/ul?ll=13.8152576,-88.8626189&navigate=yes', '_blank')">
                        <i class="fab fa-waze"></i> NAVEGAR EN WAZE
                    </button>
                </div>
            </div>
        </section>

    </main>
</body>
</html>