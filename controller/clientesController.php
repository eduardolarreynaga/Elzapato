<?php

class ClientesController {

    /* =============================================
    1. CREAR CLIENTE
    ============================================= */
    public function ctrCrearCliente() {
        if (isset($_POST["nuevoNombre"])) {
            
            $tabla = "clientes";
            $datos = array(
                "nombre"   => $_POST["nuevoNombre"],
                "telefono" => $_POST["nuevoTelefono"],
                "email"    => $_POST["nuevoEmail"]
            );

            $respuesta = ClientesModel::mdlIngresarCliente($tabla, $datos);

            if ($respuesta == "ok") {
                $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                if ($pagina < 1) $pagina = 1;
                header('Location: clientes.php?pagina=' . $pagina . '&res=creado');
                exit;
            }
        }
    }

    /* =============================================
    2. ACTUALIZAR CLIENTE
    ============================================= */
    public function ctrActualizarCliente() {
        if (isset($_POST["editarNombre"])) {
            
            $tabla = "clientes";
            $datos = array(
                "id_cliente" => $_POST["id_cliente"],
                "nombre"     => $_POST["editarNombre"],
                "telefono"   => $_POST["editarTelefono"],
                "email"      => $_POST["editarEmail"]
            );

            $respuesta = ClientesModel::mdlEditarCliente($tabla, $datos);

            if ($respuesta == "ok") {
                $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                if ($pagina < 1) $pagina = 1;
                header('Location: clientes.php?pagina=' . $pagina . '&res=actualizado');
                exit;
            }
        }
    }

    /* =============================================
    3. MOSTRAR CLIENTES
    ============================================= */
    static public function ctrMostrarClientes($item = null, $valor = null) {
        $tabla = "clientes";
        $respuesta = ClientesModel::mdlMostrarClientes($tabla, $item, $valor);
        return $respuesta;
    }

    /* =============================================
    4. MOSTRAR CLIENTES PAGINADOS
    ============================================= */
    static public function ctrMostrarClientesPaginados($item = null, $valor = null, $base = 0, $limite = 5) {
        $tabla = "clientes";
        $respuesta = ClientesModel::mdlMostrarClientesPaginados($tabla, $item, $valor, $base, $limite);
        return $respuesta;
    }
   public function ctrEliminarCliente(){
    if(isset($_POST["id_eliminar_cliente"])){

        $id = intval($_POST["id_eliminar_cliente"]);

        $respuesta = ClientesModel::mdlEliminarCliente("clientes", $id);

        return $respuesta;
    }
}

    /* =============================================
    5. ENVIAR EMAIL A CLIENTE
    ============================================= */
    public function ctrEnviarCorreoCliente() {
        if (!isset($_POST["enviar_email_cliente"])) {
            return "error";
        }

        $correo = trim($_POST["email_cliente"] ?? "");
        $nombre = trim($_POST["nombre_cliente"] ?? "Cliente");

        if ($correo === "") {
            return "sin_email";
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return "email_invalido";
        }

        $asunto = "ElZapato - Contacto para " . $nombre;
        $mensaje = "<html><body>";
        $mensaje .= "<p>Hola <strong>" . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . "</strong>,</p>";
        $mensaje .= "<p>Este es un mensaje enviado desde el panel de clientes de ElZapato.</p>";
        $mensaje .= "<p>Saludos,<br>Equipo ElZapato</p>";
        $mensaje .= "</body></html>";

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: ElZapato <elzapato@mail.com>\r\n";
        $headers .= "Reply-To: elzapato@mail.com\r\n";

        $enviado = @mail($correo, $asunto, $mensaje, $headers);

        return $enviado ? "ok" : "error_envio";
    }

}
