<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class Proveedor {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function listar() {
        $sql = "SELECT p.id, p.codigo, p.nombre, p.telefono, p.email, 
                       p.direccion, p.nit,
                       e.nombre AS estado,
                       p.creado_en, p.actualizado_en
                FROM proveedores p
                INNER JOIN bitacora_estados e ON p.estado_id = e.id
                ORDER BY p.id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id) {
        $sql = "SELECT * FROM proveedores WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function generarCodigo() {
        $sql = "SELECT MAX(id) AS ultimo_id FROM proveedores";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $ultimo = $resultado['ultimo_id'] ?? 0;
        $nuevo = $ultimo + 1;
        return 'PROV-' . str_pad($nuevo, 4, '0', STR_PAD_LEFT);
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

        $sql = "INSERT INTO proveedores 
                (codigo, nombre, telefono, email, direccion, nit, estado_id, usuario_creador_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $codigo,
            $data['nombre'],
            $data['telefono'] ?: null,
            $data['email'] ?: null,
            $data['direccion'] ?: null,
            $data['nit'] ?: null,
            $data['estado_id'],
            $usuario_id
        ]);
    }

    public function actualizar($data) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        $sql = "UPDATE proveedores SET
                    nombre = ?, telefono = ?, email = ?, direccion = ?, 
                    nit = ?, estado_id = ?, usuario_modificador_id = ?
                WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['nombre'],
            $data['telefono'] ?: null,
            $data['email'] ?: null,
            $data['direccion'] ?: null,
            $data['nit'] ?: null,
            $data['estado_id'],
            $usuario_id,
            $data['id']
        ]);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM proveedores WHERE id = ?";
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