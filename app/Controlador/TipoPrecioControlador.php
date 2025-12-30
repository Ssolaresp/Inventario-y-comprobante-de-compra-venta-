<?php
require_once __DIR__ . '/../Modelo/TipoPrecio.php';

class TipoPrecioControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new TipoPrecio();
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

    public function obtenerEstados() {
        return $this->modelo->obtenerEstados();
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