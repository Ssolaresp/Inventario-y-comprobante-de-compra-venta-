<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class Estado {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    // 📋 Listar todos los estados
    public function listar() {
        $sql = "SELECT id,
                       nombre,
                       descripcion,
                       aplica_a,
                       creado_en,
                       actualizado_en
                FROM estados
                ORDER BY id ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}