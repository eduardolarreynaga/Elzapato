<?php

if (!defined('BASE_URL')) {
    define('BASE_URL', '/ElZapato');
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim(BASE_URL, '/');
        $cleanPath = ltrim($path, '/');

        return $cleanPath === '' ? $base : $base . '/' . $cleanPath;
    }
}

if (!function_exists('redirect_to')) {
    function redirect_to(string $path): void
    {
        header('Location: ' . url($path));
        exit();
    }
}
