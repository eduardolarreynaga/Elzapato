<?php
class MarcasController {
    static public function ctrMostrarMarcas() {
        $tabla = "marcas";
        $respuesta = MarcasModel::mdlMostrarMarcas($tabla);
        return $respuesta;
    }
}
