<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class Departamento {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    /* ---------- LISTAR ---------- */
    public function listar() {
        $sql = "SELECT d.id_departamento,
                       d.codigo_departamento,
                       d.nombre_departamento,
                       d.descripcion,
                       CASE WHEN d.estado_id = 1 THEN 'Activo' ELSE 'Inactivo' END AS estado,
                       d.creado_en,
                       d.actualizado_en
                FROM departamentos d
                ORDER BY d.nombre_departamento ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ---------- OBTENER 1 ---------- */
    public function obtener($id) {
        $sql = "SELECT * FROM departamentos WHERE id_departamento = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ---------- GENERAR CÓDIGO ---------- */
    private function generarCodigo() {
        $sql = "SELECT MAX(id_departamento) AS ultimo FROM departamentos";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        $ultimo = $stmt->fetch(PDO::FETCH_ASSOC)['ultimo'] ?? 0;
        return 'DEP-' . str_pad($ultimo + 1, 3, '0', STR_PAD_LEFT);
    }
    
    public function obtenerSiguienteCodigo() {
        return $this->generarCodigo();
    }

    /* ---------- INSERTAR ---------- */
    public function insertar($data) {
        $codigo = $this->generarCodigo();

        $sql = "INSERT INTO departamentos
                (codigo_departamento, nombre_departamento, descripcion, estado_id)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $codigo,
            $data['nombre_departamento'],
            $data['descripcion'] ?: null,
            $data['estado_id'] ?? 1
        ]);
    }

    /* ---------- ACTUALIZAR ---------- */
    public function actualizar($data) {
        $sql = "UPDATE departamentos SET
                    nombre_departamento = ?,
                    descripcion = ?,
                    estado_id = ?,
                    actualizado_en = NOW()
                WHERE id_departamento = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['nombre_departamento'],
            $data['descripcion'] ?: null,
            $data['estado_id'] ?? 1,
            $data['id_departamento']
        ]);
    }

    /* ---------- ELIMINAR ---------- */
    public function eliminar($id) {
        $sql = "DELETE FROM departamentos WHERE id_departamento = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([$id]);
    }

    /* ---------- VERIFICAR SI TIENE MUNICIPIOS ---------- */
    public function tieneMunicipios($id) {
        $sql = "SELECT COUNT(*) as total FROM municipios WHERE departamento_id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }
}