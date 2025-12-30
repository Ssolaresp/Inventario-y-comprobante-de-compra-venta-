<?php
require_once __DIR__ . '/../Modelo/ListaPrecioDetalle.php';

class ListaPrecioDetalleControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new ListaPrecioDetalle();
    }

    // ========== CABECERA ==========
    
    public function listarCabecera() {
        return $this->modelo->listarCabecera();
    }

    public function obtenerCabecera($id) {
        return $this->modelo->obtenerCabecera($id);
    }

    public function guardarCabecera($data) {
        $data = $this->limpiarDatos($data);
        
        if (empty($data['id'])) {
            return $this->modelo->insertarCabecera($data);
        } else {
            $this->modelo->actualizarCabecera($data);
            return $data['id'];
        }
    }

    public function eliminarCabecera($id) {
        return $this->modelo->eliminarCabecera($id);
    }

    // ========== DETALLE ==========
    
    public function listarDetalle($lista_precio_id) {
        return $this->modelo->listarDetalle($lista_precio_id);
    }

    public function obtenerDetalle($id) {
        return $this->modelo->obtenerDetalle($id);
    }

    public function guardarDetalle($data) {
        $data = $this->limpiarDatos($data);
        
        if (empty($data['id'])) {
            $this->modelo->insertarDetalle($data);
        } else {
            $this->modelo->actualizarDetalle($data);
        }
    }

    public function eliminarDetalle($id) {
        return $this->modelo->eliminarDetalle($id);
    }

    // ========== DATOS AUXILIARES ==========
    
    public function obtenerMonedas() {
        return $this->modelo->obtenerMonedas();
    }

    public function obtenerEstados() {
        return $this->modelo->obtenerEstados();
    }

    public function obtenerProductos() {
        return $this->modelo->obtenerProductos();
    }

    public function obtenerTiposPrecios() {
        return $this->modelo->obtenerTiposPrecios();
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