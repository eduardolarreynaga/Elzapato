<?php

class ControladorProveedor {

    static public function ctrMostrarProveedores() {
        $tabla = "proveedores";
        $respuesta = ModeloProveedor::mdlMostrarProveedores($tabla);
        return $respuesta;
    }

    static public function ctrMostrarEstadisticas() {
        $respuesta = ModeloProveedor::mdlObtenerEstadisticas();
        return $respuesta;
    }

    public function ctrCrearProveedor() {
        if (isset($_POST["nuevoNombreEmpresa"])) {
            $email = trim((string)($_POST["nuevoEmail"] ?? ''));
            if ($email === '') {
                return 'email_requerido';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return 'email_invalido';
            }
            
            $tabla = "proveedores";
            $datos = array(
                "nombre_empresa" => $_POST["nuevoNombreEmpresa"],
                "contacto_nombre" => $_POST["nuevoContacto"],
                "telefono" => $_POST["nuevoTelefono"],
                "email" => $email
            );

            $respuesta = ModeloProveedor::mdlIngresarProveedor($tabla, $datos);
            return $respuesta;
        }
        return null;
    }

    public function ctrEditarProveedor() {
    if (isset($_POST["editarIdProveedor"])) {
        $email = trim((string)($_POST["editarEmail"] ?? ''));
        if ($email === '') {
            return 'email_requerido';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'email_invalido';
        }

        $tabla = "proveedores";
        $datos = array(
            "id_proveedor" => $_POST["editarIdProveedor"],
            "nombre_empresa" => $_POST["editarEmpresa"],
            "contacto_nombre" => $_POST["editarContacto"],
            "telefono" => $_POST["editarTelefono"],
            "email" => $email
        );

        $respuesta = ModeloProveedor::mdlEditarProveedor($tabla, $datos);
        return $respuesta;
    }
    return null;
}
public function ctrEliminarProveedor(){

    if(isset($_POST["id_eliminar_proveedor"])){

        $id = intval($_POST["id_eliminar_proveedor"]);

     $respuesta = ModeloProveedor::mdlEliminarProveedor("proveedores", $id);

        return $respuesta;
    }
}
}