<?php
require_once __DIR__ . '/conexion.php'; 

class UsuarioModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    public function findByUsername($username) {
        if (!$this->db) return null;
        try {
            $sql = "SELECT id_usuario, nombre_usuario, password_hash, rol 
                    FROM usuarios WHERE nombre_usuario = :u LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':u' => $username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
}