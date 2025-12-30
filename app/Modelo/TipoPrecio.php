<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class TipoPrecio {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    // 📋 Listar todos los tipos de precio con estado
    public function listar() {
        $sql = "SELECT tp.id,
                       tp.nombre,
                       tp.descripcion,
                       e.nombre AS estado,
                       tp.creado_en,
                       tp.actualizado_en
                FROM tipos_precio tp
                INNER JOIN estados e ON tp.estado_id = e.id
                ORDER BY tp.id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔍 Obtener un registro por ID
    public function obtener($id) {
        $sql = "SELECT * FROM tipos_precio WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ➕ Insertar nuevo registro
    public function insertar($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        $sql = "INSERT INTO tipos_precio
                (nombre, descripcion, estado_id, usuario_creador_id, usuario_modificador_id)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['estado_id'],
            $usuario_id,
            $usuario_id
        ]);
    }

    // ✏️ Actualizar registro existente
    public function actualizar($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        $sql = "UPDATE tipos_precio SET
                    nombre = ?,
                    descripcion = ?,
                    estado_id = ?,
                    usuario_modificador_id = ?
                WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['estado_id'],
            $usuario_id,
            $data['id']
        ]);
    }

    // 🗑️ Eliminar registro
    public function eliminar($id) {
        $sql = "DELETE FROM tipos_precio WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([$id]);
    }

    // 🔸 Obtener lista de estados
    public function obtenerEstados() {
        $sql = "SELECT id, nombre FROM estados ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}