<?php
require_once __DIR__ . '/../Modelo/Estado.php';

class EstadoControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new Estado();
    }

    public function listar() {
        return $this->modelo->listar();
    }
}