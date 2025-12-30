<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class Almacen {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function listar() {
        $sql = "SELECT a.id, a.codigo, a.nombre, a.ubicacion, a.descripcion,
                       u.nombre AS responsable,
                       e.nombre AS estado,
                       a.creado_en, a.actualizado_en
                FROM almacenes a
                LEFT JOIN usuarios u ON a.responsable_usuario_id = u.id
                INNER JOIN bitacora_estados e ON a.estado_id = e.id
                ORDER BY a.id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id) {
        $sql = "SELECT * FROM almacenes WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function generarCodigo() {
        $sql = "SELECT MAX(id) AS ultimo_id FROM almacenes";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $ultimo = $resultado['ultimo_id'] ?? 0;
        $nuevo = $ultimo + 1;
        return 'ALM-' . str_pad($nuevo, 3, '0', STR_PAD_LEFT);
    }

    public function obtenerSiguienteCodigo() {
        return $this->generarCodigo();
    }

    public function insertar($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        $codigo = $this->generarCodigo();

        $sql = "INSERT INTO almacenes 
                (codigo, nombre, ubicacion, descripcion, responsable_usuario_id, estado_id, usuario_creador_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $codigo,
            $data['nombre'],
            $data['ubicacion'],
            $data['descripcion'],
            $data['responsable_usuario_id'] ?: null,
            $data['estado_id'],
            $usuario_id
        ]);
    }

    public function actualizar($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        $sql = "UPDATE almacenes SET
                    nombre = ?, ubicacion = ?, descripcion = ?, 
                    responsable_usuario_id = ?, estado_id = ?, usuario_modificador_id = ?
                WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['nombre'],
            $data['ubicacion'],
            $data['descripcion'],
            $data['responsable_usuario_id'] ?: null,
            $data['estado_id'],
            $usuario_id,
            $data['id']
        ]);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM almacenes WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
    }

    public function obtenerEstados() {
        $sql = "SELECT id, nombre FROM bitacora_estados ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerUsuariosResponsables() {
        $sql = "SELECT id, nombre FROM usuarios ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
