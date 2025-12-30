<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class Servicio {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function listar() {
        $sql = "SELECT s.id, s.codigo, s.nombre, s.descripcion, s.precio_base,
                       s.aplica_iva, s.porcentaje_iva,
                       cs.nombre AS categoria,
                       e.nombre AS estado,
                       s.creado_en, s.actualizado_en
                FROM servicios s
                LEFT JOIN categorias_servicios cs ON s.categoria_servicio_id = cs.id
                INNER JOIN bitacora_estados e ON s.estado_id = e.id
                ORDER BY s.id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id) {
        $sql = "SELECT * FROM servicios WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function generarCodigo() {
        $sql = "SELECT MAX(id) AS ultimo_id FROM servicios";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $ultimo = $resultado['ultimo_id'] ?? 0;
        $nuevo = $ultimo + 1;
        return 'SRV-' . str_pad($nuevo, 4, '0', STR_PAD_LEFT);
    }

    public function obtenerSiguienteCodigo() {
        return $this->generarCodigo();
    }

    public function insertar($data) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        $codigo = $this->generarCodigo();

        $sql = "INSERT INTO servicios 
                (codigo, nombre, descripcion, precio_base, aplica_iva, porcentaje_iva, 
                 categoria_servicio_id, estado_id, usuario_creador_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $codigo,
            $data['nombre'],
            $data['descripcion'],
            $data['precio_base'],
            isset($data['aplica_iva']) ? 1 : 0,
            $data['porcentaje_iva'] ?? 12.00,
            $data['categoria_servicio_id'] ?: null,
            $data['estado_id'],
            $usuario_id
        ]);
    }

    public function actualizar($data) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        $sql = "UPDATE servicios SET
                    nombre = ?, descripcion = ?, precio_base = ?, 
                    aplica_iva = ?, porcentaje_iva = ?,
                    categoria_servicio_id = ?, estado_id = ?, usuario_modificador_id = ?
                WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['precio_base'],
            isset($data['aplica_iva']) ? 1 : 0,
            $data['porcentaje_iva'] ?? 12.00,
            $data['categoria_servicio_id'] ?: null,
            $data['estado_id'],
            $usuario_id,
            $data['id']
        ]);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM servicios WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function obtenerEstados() {
        $sql = "SELECT id, nombre FROM bitacora_estados ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCategorias() {
        $sql = "SELECT id, nombre FROM categorias_servicios 
                WHERE estado_id = 1 ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorCodigo($codigo) {
        $sql = "SELECT s.*, cs.nombre AS categoria
                FROM servicios s
                LEFT JOIN categorias_servicios cs ON s.categoria_servicio_id = cs.id
                WHERE s.codigo = ? AND s.estado_id = 1";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$codigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
