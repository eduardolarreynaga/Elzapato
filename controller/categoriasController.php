<?php
class CategoriasController {
    static public function ctrMostrarCategorias() {
        $tabla = "categorias";
        $respuesta = CategoriasModel::mdlMostrarCategorias($tabla);
        return $respuesta;
    }
}
