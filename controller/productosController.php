<?php
require_once __DIR__ . '/../model/ProductosModel.php';

class ProductosController {

    /*=============================================
    CREAR PRODUCTO (MODIFICADO)
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

            $idNuevoProducto = ProductosModel::mdlRegistrarProducto("productos", $datos);

            if ($idNuevoProducto != "error" && is_numeric($idNuevoProducto)) {

                $datosVariante = array(
                    "id_producto" => $idNuevoProducto,
                    "precio" => $_POST["precio_venta"],
                    "stock" => $_POST["stock"]
                );
                ProductosModel::mdlRegistrarVariante($datosVariante);

                if (isset($_FILES['imagen_producto']) && $_FILES['imagen_producto']['error'] == 0) {
                    $dir = __DIR__ . '/../../Assets/img/productos/'; 
                    if (!file_exists($dir)) { mkdir($dir, 0777, true); }

                    $ext = pathinfo($_FILES['imagen_producto']['name'], PATHINFO_EXTENSION);
                    $nombre_archivo = $idNuevoProducto . "." . $ext;

                    $viejos = glob($dir . $idNuevoProducto . ".*");
                    foreach($viejos as $v) { @unlink($v); }

                    move_uploaded_file($_FILES['imagen_producto']['tmp_name'], $dir . $nombre_archivo);
                }
                
                // Redirigir a la página 1 después de crear
                echo '<script>window.location = "productos.php?pagina=1";</script>';
            }
        }
    }

    /*=============================================
    ACTUALIZAR PRODUCTO (MODIFICADO)
    =============================================*/
    static public function ctrActualizarProducto() {
    if (isset($_POST["id_producto"]) && !empty($_POST["id_producto"])) {
        
        $tabla = "productos";
        $datos = array(
            "id_producto"     => $_POST["id_producto"],
            "id_variante"     => $_POST["id_variante"], // <-- AGREGAR ESTA LÍNEA
            "nombre_producto" => $_POST["nombre_producto"],
            "id_categoria"    => $_POST["id_categoria"],
            "id_marca"        => $_POST["id_marca"],
            "talla"           => $_POST["talla"],
            "color"           => $_POST["color"],
            "precio_venta"    => $_POST["precio_venta"],
            "stock"           => $_POST["stock"],
            "estado_v"        => $_POST["estado_v"]
        );

        $respuesta = ProductosModel::mdlActualizarProducto($tabla, $datos);

        if ($respuesta == "ok") {
            echo '<script>
                window.location = "productos.php";
            </script>';
        }
    }
}

    /*=============================================
    MOSTRAR PRODUCTOS PAGINADOS (CRÍTICO)
    =============================================*/
    static public function ctrMostrarProductosPaginados($item, $valor, $base, $tope) {
        
        // Forzamos que los valores sean enteros para que el LIMIT de SQL no falle
        $inicio = (int)$base;
        $cantidad = (int)$tope;

        return ProductosModel::mdlMostrarProductosPaginados("productos", "producto_variante", $inicio, $cantidad);
    }

    /*=============================================
    ESTADISTICAS (DASHBOARD)
    =============================================*/

    static public function ctrProductosMasVendidos() {
        $tabla = "productos";
        return ProductosModel::mdlProductosMasVendidos($tabla);
    }

    static public function ctrVentasSemana() {
        return ProductosModel::mdlVentasSemana();
    }

    static public function ctrVentasPorCategoria() {
        return ProductosModel::mdlVentasPorCategoria();
    }
    
    static public function ctrMostrarProductos() {
        return ProductosModel::mdlMostrarProductos("productos", "producto_variante");
    }

    /*=============================================
    ELIMINAR PRODUCTO
    =============================================*/
    public function ctrEliminarProducto(){
        if(isset($_POST["id_eliminar"])){
            $id = $_POST["id_eliminar"];
            $tabla = "productos";
            $dir = __DIR__ . '/../../Assets/img/productos/';
            $archivos = glob($dir . $id . ".*");
            foreach($archivos as $archivo) {
                if(file_exists($archivo)){ @unlink($archivo); }
            }
            return ProductosModel::mdlEliminarProducto($tabla, $id);
        }
    }
}