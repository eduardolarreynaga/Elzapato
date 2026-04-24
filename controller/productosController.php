<?php
require_once __DIR__ . '/../model/ProductosModel.php';

class ProductosController {

   /*=============================================
    MOSTRAR PROVEEDORES
    =============================================*/
    static public function ctrMostrarProveedores() {
        return ProductosModel::mdlMostrarProveedores();
    }


    /*=============================================
    GENERAR SKU AUTOMÁTICO
    =============================================*/
    private function generarSKUAutomatico() {
        // Obtener el último SKU registrado
        $ultimoSKU = ProductosModel::mdlObtenerUltimoSKU();
        
        if ($ultimoSKU && isset($ultimoSKU['codigo_barras'])) {
            // Extraer el número del SKU (asumiendo formato SKU_XXXXX o solo números)
            $ultimoNumero = preg_replace('/[^0-9]/', '', $ultimoSKU['codigo_barras']);
            if (is_numeric($ultimoNumero)) {
                $nuevoNumero = intval($ultimoNumero) + 1;
                return str_pad($nuevoNumero, 7, '0', STR_PAD_LEFT);
            }
        }
        
        // Si no hay SKU previo, empezar desde 1000001
        return '1000001';
    }

    /*=============================================
    PROCESAR FORMULARIO (CREAR O ACTUALIZAR)
    =============================================*/
    public function ctrProcesarProducto() {
        if (!isset($_POST["nombre_producto"])) {
            return;
        }
        
        $accion = $_POST["accion"] ?? '';
        
        // CASO 1: CREAR NUEVO PRODUCTO
        if ($accion == 'crear') {
            
            // Buscar si ya existe producto con el mismo nombre
            $productoExistente = ProductosModel::mdlBuscarProductoPorNombre($_POST["nombre_producto"]);
            
            if ($productoExistente) {
                $idProducto = $productoExistente['id_producto'];
            } else {
                $datosProducto = array(
                    "nombre" => $_POST["nombre_producto"],
                    "id_categoria" => $_POST["id_categoria"],
                    "id_marca" => $_POST["id_marca"],
                    "id_proveedor" => $_POST["id_proveedor"] ?? null,
                    "descripcion" => $_POST["descripcion"] ?? "",
                    "estado" => "activo"
                );
                $idProducto = ProductosModel::mdlRegistrarProducto("productos", $datosProducto);
                
                if ($idProducto == "error" || !is_numeric($idProducto)) {
                    echo '<script>window.location = "productos.php?res=error";</script>';
                    return;
                }
            }
            
            // Registrar variante
            $datosVariante = array(
                "id_producto" => $idProducto,
                "talla" => $_POST["talla"],
                "color" => $_POST["color"],
                "codigo_barras" => $_POST["codigo_barras"],
                "precio" => $_POST["precio_venta"],
                "stock" => $_POST["stock"],
                "estado" => $_POST["estado_v"] ?? "activo"
            );
            
            $resultado = ProductosModel::mdlRegistrarVariante($datosVariante);
            
            if ($resultado == "duplicado") {
                echo '<script>window.location = "productos.php?res=duplicado&sku=' . urlencode($_POST["codigo_barras"]) . '";</script>';
                return;
            }
            
            $idVariante = $resultado;
            
            if(is_numeric($idVariante) && $idVariante > 0) {
                if (isset($_FILES['imagen_producto']) && $_FILES['imagen_producto']['error'] == 0) {
                    $this->subirImagen($idVariante, $_FILES['imagen_producto']);
                }
                echo '<script>window.location = "productos.php?res=creado";</script>';
            } else {
                echo '<script>window.location = "productos.php?res=error";</script>';
            }
        }
        
        // CASO 2: ACTUALIZAR PRODUCTO EXISTENTE
        elseif ($accion == 'actualizar' && !empty($_POST["id_variante"])) {
            
            $idVariante = $_POST["id_variante"];
            
            $existeVariante = ProductosModel::mdlVerificarVarianteExistente($idVariante);
            
            if(!$existeVariante) {
                echo '<script>window.location = "productos.php?res=error";</script>';
                return;
            }

            if (isset($_FILES["imagen_producto"]) && $_FILES["imagen_producto"]["error"] == 0) {
                $this->subirImagen($idVariante, $_FILES["imagen_producto"]);
            }

            $datosProducto = array(
                "id_producto"     => $_POST["id_producto"],
                "nombre_producto" => $_POST["nombre_producto"],
                "id_categoria"    => $_POST["id_categoria"],
                "id_marca"        => $_POST["id_marca"],
                "id_proveedor"    => $_POST["id_proveedor"] ?? null,
                "descripcion"     => $_POST["descripcion"] ?? ""
            );
            
            ProductosModel::mdlActualizarProductoGeneral($datosProducto);
            
            $datosVariante = array(
                "id_variante"   => $idVariante,
                "talla"         => $_POST["talla"],
                "color"         => $_POST["color"],
                "codigo_barras" => $_POST["codigo_barras"],
                "precio_venta"  => $_POST["precio_venta"],
                "stock"         => $_POST["stock"],
                "estado"        => $_POST["estado_v"]
            );
            
            $respuestaVariante = ProductosModel::mdlActualizarVarianteCompleta($datosVariante);
            
            if ($respuestaVariante == "duplicado") {
                echo '<script>window.location = "productos.php?res=duplicado&sku=' . urlencode($_POST["codigo_barras"]) . '";</script>';
                return;
            }
            
            if ($respuestaVariante == "ok") {
                echo '<script>window.location = "productos.php?res=actualizado";</script>';
            } else {
                echo '<script>window.location = "productos.php?res=error";</script>';
            }
        }
    }

