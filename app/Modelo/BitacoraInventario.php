<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class BitacoraInventario {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    /**
     * Obtiene el kardex completo o filtrado
     */
    public function obtenerKardex($filtros = []) {
        $sql = "SELECT 
                    bi.id,
                    bi.fecha_movimiento,
                    p.codigo AS codigo_producto,
                    p.nombre AS producto,
                    a.codigo AS codigo_almacen,
                    a.nombre AS almacen,
                    tm.nombre AS tipo_movimiento,
                    tm.tipo_afectacion,
                    bi.cantidad_anterior,
                    bi.cantidad_movimiento,
                    bi.cantidad_nueva,
                    bi.referencia_tipo,
                    bi.referencia_id,
                    u.nombre AS usuario,
                    bi.observaciones
                FROM bitacora_inventario bi
                INNER JOIN productos p ON bi.producto_id = p.id
                INNER JOIN almacenes a ON bi.almacen_id = a.id
                INNER JOIN tipos_movimiento tm ON bi.tipo_movimiento_id = tm.id
                LEFT JOIN usuarios u ON bi.usuario_id = u.id
                WHERE 1=1";

        $params = [];

        // Filtro por producto
        if (!empty($filtros['producto_id'])) {
            $sql .= " AND bi.producto_id = ?";
            $params[] = $filtros['producto_id'];
        }

        // Filtro por almacén
        if (!empty($filtros['almacen_id'])) {
            $sql .= " AND bi.almacen_id = ?";
            $params[] = $filtros['almacen_id'];
        }

        // Filtro por tipo de referencia
        if (!empty($filtros['referencia_tipo'])) {
            $sql .= " AND bi.referencia_tipo = ?";
            $params[] = $filtros['referencia_tipo'];
        }

        // Filtro por fecha desde
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(bi.fecha_movimiento) >= ?";
            $params[] = $filtros['fecha_desde'];
        }

        // Filtro por fecha hasta
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(bi.fecha_movimiento) <= ?";
            $params[] = $filtros['fecha_hasta'];
        }

        $sql .= " ORDER BY bi.fecha_movimiento DESC, bi.id DESC";

        // Límite de registros
        if (!empty($filtros['limite'])) {
            $sql .= " LIMIT " . intval($filtros['limite']);
        }

        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene kardex por producto específico
     */
    public function obtenerKardexPorProducto($producto_id, $almacen_id = null) {
        $sql = "SELECT 
                    bi.id,
                    bi.fecha_movimiento,
                    a.nombre AS almacen,
                    tm.nombre AS tipo_movimiento,
                    tm.tipo_afectacion,
                    bi.cantidad_anterior,
                    bi.cantidad_movimiento,
                    bi.cantidad_nueva,
                    bi.referencia_tipo,
                    bi.referencia_id,
                    u.nombre AS usuario,
                    bi.observaciones
                FROM bitacora_inventario bi
                INNER JOIN almacenes a ON bi.almacen_id = a.id
                INNER JOIN tipos_movimiento tm ON bi.tipo_movimiento_id = tm.id
                LEFT JOIN usuarios u ON bi.usuario_id = u.id
                WHERE bi.producto_id = ?";

        $params = [$producto_id];

        if ($almacen_id) {
            $sql .= " AND bi.almacen_id = ?";
            $params[] = $almacen_id;
        }

        $sql .= " ORDER BY bi.fecha_movimiento DESC, bi.id DESC";

        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene resumen de stock actual por producto y almacén
     */
    public function obtenerResumenStock() {
        $sql = "SELECT 
                    p.id AS producto_id,
                    p.codigo AS codigo_producto,
                    p.nombre AS producto,
                    a.id AS almacen_id,
                    a.codigo AS codigo_almacen,
                    a.nombre AS almacen,
                    ia.cantidad_actual AS stock_actual
                FROM inventario_almacen ia
                INNER JOIN productos p ON ia.producto_id = p.id
                INNER JOIN almacenes a ON ia.almacen_id = a.id
                WHERE p.estado_id = 1 AND a.estado_id = 1
                ORDER BY p.nombre ASC, a.nombre ASC";

        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene estadísticas de movimientos
     */
    public function obtenerEstadisticas($fecha_desde = null, $fecha_hasta = null) {
        $sql = "SELECT 
                    tm.nombre AS tipo_movimiento,
                    tm.tipo_afectacion,
                    COUNT(*) AS total_movimientos,
                    SUM(bi.cantidad_movimiento) AS cantidad_total
                FROM bitacora_inventario bi
                INNER JOIN tipos_movimiento tm ON bi.tipo_movimiento_id = tm.id
                WHERE 1=1";

        $params = [];

        if ($fecha_desde) {
            $sql .= " AND DATE(bi.fecha_movimiento) >= ?";
            $params[] = $fecha_desde;
        }

        if ($fecha_hasta) {
            $sql .= " AND DATE(bi.fecha_movimiento) <= ?";
            $params[] = $fecha_hasta;
        }

        $sql .= " GROUP BY tm.id, tm.nombre, tm.tipo_afectacion
                  ORDER BY total_movimientos DESC";

        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene detalle de un movimiento específico
     */
    public function obtenerMovimiento($id) {
        $sql = "SELECT 
                    bi.*,
                    p.codigo AS codigo_producto,
                    p.nombre AS producto,
                    a.codigo AS codigo_almacen,
                    a.nombre AS almacen,
                    tm.nombre AS tipo_movimiento,
                    tm.tipo_afectacion,
                    u.nombre AS usuario
                FROM bitacora_inventario bi
                INNER JOIN productos p ON bi.producto_id = p.id
                INNER JOIN almacenes a ON bi.almacen_id = a.id
                INNER JOIN tipos_movimiento tm ON bi.tipo_movimiento_id = tm.id
                LEFT JOIN usuarios u ON bi.usuario_id = u.id
                WHERE bi.id = ?";

        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los productos activos
     */
    public function obtenerProductos() {
        $sql = "SELECT id, codigo, nombre FROM productos WHERE estado_id = 1 ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los almacenes activos
     */
    public function obtenerAlmacenes() {
        $sql = "SELECT id, codigo, nombre FROM almacenes WHERE estado_id = 1 ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene tipos de referencia únicos
     */
    public function obtenerTiposReferencia() {
        $sql = "SELECT DISTINCT referencia_tipo 
                FROM bitacora_inventario 
                WHERE referencia_tipo IS NOT NULL 
                ORDER BY referencia_tipo ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Exportar kardex a CSV
     */
    public function exportarKardex($filtros = []) {
        $datos = $this->obtenerKardex($filtros);
        
        $csv = "Fecha,Producto,Almacén,Tipo Movimiento,Cant. Anterior,Movimiento,Cant. Nueva,Referencia,Usuario,Observaciones\n";
        
        foreach ($datos as $row) {
            $csv .= sprintf(
                '"%s","%s","%s","%s",%s,%s,%s,"%s","%s","%s"' . "\n",
                $row['fecha_movimiento'],
                $row['producto'],
                $row['almacen'],
                $row['tipo_movimiento'],
                $row['cantidad_anterior'],
                $row['cantidad_movimiento'],
                $row['cantidad_nueva'],
                $row['referencia_tipo'] . '-' . $row['referencia_id'],
                $row['usuario'],
                str_replace('"', '""', $row['observaciones'])
            );
        }
        
        return $csv;
    }



    // Agregar este método en la clase BitacoraInventario (después de obtenerKardexPorProducto)

/**
 * Obtiene información resumida de un producto para vista individual
 * Incluye estadísticas y stock actual
 */
public function obtenerInfoProducto($producto_id, $almacen_id = null) {
    // Información básica del producto
    $sql = "SELECT p.id, p.codigo, p.nombre
            FROM productos p
            WHERE p.id = ?";
    $stmt = $this->conexion->getConexion()->prepare($sql);
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        throw new Exception("Producto no encontrado");
    }
    
    // Stock actual
    $sqlStock = "SELECT COALESCE(SUM(ia.cantidad_actual), 0) as stock_actual
                 FROM inventario_almacen ia
                 WHERE ia.producto_id = ?";
    $params = [$producto_id];
    
    if ($almacen_id) {
        $sqlStock .= " AND ia.almacen_id = ?";
        $params[] = $almacen_id;
    }
    
    $stmtStock = $this->conexion->getConexion()->prepare($sqlStock);
    $stmtStock->execute($params);
    $stock = $stmtStock->fetch(PDO::FETCH_ASSOC);
    
    // Estadísticas de movimientos
    $sqlStats = "SELECT 
                    SUM(CASE WHEN tm.tipo_afectacion = 'suma' THEN bi.cantidad_movimiento ELSE 0 END) as total_entradas,
                    SUM(CASE WHEN tm.tipo_afectacion = 'resta' THEN bi.cantidad_movimiento ELSE 0 END) as total_salidas,
                    COUNT(*) as total_movimientos,
                    MAX(bi.fecha_movimiento) as ultimo_movimiento
                 FROM bitacora_inventario bi
                 INNER JOIN tipos_movimiento tm ON bi.tipo_movimiento_id = tm.id
                 WHERE bi.producto_id = ?";
    
    $paramsStats = [$producto_id];
    
    if ($almacen_id) {
        $sqlStats .= " AND bi.almacen_id = ?";
        $paramsStats[] = $almacen_id;
    }
    
    $stmtStats = $this->conexion->getConexion()->prepare($sqlStats);
    $stmtStats->execute($paramsStats);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
    
    // Nombre del almacén si se especificó
    $almacen = null;
    if ($almacen_id) {
        $sqlAlm = "SELECT nombre FROM almacenes WHERE id = ?";
        $stmtAlm = $this->conexion->getConexion()->prepare($sqlAlm);
        $stmtAlm->execute([$almacen_id]);
        $almacenData = $stmtAlm->fetch(PDO::FETCH_ASSOC);
        $almacen = $almacenData ? $almacenData['nombre'] : null;
    }
    
    return [
        'id' => $producto['id'],
        'codigo' => $producto['codigo'],
        'nombre' => $producto['nombre'],
        'almacen' => $almacen,
        'stock_actual' => $stock['stock_actual'] ?? 0,
        'total_entradas' => $stats['total_entradas'] ?? 0,
        'total_salidas' => $stats['total_salidas'] ?? 0,
        'total_movimientos' => $stats['total_movimientos'] ?? 0,
        'ultimo_movimiento' => $stats['ultimo_movimiento']
    ];
}


}