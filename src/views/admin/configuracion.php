<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

$activeMenu = 'configuracion';
$pageTitle = 'Ajustes | ' . SYSTEM_NAME;
$pageStyles = ['/ElZapato/Assets/css/pages/admin-config.css']; 

require __DIR__ . '/../layouts/admin-shell-start.php';
require __DIR__ . '/../layouts/admin-header.php';
?>

<div class="config-container">
    <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle"></i> ¡Identidad actualizada con éxito!
        </div>
    <?php endif; ?>

    <form action="procesar_configuracion.php" method="POST" enctype="multipart/form-data" class="config-grid-simple" id="config-form">
        <div class="config-card main-card">
            <div class="logo-edit-group">
                <div class="logo-preview-container" onclick="document.getElementById('logo-input').click()">
                    <img src="/ElZapato/Assets/img/logo.png?v=<?php echo time(); ?>" alt="Logo" id="img-preview">
                    <div class="overlay"><i class="fas fa-camera"></i></div>
                </div>
                <input type="file" id="logo-input" name="nuevo_logo" hidden accept="image/*">
                <small id="file-info">Click en la imagen para cambiar el logo</small>
            </div>

            <div class="form-group">
                <label><i class="fas fa-signature"></i> Nombre del Sistema</label>
                <input type="text" name="nuevo_nombre" value="<?php echo htmlspecialchars(SYSTEM_NAME); ?>" required>
            </div>

            <button type="submit" class="btn-save">
                <i class="fas fa-sync-alt"></i> GUARDAR CAMBIOS
            </button>
        </div>
    </form>
</div>

<script>
// Previsualización inmediata al elegir archivo
document.getElementById('logo-input').onchange = function (e) {
    if (e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = (ex) => { document.getElementById('img-preview').src = ex.target.result; };
        reader.readAsDataURL(e.target.files[0]);
    }
}

// Al cargar la página, verificamos si venimos de una actualización exitosa
window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('status') === 'success') {
        const timestamp = new Date().getTime();
        // Buscamos todas las imágenes de la página (logo del header y del preview)
        const imagenes = document.querySelectorAll('img');
        
        imagenes.forEach(img => {
            // Si la ruta contiene "logo.png", le inyectamos un nuevo parámetro de tiempo
            if (img.src.includes('logo.png')) {
                const urlLimpia = img.src.split('?')[0];
                img.src = urlLimpia + '?refresh=' + timestamp;
            }
        });

        // Opcional: Limpiar la URL para que no diga ?status=success tras recargar manual
        const nuevaUrl = window.location.pathname;
        window.history.replaceState({}, document.title, nuevaUrl);
    }
});

// Animación del botón al enviar
document.getElementById('config-form').onsubmit = function() {
    this.querySelector('.btn-save').innerHTML = '<i class="fas fa-spinner fa-spin"></i> ACTUALIZANDO...';
};
</script>

<?php 
require __DIR__ . '/../layouts/admin-shell-end.php'; 
require __DIR__ . '/../layouts/admin-html-end.php'; 
?>