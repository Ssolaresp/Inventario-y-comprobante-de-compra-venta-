<?php
require_once __DIR__ . '/../Modelo/Municipio.php';

class MunicipiosControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new Municipio();
    }

    public function listar() {
        return $this->modelo->listar();
    }
    
    public function obtener($id) {
        return $this->modelo->obtener($id);
    }
    
    public function guardar($data) {
        if (empty($data['id_municipio'])) {
            return $this->modelo->insertar($data);
        } else {
            return $this->modelo->actualizar($data);
        }
    }
    
    public function eliminar($id) {
        return $this->modelo->eliminar($id);
    }

    public function obtenerSiguienteCodigo() {
        return $this->modelo->obtenerSiguienteCodigo();
    }

    public function obtenerDepartamentos() {
        return $this->modelo->obtenerDepartamentos();
    }
}