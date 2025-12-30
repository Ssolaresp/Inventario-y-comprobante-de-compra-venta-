<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class Rol {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function listar() {
        $sql = "SELECT * FROM roles ORDER BY id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id) {
        $sql = "SELECT * FROM roles WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertar($data) {
        $sql = "INSERT INTO roles (nombre, descripcion) VALUES (?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['descripcion']
        ]);
    }

    public function actualizar($data) {
        $sql = "UPDATE roles SET nombre = ?, descripcion = ? WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['id']
        ]);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM roles WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
    }
}
