-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 01-06-2026 a las 23:29:53
-- Versión del servidor: 8.4.7
-- Versión de PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `pos_zapateria`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cajas`
--

DROP TABLE IF EXISTS `cajas`;
CREATE TABLE IF NOT EXISTS `cajas` (
  `id_caja` int NOT NULL AUTO_INCREMENT,
  `nombre_caja` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `estado` enum('activa','inactiva') COLLATE utf8mb4_general_ci DEFAULT 'activa',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_caja`),
  UNIQUE KEY `nombre_caja` (`nombre_caja`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cajas`
--

INSERT INTO `cajas` (`id_caja`, `nombre_caja`, `estado`, `fecha_creacion`) VALUES
(1, 'Caja Principal', 'activa', '2026-04-24 14:45:46'),
(2, 'Caja Norte', 'activa', '2026-04-24 14:45:46'),
(3, 'Caja Express', 'activa', '2026-04-24 14:45:46'),
(4, 'Caja Respaldo', 'inactiva', '2026-04-24 14:45:46'),
(5, 'caja 1', 'activa', '2026-04-24 15:45:30'),
(6, 'Caja Sur', 'activa', '2026-05-31 22:55:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_aperturas`
--

DROP TABLE IF EXISTS `caja_aperturas`;
CREATE TABLE IF NOT EXISTS `caja_aperturas` (
  `id_apertura` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_caja` int NOT NULL,
  `monto_inicial` decimal(10,2) NOT NULL,
  `monto_cierre` decimal(10,2) DEFAULT NULL,
  `total_ventas` int DEFAULT '0',
  `total_ingresos` decimal(10,2) DEFAULT '0.00',
  `total_vuelto` decimal(10,2) DEFAULT '0.00',
  `fecha_apertura` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_cierre` datetime DEFAULT NULL,
  `estado` enum('abierta','cerrada','cancelada') DEFAULT 'abierta',
  PRIMARY KEY (`id_apertura`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_caja` (`id_caja`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `caja_aperturas`
--

INSERT INTO `caja_aperturas` (`id_apertura`, `id_usuario`, `id_caja`, `monto_inicial`, `monto_cierre`, `total_ventas`, `total_ingresos`, `total_vuelto`, `fecha_apertura`, `fecha_cierre`, `estado`) VALUES
(1, 7, 6, 0.00, 1.00, 0, 0.00, 0.00, '2026-05-31 18:03:29', '2026-05-31 18:04:01', 'cerrada'),
(2, 7, 6, 0.00, 223.50, 1, 223.50, 0.00, '2026-05-31 18:18:58', '2026-05-31 18:19:43', 'cerrada'),
(3, 7, 6, 0.00, 359.96, 1, 359.96, 0.00, '2026-06-01 08:30:45', '2026-06-01 08:31:12', 'cerrada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_movimientos`
--

DROP TABLE IF EXISTS `caja_movimientos`;
CREATE TABLE IF NOT EXISTS `caja_movimientos` (
  `id_movimiento` int NOT NULL AUTO_INCREMENT,
  `id_apertura` int NOT NULL,
  `id_usuario` int NOT NULL,
  `tipo_movimiento` enum('apertura','venta','vuelto','cierre') NOT NULL,
  `concepto` varchar(255) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `id_venta` int DEFAULT NULL,
  `fecha_movimiento` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_movimiento`),
  KEY `id_apertura` (`id_apertura`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_venta` (`id_venta`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `caja_movimientos`
--

INSERT INTO `caja_movimientos` (`id_movimiento`, `id_apertura`, `id_usuario`, `tipo_movimiento`, `concepto`, `monto`, `id_venta`, `fecha_movimiento`) VALUES
(1, 1, 7, 'apertura', 'Apertura de caja', 0.00, NULL, '2026-05-31 18:03:29'),
(2, 1, 7, 'cierre', 'Cierre de caja', 1.00, NULL, '2026-05-31 18:04:01'),
(3, 2, 7, 'apertura', 'Apertura de caja', 0.00, NULL, '2026-05-31 18:18:58'),
(4, 2, 7, 'venta', 'Venta #21', 223.50, 21, '2026-05-31 18:19:11'),
(5, 2, 7, 'cierre', 'Cierre de caja', 223.50, NULL, '2026-05-31 18:19:43'),
(6, 3, 7, 'apertura', 'Apertura de caja', 0.00, NULL, '2026-06-01 08:30:45'),
(7, 3, 7, 'venta', 'Venta #22', 359.96, 22, '2026-06-01 08:31:02'),
(8, 3, 7, 'cierre', 'Cierre de caja', 359.96, NULL, '2026-06-01 08:31:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nombre_categoria` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre_categoria`) VALUES
(1, 'Deportivos'),
(2, 'Casuales'),
(3, 'Formales'),
(4, 'Sandalias'),
(5, 'Botas'),
(6, 'Urbanos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE IF NOT EXISTS `clientes` (
  `id_cliente` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cliente`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `telefono`, `email`, `fecha_registro`) VALUES
(1, 'Consumidor Final', NULL, NULL, '2026-04-24 14:45:46'),
(2, 'Juan Pérez', '70001111', 'juan.perez@gmail.com', '2026-04-24 14:45:46'),
(3, 'María González', '70002222', 'maria.gonzalez@gmail.com', '2026-04-24 14:45:46'),
(4, 'Roberto Molina', '70003333', 'roberto.molina@gmail.com', '2026-04-24 14:45:46'),
(5, 'Daniela Flores', '70004444', 'daniela.flores@gmail.com', '2026-04-24 14:45:46'),
(6, 'Andrea Rivas', '70005555', 'andrea.rivas@gmail.com', '2026-04-24 14:45:46'),
(7, 'Carlos Mejía', '70006666', 'carlos.mejia@gmail.com', '2026-04-24 14:45:46'),
(8, 'Paola Rodríguez', '70007777', 'paola.rodriguez@gmail.com', '2026-04-24 14:45:46'),
(9, 'Kevin Duarte', '70008888', 'kevin.duarte@gmail.com', '2026-04-24 14:45:46'),
(10, 'Lucía Portillo', '70009999', 'lucia.portillo@gmail.com', '2026-04-24 14:45:46'),
(11, 'Erick Hernández', '70110001', 'erick.hernandez@gmail.com', '2026-04-24 14:45:46'),
(12, 'Sonia Castillo', '70110002', 'sonia.castillo@gmail.com', '2026-04-24 14:45:46'),
(13, 'Mauricio Beltrán', '70110003', 'mauricio.beltran@gmail.com', '2026-04-24 14:45:46'),
(14, 'Patricia Orellana', '70110004', 'patricia.orellana@gmail.com', '2026-04-24 14:45:46'),
(15, 'Gabriel Chávez', '70110005', 'gabriel.chavez@gmail.com', '2026-04-24 14:45:46'),
(16, 'jeremias', '23423123', '', '2026-04-24 17:29:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

DROP TABLE IF EXISTS `compras`;
CREATE TABLE IF NOT EXISTS `compras` (
  `id_compra` int NOT NULL AUTO_INCREMENT,
  `id_proveedor` int NOT NULL,
  `fecha_compra` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_compra`),
  KEY `id_proveedor` (`id_proveedor`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id_compra`, `id_proveedor`, `fecha_compra`) VALUES
(1, 1, '2026-03-03 15:10:00'),
(2, 2, '2026-03-10 17:40:00'),
(3, 3, '2026-03-18 21:15:00'),
(4, 5, '2026-04-01 14:25:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_compra`
--

DROP TABLE IF EXISTS `detalle_compra`;
CREATE TABLE IF NOT EXISTS `detalle_compra` (
  `id_detalle_compra` int NOT NULL AUTO_INCREMENT,
  `id_compra` int NOT NULL,
  `id_variante` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_detalle_compra`),
  KEY `id_compra` (`id_compra`),
  KEY `id_variante` (`id_variante`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_compra`
--

INSERT INTO `detalle_compra` (`id_detalle_compra`, `id_compra`, `id_variante`, `cantidad`, `precio_unitario`) VALUES
(1, 1, 1, 12, 62.00),
(2, 1, 2, 12, 62.00),
(3, 1, 3, 10, 52.00),
(4, 2, 5, 8, 56.00),
(5, 2, 6, 8, 57.00),
(6, 2, 12, 10, 49.00),
(7, 3, 9, 10, 50.00),
(8, 3, 10, 10, 50.00),
(9, 4, 17, 6, 64.00),
(10, 4, 18, 6, 64.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
--

DROP TABLE IF EXISTS `detalle_venta`;
CREATE TABLE IF NOT EXISTS `detalle_venta` (
  `id_detalle_venta` int NOT NULL AUTO_INCREMENT,
  `id_venta` int NOT NULL,
  `id_variante` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_detalle_venta`),
  KEY `id_venta` (`id_venta`),
  KEY `id_variante` (`id_variante`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_venta`
--

INSERT INTO `detalle_venta` (`id_detalle_venta`, `id_venta`, `id_variante`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 1, 2, 89.99, 179.98),
(2, 2, 4, 2, 76.00, 152.00),
(3, 3, 7, 1, 69.00, 69.00),
(4, 4, 8, 1, 69.00, 69.00),
(5, 5, 13, 1, 84.99, 84.99),
(6, 5, 14, 1, 84.99, 84.99),
(7, 6, 15, 2, 73.40, 146.80),
(8, 7, 23, 1, 77.30, 77.30),
(9, 9, 11, 1, 68.90, 68.90),
(10, 9, 3, 1, 74.50, 74.50),
(11, 10, 17, 2, 92.00, 184.00),
(12, 11, 11, 1, 68.90, 68.90),
(13, 11, 22, 1, 49.90, 49.90),
(14, 12, 5, 2, 80.00, 160.00),
(15, 13, 9, 1, 72.00, 72.00),
(16, 14, 1, 1, 89.99, 89.99),
(17, 15, 2, 1, 89.99, 89.99),
(18, 16, 23, 1, 77.30, 77.30),
(19, 17, 25, 15, 85.04, 1275.60),
(20, 18, 1, 1, 89.99, 89.99),
(21, 19, 8, 6, 69.00, 414.00),
(22, 20, 1, 2, 89.99, 179.98),
(23, 21, 3, 3, 74.50, 223.50),
(24, 22, 1, 4, 89.99, 359.96);

--
-- Disparadores `detalle_venta`
--
DROP TRIGGER IF EXISTS `actualizar_stock_venta`;
DELIMITER $$
CREATE TRIGGER `actualizar_stock_venta` AFTER INSERT ON `detalle_venta` FOR EACH ROW BEGIN
    UPDATE producto_variante 
    SET stock = stock - NEW.cantidad
    WHERE id_variante = NEW.id_variante;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `verificar_caja_abierta`;
DELIMITER $$
CREATE TRIGGER `verificar_caja_abierta` BEFORE INSERT ON `detalle_venta` FOR EACH ROW BEGIN
    DECLARE caja_abierta INT;
    
    -- Obtener el id_usuario de la venta
    SELECT id_usuario INTO @id_usuario FROM ventas WHERE id_venta = NEW.id_venta;
    
    -- Verificar si el usuario tiene caja abierta
    SELECT COUNT(*) INTO caja_abierta 
    FROM caja_aperturas 
    WHERE id_usuario = @id_usuario AND estado = 'abierta';
    
    IF caja_abierta = 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'El usuario debe tener una caja abierta para realizar ventas';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `devoluciones_venta`
--

DROP TABLE IF EXISTS `devoluciones_venta`;
CREATE TABLE IF NOT EXISTS `devoluciones_venta` (
  `id_devolucion` int NOT NULL AUTO_INCREMENT,
  `id_venta` int NOT NULL,
  `id_detalle_venta` int NOT NULL,
  `id_variante` int NOT NULL,
  `cantidad_devuelta` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `total_devuelto` decimal(10,2) NOT NULL,
  `fecha_devolucion` datetime NOT NULL,
  `id_usuario` int NOT NULL,
  `motivo` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_devolucion`),
  KEY `id_venta` (`id_venta`),
  KEY `id_detalle_venta` (`id_detalle_venta`),
  KEY `id_variante` (`id_variante`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `devoluciones_venta`
--

INSERT INTO `devoluciones_venta` (`id_devolucion`, `id_venta`, `id_detalle_venta`, `id_variante`, `cantidad_devuelta`, `precio_unitario`, `total_devuelto`, `fecha_devolucion`, `id_usuario`, `motivo`, `created_at`) VALUES
(1, 20, 22, 1, 1, 89.99, 89.99, '2026-05-31 15:43:31', 1, NULL, '2026-05-31 21:43:31'),
(2, 20, 22, 1, 1, 89.99, 89.99, '2026-05-31 15:52:37', 1, NULL, '2026-05-31 21:52:37'),
(3, 19, 21, 8, 4, 69.00, 276.00, '2026-05-31 16:03:10', 1, NULL, '2026-05-31 22:03:10'),
(4, 18, 20, 1, 1, 89.99, 89.99, '2026-05-31 16:05:17', 1, NULL, '2026-05-31 22:05:17'),
(5, 4, 4, 8, 1, 69.00, 69.00, '2026-05-31 16:10:20', 1, NULL, '2026-05-31 22:10:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_logs`
--

DROP TABLE IF EXISTS `historial_logs`;
CREATE TABLE IF NOT EXISTS `historial_logs` (
  `id_log` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `tabla_afectada` varchar(50) DEFAULT NULL,
  `registro_id` int DEFAULT NULL,
  `detalle` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `idx_usuario` (`id_usuario`),
  KEY `idx_accion` (`accion`),
  KEY `idx_fecha` (`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `historial_logs`
--

INSERT INTO `historial_logs` (`id_log`, `id_usuario`, `nombre_usuario`, `accion`, `tabla_afectada`, `registro_id`, `detalle`, `ip_address`, `fecha`) VALUES
(1, 1, 'Diego', 'editar', 'producto_variante', 1, 'Producto: Nike Air Max Pulse | Talla: 39 | Color: Gris | SKU: 9001001 | Precio: $89.99 | Stock: 21', '::1', '2026-06-01 10:00:56'),
(2, 1, 'Diego', 'login', 'usuarios', 1, 'Inicio de sesión exitoso', '::1', '2026-06-01 14:25:23'),
(3, 1, 'Diego', 'login', 'usuarios', 1, 'Inicio de sesión exitoso', '::1', '2026-06-01 14:40:34'),
(4, 1, 'Diego', 'logout', 'sesion', 1, 'Cierre de sesión del usuario: Diego', '::1', '2026-06-01 14:50:06'),
(5, 1, 'Diego', 'logout', 'sesion', 1, 'Cierre de sesión', '::1', '2026-06-01 14:50:06'),
(6, 7, 'Jose', 'login', 'usuarios', 7, 'Inicio de sesión exitoso', '::1', '2026-06-01 14:50:22'),
(7, 7, 'Jose', 'logout', 'sesion', 7, 'Cierre de sesión del usuario: Jose', '::1', '2026-06-01 14:50:25'),
(8, 7, 'Jose', 'logout', 'sesion', 7, 'Cierre de sesión', '::1', '2026-06-01 14:50:25'),
(9, 1, 'Diego', 'login', 'usuarios', 1, 'Inicio de sesión exitoso', '::1', '2026-06-01 14:50:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas`
--

DROP TABLE IF EXISTS `marcas`;
CREATE TABLE IF NOT EXISTS `marcas` (
  `id_marca` int NOT NULL AUTO_INCREMENT,
  `nombre_marca` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_marca`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marcas`
--

INSERT INTO `marcas` (`id_marca`, `nombre_marca`) VALUES
(1, 'Nike'),
(2, 'Adidas'),
(3, 'Puma'),
(4, 'Converse'),
(5, 'Vans'),
(6, 'Skechers'),
(7, 'New Balance'),
(8, 'Reebok');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodos_pago`
--

DROP TABLE IF EXISTS `metodos_pago`;
CREATE TABLE IF NOT EXISTS `metodos_pago` (
  `id_metodo_pago` int NOT NULL AUTO_INCREMENT,
  `nombre_metodo` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_metodo_pago`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `metodos_pago`
--

INSERT INTO `metodos_pago` (`id_metodo_pago`, `nombre_metodo`) VALUES
(1, 'Efectivo'),
(2, 'Tarjeta'),
(3, 'Transferencia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

DROP TABLE IF EXISTS `productos`;
CREATE TABLE IF NOT EXISTS `productos` (
  `id_producto` int NOT NULL AUTO_INCREMENT,
  `id_proveedor` int DEFAULT NULL,
  `nombre_producto` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `id_marca` int DEFAULT NULL,
  `id_categoria` int DEFAULT NULL,
  `estado` enum('activo','inactivo') COLLATE utf8mb4_general_ci DEFAULT 'activo',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_producto`),
  KEY `id_marca` (`id_marca`),
  KEY `id_categoria` (`id_categoria`),
  KEY `id_proveedor` (`id_proveedor`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `id_proveedor`, `nombre_producto`, `descripcion`, `id_marca`, `id_categoria`, `estado`, `fecha_registro`) VALUES
(1, 1, 'Nike Air Max Pulse', '', 1, 1, 'activo', '2026-04-24 14:45:46'),
(2, 1, 'Adidas Grand Court', '', 2, 2, 'activo', '2026-04-24 14:45:46'),
(3, 2, 'Puma Velocity Nitro', '', 3, 1, 'activo', '2026-04-24 14:45:46'),
(4, 2, 'Converse Chuck Taylor', '', 4, 6, 'activo', '2026-04-24 14:45:46'),
(5, 3, 'Vans Old Skool', 'Sneaker urbano de suela vulcanizada y look skater.', 5, 6, 'activo', '2026-04-24 14:45:46'),
(6, 3, 'Skechers Arch Fit', 'Zapatilla cómoda para caminatas prolongadas.', 6, 2, 'activo', '2026-04-24 14:45:46'),
(7, 4, 'New Balance 574', 'Diseño retro con soporte y estilo casual.', 7, 2, 'activo', '2026-04-24 14:45:46'),
(8, 4, 'Reebok Club C', '', 8, 2, 'activo', '2026-04-24 14:45:46'),
(9, 5, 'Oxford Executive', '', 8, 3, 'activo', '2026-04-24 14:45:46'),
(10, 5, 'Botín Urban Leather', '', 8, 5, 'activo', '2026-04-24 14:45:46'),
(11, 1, 'Sandalia Comfort Plus', '', 2, 4, 'activo', '2026-04-24 14:45:46'),
(12, 2, 'Runner Street Flex', '', 3, 1, 'activo', '2026-04-24 14:45:46'),
(13, 2, 'Nike New Slides', '', 1, 4, 'activo', '2026-04-24 15:13:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_variante`
--

DROP TABLE IF EXISTS `producto_variante`;
CREATE TABLE IF NOT EXISTS `producto_variante` (
  `id_variante` int NOT NULL AUTO_INCREMENT,
  `id_producto` int NOT NULL,
  `talla` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `color` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `codigo_barras` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `stock` int DEFAULT '0',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_general_ci DEFAULT 'activo',
  PRIMARY KEY (`id_variante`),
  UNIQUE KEY `codigo_barras` (`codigo_barras`),
  KEY `id_producto` (`id_producto`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto_variante`
--

INSERT INTO `producto_variante` (`id_variante`, `id_producto`, `talla`, `color`, `codigo_barras`, `precio_venta`, `stock`, `estado`) VALUES
(1, 1, '39', 'Gris', '9001001', 89.99, 21, 'activo'),
(2, 1, '40', 'Blanco', '90010002', 89.99, 23, 'activo'),
(3, 2, '38', 'Blanco', '9002001', 74.50, 24, 'activo'),
(4, 2, '39', 'Negro', '9002002', 76.00, 17, 'activo'),
(5, 3, '40', 'Azul', '9003001', 79.90, 15, 'activo'),
(6, 3, '41', 'Negro', '90030002', 81.50, 14, 'activo'),
(7, 4, '39', 'Rojo', '90040001', 69.00, 14, 'activo'),
(8, 4, '40', 'Negro', '9004002', 69.00, 15, 'activo'),
(9, 5, '39', 'Negro', '90050001', 72.00, 9, 'activo'),
(10, 5, '40', 'Negro', '90050002', 72.00, 8, 'activo'),
(11, 6, '40', 'Gris', '90060001', 68.90, 20, 'activo'),
(12, 6, '41', 'Negro', '90060002', 68.90, 18, 'activo'),
(13, 7, '40', 'Azul', '90070001', 84.99, 15, 'activo'),
(14, 7, '41', 'Gris', '90070002', 84.99, 12, 'activo'),
(15, 8, '39', 'Blanco', '90080001', 73.40, 18, 'activo'),
(16, 8, '40', 'Negro', '9008002', 73.40, 14, 'activo'),
(17, 9, '41', 'Café', '9009001', 92.00, 9, 'activo'),
(18, 9, '42', 'Negro', '9009002', 92.00, 9, 'activo'),
(19, 10, '40', 'Café', '9010001', 88.75, 7, 'activo'),
(20, 10, '41', 'Negro', '9010002', 88.75, 6, 'activo'),
(21, 11, '38', 'Beige', '9011001', 49.90, 25, 'activo'),
(22, 11, '39', 'Negro', '9011002', 49.90, 20, 'activo'),
(23, 12, '40', 'Verde', '9012001', 77.30, 10, 'activo'),
(25, 13, '38', 'Verde', '2348923', 85.04, 31, 'activo'),
(26, 4, '40', 'Rojos', '9004003', 70.00, 10, 'activo');

--
-- Disparadores `producto_variante`
--
DROP TRIGGER IF EXISTS `sincronizar_estado_producto`;
DELIMITER $$
CREATE TRIGGER `sincronizar_estado_producto` AFTER UPDATE ON `producto_variante` FOR EACH ROW BEGIN
    DECLARE total_activas INT;
    
    SELECT COUNT(*) INTO total_activas 
    FROM producto_variante 
    WHERE id_producto = NEW.id_producto AND estado = 'activo';
    
    IF total_activas = 0 THEN
        UPDATE productos SET estado = 'inactivo' WHERE id_producto = NEW.id_producto;
    ELSE
        UPDATE productos SET estado = 'activo' WHERE id_producto = NEW.id_producto;
    END IF;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `sincronizar_estado_producto_delete`;
DELIMITER $$
CREATE TRIGGER `sincronizar_estado_producto_delete` AFTER DELETE ON `producto_variante` FOR EACH ROW BEGIN
    DECLARE total_activas INT;
    
    SELECT COUNT(*) INTO total_activas 
    FROM producto_variante 
    WHERE id_producto = OLD.id_producto AND estado = 'activo';
    
    IF total_activas = 0 THEN
        UPDATE productos SET estado = 'inactivo' WHERE id_producto = OLD.id_producto;
    END IF;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `sincronizar_estado_producto_insert`;
DELIMITER $$
CREATE TRIGGER `sincronizar_estado_producto_insert` AFTER INSERT ON `producto_variante` FOR EACH ROW BEGIN
    UPDATE productos SET estado = 'activo' WHERE id_producto = NEW.id_producto;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id_proveedor` int NOT NULL AUTO_INCREMENT,
  `nombre_empresa` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `contacto_nombre` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_proveedor`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor`, `nombre_empresa`, `contacto_nombre`, `telefono`, `email`, `fecha_registro`) VALUES
(1, 'Distribuidora Deportiva SA', 'Carlos Ramírez', '77771111', 'ventas@deportiva.com', '2026-04-24 14:45:46'),
(2, 'Importadora Shoes', 'Ana López', '77772222', 'contacto@importshoes.com', '2026-04-24 14:45:46'),
(3, 'Calzado Centroamericano', 'Luis Martínez', '77773333', 'ventas@calzadoca.com', '2026-04-24 14:45:46'),
(4, 'Urban Footwear Group', 'Sofía Herrera', '77774444', 'hola@urbanfootwear.com', '2026-04-24 14:45:46'),
(5, 'Premium Leather Supply', 'Jorge Meléndez', '77775555', 'comercial@premiumleather.com', '2026-04-24 14:45:46'),
(6, 'Mayorista Andina Shoes', 'Raúl Torres', '77776661', 'ventas@andinashoes.com', '2026-04-24 14:45:46'),
(7, 'Global Sneakers Hub', 'Mónica Fuentes', '77776662', 'compras@globalsneakers.com', '2026-04-24 14:45:46'),
(8, 'Leather Craft Imports', 'Héctor Salinas', '77776663', 'contacto@leathercraft.com', '2026-04-24 14:45:46'),
(9, 'Pasarela Calzado Pro', 'Valeria Méndez', '77776664', 'pro@pasarelacalzado.com', '2026-04-24 14:45:46'),
(10, 'StepOne Distribuciones', 'Fernando Lima', '77776665', 'ventas@stepone.com', '2026-04-24 14:45:46'),
(11, 'nike2', 'nikeppl', '12389012', 'nike@mail.com', '2026-04-24 17:32:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `nombre_usuario` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `rol` enum('admin','cajero') COLLATE utf8mb4_general_ci DEFAULT 'cajero',
  `id_caja` int DEFAULT NULL,
  `monto_caja` decimal(10,2) NOT NULL DEFAULT '0.00',
  `caja_activa` int DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  KEY `fk_usuarios_caja` (`id_caja`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre_usuario`, `password_hash`, `rol`, `id_caja`, `monto_caja`, `caja_activa`, `fecha_creacion`) VALUES
(1, 'Diego', '$2y$10$pDJKVoW8dG.njo/arR0jxOtVqk5y2dlABvoVuftAbGrIbevG.Xkn2', 'admin', NULL, 0.00, NULL, '2026-04-24 14:45:46'),
(2, 'Jeremias', '$2y$10$hW5UeshaQFPelXp7HMaBe.jer6jHsTG/kR7qSn/kP3B04eMwFgvgC', 'cajero', 2, 0.00, NULL, '2026-04-24 14:56:40'),
(3, 'veronica', '$2y$10$eIqiihAySLAP2i4SkcNg.OHZ5g61sig76Gs48DE3/nVmFauumspni', 'admin', NULL, 0.00, NULL, '2026-04-24 15:43:39'),
(4, 'Fredis', '$2y$10$N2ZRx4tOlqWBuubpHvgwZud0Z7uOuS88xfmSZPehGL7Yf5lIebaVS', 'cajero', 1, 0.00, NULL, '2026-04-24 15:44:13'),
(5, 'Eduardo', '$2y$10$bx3Lz.hRUwPsmRQeCHRt4ufIg8Yx8RUxXUNBmNkYkfoii4X4FZkNW', 'cajero', 5, 200.00, NULL, '2026-04-24 15:46:40'),
(6, 'jere', '$2y$10$y6wu5TUo2CWgzyFxxKIy8e3ytKEV3ozpfa0ZbKM9lDW0WYpSfe6mq', 'cajero', 3, 0.00, NULL, '2026-04-24 17:31:25'),
(7, 'Jose', '$2y$10$5wAfwQ7mydcxcUAwuG3FIeVAXtnTP7u9lvKxOONcosOQhRYwyByBm', 'cajero', 6, 0.00, NULL, '2026-05-28 16:20:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

DROP TABLE IF EXISTS `ventas`;
CREATE TABLE IF NOT EXISTS `ventas` (
  `id_venta` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_cliente` int DEFAULT NULL,
  `id_metodo_pago` int NOT NULL,
  `fecha_venta` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `total_venta` decimal(10,2) NOT NULL,
  `estado` enum('completada','anulada') COLLATE utf8mb4_general_ci DEFAULT 'completada',
  PRIMARY KEY (`id_venta`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_metodo_pago` (`id_metodo_pago`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id_venta`, `id_usuario`, `id_cliente`, `id_metodo_pago`, `fecha_venta`, `total_venta`, `estado`) VALUES
(1, 1, 2, 1, '2026-04-22 16:15:00', 179.98, 'completada'),
(2, 1, 3, 2, '2026-04-21 18:40:00', 152.00, 'completada'),
(3, 1, 1, 1, '2026-04-11 15:20:00', 69.00, 'completada'),
(4, 1, 5, 3, '2026-04-11 22:05:00', 69.00, 'completada'),
(5, 1, 4, 2, '2026-04-12 19:35:00', 171.98, 'completada'),
(6, 1, 7, 1, '2026-04-13 23:55:00', 146.80, 'completada'),
(7, 1, 8, 3, '2026-04-15 17:30:00', 77.30, 'completada'),
(8, 1, 6, 1, '2026-04-16 20:10:00', 0.00, 'anulada'),
(9, 1, 11, 1, '2026-02-05 16:20:00', 149.00, 'completada'),
(10, 1, 12, 2, '2026-02-09 21:45:00', 184.00, 'completada'),
(11, 1, 13, 3, '2026-02-14 18:10:00', 98.80, 'completada'),
(12, 1, 14, 1, '2026-02-21 23:05:00', 160.00, 'completada'),
(13, 1, 15, 2, '2026-02-27 17:55:00', 72.00, 'completada'),
(14, 1, NULL, 2, '2026-04-24 14:55:09', 89.99, 'completada'),
(15, 2, NULL, 2, '2026-04-24 14:57:25', 89.99, 'completada'),
(16, 1, NULL, 1, '2026-04-24 15:32:09', 77.30, 'completada'),
(17, 1, NULL, 2, '2026-04-24 15:38:02', 1275.60, 'completada'),
(18, 1, NULL, 2, '2026-04-24 17:40:05', 53.99, 'completada'),
(19, 1, NULL, 2, '2026-04-24 17:41:06', 414.00, 'completada'),
(20, 1, NULL, 1, '2026-05-29 21:06:45', 179.98, 'completada'),
(21, 7, NULL, 2, '2026-06-01 00:19:11', 223.50, 'completada'),
(22, 7, NULL, 2, '2026-06-01 14:31:02', 359.96, 'completada');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
