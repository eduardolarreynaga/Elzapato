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
        // Verificar que se envió el formulario
        if (!isset($_POST["nombre_producto"])) {
            return;
        }
        
        $accion = $_POST["accion"] ?? '';
        
        // CASO 1: CREAR NUEVO PRODUCTO (INSERT)
        if ($accion == 'crear') {
            
            // Generar SKU automático si no se proporcionó uno
            $sku = $_POST["codigo_barras"];
            if (empty($sku)) {
                $sku = $this->generarSKUAutomatico();
            }
            
            $datos = array(
                "nombre" => $_POST["nombre_producto"],
                "id_categoria" => $_POST["id_categoria"],
                "id_marca" => $_POST["id_marca"],
                "id_proveedor" => $_POST["id_proveedor"] ?? null,
                "descripcion" => $_POST["descripcion"] ?? "",
                "estado" => "activo"
            );

            // Registrar producto base
            $idNuevoProducto = ProductosModel::mdlRegistrarProducto("productos", $datos);

            if ($idNuevoProducto != "error" && is_numeric($idNuevoProducto)) {

                $datosVariante = array(
                    "id_producto" => $idNuevoProducto,
                    "talla" => $_POST["talla"],
                    "color" => $_POST["color"],
                    "codigo_barras" => $sku,
                    "precio" => $_POST["precio_venta"],
                    "stock" => $_POST["stock"],
                    "estado" => $_POST["estado_v"] ?? "activo"
                );
                
                // Registrar variante (con manejo de duplicados)
                $resultado = ProductosModel::mdlRegistrarVariante($datosVariante);
                
                if ($resultado == "duplicado") {
                    // Si hay duplicado, generar un SKU nuevo automáticamente
                    $nuevoSKU = $this->generarSKUAutomatico();
                    $datosVariante["codigo_barras"] = $nuevoSKU;
                    $resultado = ProductosModel::mdlRegistrarVariante($datosVariante);
                    
                    if ($resultado == "duplicado") {
                        echo '<script>window.location = "productos.php?res=error_sku";</script>';
                        return;
                    }
                }
                
                $idVariante = $resultado;
                
                if(is_numeric($idVariante) && $idVariante > 0) {
                    // Guardar imagen
                    if (isset($_FILES['imagen_producto']) && $_FILES['imagen_producto']['error'] == 0) {
                        $this->subirImagen($idVariante, $_FILES['imagen_producto']);
                    }
                    echo '<script>window.location = "productos.php?res=creado";</script>';
                } else {
                    echo '<script>window.location = "productos.php?res=error";</script>';
                }
            }
        }
        
        // CASO 2: ACTUALIZAR PRODUCTO EXISTENTE (UPDATE)
        elseif ($accion == 'actualizar' && !empty($_POST["id_variante"])) {
            
            $idVariante = $_POST["id_variante"];
            
            // Verificar que la variante existe en la base de datos
            $existeVariante = ProductosModel::mdlVerificarVarianteExistente($idVariante);
            
            if(!$existeVariante) {
                error_log("ERROR: Intento de actualizar variante inexistente ID: $idVariante");
                echo '<script>window.location = "productos.php?res=error";</script>';
                return;
            }

            // Procesar imagen si se subió una nueva
            if (isset($_FILES["imagen_producto"]) && $_FILES["imagen_producto"]["error"] == 0 && $_FILES["imagen_producto"]["tmp_name"] != "") {
                $this->subirImagen($idVariante, $_FILES["imagen_producto"]);
            }

            // Actualizar producto general
            $datosProducto = array(
                "id_producto"     => $_POST["id_producto"],
                "nombre_producto" => $_POST["nombre_producto"],
                "id_categoria"    => $_POST["id_categoria"],
                "id_marca"        => $_POST["id_marca"],
                "id_proveedor"    => $_POST["id_proveedor"] ?? null,
                "descripcion"     => $_POST["descripcion"] ?? ""
            );
            
            $respuestaProducto = ProductosModel::mdlActualizarProductoGeneral($datosProducto);
            
            // Actualizar variante (con manejo de duplicados en SKU)
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
                // SKU duplicado en actualización
                echo '<script>window.location = "productos.php?res=duplicado&sku=' . urlencode($_POST["codigo_barras"]) . '";</script>';
                return;
            }
            
            if ($respuestaProducto == "ok" || $respuestaVariante == "ok") {
                echo '<script>window.location = "productos.php?res=actualizado";</script>';
            } else {
                error_log("ERROR al actualizar. Producto: " . print_r($respuestaProducto, true) . " Variante: " . print_r($respuestaVariante, true));
                echo '<script>window.location = "productos.php?res=error";</script>';
            }
        }
    }

    private function subirImagen($idVariante, $file) {
        // Usar DOCUMENT_ROOT para ruta absoluta confiable
        $directorio = $_SERVER['DOCUMENT_ROOT'] . '/ElZapato/Assets/img/productos/';
        
        // Crear carpeta si no existe
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $nombreArchivo = $idVariante . ".jpg";
        $rutaFinal = $directorio . $nombreArchivo;

        // Limpiar archivos anteriores del mismo ID
        $viejos = glob($directorio . $idVariante . ".*");
        foreach($viejos as $v) { 
            if(file_exists($v)) @unlink($v); 
        }

        // Mover el archivo subido
        if (move_uploaded_file($file['tmp_name'], $rutaFinal)) {
            // Verificar que se guardó correctamente
            if(file_exists($rutaFinal)) {
                return "ok";
            }
        }
        
        // Log de error para depuración
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
    
    static public function ctrMostrarProductos() {
        return ProductosModel::mdlMostrarProductos("productos", "producto_variante");
    }

    /*=============================================
    ELIMINAR VARIANTE
    =============================================*/
    public function ctrEliminarProducto() {
        if(isset($_POST["id_eliminar_v"])){ 
            $id_variante = $_POST["id_eliminar_v"];
            
            // Borrar la imagen física
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