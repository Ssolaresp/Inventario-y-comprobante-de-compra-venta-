<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class UnidadMedida {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    /** 🔹 Listar todas las unidades de medida */
    public function listar() {
        $sql = "SELECT u.id, u.nombre, u.abreviatura, u.descripcion,
                       e.nombre AS estado, u.creado_en, u.actualizado_en
                FROM unidades_medida u
                INNER JOIN bitacora_estados e ON u.estado_id = e.id
                ORDER BY u.id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** 🔹 Obtener una unidad por ID */
    public function obtener($id) {
        $sql = "SELECT * FROM unidades_medida WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** 🔹 Insertar nueva unidad */
    public function insertar($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        $sql = "INSERT INTO unidades_medida (nombre, abreviatura, descripcion, estado_id, usuario_creador_id)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['abreviatura'],
            $data['descripcion'],
            $data['estado_id'],
            $usuario_id
        ]);
    }

    /** 🔹 Actualizar una unidad existente */
    public function actualizar($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        $sql = "UPDATE unidades_medida
                SET nombre = ?, abreviatura = ?, descripcion = ?, estado_id = ?, usuario_modificador_id = ?
                WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['abreviatura'],
            $data['descripcion'],
            $data['estado_id'],
            $usuario_id,
            $data['id']
        ]);
    }

    /** 🔹 Eliminar */
    public function eliminar($id) {
        $sql = "DELETE FROM unidades_medida WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
    }

    /** 🔹 Obtener estados */
    public function obtenerEstados() {
        $sql = "SELECT id, nombre FROM bitacora_estados ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
