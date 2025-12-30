<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class TiposSalida {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function listar() {
        $sql = "SELECT ts.id, ts.nombre, ts.descripcion, ts.requiere_autorizacion,
                       e.nombre AS estado,
                       uc.nombre AS creador,
                       um.nombre AS modificador,
                       ts.creado_en, ts.actualizado_en
                FROM tipos_salida ts
                INNER JOIN estados e ON ts.estado_id = e.id
                LEFT JOIN usuarios uc ON ts.usuario_creador_id = uc.id
                LEFT JOIN usuarios um ON ts.usuario_modificador_id = um.id
                ORDER BY ts.id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id) {
        $sql = "SELECT * FROM tipos_salida WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertar($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        $sql = "INSERT INTO tipos_salida 
                (nombre, descripcion, requiere_autorizacion, estado_id, usuario_creador_id)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['requiere_autorizacion'] ?? 0,
            $data['estado_id'],
            $usuario_id
        ]);
    }

    public function actualizar($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        $sql = "UPDATE tipos_salida SET
                    nombre = ?, descripcion = ?, requiere_autorizacion = ?, 
                    estado_id = ?, usuario_modificador_id = ?, actualizado_en = NOW()
                WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['requiere_autorizacion'] ?? 0,
            $data['estado_id'],
            $usuario_id,
            $data['id']
        ]);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM tipos_salida WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function obtenerEstados() {
        $sql = "SELECT id, nombre FROM estados WHERE aplica_a = 'tipos_salida' ORDER BY nombre";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}