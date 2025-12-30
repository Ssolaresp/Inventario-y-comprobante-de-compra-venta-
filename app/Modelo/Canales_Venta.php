<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class CanalVenta {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function listar() {
        $sql = "SELECT 
                    c.id_canal,
                    c.codigo_canal,
                    c.nombre_canal,
                    c.descripcion,
                    e.nombre AS estado,
                    c.fecha_registro,
                    c.creado_en,
                    c.actualizado_en
                FROM canales_venta c
                INNER JOIN bitacora_estados e ON c.estado_id = e.id
                ORDER BY c.id_canal DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id) {
        $sql = "SELECT * FROM canales_venta WHERE id_canal = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function generarCodigo() {
        $sql = "SELECT MAX(id_canal) AS ultimo_id FROM canales_venta";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $ultimo = $resultado['ultimo_id'] ?? 0;
        $nuevo = $ultimo + 1;
        return 'CAN-' . str_pad($nuevo, 3, '0', STR_PAD_LEFT);
    }

    public function obtenerSiguienteCodigo() {
        return $this->generarCodigo();
    }

    public function insertar($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        $codigo = $this->generarCodigo();

        $sql = "INSERT INTO canales_venta
                (codigo_canal, nombre_canal, descripcion, estado_id, fecha_registro, creado_en)
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $codigo,
            $data['nombre_canal'],
            $data['descripcion'],
            $data['estado_id']
        ]);
    }

    public function actualizar($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        $sql = "UPDATE canales_venta SET
                    nombre_canal = ?,
                    descripcion = ?,
                    estado_id = ?,
                    actualizado_en = CURRENT_TIMESTAMP
                WHERE id_canal = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['nombre_canal'],
            $data['descripcion'],
            $data['estado_id'],
            $data['id_canal']
        ]);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM canales_venta WHERE id_canal = ?";
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
