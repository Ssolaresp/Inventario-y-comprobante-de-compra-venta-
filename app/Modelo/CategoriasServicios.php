<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class CategoriaServicio {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function listar() {
        $sql = "SELECT cs.id, cs.codigo, cs.nombre, cs.descripcion,
                       e.nombre AS estado,
                       cs.creado_en, cs.actualizado_en
                FROM categorias_servicios cs
                INNER JOIN bitacora_estados e ON cs.estado_id = e.id
                ORDER BY cs.id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id) {
        $sql = "SELECT * FROM categorias_servicios WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function generarCodigo() {
        $sql = "SELECT MAX(id) AS ultimo_id FROM categorias_servicios";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $ultimo = $resultado['ultimo_id'] ?? 0;
        $nuevo = $ultimo + 1;
        return 'CAT-' . str_pad($nuevo, 3, '0', STR_PAD_LEFT);
    }

    public function obtenerSiguienteCodigo() {
        return $this->generarCodigo();
    }

    public function insertar($data) {
        $codigo = $this->generarCodigo();

        $sql = "INSERT INTO categorias_servicios 
                (codigo, nombre, descripcion, estado_id)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $codigo,
            $data['nombre'],
            $data['descripcion'],
            $data['estado_id']
        ]);
    }

    public function actualizar($data) {
        $sql = "UPDATE categorias_servicios SET
                    nombre = ?, descripcion = ?, estado_id = ?
                WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['estado_id'],
            $data['id']
        ]);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM categorias_servicios WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function obtenerEstados() {
        $sql = "SELECT id, nombre FROM bitacora_estados ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}