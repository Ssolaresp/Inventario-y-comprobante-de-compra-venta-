<?php
require_once __DIR__ . '/../Conexion/Conexion.php';
class TransferenciasEstados {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function listar() {
        $sql = "SELECT id, nombre, descripcion, orden, creado_en, actualizado_en
                FROM transferencias_estados
                ORDER BY orden ASC, id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id) {
        $sql = "SELECT * FROM transferencias_estados WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertar($data) {
        $sql = "INSERT INTO transferencias_estados (nombre, descripcion, orden)
                VALUES (?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['orden']
        ]);
    }

    public function actualizar($data) {
        $sql = "UPDATE transferencias_estados SET
                    nombre = ?, descripcion = ?, orden = ?, actualizado_en = NOW()
                WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['orden'],
            $data['id']
        ]);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM transferencias_estados WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([$id]);
    }
}