USE pos_zapateria;

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

-- PRODUCTOS
INSERT INTO productos (id_proveedor, nombre_producto, descripcion, id_marca, id_categoria) VALUES
(1, 'Nike Air Max', 'Zapatilla deportiva con amortiguación Air', 1, 1),
(2, 'Adidas Superstar', 'Zapatilla clásica con puntera de goma', 2, 2),
(3, 'Puma Runner', 'Zapatilla ligera para correr', 3, 1),
(4, 'Converse Chuck Taylor', 'Zapatilla clásica de lona', 4, 2),
(2, 'Vans Old Skool', 'Zapatilla de skate clásica', 5, 2);

-- VARIANTES DE PRODUCTO
INSERT INTO producto_variante (id_producto, talla, color, codigo_barras, precio_venta, stock) VALUES
(1, '38', 'Negro', '1000001', 85.00, 10),
(1, '39', 'Negro', '1000002', 85.00, 8),
(1, '40', 'Blanco', '1000003', 90.00, 6),

(2, '38', 'Blanco', '2000001', 75.00, 12),
(2, '39', 'Blanco', '2000002', 75.00, 9),
(2, '40', 'Negro', '2000003', 78.00, 7),

(3, '39', 'Azul', '3000001', 65.00, 15),
(3, '40', 'Azul', '3000002', 65.00, 11),

(4, '38', 'Rojo', '4000001', 60.00, 14),
(4, '39', 'Negro', '4000002', 60.00, 10),

(5, '39', 'Negro', '5000001', 70.00, 9),
(5, '40', 'Negro', '5000002', 70.00, 5);

-- PROVEEDORES
INSERT INTO proveedores (nombre_empresa, contacto_nombre, telefono, email) VALUES
('Distribuidora Deportiva SA', 'Carlos Ramirez', '7777-1111', 'ventas@deportiva.com'),
('Importadora Shoes', 'Ana Lopez', '7777-2222', 'contacto@importshoes.com'),
('Calzado Centroamericano', 'Luis Martinez', '7777-3333', 'ventas@calzadoca.com');

-- CLIENTES
INSERT INTO clientes (nombre, telefono, email) VALUES
('Consumidor Final', NULL, NULL),
('Juan Perez', '7000-1111', 'juan@gmail.com'),
('Maria Gonzalez', '7000-2222', 'maria@gmail.com');

-- USUARIOS
INSERT INTO usuarios (nombre_usuario, password_hash, rol) VALUES
('admin', '$2y$10$abcdefghijklmnopqrstuv', 'admin'),
('cajero1', '$2y$10$abcdefghijklmnopqrstuv', 'cajero');


-- COMPRA DE EJEMPLO
INSERT INTO compras (id_proveedor) VALUES
(1);

-- detalle compra
INSERT INTO detalle_compra (id_compra, id_variante, cantidad, precio_unitario) VALUES
(1, 1, 10, 60.00),
(1, 2, 10, 60.00),
(1, 4, 12, 55.00);


-- PASO A: hacer una venta
INSERT INTO ventas (id_usuario, id_cliente, id_metodo_pago, total_venta) 
VALUES (1, 1, 1, 85.00);

-- PASO B: Registrar el detalle de la venta
INSERT INTO detalle_venta (id_venta, id_variante, cantidad, precio_unitario, subtotal) 
VALUES (1, 1, 1, 85.00, 85.00);

-- DATOS INICIALES METODOS DE PAGO
INSERT INTO metodos_pago (nombre_metodo) VALUES
('Efectivo'),
('Tarjeta'),
('Transferencia');
