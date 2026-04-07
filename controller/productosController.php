<?php

class ProductosController {

    /*=============================================
    CREAR PRODUCTO (CON LOGICA DE IMAGEN INCLUIDA)
    =============================================*/
    public function ctrCrearProducto() {
        if (isset($_POST["nombre_producto"]) && empty($_POST["id_producto"])) {
            
            $datos = array(
                "nombre" => $_POST["nombre_producto"],
                "id_categoria" => $_POST["id_categoria"],
                "id_marca" => $_POST["id_marca"],
                "descripcion" => $_POST["descripcion"] ?? "",
                "estado" => "activo"
            );

            // 1. Registramos el producto y obtenemos el ID real
            $idNuevoProducto = ProductosModel::mdlRegistrarProducto("productos", $datos);

            if ($idNuevoProducto != "error" && is_numeric($idNuevoProducto)) {

                // 2. Registramos la variante (Precio y Stock)
                $datosVariante = array(
                    "id_producto" => $idNuevoProducto,
                    "precio" => $_POST["precio_venta"],
                    "stock" => $_POST["stock"]
                );
                ProductosModel::mdlRegistrarVariante($datosVariante);

                // 3. PROCESAR IMAGEN (NUEVO)
                if (isset($_FILES['imagen_producto']) && $_FILES['imagen_producto']['error'] == 0) {
                    $dir = __DIR__ . '/../Assets/img/productos/'; // Ajusta la ruta según tu carpeta
                    if (!file_exists($dir)) { mkdir($dir, 0777, true); }

                    $ext = pathinfo($_FILES['imagen_producto']['name'], PATHINFO_EXTENSION);
                    $nombre_archivo = $idNuevoProducto . "." . $ext;

                    // Limpiar versiones anteriores si existieran
                    $viejos = glob($dir . $idNuevoProducto . ".*");
                    foreach($viejos as $v) { @unlink($v); }

                    move_uploaded_file($_FILES['imagen_producto']['tmp_name'], $dir . $nombre_archivo);
                }
                
                // 4. Redirigir SOLO después de procesar todo
                echo '<script>window.location = "productos";</script>';
            }
        }
    }

    /*=============================================
    ACTUALIZAR PRODUCTO (CON LOGICA DE IMAGEN)
    =============================================*/
    public function ctrActualizarProducto() {
        if (isset($_POST["nombre_producto"]) && !empty($_POST["id_producto"])) {
            
            $id = $_POST["id_producto"];
            $datos = array(
                "id_producto" => $id,
                "nombre" => $_POST["nombre_producto"],
                "id_categoria" => $_POST["id_categoria"],
                "id_marca" => $_POST["id_marca"],
                "descripcion" => $_POST["descripcion"] ?? "",
                "estado" => $_POST["estado"]
            );

            $respuesta = ProductosModel::mdlActualizarProducto("productos", $datos);

            if ($respuesta == "ok") {
                $datosVariante = array(
                    "id_producto" => $id,
                    "precio" => $_POST["precio_venta"],
                    "stock" => $_POST["stock"]
                );
                ProductosModel::mdlActualizarVariante($datosVariante);

                // PROCESAR IMAGEN EN EDICIÓN
                if (isset($_FILES['imagen_producto']) && $_FILES['imagen_producto']['error'] == 0) {
                    $dir = __DIR__ . '/../Assets/img/productos/';
                    $ext = pathinfo($_FILES['imagen_producto']['name'], PATHINFO_EXTENSION);
                    $nombre_archivo = $id . "." . $ext;

                    $viejos = glob($dir . $id . ".*");
                    foreach($viejos as $v) { @unlink($v); }

                    move_uploaded_file($_FILES['imagen_producto']['tmp_name'], $dir . $nombre_archivo);
                }

                echo '<script>window.location = "productos";</script>';
            }
        }
    }

    static public function ctrProductosMasVendidos() {
        $tabla = "productos";
        // Llama al modelo que ya tienes definido
        $respuesta = ProductosModel::mdlProductosMasVendidos($tabla);
        return $respuesta;
    }

    static public function ctrVentasSemana() {
        // Llama al modelo para las ventas de los últimos 7 días
        $respuesta = ProductosModel::mdlVentasSemana();
        return $respuesta;
    }

    static public function ctrVentasPorCategoria() {
        // Llama al modelo para la gráfica de dona
        $respuesta = ProductosModel::mdlVentasPorCategoria();
        return $respuesta;
    }
    
    static public function ctrMostrarProductos() {
        return ProductosModel::mdlMostrarProductos("productos", "producto_variante");
    }

    static public function ctrMostrarProductosPaginados($item, $valor, $base, $tope) {
        return ProductosModel::mdlMostrarProductosPaginados("productos", "producto_variante", $base, $tope);
    }
}