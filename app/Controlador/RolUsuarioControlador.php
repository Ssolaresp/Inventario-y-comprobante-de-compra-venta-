<?php
require_once __DIR__ . '/../Modelo/RolUsuario.php';

class RolUsuarioControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new RolUsuario();
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

    public function obtenerUsuarios() {
        return $this->modelo->obtenerUsuarios();
    }

    public function obtenerRoles() {
        return $this->modelo->obtenerRoles();
    }
}
