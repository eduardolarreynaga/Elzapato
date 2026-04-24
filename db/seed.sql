USE pos_zapateria;

-- ============================================
-- SEED DEMO EL ZAPATO
-- Usuario único de acceso:
--   usuario: admin
--   contraseña: a123
-- ============================================

-- MARCAS
INSERT INTO marcas (nombre_marca) VALUES
('Nike'),
('Adidas'),
('Puma'),
('Converse'),
('Vans'),
('Skechers'),
('New Balance'),
('Reebok');

-- CATEGORIAS
INSERT INTO categorias (nombre_categoria) VALUES
('Deportivos'),
('Casuales'),
('Formales'),
('Sandalias'),
('Botas'),
('Urbanos');

-- PROVEEDORES
INSERT INTO proveedores (nombre_empresa, contacto_nombre, telefono, email) VALUES
('Distribuidora Deportiva SA', 'Carlos Ramírez', '77771111', 'ventas@deportiva.com'),
('Importadora Shoes', 'Ana López', '77772222', 'contacto@importshoes.com'),
('Calzado Centroamericano', 'Luis Martínez', '77773333', 'ventas@calzadoca.com'),
('Urban Footwear Group', 'Sofía Herrera', '77774444', 'hola@urbanfootwear.com'),
('Premium Leather Supply', 'Jorge Meléndez', '77775555', 'comercial@premiumleather.com');

-- METODOS DE PAGO
INSERT INTO metodos_pago (nombre_metodo) VALUES
('Efectivo'),
('Tarjeta'),
('Transferencia');

-- CAJAS
INSERT INTO cajas (nombre_caja, estado) VALUES
('Caja Principal', 'activa'),
('Caja Norte', 'activa'),
('Caja Express', 'activa'),
('Caja Respaldo', 'inactiva');

-- CLIENTES
INSERT INTO clientes (nombre, telefono, email) VALUES
('Consumidor Final', NULL, NULL),
('Juan Pérez', '70001111', 'juan.perez@gmail.com'),
('María González', '70002222', 'maria.gonzalez@gmail.com'),
('Roberto Molina', '70003333', 'roberto.molina@gmail.com'),
('Daniela Flores', '70004444', 'daniela.flores@gmail.com'),
('Andrea Rivas', '70005555', 'andrea.rivas@gmail.com'),
('Carlos Mejía', '70006666', 'carlos.mejia@gmail.com'),
('Paola Rodríguez', '70007777', 'paola.rodriguez@gmail.com'),
('Kevin Duarte', '70008888', 'kevin.duarte@gmail.com'),
('Lucía Portillo', '70009999', 'lucia.portillo@gmail.com');

-- USUARIOS (solo administrador)
INSERT INTO usuarios (nombre_usuario, password_hash, rol, id_caja) VALUES
('admin', '$2y$12$a03wqz.B58dUSHPyisaDqOLgL4qNWBZlbvScLRbs2r3aPhwqlGCFS', 'admin', NULL);

-- PRODUCTOS
INSERT INTO productos (id_proveedor, nombre_producto, descripcion, id_marca, id_categoria, estado) VALUES
(1, 'Nike Air Max Pulse', 'Zapatilla deportiva con cámara de aire y suela de alto rebote.', 1, 1, 'activo'),
(1, 'Adidas Grand Court', 'Modelo casual de perfil bajo para uso diario.', 2, 2, 'activo'),
(2, 'Puma Velocity Nitro', 'Calzado running ligero con buena amortiguación.', 3, 1, 'activo'),
(2, 'Converse Chuck Taylor', 'Clásico urbano de lona con diseño atemporal.', 4, 6, 'activo'),
(3, 'Vans Old Skool', 'Sneaker urbano de suela vulcanizada y look skater.', 5, 6, 'activo'),
(3, 'Skechers Arch Fit', 'Zapatilla cómoda para caminatas prolongadas.', 6, 2, 'activo'),
(4, 'New Balance 574', 'Diseño retro con soporte y estilo casual.', 7, 2, 'activo'),
(4, 'Reebok Club C', 'Tenis versátil para outfits urbanos y casuales.', 8, 2, 'activo'),
(5, 'Oxford Executive', 'Zapato formal de cuero para oficina y eventos.', 8, 3, 'activo'),
(5, 'Botín Urban Leather', 'Botín de cuero para uso diario y clima fresco.', 8, 5, 'activo'),
(1, 'Sandalia Comfort Plus', 'Sandalia ligera y ergonómica para uso casual.', 2, 4, 'activo'),
(2, 'Runner Street Flex', 'Zapatilla híbrida para ciudad y entrenamiento.', 3, 1, 'activo');

