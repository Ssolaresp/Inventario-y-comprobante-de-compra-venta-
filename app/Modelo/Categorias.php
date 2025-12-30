<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class Categoria {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function listar() {
        $sql = "SELECT c.id, c.codigo, c.nombre, c.descripcion, 
                       cp.nombre AS categoria_padre,
                       e.nombre AS estado, 
                       c.creado_en, c.actualizado_en
                FROM categorias c
                LEFT JOIN categorias cp ON c.categoria_padre_id = cp.id
                INNER JOIN bitacora_estados e ON c.estado_id = e.id
                ORDER BY c.id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id) {
        $sql = "SELECT * FROM categorias WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



// 🔹 Permite obtener el siguiente código desde fuera del modelo
public function obtenerSiguienteCodigo() {
    return $this->generarCodigo();
}


/*
    /** 🔹 Genera el siguiente código tipo CAT-0001, CAT-0002, ... 
    private function generarCodigo() {
        $sql = "SELECT MAX(CAST(SUBSTRING(codigo, 5) AS INTEGER)) AS ultimo
                FROM categorias
                WHERE codigo LIKE 'CAT-%'";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $ultimo = $resultado['ultimo'] ?? 0;
        $nuevo = $ultimo + 1;
        return 'CAT-' . str_pad($nuevo, 3, '0', STR_PAD_LEFT);
    }
        



    */


    /** 🔹 Genera el siguiente código tipo CAT-001, CAT-002, ... */
private function generarCodigo() {
    $sql = "SELECT MAX(CAST(SUBSTRING(codigo, 5) AS UNSIGNED)) AS ultimo
            FROM categorias
            WHERE codigo LIKE 'CAT-%'";
    $stmt = $this->conexion->getConexion()->prepare($sql);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $ultimo = $resultado['ultimo'] ?? 0;
    $nuevo = $ultimo + 1;
    return 'CAT-' . str_pad($nuevo, 3, '0', STR_PAD_LEFT);
}




    public function insertar($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        // 🔸 Generar el código automáticamente
        $codigo = $this->generarCodigo();

        $sql = "INSERT INTO categorias (codigo, nombre, descripcion, categoria_padre_id, estado_id, usuario_creador_id)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([
            $codigo,
            $data['nombre'],
            $data['descripcion'],
            $data['categoria_padre_id'] ?: null,
            $data['estado_id'],
            $usuario_id
        ]);
    }

    public function actualizar($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        // 🔸 Código NO se modifica
        $sql = "UPDATE categorias
                SET nombre = ?, descripcion = ?, categoria_padre_id = ?, estado_id = ?, usuario_modificador_id = ?
                WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['categoria_padre_id'] ?: null,
            $data['estado_id'],
            $usuario_id,
            $data['id']
        ]);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM categorias WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
    }

    public function obtenerEstados() {
        $sql = "SELECT id, nombre FROM bitacora_estados ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCategoriasPadre() {
        $sql = "SELECT id, nombre FROM categorias ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
