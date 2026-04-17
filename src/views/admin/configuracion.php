<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

$activeMenu = 'configuracion';
$pageTitle = 'Configuración';
$pageStyles = ['/ElZapato/Assets/css/pages/admin-config.css']; 
require __DIR__ . '/../layouts/admin-shell-start.php';

$pageHeading = 'Configuración';
require __DIR__ . '/../layouts/admin-header.php';
?>

<div class="config-container">
    <header class="config-sub-header">
        <p>Personaliza la información y apariencia de tu empresa</p>
    </header>

    <form action="/ElZapato/admin/configuracion/guardar" method="POST" enctype="multipart/form-data" class="config-grid">
        
        <aside class="config-card logo-section">
            <h3>Logo de la Empresa</h3>
            <div class="logo-upload-container">
                <div class="logo-preview">
                    <img src="/ElZapato/Assets/img/logo.png" alt="Logo ElZapato" id="img-preview">
                </div>
                <label for="logo-input" class="upload-label">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Haz clic en el logo para cambiar la imagen</span>
                    <small id="file-info">JPG, PNG, WEBP, SVG - máx 2MB</small>
                </label>
                <input type="file" id="logo-input" name="empresa_logo" hidden 
                       accept=".jpg, .jpeg, .png, .webp, .svg">
            </div>
        </aside>

        <section class="config-card info-section">
            <h3>Información de la Empresa</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label>NOMBRE DE LA EMPRESA</label>
                    <input type="text" name="nombre" placeholder="ElZapato S.A. de C.V.">
                </div>
                <div class="form-group">
                    <label>NIT / RUC</label>
                    <input type="text" name="nit" placeholder="0614-000000-000-0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>TELÉFONO</label>
                    <input type="text" name="telefono" placeholder="+503 2200-0000">
                </div>
                <div class="form-group">
                    <label>EMAIL</label>
                    <input type="email" name="email" placeholder="contacto@elzapato.com">
                </div>
            </div>

            <div class="form-group full-width">
                <label>DIRECCIÓN</label>
                <input type="text" name="direccion" placeholder="Calle Principal, Ilobasco, Cabañas">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>SITIO WEB</label>
                    <input type="url" name="web" placeholder="www.elzapato.com">
                </div>
                <div class="form-group">
                    <label>INSTAGRAM</label>
                    <input type="text" name="instagram" placeholder="@elzapato_sv">
                </div>
            </div>

            <div class="form-group full-width">
                <label>DESCRIPCIÓN / SLOGAN</label>
                <textarea name="descripcion" placeholder="Calidad a cada paso"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save" id="btn-submit">
                    <i class="fas fa-save"></i> GUARDAR CAMBIOS
                </button>
            </div>
        </section>
    </form>
</div>

<script>
document.getElementById('logo-input').onchange = function (evt) {
    const file = evt.target.files[0];
    const maxSize = 2 * 1024 * 1024; // 2MB
    const allowedExtensions = /(\.jpg|\.jpeg|\.png|\.webp|\.svg)$/i;
    const fileInfo = document.getElementById('file-info');

    if (file) {
        // 1. Validar Extensión por nombre de archivo
        if (!allowedExtensions.exec(this.value)) {
            alert("⚠️ Formato no permitido. Por favor usa JPG, PNG, WEBP o SVG.");
            this.value = '';
            return false;
        }

        // 2. Validar Tamaño (Máximo 2MB)
        if (file.size > maxSize) {
            alert("⚠️ El archivo es muy pesado. El límite es de 2MB.");
            this.value = '';
            return false;
        }

        // 3. Si todo es correcto, previsualizar la imagen
        const fr = new FileReader();
        fr.onload = function () {
            document.getElementById('img-preview').src = fr.result;
            fileInfo.style.color = "#28a745"; // Cambia a verde si es válido
            fileInfo.innerHTML = "✓ Imagen lista para subir";
        }
        fr.readAsDataURL(file);
    }
}
</script>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>
<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>