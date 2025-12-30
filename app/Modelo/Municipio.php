<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class Municipio {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    /* ---------- LISTAR ---------- */
    public function listar() {
        $sql = "SELECT m.id_municipio,
                       m.codigo_municipio,
                       m.nombre_municipio,
                       m.descripcion,
                       d.nombre_departamento,
                       CASE WHEN m.estado_id = 1 THEN 'Activo' ELSE 'Inactivo' END AS estado,
                       m.creado_en,
                       m.actualizado_en
                FROM municipios m
                INNER JOIN departamentos d ON m.departamento_id = d.id_departamento
                ORDER BY d.nombre_departamento ASC, m.nombre_municipio ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ---------- OBTENER 1 ---------- */
    public function obtener($id) {
        $sql = "SELECT * FROM municipios WHERE id_municipio = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ---------- GENERAR CÓDIGO ---------- */
    private function generarCodigo() {
        $sql = "SELECT MAX(id_municipio) AS ultimo FROM municipios";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        $ultimo = $stmt->fetch(PDO::FETCH_ASSOC)['ultimo'] ?? 0;
        return 'MUN-' . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
    
    public function obtenerSiguienteCodigo() {
        return $this->generarCodigo();
    }

    /* ---------- INSERTAR ---------- */
    public function insertar($data) {
        $codigo = $this->generarCodigo();

        $sql = "INSERT INTO municipios
                (codigo_municipio, nombre_municipio, descripcion, departamento_id, estado_id)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $codigo,
            $data['nombre_municipio'],
            $data['descripcion'] ?: null,
            $data['departamento_id'],
            $data['estado_id'] ?? 1
        ]);
    }

    /* ---------- ACTUALIZAR ---------- */
    public function actualizar($data) {
        $sql = "UPDATE municipios SET
                    nombre_municipio = ?,
                    descripcion = ?,
                    departamento_id = ?,
                    estado_id = ?,
                    actualizado_en = NOW()
                WHERE id_municipio = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['nombre_municipio'],
            $data['descripcion'] ?: null,
            $data['departamento_id'],
            $data['estado_id'] ?? 1,
            $data['id_municipio']
        ]);
    }

    /* ---------- ELIMINAR ---------- */
    public function eliminar($id) {
        $sql = "DELETE FROM municipios WHERE id_municipio = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([$id]);
    }

    /* ---------- OBTENER DEPARTAMENTOS ---------- */
    public function obtenerDepartamentos() {
        $sql = "SELECT id_departamento, nombre_departamento 
                FROM departamentos 
                WHERE estado_id = 1
                ORDER BY nombre_departamento";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}