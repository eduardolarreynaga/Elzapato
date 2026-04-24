<?php

class ProductoVarianteController {

    /*=============================================
    MOSTRAR TODO EL STOCK PARA REPORTES
    =============================================*/
    static public function ctrMostrarTodoElStock() {
        // Llamamos al método estático del modelo
        return ProductoVarianteModel::mdlObtenerTodoElStock();
    }

    /*=============================================
    MOSTRAR ALERTAS DE STOCK BAJO
    =============================================*/
    static public function ctrMostrarStockBajo($umbral = 10) {
        // Por defecto el umbral es 10, pero se puede cambiar desde la vista
        return ProductoVarianteModel::mdlObtenerStockBajo($umbral);
    }

    /*=============================================
    MOSTRAR RESUMEN DE ESTADÍSTICAS (CARDS)
    =============================================*/
    static public function ctrMostrarResumenReportes() {
        return ProductoVarianteModel::mdlObtenerResumenReportes();
    }

    /*=============================================
    MÉTODO PARA INICIALIZAR LA VISTA DE REPORTES
    (Este método prepara todas las variables para la vista)
    =============================================*/
    public function ctrVerReportes() {
        
        // 1. Cargamos los datos reales usando los métodos estáticos anteriores
        $dataStock   = self::ctrMostrarTodoElStock();
        $dataAlertas = self::ctrMostrarStockBajo(10);
        $stats       = self::ctrMostrarResumenReportes();

        // 2. Definimos las variables de configuración de la página
        $activeMenu = 'reportes';
        $pageTitle  = 'Reportes | ElZapato';
        $pageStyles = [
            '/ElZapato/Assets/css/pages/admin-stats.css', 
            '/ElZapato/Assets/css/pages/admin-reportes.css'
        ];

        // 3. Incluimos la vista (La vista podrá usar $dataStock, $dataAlertas y $stats)
        // Ajusta la ruta según donde se encuentre tu archivo físico
        include __DIR__ . '/../src/views/admin/reportes.php';
    }

    static public function ctrMostrarHistorialMovimientos() {
        $tabla = "movimientos"; // Nombre lógico
        $respuesta = ProductoVarianteModel::mdlMostrarMovimientos($tabla);
        return $respuesta;
    }

    static public function ctrTopClientes() {
        return ProductoVarianteModel::mdlTopClientes();
    }

    static public function ctrResumenTickets() {
        return ProductoVarianteModel::mdlResumenTickets();
    }

    static public function ctrUltimasCompras() {
        return ProductoVarianteModel::mdlUltimasCompras();
    }

    static public function ctrResumenCaja() {
        return ProductoVarianteModel::mdlResumenCaja();
    }
}
