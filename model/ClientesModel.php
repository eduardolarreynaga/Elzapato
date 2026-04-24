<?php
require_once "conexion.php";

class ClientesModel {

    static private function mdlAsegurarFechaRegistroClientes() {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->query("SHOW COLUMNS FROM clientes LIKE 'fecha_registro'");
            $col = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;

            if (!$col) {
                $conexion->exec("ALTER TABLE clientes ADD COLUMN fecha_registro TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER email");
            }
        } catch (Throwable $e) {
        }
    }

    /* =============================================
    MOSTRAR CLIENTES
    ============================================= */
    static public function mdlMostrarClientes($tabla, $item, $valor) {
        if ($item != null) {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
            $stmt->bindParam(":".$item, $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } else {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id_cliente DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        }
        $stmt = null;
    }

    /* =============================================
    MOSTRAR CLIENTES PAGINADOS
    ============================================= */
    static public function mdlMostrarClientesPaginados($tabla, $item, $valor, $base, $limite) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id_cliente DESC LIMIT :base, :limite");
        $stmt->bindParam(":base", $base, PDO::PARAM_INT);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
        $stmt = null;
    }

    /* =============================================
    INGRESAR CLIENTE
    ============================================= */
    static public function mdlIngresarCliente($tabla, $datos) {
        self::mdlAsegurarFechaRegistroClientes();
        $stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(nombre, telefono, email) VALUES (:nombre, :telefono, :email)");
        
        $stmt->bindParam(":nombre",   $datos["nombre"],   PDO::PARAM_STR);
        $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
        $stmt->bindParam(":email",    $datos["email"],    PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
        $stmt = null;
    }

    /* =============================================
    EDITAR CLIENTE
    ============================================= */
    static public function mdlEditarCliente($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, telefono = :telefono, email = :email WHERE id_cliente = :id_cliente");

        $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
        $stmt->bindParam(":nombre",     $datos["nombre"],     PDO::PARAM_STR);
        $stmt->bindParam(":telefono",   $datos["telefono"],   PDO::PARAM_STR);
        $stmt->bindParam(":email",      $datos["email"],      PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
        $stmt = null;
    }
    static public function mdlEliminarCliente($tabla, $id){
         $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id_cliente = :id");
         $stmt->bindParam(":id", $id, PDO::PARAM_INT);
             return ($stmt->execute()) ? "ok" : "error";
}

}
