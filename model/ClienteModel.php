<?php
require_once __DIR__ . '/conexion.php';

class ClienteModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    // Listar todos los clientes
    public function listar() {
        $sql = "SELECT id_cliente, nombre, telefono, email,
                       CASE
                           WHEN email IS NULL OR email = '' THEN 'Sin datos'
                           WHEN id_cliente IN (SELECT DISTINCT id_cliente FROM ventas) THEN 'Activo'
                           ELSE 'Incompleto'
                       END AS estado
                FROM clientes
                ORDER BY id_cliente DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estadísticas
    public function obtenerEstadisticas() {
        $stmtTotal = $this->db->query("SELECT COUNT(*) AS total_clientes FROM clientes");
        $total = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total_clientes'];

        $stmtEmail = $this->db->query("SELECT COUNT(*) AS con_email FROM clientes WHERE email IS NOT NULL AND email != ''");
        $conEmail = $stmtEmail->fetch(PDO::FETCH_ASSOC)['con_email'];

        // Para simplificar, dejamos nuevos_mes y con_compras como 0
        return [
            'total_clientes' => (int)$total,
            'nuevos_mes' => 0,
            'con_compras' => 0,
            'con_email' => (int)$conEmail
        ];
    }

    // Agregar un nuevo cliente
    public function agregar($data) {
        $sql = "INSERT INTO clientes (nombre, telefono, email) VALUES (:nombre, :telefono, :email)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':email', $data['email']);
        return $stmt->execute();
    }

    // Editar un cliente existente
    public function editar($id, $data) {
        $sql = "UPDATE clientes SET nombre = :nombre, telefono = :telefono, email = :email WHERE id_cliente = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Eliminar cliente
    public function eliminar($id) {
        $sql = "DELETE FROM clientes WHERE id_cliente = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}