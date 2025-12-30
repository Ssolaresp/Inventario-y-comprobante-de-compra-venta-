<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class Usuario {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function listar() {
        $sql = "SELECT u.id, u.nombre, u.correo, b.nombre AS estado, u.creado_en, u.actualizado_en
                FROM usuarios u
                INNER JOIN bitacora_estados b ON u.estado_id = b.id
                ORDER BY u.id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id) {
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertar($data) {
        $hashed_password = password_hash($data['contrasena'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nombre, correo, contrasena, estado_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['correo'],
            $hashed_password,
            $data['estado_id']
        ]);
    }

    public function actualizar($data) {
        if (!empty($data['contrasena'])) {
            // Actualiza la contraseña (hasheada)
            $hashed_password = password_hash($data['contrasena'], PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET nombre = ?, correo = ?, contrasena = ?, estado_id = ? WHERE id = ?";
            $params = [
                $data['nombre'],
                $data['correo'],
                $hashed_password,
                $data['estado_id'],
                $data['id']
            ];
        } else {
            // No cambia la contraseña
            $sql = "UPDATE usuarios SET nombre = ?, correo = ?, estado_id = ? WHERE id = ?";
            $params = [
                $data['nombre'],
                $data['correo'],
                $data['estado_id'],
                $data['id']
            ];
        }

        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute($params);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
    }

    public function obtenerEstados() {
        $sql = "SELECT id, nombre FROM bitacora_estados ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
