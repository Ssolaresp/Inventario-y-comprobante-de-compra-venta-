<?php
require_once __DIR__ . '/../Conexion/conexion.php';

class UsuariosLogin {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function obtenerPorCorreo($correo) {
        $sql = "SELECT * FROM usuarios WHERE correo = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
