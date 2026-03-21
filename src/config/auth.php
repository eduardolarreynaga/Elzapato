<?php

require_once __DIR__ . '/app.php';

$usuarioModelPath = __DIR__ . '/../models/Usuario.php';
if (file_exists($usuarioModelPath)) {
    require_once $usuarioModelPath;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('is_authenticated')) {
    function is_authenticated(): bool
    {
        return isset($_SESSION['usuario'], $_SESSION['rol'], $_SESSION['id_usuario']);
    }
}

if (!function_exists('require_auth')) {
    function require_auth(?string $requiredRole = null): void
    {
        if (!is_authenticated()) {
            redirect_to('index.php');
        }

        if ($requiredRole !== null && ($_SESSION['rol'] ?? null) !== $requiredRole) {
            redirect_to('index.php');
        }
    }
}

if (!function_exists('login_user')) {
    function login_user(array $userData): void
    {
        session_regenerate_id(true);
        $_SESSION['id_usuario'] = $userData['id_usuario'];
        $_SESSION['usuario'] = $userData['username'];
        $_SESSION['rol'] = $userData['rol'];
        $_SESSION['email'] = $userData['email'];
        $_SESSION['last_activity'] = time();
        
        
        if (class_exists('Usuario')) {
            try {
                $usuarioClass = 'Usuario';
                $usuarioModel = new $usuarioClass();
                $usuarioModel->registrarLoginLog($userData['id_usuario'], true);
            } catch (Exception $e) {
                error_log("Error al registrar log de login: " . $e->getMessage());
            }
        }
    }
}

if (!function_exists('logout_user')) {
    function logout_user(): void
    {
        
        if (isset($_SESSION['id_usuario']) && class_exists('Usuario')) {
            try {
                $usuarioClass = 'Usuario';
                $usuarioModel = new $usuarioClass();
                $usuarioModel->registrarLogoutLog($_SESSION['id_usuario']);
            } catch (Exception $e) {
                error_log("Error al registrar log de logout: " . $e->getMessage());
            }
        }
        
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}

if (!function_exists('redirect_dashboard_by_role')) {
    function redirect_dashboard_by_role(string $role): void
    {
        switch ($role) {
            case 'alumno':
                redirect_to('src/views/alumno/dashboard.php');
                break;
            case 'docente':
                redirect_to('src/views/docente/dashboard.php');
                break;
            case 'admin':
                redirect_to('src/views/admin/dashboard.php');
                break;
            default:
                redirect_to('index.php');
        }
    }
}

if (!function_exists('get_current_user_id')) {
    function get_current_user_id(): ?int
    {
        return $_SESSION['id_usuario'] ?? null;
    }
}

if (!function_exists('get_current_user_role')) {
    function get_current_user_role(): ?string
    {
        return $_SESSION['rol'] ?? null;
    }
}

if (!function_exists('get_current_username')) {
    function get_current_username(): ?string
    {
        return $_SESSION['usuario'] ?? null;
    }
}

