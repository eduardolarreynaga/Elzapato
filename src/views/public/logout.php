<?php
/**
 * PROCESADOR DE CIERRE DE SESIÓN
 */

// Subimos dos niveles (../..) para salir de public y views, luego entramos a config
require_once __DIR__ . '/../../config/auth.php';

// Ejecutamos la función de cierre que ya tiene la redirección al login
logout_user();