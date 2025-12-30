<?php
require_once __DIR__ . '/../Modelo/InventarioAlmacen.php';

class InventarioAlmacenControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new InventarioAlmacen();
    }

    public function listar() {
        return $this->modelo->listar();
    }

    public function obtener($id) {
        return $this->modelo->obtener($id);
    }

    public function guardar($data) {
        // Convertir valores vacíos a NULL
        $data = $this->limpiarDatos($data);
        
        if (empty($data['id'])) {
            $this->modelo->insertar($data);
        } else {
            $this->modelo->actualizar($data);
        }
    }

    public function eliminar($id) {
        $this->modelo->eliminar($id);
    }

    public function obtenerProductos() {
        return $this->modelo->obtenerProductos();
    }

    public function obtenerAlmacenes() {
        return $this->modelo->obtenerAlmacenes();
    }

    // Convertir strings vacíos a NULL
    private function limpiarDatos($data) {
        foreach ($data as $key => $value) {
            if ($value === '' || $value === null) {
                $data[$key] = null;
            }
        }
        return $data;
    }
}