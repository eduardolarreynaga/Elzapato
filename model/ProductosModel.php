<?php
require_once "conexion.php";

class ProductosModel {

    /*=============================================
    MOSTRAR PRODUCTOS (VERSIÓN ÚNICA CON PROVEEDOR)
    =============================================*/
    static public function mdlMostrarProductos($tabla1, $tabla2) {
        $stmt = Conexion::conectar()->prepare("SELECT 
            p.id_producto, 
            p.nombre_producto, 
            p.id_categoria, 
            p.id_marca,
            p.id_proveedor,
            p.descripcion,
            p.estado as estado_producto,
            c.nombre_categoria,
            m.nombre_marca,
            prov.nombre_empresa,
            GROUP_CONCAT(CONCAT(v.talla, '|', v.color, '|', v.precio_venta, '|', v.stock, '|', v.codigo_barras, '|', v.estado, '|', v.id_variante) SEPARATOR '||') as info_variantes
        FROM $tabla1 p
        LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
        LEFT JOIN marcas m ON p.id_marca = m.id_marca
        LEFT JOIN proveedores prov ON p.id_proveedor = prov.id_proveedor
        LEFT JOIN $tabla2 v ON p.id_producto = v.id_producto
        GROUP BY p.id_producto");

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*=============================================
    MOSTRAR PRODUCTOS PAGINADOS
    =============================================*/
    static public function mdlMostrarProductosPaginados($tabla1, $tabla2, $base, $tope) {
        $sql = "SELECT 
                    p.id_producto, 
                    p.nombre_producto, 
                    p.id_categoria, 
                    p.id_marca,
                    p.id_proveedor,
                    p.descripcion,
                    p.estado as estado_producto,
                    c.nombre_categoria,
                    m.nombre_marca,
                    prov.nombre_empresa,
                    GROUP_CONCAT(CONCAT(v.talla, '|', v.color, '|', v.precio_venta, '|', v.stock, '|', v.codigo_barras, '|', v.estado, '|', v.id_variante) SEPARATOR '||') as info_variantes
                FROM $tabla1 p
                LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
                LEFT JOIN marcas m ON p.id_marca = m.id_marca
                LEFT JOIN proveedores prov ON p.id_proveedor = prov.id_proveedor
                LEFT JOIN $tabla2 v ON p.id_producto = v.id_producto
                GROUP BY p.id_producto
                ORDER BY p.id_producto DESC
                LIMIT :base, :tope";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindValue(":base", (int)$base, PDO::PARAM_INT);
        $stmt->bindValue(":tope", (int)$tope, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*=============================================
    MOSTRAR PROVEEDORES (SIN FILTRO DE ESTADO)
    =============================================*/
    static public function mdlMostrarProveedores() {
        $stmt = Conexion::conectar()->prepare("SELECT id_proveedor, nombre_empresa FROM proveedores ORDER BY nombre_empresa");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*=============================================
    REGISTRAR PRODUCTO
    =============================================*/
    static public function mdlRegistrarProducto($tabla, $datos) {
        $db = Conexion::conectar();
        
        $stmt = $db->prepare("INSERT INTO $tabla(nombre_producto, descripcion, id_marca, id_categoria, id_proveedor, estado) 
                              VALUES (:nombre, :descripcion, :id_marca, :id_categoria, :id_proveedor, :estado)");
        
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
        $stmt->bindParam(":id_marca", $datos["id_marca"], PDO::PARAM_INT);
        $stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
        $stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_INT);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $db->lastInsertId(); 
        } else {
            return "error";
        }
    }

    /*=============================================
    REGISTRAR VARIANTE COMPLETA
    =============================================*/
    /*=============================================
    REGISTRAR VARIANTE COMPLETA (CON MANEJO DE ERRORES)
    =============================================*/
    static public function mdlRegistrarVariante($datos) {
        try {
            $stmt = Conexion::conectar()->prepare("INSERT INTO producto_variante(id_producto, talla, color, codigo_barras, precio_venta, stock, estado) 
                                                VALUES (:id_producto, :talla, :color, :codigo_barras, :precio, :stock, :estado)");
            
            $stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
            $stmt->bindParam(":talla", $datos["talla"], PDO::PARAM_STR);
            $stmt->bindParam(":color", $datos["color"], PDO::PARAM_STR);
            $stmt->bindParam(":codigo_barras", $datos["codigo_barras"], PDO::PARAM_STR);
            $stmt->bindParam(":precio", $datos["precio"], PDO::PARAM_STR);
            $stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_INT);
            $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return Conexion::conectar()->lastInsertId();
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            // Verificar si es error de duplicado (código 1062)
            if ($e->errorInfo[1] == 1062) {
                return "duplicado";
            }
            return "error";
        }
    }

    /*=============================================
    ACTUALIZAR PRODUCTO GENERAL
    =============================================*/
    static public function mdlActualizarProductoGeneral($datos) {
        $stmt = Conexion::conectar()->prepare("UPDATE productos SET 
                    nombre_producto = :nombre, 
                    id_categoria = :id_cat, 
                    id_marca = :id_mar,
                    id_proveedor = :id_prov,
                    descripcion = :desc
                    WHERE id_producto = :id_p");
        
        $stmt->bindParam(":nombre", $datos["nombre_producto"], PDO::PARAM_STR);
        $stmt->bindParam(":id_cat", $datos["id_categoria"], PDO::PARAM_INT);
        $stmt->bindParam(":id_mar", $datos["id_marca"], PDO::PARAM_INT);
        $stmt->bindParam(":id_prov", $datos["id_proveedor"], PDO::PARAM_INT);
        $stmt->bindParam(":desc", $datos["descripcion"], PDO::PARAM_STR);
        $stmt->bindParam(":id_p", $datos["id_producto"], PDO::PARAM_INT);

        return $stmt->execute() ? "ok" : "error";
    }

    /*=============================================
    ACTUALIZAR VARIANTE COMPLETA
    =============================================*/
    static public function mdlActualizarVarianteCompleta($datos) {
        $stmt = Conexion::conectar()->prepare("UPDATE producto_variante SET 
                    talla = :talla, 
                    color = :color, 
                    codigo_barras = :codigo,
                    precio_venta = :precio, 
                    stock = :stock,
                    estado = :estado
                    WHERE id_variante = :id_v");

        $stmt->bindParam(":talla", $datos["talla"], PDO::PARAM_STR);
        $stmt->bindParam(":color", $datos["color"], PDO::PARAM_STR);
        $stmt->bindParam(":codigo", $datos["codigo_barras"], PDO::PARAM_STR);
        $stmt->bindParam(":precio", $datos["precio_venta"], PDO::PARAM_STR);
        $stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_INT);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
        $stmt->bindParam(":id_v", $datos["id_variante"], PDO::PARAM_INT);

        return $stmt->execute() ? "ok" : "error";
    }

    /*=============================================
    ACTUALIZAR PRODUCTO (MANTENIDO POR COMPATIBILIDAD)
    =============================================*/
    static public function mdlActualizarProducto($tabla, $datos) {
        $db = Conexion::conectar();
        
        $stmt1 = $db->prepare("UPDATE productos SET 
                    nombre_producto = :nombre, 
                    id_categoria = :id_cat, 
                    id_marca = :id_mar 
                    WHERE id_producto = :id_p");
        
        $stmt1->bindParam(":nombre", $datos["nombre_producto"], PDO::PARAM_STR);
        $stmt1->bindParam(":id_cat", $datos["id_categoria"], PDO::PARAM_INT);
        $stmt1->bindParam(":id_mar", $datos["id_marca"], PDO::PARAM_INT);
        $stmt1->bindParam(":id_p", $datos["id_producto"], PDO::PARAM_INT);
        $stmt1->execute();

        $stmt2 = $db->prepare("UPDATE producto_variante SET 
                    talla = :talla, 
                    color = :color, 
                    precio_venta = :precio, 
                    stock = :stock,
                    estado = :estado
                    WHERE id_variante = :id_v");

        $stmt2->bindParam(":talla", $datos["talla"], PDO::PARAM_STR);
        $stmt2->bindParam(":color", $datos["color"], PDO::PARAM_STR);
        $stmt2->bindParam(":precio", $datos["precio_venta"], PDO::PARAM_STR);
        $stmt2->bindParam(":stock", $datos["stock"], PDO::PARAM_INT);
        $stmt2->bindParam(":estado", $datos["estado_v"], PDO::PARAM_STR);
        $stmt2->bindParam(":id_v", $datos["id_variante"], PDO::PARAM_INT);

        return $stmt2->execute() ? "ok" : "error";
    }

    /*=============================================
    PRODUCTOS MÁS VENDIDOS
    =============================================*/
    static public function mdlProductosMasVendidos($tabla) {
        $stmt = Conexion::conectar()->prepare("
            SELECT p.nombre_producto, SUM(dv.cantidad) as total_vendido 
            FROM $tabla p
            INNER JOIN producto_variante pv ON p.id_producto = pv.id_producto
            INNER JOIN detalle_venta dv ON pv.id_variante = dv.id_variante
            GROUP BY p.id_producto 
            ORDER BY total_vendido DESC 
            LIMIT 5
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*=============================================
    VENTAS POR SEMANA
    =============================================*/
    static public function mdlVentasSemana() {
        $stmt = Conexion::conectar()->prepare("
            SELECT DAYNAME(fecha_venta) as dia, SUM(total_venta) as total 
            FROM ventas 
            WHERE fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DAYOFWEEK(fecha_venta) 
            ORDER BY fecha_venta ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*=============================================
    VENTAS POR CATEGORÍA
    =============================================*/
    static public function mdlVentasPorCategoria() {
        $stmt = Conexion::conectar()->prepare("
            SELECT c.nombre_categoria as etiqueta, SUM(dv.cantidad) as valor
            FROM detalle_venta dv
            INNER JOIN producto_variante pv ON dv.id_variante = pv.id_variante
            INNER JOIN productos p ON pv.id_producto = p.id_producto
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            GROUP BY c.id_categoria
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*=============================================
    ELIMINAR VARIANTE ESPECÍFICA
    =============================================*/
    static public function mdlEliminarVariante($id_variante) {
        $stmt = Conexion::conectar()->prepare("DELETE FROM producto_variante WHERE id_variante = :id");
        $stmt->bindParam(":id", $id_variante, PDO::PARAM_INT);

        if($stmt->execute()){
            return "ok";
        } else {
            return "error";
        }
    }

    /*=============================================
    ELIMINAR PRODUCTO COMPLETO
    =============================================*/
    static public function mdlEliminarProducto($tabla, $id){
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id_producto = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    /*=============================================
OBTENER TODAS LAS VARIANTES (SOLO IDs)
=============================================*/
static public function mdlObtenerTodasLasVariantes() {
    $stmt = Conexion::conectar()->prepare("SELECT id_variante FROM producto_variante");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/*=============================================
VERIFICAR SI UNA VARIANTE EXISTE
=============================================*/
static public function mdlVerificarVarianteExistente($id_variante) {
    $stmt = Conexion::conectar()->prepare("SELECT id_variante FROM producto_variante WHERE id_variante = :id");
    $stmt->bindParam(":id", $id_variante, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/*=============================================
OBTENER EL ÚLTIMO SKU REGISTRADO
=============================================*/
static public function mdlObtenerUltimoSKU() {
    $stmt = Conexion::conectar()->prepare("SELECT codigo_barras FROM producto_variante ORDER BY id_variante DESC LIMIT 1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
}
?>