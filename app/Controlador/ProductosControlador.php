<?php
require_once __DIR__ . '/../Modelo/Productos.php';

class ProductosControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new Producto();
    }

    public function listar() {
        return $this->modelo->listar();
    }

    public function obtener($id) {
        return $this->modelo->obtener($id);
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

    public function obtenerCategorias() {
        return $this->modelo->obtenerCategorias();
    }

    public function obtenerUnidadesMedida() {
        return $this->modelo->obtenerUnidadesMedida();
    }

    public function obtenerProveedores() {
        return $this->modelo->obtenerProveedores();
    }

    public function obtenerSiguienteCodigo() {
        return $this->modelo->obtenerSiguienteCodigo();
    }
}