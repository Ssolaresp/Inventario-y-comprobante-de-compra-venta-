<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class RolUsuario {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function listar() {
        $sql = "SELECT ru.id, u.nombre AS usuario, r.nombre AS rol, ru.asignado_en
                FROM rol_usuario ru
                INNER JOIN usuarios u ON ru.usuario_id = u.id
                INNER JOIN roles r ON ru.rol_id = r.id
                ORDER BY ru.id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id) {
        $sql = "SELECT * FROM rol_usuario WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertar($data) {
        $sql = "INSERT INTO rol_usuario (usuario_id, rol_id) VALUES (?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([
            $data['usuario_id'],
            $data['rol_id']
        ]);
    }

    public function actualizar($data) {
        $sql = "UPDATE rol_usuario SET usuario_id = ?, rol_id = ? WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([
            $data['usuario_id'],
            $data['rol_id'],
            $data['id']
        ]);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM rol_usuario WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
    }

    public function obtenerUsuarios() {
        $sql = "SELECT id, nombre FROM usuarios ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerRoles() {
        $sql = "SELECT id, nombre FROM roles ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
