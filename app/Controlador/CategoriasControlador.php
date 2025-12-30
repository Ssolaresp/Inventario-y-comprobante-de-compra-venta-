<?php
require_once __DIR__ . '/../Modelo/Categorias.php';

class CategoriasControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new Categoria();
    }

    public function listar() {
        return $this->modelo->listar();
    }

    public function obtener($id) {
        return $this->modelo->obtener($id);
    }

public function obtenerSiguienteCodigo() {
    return $this->modelo->obtenerSiguienteCodigo();
}


    public function guardar($data) {
        if (empty($data['id'])) {
            $this->modelo->insertar($data);
        } else {
            $this->modelo->actualizar($data);
        }
    }

    public function eliminar($id) {
        $this->modelo->eliminar($id);
    }

    public function obtenerEstados() {
        return $this->modelo->obtenerEstados();
    }

    public function obtenerCategoriasPadre() {
        return $this->modelo->obtenerCategoriasPadre();
    }
}
