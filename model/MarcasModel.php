<?php
require_once "conexion.php";
class MarcasModel {
    static public function mdlMostrarMarcas($tabla) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY nombre_marca ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
