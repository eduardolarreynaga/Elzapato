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
    
    /* =============================================
    ELIMINAR CLIENTE
    ============================================= */
    static public function mdlEliminarCliente($tabla, $id){
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id_cliente = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return ($stmt->execute()) ? "ok" : "error";
    }
    
    /* =============================================
    BUSCAR CLIENTE POR TELÉFONO
    ============================================= */
    static public function mdlBuscarClientePorTelefono($telefono) {
        $stmt = Conexion::conectar()->prepare("SELECT id_cliente, nombre, telefono, email, total_compras, total_gastado FROM clientes WHERE telefono = :telefono");
        $stmt->bindParam(":telefono", $telefono);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /* =============================================
    BUSCAR CLIENTE POR NOMBRE
    ============================================= */
    static public function mdlBuscarClientePorNombre($nombre) {
        $nombreLike = '%' . $nombre . '%';
        $stmt = Conexion::conectar()->prepare("SELECT id_cliente, nombre, telefono, email, total_compras, total_gastado FROM clientes WHERE nombre LIKE :nombre ORDER BY total_gastado DESC");
        $stmt->bindParam(":nombre", $nombreLike);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /* =============================================
    REGISTRAR CLIENTE RÁPIDO
    ============================================= */
    static public function mdlRegistrarClienteRapido($datos) {
        self::mdlAsegurarFechaRegistroClientes();
        $stmt = Conexion::conectar()->prepare("INSERT INTO clientes(nombre, telefono, email) VALUES (:nombre, :telefono, :email)");
        
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
        $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return Conexion::conectar()->lastInsertId();
        } else {
            return "error";
        }
    }
    
    /* =============================================
    ACTUALIZAR ESTADÍSTICAS DEL CLIENTE (CORREGIDO)
    ============================================= */
    static public function mdlActualizarEstadisticasCliente($id_cliente, $monto_compra) {
        try {
            $conexion = Conexion::conectar();
            
            // Verificar que el cliente existe
            $stmtCheck = $conexion->prepare("SELECT id_cliente FROM clientes WHERE id_cliente = :id");
            $stmtCheck->bindParam(":id", $id_cliente);
            $stmtCheck->execute();
            $cliente = $stmtCheck->fetch();
            
            if (!$cliente) {
                error_log("Cliente $id_cliente no encontrado");
                return false;
            }
            
            // CORREGIDO: La columna se llama 'ultima_compra' (sin S)
            $sql = "UPDATE clientes SET 
                    total_compras = total_compras + 1,
                    total_gastado = total_gastado + :monto,
                    ultima_compra = NOW()
                    WHERE id_cliente = :id";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(":monto", $monto_compra);
            $stmt->bindParam(":id", $id_cliente);
            $resultado = $stmt->execute();
            
            return $resultado;
            
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarEstadisticasCliente: " . $e->getMessage());
            return false;
        }
    }
    
    /* =============================================
    CALCULAR DESCUENTO POR FIDELIDAD
    ============================================= */
    static public function mdlCalcularDescuentoFidelidad($id_cliente) {
        $stmt = Conexion::conectar()->prepare("SELECT total_compras, total_gastado FROM clientes WHERE id_cliente = :id");
        $stmt->bindParam(":id", $id_cliente);
        $stmt->execute();
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cliente) {
            return ['descuento' => 0, 'nivel' => 'Sin compras'];
        }
        
        $total_compras = $cliente['total_compras'];
        $total_gastado = $cliente['total_gastado'];
        
        if ($total_compras >= 20 || $total_gastado >= 2000) {
            return ['descuento' => 15, 'nivel' => 'VIP Diamante'];
        } elseif ($total_compras >= 10 || $total_gastado >= 1000) {
            return ['descuento' => 10, 'nivel' => 'VIP Oro'];
        } elseif ($total_compras >= 5 || $total_gastado >= 500) {
            return ['descuento' => 5, 'nivel' => 'VIP Plata'];
        } else {
            return ['descuento' => 0, 'nivel' => 'Regular'];
        }
    }
}
?>