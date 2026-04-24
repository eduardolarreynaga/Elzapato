<?php
require_once __DIR__ . '/../model/ClienteModel.php';

class ClienteController {
    private $model;

    public function __construct() {
        $this->model = new ClienteModel();
    }

    // Vista principal: lista clientes y estadísticas
    public function index() {
        $clientes = $this->model->listar();
        $stats = $this->model->obtenerEstadisticas();

        return [
            'clientes' => $clientes,
            'stats' => $stats
        ];
    }

    // Agregar cliente
    public function agregar($data) {
        return $this->model->agregar($data);
    }

    // Editar cliente
    public function editar($id, $data) {
        return $this->model->editar($id, $data);
    }

    // Eliminar cliente
    public function eliminar($id) {
        return $this->model->eliminar($id);
    }
}