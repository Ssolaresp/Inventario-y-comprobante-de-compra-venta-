<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class ListaPrecio {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    // 📋 Listar todas las listas de precios con relaciones
    public function listar() {
        $sql = "SELECT lp.id,
                       lp.nombre,
                       lp.descripcion,
                       m.codigo AS moneda_codigo,
                       m.simbolo AS moneda_simbolo,
                       m.nombre AS moneda,
                       lp.vigente_desde,
                       lp.vigente_hasta,
                       e.nombre AS estado,
                       lp.creado_en,
                       lp.actualizado_en
                FROM listas_precios lp
                INNER JOIN monedas m ON lp.moneda_id = m.id
                INNER JOIN estados e ON lp.estado_id = e.id
                ORDER BY lp.id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔍 Obtener un registro por ID
    public function obtener($id) {
        $sql = "SELECT * FROM listas_precios WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ➕ Insertar nuevo registro
    public function insertar($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        $sql = "INSERT INTO listas_precios
                (nombre, descripcion, moneda_id, vigente_desde, vigente_hasta, 
                 estado_id, usuario_creador_id, usuario_modificador_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['moneda_id'],
            $data['vigente_desde'],
            $data['vigente_hasta'],
            $data['estado_id'],
            $usuario_id,
            $usuario_id
        ]);
    }

    // ✏️ Actualizar registro existente
    public function actualizar($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        $sql = "UPDATE listas_precios SET
                    nombre = ?,
                    descripcion = ?,
                    moneda_id = ?,
                    vigente_desde = ?,
                    vigente_hasta = ?,
                    estado_id = ?,
                    usuario_modificador_id = ?
                WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['moneda_id'],
            $data['vigente_desde'],
            $data['vigente_hasta'],
            $data['estado_id'],
            $usuario_id,
            $data['id']
        ]);
    }

    // 🗑️ Eliminar registro
    public function eliminar($id) {
        $sql = "DELETE FROM listas_precios WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([$id]);
    }

    // 🔸 Obtener lista de monedas activas
    public function obtenerMonedas() {
        $sql = "SELECT id, codigo, nombre, simbolo 
                FROM monedas 
                WHERE estado_id = 1 
                ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔸 Obtener lista de estados
    public function obtenerEstados() {
        $sql = "SELECT id, nombre FROM estados ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}