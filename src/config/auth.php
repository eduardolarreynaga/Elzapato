<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
 * Si no está autenticado, lo manda al login.
 * Si se pide un rol específico (ej: 'admin') y no lo tiene, lo manda a error.
 */
if (!function_exists('require_auth')) {
    function require_auth($roleRequired = null) {
        // 1. ¿Está logueado?
        if (!is_authenticated()) {
            header('Location: /ElZapato/src/views/public/login.php');
            exit;
        }

        // 2. ¿Tiene el rol necesario?
        if ($roleRequired !== null && $_SESSION['rol'] !== $roleRequired) {
            // Si es un usuario normal queriendo entrar a admin, o viceversa
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