<?php
require_once __DIR__ . '/../../config/auth.php';
require_auth('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rutaLogoBase = __DIR__ . '/../../../../Assets/img/';
    $configPath = __DIR__ . '/../../config/settings.php';
    $cambios = 0;

    // 1. ACTUALIZAR NOMBRE
    if (isset($_POST['nuevo_nombre']) && !empty(trim($_POST['nuevo_nombre']))) {
        $nombre = trim($_POST['nuevo_nombre']);
        $contenido = "<?php\n// Generado automaticamente\ndefine('SYSTEM_NAME', '" . addslashes($nombre) . "');";
        
        if (file_put_contents($configPath, $contenido)) {
            $cambios++;
        }
    }

    // 2. ACTUALIZAR LOGO
    if (isset($_FILES['nuevo_logo']) && $_FILES['nuevo_logo']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['nuevo_logo']['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'ico'];

        if (in_array($ext, $permitidos)) {
            // Eliminar versiones previas para evitar conflictos
            foreach ($permitidos as $e) {
                if (file_exists($rutaLogoBase . "logo." . $e)) @unlink($rutaLogoBase . "logo." . $e);
            }
            // Guardar siempre como logo.png para mantener consistencia
            if (move_uploaded_file($_FILES['nuevo_logo']['tmp_name'], $rutaLogoBase . "logo.png")) {
                $cambios++;
            }
        }
    }

    // 3. LIMPIEZA DE OPCACHE (Si está activo en el servidor)
    if (function_exists('opcache_reset')) {
        @opcache_reset();
    }

    // Redirección con flag de éxito para disparar el JS de actualización forzada
    $status = ($cambios > 0) ? 'success' : 'no_changes';
    header("Location: configuracion.php?status=" . $status);
    exit;
}