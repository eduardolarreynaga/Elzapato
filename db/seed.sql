USE pos_zapateria;

-- ============================================
-- INSERTAR DATOS
-- ============================================

-- MARCAS
INSERT INTO marcas (nombre_marca) VALUES
('Nike'),
('Adidas'),
('Puma'),
('Converse'),
('Vans');

-- CATEGORIAS
INSERT INTO categorias (nombre_categoria) VALUES
('Deportivos'),
('Casuales'),
('Formales'),
('Sandalias'),
('Botas');

-- PROVEEDORES
INSERT INTO proveedores (nombre_empresa, contacto_nombre, telefono, email) VALUES
('Distribuidora Deportiva SA', 'Carlos Ramirez', '7777-1111', 'ventas@deportiva.com'),
('Importadora Shoes', 'Ana Lopez', '7777-2222', 'contacto@importshoes.com'),
('Calzado Centroamericano', 'Luis Martinez', '7777-3333', 'ventas@calzadoca.com');

-- METODOS DE PAGO
INSERT INTO metodos_pago (nombre_metodo) VALUES
('Efectivo'),
('Tarjeta'),
('Transferencia');

-- CLIENTES
INSERT INTO clientes (nombre, telefono, email) VALUES
('Consumidor Final', NULL, NULL),
('Juan Perez', '7000-1111', 'juan@gmail.com'),
('Maria Gonzalez', '7000-2222', 'maria@gmail.com');

-- USUARIOS (contraseña: admin123 / cajero123)
INSERT INTO usuarios (nombre_usuario, password_hash, rol) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('cajero1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cajero');

-- PRODUCTOS (primero sin id_proveedor para evitar errores)
INSERT INTO productos (nombre_producto, descripcion, id_marca, id_categoria) VALUES
('Nike Air Max', 'Zapatilla deportiva con amortiguación Air', 1, 1),
('Adidas Superstar', 'Zapatilla clásica con puntera de goma', 2, 2),
('Puma Runner', 'Zapatilla ligera para correr', 3, 1),
('Converse Chuck Taylor', 'Zapatilla clásica de lona', 4, 2),
('Vans Old Skool', 'Zapatilla de skate clásica', 5, 2);

-- Actualizar id_proveedor después de insertar productos
UPDATE productos SET id_proveedor = 1 WHERE id_producto IN (1, 2);
UPDATE productos SET id_proveedor = 2 WHERE id_producto IN (3, 4);
UPDATE productos SET id_proveedor = 3 WHERE id_producto = 5;

-- VARIANTES DE PRODUCTO (con estado incluido)
INSERT INTO producto_variante (id_producto, talla, color, codigo_barras, precio_venta, stock, estado) VALUES
(1, '38', 'Negro', '1000001', 85.00, 10, 'activo'),
(1, '39', 'Negro', '1000002', 85.00, 8, 'activo'),
(1, '40', 'Blanco', '1000003', 90.00, 6, 'activo'),
(2, '38', 'Blanco', '2000001', 75.00, 12, 'activo'),
(2, '39', 'Blanco', '2000002', 75.00, 9, 'activo'),
(2, '40', 'Negro', '2000003', 78.00, 7, 'activo'),
(3, '39', 'Azul', '3000001', 65.00, 15, 'activo'),
(3, '40', 'Azul', '3000002', 65.00, 11, 'activo'),
(4, '38', 'Rojo', '4000001', 60.00, 14, 'activo'),
(4, '39', 'Negro', '4000002', 60.00, 10, 'activo'),
(5, '39', 'Negro', '5000001', 70.00, 9, 'activo'),
(5, '40', 'Negro', '5000002', 70.00, 5, 'activo');

-- COMPRAS
INSERT INTO compras (id_proveedor) VALUES (1);

-- DETALLE COMPRA
INSERT INTO detalle_compra (id_compra, id_variante, cantidad, precio_unitario) VALUES
(1, 1, 10, 60.00),
(1, 2, 10, 60.00),
(1, 4, 12, 55.00);

-- VENTAS
INSERT INTO ventas (id_usuario, id_cliente, id_metodo_pago, total_venta) VALUES (1, 1, 1, 85.00);

-- DETALLE VENTA
INSERT INTO detalle_venta (id_venta, id_variante, cantidad, precio_unitario, subtotal) VALUES (1, 1, 1, 85.00, 85.00);