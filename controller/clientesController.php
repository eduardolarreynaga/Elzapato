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
                echo '<script>
                    window.location = "clientes.php";
                </script>';
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
                echo '<script>
                    window.location = "clientes.php";
                </script>';
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

}
