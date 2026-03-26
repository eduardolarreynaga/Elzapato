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
}