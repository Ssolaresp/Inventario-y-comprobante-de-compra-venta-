<?php
require_once __DIR__ . '/../Modelo/Cliente.php';

class ClientesControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new Cliente();
    }

    public function listar() {
        return $this->modelo->listar();
    }
    
    public function obtener($id) {
        return $this->modelo->obtener($id);
    }
    
    public function guardar($data) {
        if (empty($data['id_cliente'])) {
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
    
    public function obtenerCanales() {
        return $this->modelo->obtenerCanales();
    }
    
    public function obtenerDepartamentos() {
        return $this->modelo->obtenerDepartamentos();
    }
    
    public function obtenerMunicipios($idDepa) {
        return $this->modelo->obtenerMunicipios($idDepa);
    }
    
    public function obtenerSiguienteCodigo() {
        return $this->modelo->obtenerSiguienteCodigo();
    }
}