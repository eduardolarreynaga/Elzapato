<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * CARGA DE AJUSTES DEL SISTEMA
 */
if (file_exists(__DIR__ . '/settings.php')) {
    require_once __DIR__ . '/settings.php';
}

// 2. DEFINIR NOMBRE POR DEFECTO (Si el archivo falla o no existe)
if (!defined('SYSTEM_NAME')) {
    define('SYSTEM_NAME', 'ElZapato');
}

/**
 * Registra los datos del usuario en la sesión
 */
if (!function_exists('login_user')) {
    function login_user(array $userData) {
        session_regenerate_id(true);
        $_SESSION['id_usuario'] = $userData['id_usuario'];
        $_SESSION['usuario']    = $userData['username'];
        $_SESSION['rol']        = $userData['rol'];
        $_SESSION['auth']       = true;
    }
}

/**
 * Verifica si existe una sesión activa
 */
if (!function_exists('is_authenticated')) {
    function is_authenticated() {
        return isset($_SESSION['auth']) && $_SESSION['auth'] === true;
    }
}

/**
 * PROTECCIÓN DE VISTAS
 */
if (!function_exists('require_auth')) {
    function require_auth($roleRequired = null) {
        if (!is_authenticated()) {
            header('Location: /ElZapato/src/views/public/login.php');
            exit;
        }
        if ($roleRequired !== null && $_SESSION['rol'] !== $roleRequired) {
            header('Location: /ElZapato/src/views/public/login.php?error=no_access');
            exit;
        }
    }
}

/**
 * Cierra la sesión
 */
if (!function_exists('logout_user')) {
    function logout_user() {
        session_unset();
        session_destroy();
        header('Location: /ElZapato/src/views/public/login.php');
        exit;
    }
}