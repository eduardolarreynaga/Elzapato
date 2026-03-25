<?php
require_once "Conexion.php";

class ProductosModel {

    public static function mdlRegistrarProducto($tabla, $datos) {
        $db = Conexion::conectar();
        
        try {
            $db->beginTransaction();

            // 1. Insertar en la tabla 'productos'
            $stmt = $db->prepare("INSERT INTO productos (nombre_producto, id_categoria, id_marca, descripcion) 
                                  VALUES (:nombre, :id_cat, :id_marca, :desc)");

            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":id_cat", $datos["id_categoria"], PDO::PARAM_INT);
            $stmt->bindParam(":id_marca", $datos["id_marca"], PDO::PARAM_INT);
            $stmt->bindParam(":desc", $datos["descripcion"], PDO::PARAM_STR);

            $stmt->execute();
            $idNuevoProducto = $db->lastInsertId();

            // 2. Insertar en la tabla 'producto_variante'
            // Nota: Aquí usamos el ID que acabamos de generar arriba
            $stmtVar = $db->prepare("INSERT INTO producto_variante (id_producto, stock, precio_venta) 
                                     VALUES (:id_prod, :stock, :precio)");

            $stmtVar->bindParam(":id_prod", $idNuevoProducto, PDO::PARAM_INT);
            $stmtVar->bindParam(":stock", $datos["stock"], PDO::PARAM_INT);
            $stmtVar->bindParam(":precio", $datos["precio"], PDO::PARAM_STR);

            $stmtVar->execute();

            $db->commit();
            return "ok";

        } catch (Exception $e) {
            $db->rollBack();
            return "error: " . $e->getMessage();
        }
    }

   static public function mdlMostrarProductos(){
        // Este query une las 3 tablas para traer toda la info de la vista
        $stmt = Conexion::conectar()->prepare("SELECT p.*, c.nombre_categoria, pv.stock, pv.precio_venta 
        FROM productos p 
        INNER JOIN categorias c ON p.id_categoria = c.id_categoria 
        INNER JOIN producto_variante pv ON p.id_producto = pv.id_producto
        ");

        $stmt -> execute();
        return $stmt -> fetchAll();
    }
static public function mdlActualizarProducto($datos) {
    try {
        // Conectamos y preparamos el UPDATE en ambas tablas
        $stmt = Conexion::conectar()->prepare("UPDATE productos p 
            INNER JOIN producto_variante pv ON p.id_producto = pv.id_producto 
            SET p.nombre_producto = :nombre, 
                pv.precio_venta = :precio, 
                pv.stock = :stock 
            WHERE p.id_producto = :id");

        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":precio", $datos["precio"], PDO::PARAM_STR);
        $stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_INT);
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

        if($stmt->execute()) {
            return "ok";
        } else {
            return "error_ejecucion";
        }
    } catch (Exception $e) {
        return "error: " . $e->getMessage();
    }
}


}
