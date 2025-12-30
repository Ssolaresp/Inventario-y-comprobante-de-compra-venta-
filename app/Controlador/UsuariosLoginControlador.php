<?php
require_once __DIR__ . '/../Modelo/UsuariosLogin.php';

class UsuariosLoginControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new UsuariosLogin();
    }

    public function verificarCredenciales($correo, $contrasena) {
        $usuario = $this->modelo->obtenerPorCorreo($correo);
        if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
            return $usuario;
        }
        return false;
    }
}
