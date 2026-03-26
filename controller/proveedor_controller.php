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
            
            $tabla = "proveedores";
            $datos = array(
                "nombre_empresa" => $_POST["nuevoNombreEmpresa"],
                "contacto_nombre" => $_POST["nuevoContacto"],
                "telefono" => $_POST["nuevoTelefono"],
                "email" => $_POST["nuevoEmail"]
            );

            $respuesta = ModeloProveedor::mdlIngresarProveedor($tabla, $datos);

            if ($respuesta == "ok") {
                echo '<script>alert("Proveedor guardado correctamente");</script>';
            }
        }
    }

    public function ctrEditarProveedor() {
    if (isset($_POST["editarIdProveedor"])) {
        $tabla = "proveedores";
        $datos = array(
            "id_proveedor" => $_POST["editarIdProveedor"],
            "nombre_empresa" => $_POST["editarEmpresa"],
            "contacto_nombre" => $_POST["editarContacto"],
            "telefono" => $_POST["editarTelefono"],
            "email" => $_POST["editarEmail"]
        );

        $respuesta = ModeloProveedor::mdlEditarProveedor($tabla, $datos);
        return $respuesta;
    }
}
}