<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class Dashboard
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    /* ----------  CONTADORES BÁSICOS  ---------- */
    public function contarUsuariosActivos()
    {
        $sql = "SELECT COUNT(*) AS total FROM usuarios WHERE estado_id = 1";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /* Almacenes físicos activos */
    public function contarAlmacenesActivos()
    {
        $sql = "SELECT COUNT(*) FROM almacenes WHERE estado_id = 1";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /* Entradas registradas HOY */
    public function contarEntradasHoy()
    {
        $sql = "SELECT COUNT(*) 
                FROM entradas_almacen 
                WHERE DATE(fecha_entrada) = CURDATE() AND estado_id = 1";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /* Salidas registradas HOY */
    public function contarSalidasHoy()
    {
        $sql = "SELECT COUNT(*) 
                FROM salidas_almacen 
                WHERE DATE(fecha_salida) = CURDATE() AND estado_id = 1";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /* Transferencias finalizadas HOY (estado = 3 → recibido, ajústalo si tu id es otro) */
    public function contarTransferenciasHoy()
    {
        $sql = "SELECT COUNT(*) 
                FROM transferencias 
                WHERE DATE(fecha_recepcion) = CURDATE() 
                  AND transferencia_estado_id = 3";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /* ----------  GRÁFICO: almacén vs cantidad de productos  ---------- */
    public function almacenesConCantidadProductos()
    {
        /* Un producto puede tener varios lotes; aquí contamos *items* distintos por almacén */
        $sql = "SELECT a.nombre,
                       COUNT(DISTINCT ia.producto_id) AS total_productos
                FROM inventario_almacen ia
                JOIN almacenes a ON a.id = ia.almacen_id
                WHERE a.estado_id = 1
                GROUP BY ia.almacen_id
                ORDER BY total_productos DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ----------  ALERTA: productos próximos a vencer (≤ 6 meses)  ---------- */
    public function productosProximosAVencer()
    {
        $sql = "SELECT ia.lote,
                       p.nombre AS producto,
                       a.nombre AS almacen,
                       ia.fecha_vencimiento,
                       TIMESTAMPDIFF(MONTH, CURDATE(), ia.fecha_vencimiento) AS meses_para_vencer
                FROM inventario_almacen ia
                JOIN productos p ON p.id = ia.producto_id
                JOIN almacenes a ON a.id = ia.almacen_id
                WHERE ia.fecha_vencimiento IS NOT NULL
                  AND ia.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
                  AND ia.cantidad_actual > 0
                ORDER BY ia.fecha_vencimiento ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}