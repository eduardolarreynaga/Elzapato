<?php
class Conexion {
    public static function conectar() {
    
        $host = "localhost";
        $dbName = "pos_zapateria"; 
        $user = "root";
        $password = ""; // en WAMP no tiene contraseña

        try {
            $link = new PDO("mysql:host=$host;dbname=$dbName", $user, $password);
            $link->exec("set names utf8"); 
            return $link;
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
}
