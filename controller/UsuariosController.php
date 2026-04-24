<?php
require_once __DIR__ . '/../model/conexion.php';

class UsuariosController {
    public function login() {
        if (isset($_POST["username"]) && isset($_POST["password"])) {
            
            $tabla = "usuarios";
            $usuarioInput = $_POST["username"];
            $passwordInput = $_POST["password"];

            $db = Conexion::conectar();
            $stmt = $db->prepare("SELECT * FROM $tabla WHERE nombre_usuario = :usuario");
            $stmt->bindParam(":usuario", $usuarioInput, PDO::PARAM_STR);
            $stmt->execute();
            
            $respuesta = $stmt->fetch();

            $esValido = false;
            if ($respuesta) {
                $hashGuardado = (string)$respuesta["password_hash"];
                $esValido = password_verify($passwordInput, $hashGuardado) || $passwordInput === $hashGuardado;
            }

            if ($respuesta && $esValido) {
                
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                $_SESSION["validarSesion"] = "ok";
                $_SESSION["usuario"] = $respuesta["nombre_usuario"];
                $_SESSION["rol"] = $respuesta["rol"];

                if ($respuesta["rol"] == "admin") {
                    echo '<script>window.location = "src/views/admin/dashboard.php";</script>';
                } else {
                    echo '<script>window.location = "src/views/layouts/principal.php";</script>';
                }
                return "ok";
                exit();
            } else {
                return "error";
            }
        }
    }
}
