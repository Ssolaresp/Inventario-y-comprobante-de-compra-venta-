<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class ListaPrecioDetalle {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    // ========== CABECERA ==========
    
    // 📋 Listar todas las listas de precios (cabecera)
    public function listarCabecera() {
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
                       lp.actualizado_en,
                       COUNT(lpd.id) as cantidad_productos
                FROM listas_precios lp
                INNER JOIN monedas m ON lp.moneda_id = m.id
                INNER JOIN estados e ON lp.estado_id = e.id
                LEFT JOIN listas_precios_detalle lpd ON lp.id = lpd.lista_precio_id
                GROUP BY lp.id
                ORDER BY lp.id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔍 Obtener cabecera por ID
    public function obtenerCabecera($id) {
        $sql = "SELECT * FROM listas_precios WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ➕ Insertar nueva cabecera
    public function insertarCabecera($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        $sql = "INSERT INTO listas_precios
                (nombre, descripcion, moneda_id, vigente_desde, vigente_hasta, 
                 estado_id, usuario_creador_id, usuario_modificador_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['moneda_id'],
            $data['vigente_desde'],
            $data['vigente_hasta'],
            $data['estado_id'],
            $usuario_id,
            $usuario_id
        ]);
        return $this->conexion->getConexion()->lastInsertId();
    }

    // ✏️ Actualizar cabecera
    public function actualizarCabecera($data) {
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

    // 🗑️ Eliminar cabecera (y sus detalles por CASCADE)
    public function eliminarCabecera($id) {
        $sql = "DELETE FROM listas_precios WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([$id]);
    }

    // ========== DETALLE ==========
    
    // 📋 Listar detalles de una lista de precios
    public function listarDetalle($lista_precio_id) {
        $sql = "SELECT lpd.id,
                       lpd.lista_precio_id,
                       p.codigo AS producto_codigo,
                       p.nombre AS producto,
                       tp.nombre AS tipo_precio,
                       lpd.precio,
                       lpd.creado_en,
                       lpd.actualizado_en,
                       lpd.producto_id,
                       lpd.tipo_precio_id
                FROM listas_precios_detalle lpd
                INNER JOIN productos p ON lpd.producto_id = p.id
                INNER JOIN tipos_precio tp ON lpd.tipo_precio_id = tp.id
                WHERE lpd.lista_precio_id = ?
                ORDER BY p.nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$lista_precio_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔍 Obtener detalle por ID
    public function obtenerDetalle($id) {
        $sql = "SELECT * FROM listas_precios_detalle WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ➕ Insertar nuevo detalle
    public function insertarDetalle($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        $sql = "INSERT INTO listas_precios_detalle
                (lista_precio_id, producto_id, tipo_precio_id, precio, 
                 usuario_creador_id, usuario_modificador_id)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['lista_precio_id'],
            $data['producto_id'],
            $data['tipo_precio_id'],
            $data['precio'],
            $usuario_id,
            $usuario_id
        ]);
    }

    // ✏️ Actualizar detalle
    public function actualizarDetalle($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        $sql = "UPDATE listas_precios_detalle SET
                    producto_id = ?,
                    tipo_precio_id = ?,
                    precio = ?,
                    usuario_modificador_id = ?
                WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['producto_id'],
            $data['tipo_precio_id'],
            $data['precio'],
            $usuario_id,
            $data['id']
        ]);
    }

    // 🗑️ Eliminar detalle
    public function eliminarDetalle($id) {
        $sql = "DELETE FROM listas_precios_detalle WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([$id]);
    }

    // ========== DATOS AUXILIARES ==========
    
    // 🔸 Obtener monedas
    public function obtenerMonedas() {
        $sql = "SELECT id, codigo, nombre, simbolo 
                FROM monedas 
                WHERE estado_id = 1 
                ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔸 Obtener estados
    public function obtenerEstados() {
        $sql = "SELECT id, nombre FROM estados ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔸 Obtener productos
    public function obtenerProductos() {
        $sql = "SELECT id, codigo, nombre 
                FROM productos 
                WHERE estado_id = 1 
                ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔸 Obtener tipos de precio
    public function obtenerTiposPrecios() {
        $sql = "SELECT id, nombre 
                FROM tipos_precio 
                WHERE estado_id = 1 
                ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}