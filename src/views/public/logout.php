<?php
/**
 * PROCESADOR DE CIERRE DE SESIÓN
 */

// Subimos dos niveles (../..) para salir de public y views, luego entramos a config
require_once __DIR__ . '/../../config/auth.php';

// CORREGIDO: Ruta correcta al LogHelper
require_once __DIR__ . '/../../../helpers/LogHelper.php';

// Registrar el cierre de sesión ANTES de destruir la sesión
if (isset($_SESSION['id_usuario']) && isset($_SESSION['usuario'])) {
    $detalle = "Cierre de sesión del usuario: " . $_SESSION['usuario'];
    LogHelper::registrar('logout', 'sesion', $_SESSION['id_usuario'], $detalle);
}

// Ejecutamos la función de cierre que ya tiene la redirección al login
logout_user();
?>