<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

$activeMenu = 'configuracion';
$pageTitle = 'Configuración | El Zapato';
$pageStyles = ['/ElZapato/Assets/css/pages/admin-config.css']; 
require __DIR__ . '/../layouts/admin-shell-start.php';

$pageHeading = 'Configuración General';
require __DIR__ . '/../layouts/admin-header.php';
?>

<div class="config-container">
    <div class="config-header-wrapper">
        <header class="config-sub-header">
            <p>Personaliza la información y apariencia de tu empresa</p>
        </header>
    </div>

    <form action="#" method="POST" enctype="multipart/form-data" class="config-grid" id="configForm">
        
        <!-- Sección Logo -->
        <aside class="config-card logo-section">
            <div class="card-header">
                <h3>
                    <i class="fas fa-image"></i>
                    Identidad Visual
                </h3>
            </div>
            <div class="card-body">
                <div class="logo-upload-container">
                    <div class="logo-preview" onclick="document.getElementById('logo-input').click()">
                        <img src="/ElZapato/Assets/img/logo.png" alt="Logo ElZapato" id="img-preview">
                    </div>
                    <label for="logo-input" class="upload-label">
                        <div class="upload-content">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Haz clic para cambiar el logo</span>
                            <small id="file-info">JPG, PNG, WEBP, SVG • Máx 2MB</small>
                            <small style="display: block; margin-top: 0.25rem; font-size: 0.65rem;">
                                <i class="fas fa-info-circle"></i> Recomendado: 200x200px
                            </small>
                        </div>
                    </label>
                    <input type="file" id="logo-input" name="empresa_logo" hidden 
                           accept=".jpg, .jpeg, .png, .webp, .svg">
                </div>
            </div>
        </aside>

        <!-- Sección Información -->
        <section class="config-card info-section">
            <div class="card-header">
                <h3>
                    <i class="fas fa-building"></i>
                    Datos de la Empresa
                </h3>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-store"></i>
                            NOMBRE DE LA EMPRESA
                        </label>
                        <input type="text" name="nombre" placeholder="ElZapato S.A. de C.V." 
                               data-tooltip="Nombre legal de la empresa">
                    </div>
                    <div class="form-group">
                        <label>
                            <i class="fas fa-id-card"></i>
                            NIT / RUC
                        </label>
                        <input type="text" name="nit" placeholder="0614-000000-000-0"
                               data-tooltip="Número de identificación tributaria">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-phone-alt"></i>
                            TELÉFONO
                        </label>
                        <input type="text" name="telefono" placeholder="+503 2200-0000"
                               data-tooltip="Teléfono de contacto">
                    </div>
                    <div class="form-group">
                        <label>
                            <i class="fas fa-envelope"></i>
                            EMAIL
                        </label>
                        <input type="email" name="email" placeholder="contacto@elzapato.com"
                               data-tooltip="Correo electrónico principal">
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>
                        <i class="fas fa-map-marker-alt"></i>
                        DIRECCIÓN
                    </label>
                    <input type="text" name="direccion" placeholder="Calle Principal, Ilobasco, Cabañas"
                           data-tooltip="Dirección física de la tienda">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-globe"></i>
                            SITIO WEB
                        </label>
                        <input type="url" name="web" placeholder="www.elzapato.com"
                               data-tooltip="Sitio web oficial">
                    </div>
                    <div class="form-group">
                        <label>
                            <i class="fab fa-instagram"></i>
                            INSTAGRAM
                        </label>
                        <input type="text" name="instagram" placeholder="@elzapato_sv"
                               data-tooltip="Usuario de Instagram">
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>
                        <i class="fas fa-tag"></i>
                        DESCRIPCIÓN / SLOGAN
                    </label>
                    <textarea name="descripcion" placeholder="Calidad a cada paso..." 
                              data-tooltip="Eslogan o descripción de la empresa"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save" id="btn-submit">
                        <i class="fas fa-save"></i> 
                        GUARDAR CAMBIOS
                    </button>
                </div>
            </div>
        </section>
    </form>
</div>

<script>
// Solo animaciones y preview del logo, sin envío de formulario
document.getElementById('logo-input').onchange = function (evt) {
    const file = evt.target.files[0];
    const maxSize = 2 * 1024 * 1024;
    const allowedExtensions = /(\.jpg|\.jpeg|\.png|\.webp|\.svg)$/i;
    const fileInfo = document.getElementById('file-info');

    if (file) {
        if (!allowedExtensions.exec(this.value)) {
            alert("⚠️ Formato no permitido. Por favor usa JPG, PNG, WEBP o SVG.");
            this.value = '';
            return false;
        }

        if (file.size > maxSize) {
            alert("⚠️ El archivo es muy pesado. El límite es de 2MB.");
            this.value = '';
            return false;
        }

        const fr = new FileReader();
        fr.onload = function () {
            document.getElementById('img-preview').src = fr.result;
            fileInfo.innerHTML = "✓ Imagen lista para subir";
            fileInfo.style.color = "#10B981";
        }
        fr.readAsDataURL(file);
    }
}

// Animaciones adicionales
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);
</script>

<?php require __DIR__ . '/../layouts/admin-shell-end.php'; ?>
<?php require __DIR__ . '/../layouts/admin-html-end.php'; ?>