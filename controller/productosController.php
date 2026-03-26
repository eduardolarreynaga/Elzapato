<?php

class ProductosController {

    /*=============================================
    MOSTRAR PRODUCTOS
    =============================================*/
    static public function ctrMostrarProductos() {

        $tabla1 = "productos";
        $tabla2 = "producto_variante";

        $respuesta = ProductosModel::mdlMostrarProductos($tabla1, $tabla2);

        return $respuesta;
    }

    /*=============================================
    CREAR PRODUCTO (NUEVO)
    =============================================*/
    public function ctrCrearProducto() {
        if (isset($_POST["nombre_producto"]) && empty($_POST["id_producto"])) {
            
            $datos = array(
                "nombre" => $_POST["nombre_producto"],
                "id_categoria" => $_POST["id_categoria"],
                "id_marca" => $_POST["id_marca"],
                "descripcion" => $_POST["descripcion"],
                "estado" => "activo"
            );

            // Intentamos registrar y recibimos el ID (ej: 21, 22...)
            $idNuevoProducto = ProductosModel::mdlRegistrarProducto("productos", $datos);

            if ($idNuevoProducto != "error") {

                $datosVariante = array(
                    "id_producto" => $idNuevoProducto, // Aquí ya NO será 0
                    "precio" => $_POST["precio_venta"],
                    "stock" => $_POST["stock"]
                );

                ProductosModel::mdlRegistrarVariante($datosVariante);
                
                echo '<script>window.location = "productos";</script>';
            }
        }
    }


    /*=============================================
    ACTUALIZAR PRODUCTO (EDICIÓN)
    =============================================*/
    public function ctrActualizarProducto() {

        // Validamos que llegue el nombre y que el ID NO esté vacío (es una edición)
        if (isset($_POST["nombre_producto"]) && !empty($_POST["id_producto"])) {

            $tabla = "productos";

            $datos = array(
                "id_producto" => $_POST["id_producto"],
                "nombre" => $_POST["nombre_producto"],
                "id_categoria" => $_POST["id_categoria"],
                "id_marca" => $_POST["id_marca"],
                "descripcion" => $_POST["descripcion"],
                "estado" => $_POST["estado"]
            );

            // 1. Actualizar tabla 'productos'
            $respuesta = ProductosModel::mdlActualizarProducto($tabla, $datos);

            if ($respuesta == "ok") {

                // 2. Actualizar tabla 'producto_variante' (Precio y Stock)
                $datosVariante = array(
                    "id_producto" => $_POST["id_producto"],
                    "precio" => $_POST["precio_venta"],
                    "stock" => $_POST["stock"]
                );

                $resVariante = ProductosModel::mdlActualizarVariante($datosVariante);

                if ($resVariante == "ok") {
                    echo '<script>
                        alert("¡Producto actualizado con éxito!");
                        window.location = "productos";
                    </script>';
                }
            }
        }
    }

    static public function ctrMostrarProductosPaginados($item, $valor, $base, $tope) {
        $tabla1 = "productos";
        $tabla2 = "producto_variante";
        
        // Llamamos al modelo con los límites de la página
        $respuesta = ProductosModel::mdlMostrarProductosPaginados($tabla1, $tabla2, $base, $tope);
        return $respuesta;
    }

    static public function ctrProductosMasVendidos() {
        $tabla = "productos";
        $respuesta = ProductosModel::mdlProductosMasVendidos($tabla);
        return $respuesta;
    }
}