-- VARIANTES DE PRODUCTO
INSERT INTO producto_variante (id_producto, talla, color, codigo_barras, precio_venta, stock, estado) VALUES
(1, '39', 'Negro', '90010001', 89.99, 30, 'activo'),
(1, '40', 'Blanco', '90010002', 89.99, 24, 'activo'),
(2, '38', 'Blanco', '90020001', 74.50, 28, 'activo'),
(2, '39', 'Negro', '90020002', 76.00, 19, 'activo'),
(3, '40', 'Azul', '90030001', 79.90, 17, 'activo'),
(3, '41', 'Negro', '90030002', 81.50, 14, 'activo'),
(4, '39', 'Rojo', '90040001', 69.00, 15, 'activo'),
(4, '40', 'Negro', '90040002', 69.00, 12, 'activo'),
(5, '39', 'Negro', '90050001', 72.00, 10, 'activo'),
(5, '40', 'Negro', '90050002', 72.00, 8, 'activo'),
(6, '40', 'Gris', '90060001', 68.90, 22, 'activo'),
(6, '41', 'Negro', '90060002', 68.90, 18, 'activo'),
(7, '40', 'Azul', '90070001', 84.99, 16, 'activo'),
(7, '41', 'Gris', '90070002', 84.99, 13, 'activo'),
(8, '39', 'Blanco', '90080001', 73.40, 20, 'activo'),
(8, '40', 'Negro', '90080002', 73.40, 14, 'activo'),
(9, '41', 'Café', '90090001', 92.00, 11, 'activo'),
(9, '42', 'Negro', '90090002', 92.00, 9, 'activo'),
(10, '40', 'Café', '90100001', 88.75, 7, 'activo'),
(10, '41', 'Negro', '90100002', 88.75, 6, 'activo'),
(11, '38', 'Beige', '90110001', 49.90, 25, 'activo'),
(11, '39', 'Negro', '90110002', 49.90, 21, 'activo'),
(12, '40', 'Verde', '90120001', 77.30, 12, 'activo'),
(12, '41', 'Negro', '90120002', 77.30, 5, 'activo');

-- COMPRAS
INSERT INTO compras (id_proveedor, fecha_compra) VALUES
(1, '2026-03-03 09:10:00'),
(2, '2026-03-10 11:40:00'),
(3, '2026-03-18 15:15:00'),
(5, '2026-04-01 08:25:00');

-- DETALLE COMPRA
INSERT INTO detalle_compra (id_compra, id_variante, cantidad, precio_unitario) VALUES
(1, 1, 12, 62.00),
(1, 2, 12, 62.00),
(1, 3, 10, 52.00),
(2, 5, 8, 56.00),
(2, 6, 8, 57.00),
(2, 12, 10, 49.00),
(3, 9, 10, 50.00),
(3, 10, 10, 50.00),
(4, 17, 6, 64.00),
(4, 18, 6, 64.00);

-- VENTAS
INSERT INTO ventas (id_usuario, id_cliente, id_metodo_pago, fecha_venta, total_venta, estado) VALUES
(1, 2, 1, '2026-04-10 10:15:00', 179.98, 'completada'),
(1, 3, 2, '2026-04-10 12:40:00', 152.00, 'completada'),
(1, 1, 1, '2026-04-11 09:20:00', 69.00, 'completada'),
(1, 5, 3, '2026-04-11 16:05:00', 138.00, 'completada'),
(1, 4, 2, '2026-04-12 13:35:00', 171.98, 'completada'),
(1, 7, 1, '2026-04-13 17:55:00', 146.80, 'completada'),
(1, 8, 3, '2026-04-15 11:30:00', 77.30, 'completada'),
(1, 6, 1, '2026-04-16 14:10:00', 0.00, 'anulada');

-- DETALLE VENTA
INSERT INTO detalle_venta (id_venta, id_variante, cantidad, precio_unitario, subtotal) VALUES
(1, 1, 2, 89.99, 179.98),
(2, 4, 2, 76.00, 152.00),
(3, 7, 1, 69.00, 69.00),
(4, 8, 2, 69.00, 138.00),
(5, 13, 1, 84.99, 84.99),
(5, 14, 1, 84.99, 84.99),
(6, 15, 2, 73.40, 146.80),
(7, 23, 1, 77.30, 77.30);