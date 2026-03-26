<?php
require_once "conexion.php";

class ModeloProveedor {

    static public function mdlMostrarProveedores($tabla) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id_proveedor DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    static public function mdlIngresarProveedor($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre_empresa, contacto_nombre, telefono, email) VALUES (:nombre, :contacto, :telefono, :email)");

        $stmt->bindParam(":nombre", $datos["nombre_empresa"], PDO::PARAM_STR);
        $stmt->bindParam(":contacto", $datos["contacto_nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
        $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }

    static public function mdlObtenerEstadisticas() {
        // Esta consulta obtiene los totales que pide tu vista
        $stmt = Conexion::conectar()->prepare("
            SELECT 
                (SELECT COUNT(*) FROM proveedores) as total_proveedores,
                (SELECT COUNT(*) FROM compras WHERE MONTH(fecha_compra) = MONTH(CURRENT_DATE())) as compras_mes,
                (SELECT IFNULL(SUM(cantidad),0) FROM detalle_compra dc JOIN compras c ON dc.id_compra = c.id_compra WHERE MONTH(c.fecha_compra) = MONTH(CURRENT_DATE())) as unidades_compradas,
                (SELECT IFNULL(SUM(cantidad * precio_unitario),0) FROM detalle_compra dc JOIN compras c ON dc.id_compra = c.id_compra WHERE MONTH(c.fecha_compra) = MONTH(CURRENT_DATE())) as monto_comprado
        ");
        $stmt->execute();
        return $stmt->fetch();
    }

    static public function mdlEditarProveedor($tabla, $datos) {
    $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre_empresa = :nombre, contacto_nombre = :contacto, telefono = :tel, email = :email WHERE id_proveedor = :id");

    $stmt->bindParam(":nombre", $datos["nombre_empresa"], PDO::PARAM_STR);
    $stmt->bindParam(":contacto", $datos["contacto_nombre"], PDO::PARAM_STR);
    $stmt->bindParam(":tel", $datos["telefono"], PDO::PARAM_STR);
    $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
    $stmt->bindParam(":id", $datos["id_proveedor"], PDO::PARAM_INT);

    if ($stmt->execute()) {
        return "ok";
    } else {
        return "error";
    }
}
}