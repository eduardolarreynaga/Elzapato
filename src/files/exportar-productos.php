<?php
require_once "../../model/ProductosModel.php";
require_once "../../model/conexion.php";

// 1. Obtener los datos reales
$productos = ProductosModel::mdlMostrarProductos("productos", "producto_variante");

// 2. Configurar cabeceras para descarga segura
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=reporte_productos_'.date('Y-m-d').'.csv');

// 3. Crear el archivo de salida
$output = fopen('php://output', 'w');

// Cabeceras de las columnas
fputcsv($output, array('ID', 'Producto', 'Categoria', 'Marca', 'Precio', 'Stock', 'Estado'));

// Rellenar con los datos de la base de datos
foreach ($productos as $row) {
    fputcsv($output, array(
        $row['id_producto'],
        $row['nombre_producto'],
        $row['nombre_categoria'],
        $row['nombre_marca'],
        $row['precio_venta'],
        $row['stock'],
        $row['estado']
    ));
}
fclose($output);
