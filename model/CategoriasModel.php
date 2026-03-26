<?php
require_once "conexion.php";
class CategoriasModel {
    static public function mdlMostrarCategorias($tabla) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY nombre_categoria ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