    private function subirImagen($idVariante, $file) {
        $directorio = $_SERVER['DOCUMENT_ROOT'] . '/ElZapato/Assets/img/productos/';
        
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $nombreArchivo = $idVariante . ".jpg";
        $rutaFinal = $directorio . $nombreArchivo;

        $viejos = glob($directorio . $idVariante . ".*");
        foreach($viejos as $v) { 
            if(file_exists($v)) @unlink($v); 
        }

        if (move_uploaded_file($file['tmp_name'], $rutaFinal)) {
            if(file_exists($rutaFinal)) {
                return "ok";
            }
        }
        
        error_log("Error al subir imagen. ID: $idVariante, Temp: " . $file['tmp_name'] . ", Destino: $rutaFinal");
        return "error";
    }


    /*=============================================
    MOSTRAR PRODUCTOS PAGINADOS
    =============================================*/
    static public function ctrMostrarProductosPaginados($item, $valor, $base, $tope) {
        $inicio = (int)$base;
        $cantidad = (int)$tope;
        return ProductosModel::mdlMostrarProductosPaginados("productos", "producto_variante", $inicio, $cantidad);
    }

    /*=============================================
    ESTADISTICAS (DASHBOARD)
    =============================================*/
    static public function ctrProductosMasVendidos() {
        return ProductosModel::mdlProductosMasVendidos("productos");
    }

    static public function ctrVentasSemana() {
        return ProductosModel::mdlVentasSemana();
    }

    static public function ctrVentasPorCategoria() {
        return ProductosModel::mdlVentasPorCategoria();
    }
    
    /*=============================================
    MOSTRAR PRODUCTOS
    =============================================*/
    static public function ctrMostrarProductos() {
        return ProductosModel::mdlMostrarProductos("productos", "producto_variante");
    }

    /*=============================================
    ELIMINAR VARIANTE
    =============================================*/
    public function ctrEliminarProducto() {
        if(isset($_POST["id_eliminar_v"])){ 
            $id_variante = $_POST["id_eliminar_v"];
            
            $directorio = $_SERVER['DOCUMENT_ROOT'] . '/ElZapato/Assets/img/productos/';
            $archivos = glob($directorio . $id_variante . ".*"); 
            foreach($archivos as $archivo) {
                if(file_exists($archivo)) @unlink($archivo); 
            }

            $respuesta = ProductosModel::mdlEliminarVariante($id_variante);
            return $respuesta;
        }
    }
}
?>