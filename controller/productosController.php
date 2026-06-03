<?php
require_once __DIR__ . '/../model/ProductosModel.php';
require_once __DIR__ . '/../model/conexion.php';
require_once __DIR__ . '/../helpers/LogHelper.php';

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
        // Procesar carga CSV primero
        if (isset($_POST["accion_csv"]) && $_POST["accion_csv"] == 'cargar') {
            $this->ctrProcesarCSV();
            return;
        }
        
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
                // Registrar log de creación
                $detalle = "Producto: " . $_POST["nombre_producto"] . " | Talla: " . $_POST["talla"] . " | Color: " . $_POST["color"] . " | SKU: " . $_POST["codigo_barras"] . " | Precio: $" . $_POST["precio_venta"] . " | Stock: " . $_POST["stock"];
                LogHelper::registrar('crear', 'producto_variante', $idVariante, $detalle);
                
                $estadoImagen = 'sin_imagen';
                if (isset($_FILES['imagen_producto']) && is_array($_FILES['imagen_producto'])) {
                    $nombreImagen = trim($_FILES['imagen_producto']['name'] ?? '');
                    if ($nombreImagen !== '') {
                        $estadoImagen = $this->subirImagen($idVariante, $_FILES['imagen_producto']);
                    }
                }

                if ($estadoImagen !== 'ok' && $estadoImagen !== 'sin_imagen') {
                    echo '<script>window.location = "productos.php?res=creado_img_error&img_status=' . urlencode($estadoImagen) . '";</script>';
                    return;
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

            $estadoImagen = 'sin_imagen';
            if (isset($_FILES['imagen_producto']) && is_array($_FILES['imagen_producto'])) {
                $nombreImagen = trim($_FILES['imagen_producto']['name'] ?? '');
                if ($nombreImagen !== '') {
                    $estadoImagen = $this->subirImagen($idVariante, $_FILES['imagen_producto']);
                }
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
                // Registrar log de edición
                $detalle = "Producto: " . $_POST["nombre_producto"] . " | Talla: " . $_POST["talla"] . " | Color: " . $_POST["color"] . " | SKU: " . $_POST["codigo_barras"] . " | Precio: $" . $_POST["precio_venta"] . " | Stock: " . $_POST["stock"];
                LogHelper::registrar('editar', 'producto_variante', $idVariante, $detalle);
                
                if ($estadoImagen !== 'ok' && $estadoImagen !== 'sin_imagen') {
                    echo '<script>window.location = "productos.php?res=actualizado_img_error&img_status=' . urlencode($estadoImagen) . '";</script>';
                    return;
                }
                echo '<script>window.location = "productos.php?res=actualizado";</script>';
            } else {
                echo '<script>window.location = "productos.php?res=error";</script>';
            }
        }
    }

    private function subirImagen($idVariante, $file) {
        $errorArchivo = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errorArchivo !== UPLOAD_ERR_OK) {
            return 'upload_' . $errorArchivo;
        }

        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return 'archivo_invalido';
        }

        $tamano = (int)($file['size'] ?? 0);
        if ($tamano <= 0) {
            return 'archivo_vacio';
        }

        if ($tamano > 5 * 1024 * 1024) {
            return 'tamano';
        }

        $baseImgDir = realpath(__DIR__ . '/../Assets/img');
        if ($baseImgDir === false) {
            $baseImgDir = __DIR__ . '/../Assets/img';
        }
        $directorio = rtrim($baseImgDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'productos' . DIRECTORY_SEPARATOR;
        
        if (!is_dir($directorio)) {
            if (!mkdir($directorio, 0777, true)) {
                return 'directorio';
            }
        }

        if (!is_writable($directorio)) {
            return 'sin_permisos';
        }

        $mime = function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : '';
        $mapaMime = [
            'image/jpg' => 'jpg',
            'image/jpeg' => 'jpg',
            'image/pjpeg' => 'jpg',
            'image/png' => 'png',
            'image/x-png' => 'png',
            'image/webp' => 'webp'
        ];

        $extension = $mapaMime[$mime] ?? strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return 'formato';
        }

        $nombreArchivo = $idVariante . "." . $extension;
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
            
            // Obtener información del producto antes de eliminar para el log
            $db = Conexion::conectar();
            $stmt = $db->prepare("SELECT pv.id_variante, p.nombre_producto, pv.talla, pv.color, pv.codigo_barras 
                                                   FROM producto_variante pv 
                                                   INNER JOIN productos p ON pv.id_producto = p.id_producto 
                                                   WHERE pv.id_variante = :id");
            $stmt->bindParam(":id", $id_variante);
            $stmt->execute();
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($producto) {
                $detalle = "Producto: " . $producto['nombre_producto'] . " | Talla: " . $producto['talla'] . " | Color: " . $producto['color'] . " | SKU: " . $producto['codigo_barras'];
                LogHelper::registrar('eliminar', 'producto_variante', $id_variante, $detalle);
            }
            
            $directorio = realpath(__DIR__ . '/../Assets/img') . '/productos/';
            $archivos = glob($directorio . $id_variante . ".*"); 
            foreach($archivos as $archivo) {
                if(file_exists($archivo)) @unlink($archivo); 
            }

            $respuesta = ProductosModel::mdlEliminarVariante($id_variante);
            return $respuesta;
        }
    }

    /*=============================================
    PROCESAR CARGA MASIVA CSV
    =============================================*/
    public function ctrProcesarCSV() {
        if (!isset($_POST["accion_csv"]) || $_POST["accion_csv"] != 'cargar') {
            return;
        }
        
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] != UPLOAD_ERR_OK) {
            echo '<script>window.location = "productos.php?res_csv=error_archivo";</script>';
            return;
        }
        
        $modoPrueba = isset($_POST['modo_prueba']) && $_POST['modo_prueba'] == '1';
        $saltarDuplicados = isset($_POST['sobrescribir_sku']) && $_POST['sobrescribir_sku'] == '1';
        
        // Leer CSV
        $archivo = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($archivo, 'r');
        
        if (!$handle) {
            echo '<script>window.location = "productos.php?res_csv=error_lectura";</script>';
            return;
        }
        
        // Leer cabeceras
        $headers = fgetcsv($handle, 1000, ',');
        $headers = array_map('trim', $headers);
        
        $columnasRequeridas = ['nombre_producto', 'descripcion', 'id_marca', 'id_categoria', 
                               'id_proveedor', 'talla', 'color', 'codigo_barras', 'precio_venta', 'stock'];
        
        // Validar columnas requeridas
        foreach ($columnasRequeridas as $req) {
            if (!in_array($req, $headers)) {
                fclose($handle);
                echo '<script>window.location = "productos.php?res_csv=faltan_columnas&columna=' . urlencode($req) . '";</script>';
                return;
            }
        }
        
        $resultados = [
            'total' => 0,
            'creados' => 0,
            'actualizados' => 0,
            'errores' => 0,
            'skus_duplicados' => 0,
            'detalles' => []
        ];
        
        $linea = 1; // Para reporte de errores
        
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $linea++;
            
            if (count($data) != count($headers)) {
                $resultados['errores']++;
                $resultados['detalles'][] = "Línea $linea: Número de columnas incorrecto";
                continue;
            }
            
            $row = array_combine($headers, $data);
            $row = array_map('trim', $row);
            
            // Validaciones básicas
            if (empty($row['nombre_producto']) || empty($row['codigo_barras']) || empty($row['precio_venta'])) {
                $resultados['errores']++;
                $resultados['detalles'][] = "Línea $linea: Datos incompletos (nombre, SKU o precio faltante)";
                continue;
            }
            
            $precio = floatval($row['precio_venta']);
            $stock = intval($row['stock']);
            $codigoBarras = preg_replace('/[^0-9]/', '', $row['codigo_barras']);
            
            if (strlen($codigoBarras) > 7) {
                $codigoBarras = substr($codigoBarras, 0, 7);
            }
            
            if ($precio <= 0) {
                $resultados['errores']++;
                $resultados['detalles'][] = "Línea $linea: Precio inválido";
                continue;
            }
            
            $resultados['total']++;
            
            if ($modoPrueba) {
                // Solo validar en modo prueba
                $resultados['detalles'][] = "Línea $linea: [VALIDACIÓN] " . $row['nombre_producto'] . " - SKU: $codigoBarras";
                continue;
            }
            
            // Procesar inserción/actualización
            $resultado = $this->procesarFilaCSV($row, $codigoBarras, $saltarDuplicados);
            
            if ($resultado['status'] == 'creado') {
                $resultados['creados']++;
                $resultados['detalles'][] = "Línea $linea: ✓ " . $row['nombre_producto'] . " (SKU: $codigoBarras)";
            } elseif ($resultado['status'] == 'actualizado') {
                $resultados['actualizados']++;
                $resultados['detalles'][] = "Línea $linea: ↻ " . $row['nombre_producto'] . " (SKU: $codigoBarras) - Stock actualizado";
            } elseif ($resultado['status'] == 'duplicado') {
                $resultados['skus_duplicados']++;
                $resultados['detalles'][] = "Línea $linea: ⚠ SKU $codigoBarras duplicado (omitido)";
            } else {
                $resultados['errores']++;
                $resultados['detalles'][] = "Línea $linea: ✗ " . ($resultado['error'] ?? 'Error desconocido');
            }
        }
        
        fclose($handle);
        
        // Guardar resultados en sesión para mostrar
        $_SESSION['csv_resultados'] = $resultados;
        
        if ($modoPrueba) {
            echo '<script>window.location = "productos.php?res_csv=prueba";</script>';
        } else {
            echo '<script>window.location = "productos.php?res_csv=completado";</script>';
        }
    }

    private function procesarFilaCSV($row, $codigoBarras, $saltarDuplicados) {
        // Buscar si ya existe el SKU
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT id_variante, id_producto, stock FROM producto_variante WHERE codigo_barras = :sku");
        $stmt->bindParam(":sku", $codigoBarras);
        $stmt->execute();
        $existente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existente && $saltarDuplicados) {
            return ['status' => 'duplicado'];
        }
        
        // Buscar o crear producto
        $stmtProd = $db->prepare("SELECT id_producto FROM productos WHERE nombre_producto = :nombre");
        $stmtProd->bindParam(":nombre", $row['nombre_producto']);
        $stmtProd->execute();
        $productoExistente = $stmtProd->fetch(PDO::FETCH_ASSOC);
        
        if ($productoExistente) {
            $idProducto = $productoExistente['id_producto'];
        } else {
            // Crear nuevo producto
            $descripcion = $row['descripcion'] ?? '';
            $idMarca = intval($row['id_marca']);
            $idCategoria = intval($row['id_categoria']);
            $idProveedor = !empty($row['id_proveedor']) ? intval($row['id_proveedor']) : null;
            $estado = 'activo';
            
            $stmtInsert = $db->prepare("INSERT INTO productos(nombre_producto, descripcion, id_marca, id_categoria, id_proveedor, estado) 
                                         VALUES (:nombre, :desc, :marca, :categoria, :proveedor, :estado)");
            $stmtInsert->bindParam(":nombre", $row['nombre_producto']);
            $stmtInsert->bindParam(":desc", $descripcion);
            $stmtInsert->bindParam(":marca", $idMarca);
            $stmtInsert->bindParam(":categoria", $idCategoria);
            $stmtInsert->bindParam(":proveedor", $idProveedor);
            $stmtInsert->bindParam(":estado", $estado);
            
            if (!$stmtInsert->execute()) {
                return ['status' => 'error', 'error' => 'No se pudo crear el producto'];
            }
            
            $idProducto = $db->lastInsertId();
        }
        
        // Determinar estado basado en stock
        $stock = intval($row['stock']);
        $estadoVariante = ($stock <= 0) ? 'inactivo' : 'activo';
        
        // Verificar si la variante existe (por SKU o por combinación producto/talla/color)
        if ($existente) {
            // Actualizar variante existente
            $stmtUpdate = $db->prepare("UPDATE producto_variante SET 
                talla = :talla,
                color = :color,
                precio_venta = :precio,
                stock = :stock,
                estado = :estado
                WHERE id_variante = :id_v");
            
            $stmtUpdate->bindParam(":talla", $row['talla']);
            $stmtUpdate->bindParam(":color", $row['color']);
            $stmtUpdate->bindParam(":precio", $row['precio_venta']);
            $stmtUpdate->bindParam(":stock", $stock);
            $stmtUpdate->bindParam(":estado", $estadoVariante);
            $stmtUpdate->bindParam(":id_v", $existente['id_variante']);
            
            if ($stmtUpdate->execute()) {
                // Registrar log de actualización
                $detalle = "CSV - Producto: " . $row['nombre_producto'] . " | Talla: " . $row['talla'] . " | Color: " . $row['color'] . " | SKU: " . $codigoBarras . " | Stock actualizado a: " . $stock;
                LogHelper::registrar('editar', 'producto_variante', $existente['id_variante'], $detalle);
                return ['status' => 'actualizado'];
            }
            return ['status' => 'error', 'error' => 'Error al actualizar'];
        } else {
            // Crear nueva variante
            $stmtInsert = $db->prepare("INSERT INTO producto_variante(id_producto, talla, color, codigo_barras, precio_venta, stock, estado) 
                                         VALUES (:id_p, :talla, :color, :sku, :precio, :stock, :estado)");
            
            $stmtInsert->bindParam(":id_p", $idProducto);
            $stmtInsert->bindParam(":talla", $row['talla']);
            $stmtInsert->bindParam(":color", $row['color']);
            $stmtInsert->bindParam(":sku", $codigoBarras);
            $stmtInsert->bindParam(":precio", $row['precio_venta']);
            $stmtInsert->bindParam(":stock", $stock);
            $stmtInsert->bindParam(":estado", $estadoVariante);
            
            if ($stmtInsert->execute()) {
                $idVariante = $db->lastInsertId();
                
                // Registrar log
                $detalle = "Carga CSV - Producto: " . $row['nombre_producto'] . " | Talla: " . $row['talla'] . " | Color: " . $row['color'] . " | SKU: " . $codigoBarras . " | Stock: " . $stock;
                LogHelper::registrar('crear', 'producto_variante', $idVariante, $detalle);
                
                return ['status' => 'creado'];
            }
            
            return ['status' => 'error', 'error' => 'Error al insertar variante'];
        }
    }
}
?>