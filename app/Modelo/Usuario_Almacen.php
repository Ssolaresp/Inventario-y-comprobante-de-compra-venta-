<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class UsuarioAlmacen {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function listar() {
        $sql = "SELECT ua.id,
                       u.nombre AS usuario,
                       a.nombre AS almacen,
                       e.nombre AS estado,
                       ua.creado_en,
                       ua.actualizado_en
                FROM usuario_almacen ua
                INNER JOIN usuarios u ON ua.usuario_id = u.id
                INNER JOIN almacenes a ON ua.almacen_id = a.id
                INNER JOIN estados e ON ua.estado_id = e.id
                ORDER BY ua.id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id) {
        $sql = "SELECT * FROM usuario_almacen WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertar($data) {
        $sql = "INSERT INTO usuario_almacen (usuario_id, almacen_id, estado_id)
                VALUES (?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['usuario_id'],
            $data['almacen_id'],
            $data['estado_id']
        ]);
    }

    public function actualizar($data) {
        $sql = "UPDATE usuario_almacen
                SET usuario_id = ?, almacen_id = ?, estado_id = ?
                WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['usuario_id'],
            $data['almacen_id'],
            $data['estado_id'],
            $data['id']
        ]);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM usuario_almacen WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
    }

    public function obtenerUsuarios() {
        $sql = "SELECT id, nombre FROM usuarios ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerAlmacenes() {
        $sql = "SELECT id, nombre FROM almacenes ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEstados() {
        $sql = "SELECT id, nombre FROM estados ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
