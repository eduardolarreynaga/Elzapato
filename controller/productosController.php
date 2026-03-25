<?php

class ProductosController {

    public function ctrCrearProducto() {

        if (isset($_POST["nombre_producto"])) {

            // Validar que los campos no estén vacíos
            if (!empty($_POST["nombre_producto"]) && !empty($_POST["precio_venta"])) {

                $tabla = "productos";
                $datos = array(
                    "nombre"       => $_POST["nombre_producto"],
                    "id_categoria" => $_POST["id_categoria"],
                    "id_marca"     => $_POST["id_marca"],
                    "precio"       => $_POST["precio_venta"],
                    "stock"        => $_POST["stock"],
                    "descripcion"  => $_POST["descripcion"]
                );

                $respuesta = ProductosModel::mdlRegistrarProducto($tabla, $datos);

                if ($respuesta == "ok") {
                    echo '<script>
                        alert("Producto guardado con éxito");
                        window.location = "productos"; // Nombre de tu vista de inventario
                        </script>';
                } else {
                    echo '<script>alert("Error al guardar: '.$respuesta.'");</script>';
                }

            } else {
                echo '<script>alert("Por favor rellena los campos obligatorios");</script>';
            }
        }
    }

    /*=============================================
    MOSTRAR PRODUCTOS (IMPORTANTE: AGREGAR STATIC)
    =============================================*/
    static public function ctrMostrarProductos() {

        $tabla1 = "productos";
        $tabla2 = "producto_variante";

        // Llamamos al modelo (que también debe ser static)
        $respuesta = ProductosModel::mdlMostrarProductos($tabla1, $tabla2);

        return $respuesta;
    }

}