<?php

class VentasController {

    static public function ctrMostrarVentas() {
        $tabla = "ventas";
        $respuesta = VentasModel::mdlMostrarVentas($tabla);
        return $respuesta;
    }
}