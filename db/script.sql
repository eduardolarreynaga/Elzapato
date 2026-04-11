-- BASE DE DATOS ELZAPATO PARA UN POS

DROP DATABASE IF EXISTS pos_zapateria;

CREATE DATABASE pos_zapateria CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_general_ci;
USE pos_zapateria;

-- TABLA MARCAS
CREATE TABLE marcas (
    id_marca INT AUTO_INCREMENT PRIMARY KEY,
    nombre_marca VARCHAR(100) NOT NULL
);

-- TABLA CATEGORIAS
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL
);

-- TABLA PROVEEDORES
CREATE TABLE proveedores (
    id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
    nombre_empresa VARCHAR(150) NOT NULL,
    contacto_nombre VARCHAR(100),
    telefono VARCHAR(20),
    email VARCHAR(100),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TABLA PRODUCTOS
CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    id_proveedor INT,
    nombre_producto VARCHAR(150) NOT NULL,
    descripcion TEXT,
    id_marca INT,
    id_categoria INT,
    estado ENUM('activo','inactivo') DEFAULT 'activo',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_marca) REFERENCES marcas(id_marca),
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria),
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor)
);

-- VARIANTES DE PRODUCTO
CREATE TABLE producto_variante (
    id_variante INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    talla VARCHAR(10),
    color VARCHAR(50),
    codigo_barras VARCHAR(100) UNIQUE,
    precio_venta DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    estado ENUM('activo','inactivo') DEFAULT 'activo',

    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

-- COMPRAS
CREATE TABLE compras (
    id_compra INT AUTO_INCREMENT PRIMARY KEY,
    id_proveedor INT NOT NULL,
    fecha_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor)
);

-- DETALLE COMPRA
CREATE TABLE detalle_compra (
    id_detalle_compra INT AUTO_INCREMENT PRIMARY KEY,
    id_compra INT NOT NULL,
    id_variante INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (id_compra) REFERENCES compras(id_compra),
    FOREIGN KEY (id_variante) REFERENCES producto_variante(id_variante)
);

-- CLIENTES
CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150),
    telefono VARCHAR(20),
    email VARCHAR(100)
);

-- METODOS DE PAGO
CREATE TABLE metodos_pago (
    id_metodo_pago INT AUTO_INCREMENT PRIMARY KEY,
    nombre_metodo VARCHAR(50) NOT NULL
);

-- USUARIOS
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('admin','cajero') DEFAULT 'cajero',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- VENTAS
CREATE TABLE ventas (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_cliente INT,
    id_metodo_pago INT NOT NULL,
    fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_venta DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_metodo_pago) REFERENCES metodos_pago(id_metodo_pago)
);

-- DETALLE VENTA
CREATE TABLE detalle_venta (
    id_detalle_venta INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_variante INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
    FOREIGN KEY (id_variante) REFERENCES producto_variante(id_variante)
);

-- ============================================
-- TRIGGERS CORREGIDOS
-- ============================================

-- TRIGGER 1: Actualizar stock después de una venta
DELIMITER $$
CREATE TRIGGER `actualizar_stock_venta` 
AFTER INSERT ON `detalle_venta` 
FOR EACH ROW
BEGIN
    UPDATE producto_variante 
    SET stock = stock - NEW.cantidad
    WHERE id_variante = NEW.id_variante;
END$$
DELIMITER ;

-- TRIGGER 2: Sincronizar estado del producto al actualizar variante
DELIMITER $$
CREATE TRIGGER `sincronizar_estado_producto` 
AFTER UPDATE ON `producto_variante`
FOR EACH ROW
BEGIN
    DECLARE total_activas INT;
    
    SELECT COUNT(*) INTO total_activas 
    FROM producto_variante 
    WHERE id_producto = NEW.id_producto AND estado = 'activo';
    
    IF total_activas = 0 THEN
        UPDATE productos SET estado = 'inactivo' WHERE id_producto = NEW.id_producto;
    ELSE
        UPDATE productos SET estado = 'activo' WHERE id_producto = NEW.id_producto;
    END IF;
END$$
DELIMITER ;

-- TRIGGER 3: Activar producto al insertar nueva variante
DELIMITER $$
CREATE TRIGGER `sincronizar_estado_producto_insert` 
AFTER INSERT ON `producto_variante`
FOR EACH ROW
BEGIN
    UPDATE productos SET estado = 'activo' WHERE id_producto = NEW.id_producto;
END$$
DELIMITER ;

-- TRIGGER 4: Sincronizar estado al eliminar una variante
DELIMITER $$
CREATE TRIGGER `sincronizar_estado_producto_delete` 
AFTER DELETE ON `producto_variante`
FOR EACH ROW
BEGIN
    DECLARE total_activas INT;
    
    SELECT COUNT(*) INTO total_activas 
    FROM producto_variante 
    WHERE id_producto = OLD.id_producto AND estado = 'activo';
    
    IF total_activas = 0 THEN
        UPDATE productos SET estado = 'inactivo' WHERE id_producto = OLD.id_producto;
    END IF;
END$$
DELIMITER ;