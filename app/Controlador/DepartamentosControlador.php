<?php
require_once __DIR__ . '/../Modelo/Departamento.php';

class DepartamentosControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new Departamento();
    }

    public function listar() {
        return $this->modelo->listar();
    }
    
    public function obtener($id) {
        return $this->modelo->obtener($id);
    }
    
    public function guardar($data) {
        if (empty($data['id_departamento'])) {
            return $this->modelo->insertar($data);
        } else {
            return $this->modelo->actualizar($data);
        }
    }
    
    public function eliminar($id) {
        // Verificar si tiene municipios asociados
        if ($this->modelo->tieneMunicipios($id)) {
            return false;
        }
        return $this->modelo->eliminar($id);
    }

    public function obtenerSiguienteCodigo() {
        return $this->modelo->obtenerSiguienteCodigo();
    }
}