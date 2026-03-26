<?php
require_once "conexion.php";

class ProductosModel {

    /*=============================================
    MOSTRAR PRODUCTOS (CORREGIDO CON MARCAS)
    =============================================*/
    static public function mdlMostrarProductos($tabla1, $tabla2) {
        
        $stmt = Conexion::conectar()->prepare("
            SELECT 
                p.*, 
                c.nombre_categoria, 
                m.nombre_marca, -- Traemos el nombre real de la marca
                IFNULL(v.precio_venta, 0) as precio_venta, 
                IFNULL(v.stock, 0) as stock 
            FROM $tabla1 p
            LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
            LEFT JOIN marcas m ON p.id_marca = m.id_marca -- Conectamos con la tabla marcas
            LEFT JOIN $tabla2 v ON p.id_producto = v.id_producto
            GROUP BY p.id_producto
            ORDER BY p.id_producto DESC
        ");

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /*=============================================
    REGISTRAR PRODUCTO (DEVUELVE EL ID REAL)
    =============================================*/
    static public function mdlRegistrarProducto($tabla, $datos) {
        
        $db = Conexion::conectar(); // Abrimos la puerta una sola vez
        
        $stmt = $db->prepare("INSERT INTO $tabla(nombre_producto, descripcion, id_marca, id_categoria, estado) VALUES (:nombre, :descripcion, :id_marca, :id_categoria, :estado)");
        
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
        $stmt->bindParam(":id_marca", $datos["id_marca"], PDO::PARAM_INT);
        $stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            // ¡ESTO ES LO MÁS IMPORTANTE! 
            // Usamos la MISMA variable $db para pedir el ID
            return $db->lastInsertId(); 
        } else {
            return "error";
        }
    }

    /*=============================================
    REGISTRAR VARIANTE (CORREGIDO)
    =============================================*/
    static public function mdlRegistrarVariante($datos) {
        
        $stmt = Conexion::conectar()->prepare("INSERT INTO producto_variante(id_producto, talla, color, precio_venta, stock) VALUES (:id_producto, 'N/A', 'N/A', :precio, :stock)");
        
        // Verificamos que los nombres coincidan con tu tabla
        $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
        $stmt->bindParam(":precio", $datos["precio"], PDO::PARAM_STR);
        $stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_INT);

        return $stmt->execute() ? "ok" : $stmt->errorInfo();
    }

    
    /*=============================================
    ACTUALIZAR PRODUCTO (TABLA PRINCIPAL)
    =============================================*/
    static public function mdlActualizarProducto($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre_producto = :nombre, descripcion = :descripcion, id_marca = :id_marca, id_categoria = :id_categoria, estado = :estado WHERE id_producto = :id_producto");
        
        $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
        $stmt->bindParam(":id_marca", $datos["id_marca"], PDO::PARAM_INT);
        $stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

        return $stmt->execute() ? "ok" : $stmt->errorInfo();
    }

    /*=============================================
    ACTUALIZAR VARIANTE (TABLA producto_variante)
    =============================================*/
    static public function mdlActualizarVariante($datos) {
        $stmt = Conexion::conectar()->prepare("UPDATE producto_variante SET precio_venta = :precio, stock = :stock WHERE id_producto = :id_producto");
        
        $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
        $stmt->bindParam(":precio", $datos["precio"], PDO::PARAM_STR);
        $stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_INT);

        return $stmt->execute() ? "ok" : $stmt->errorInfo();
    }

    static public function mdlMostrarProductosPaginados($tabla1, $tabla2, $base, $tope) {
        $stmt = Conexion::conectar()->prepare("
            SELECT p.*, c.nombre_categoria, m.nombre_marca, IFNULL(v.precio_venta, 0) as precio_venta, IFNULL(v.stock, 0) as stock 
            FROM $tabla1 p
            LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
            LEFT JOIN marcas m ON p.id_marca = m.id_marca
            LEFT JOIN $tabla2 v ON p.id_producto = v.id_producto
            GROUP BY p.id_producto
            ORDER BY p.id_producto DESC
            LIMIT $base, $tope
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    static public function mdlProductosMasVendidos($tabla) {
    // Esta consulta suma las cantidades vendidas agrupando por el nombre del producto
        $stmt = Conexion::conectar()->prepare("
            SELECT p.nombre_producto, SUM(dv.cantidad) as total_vendido 
            FROM $tabla p
            INNER JOIN producto_variante pv ON p.id_producto = pv.id_producto
            INNER JOIN detalle_venta dv ON pv.id_variante = dv.id_variante
            GROUP BY p.id_producto 
            ORDER BY total_vendido DESC 
            LIMIT 5
        ");

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Estadísticas de ventas de los últimos 7 días
    static public function mdlVentasSemana() {
        $stmt = Conexion::conectar()->prepare("
            SELECT DAYNAME(fecha_venta) as dia, SUM(total_venta) as total 
            FROM ventas 
            WHERE fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DAYOFWEEK(fecha_venta) 
            ORDER BY fecha_venta ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ventas agrupadas por el nombre de la categoría
    static public function mdlVentasPorCategoria() {
        $stmt = Conexion::conectar()->prepare("
            SELECT c.nombre_categoria as etiqueta, SUM(dv.cantidad) as valor
            FROM detalle_venta dv
            INNER JOIN producto_variante pv ON dv.id_variante = pv.id_variante
            INNER JOIN productos p ON pv.id_producto = p.id_producto
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            GROUP BY c.id_categoria
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
