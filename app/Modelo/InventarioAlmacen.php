<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class InventarioAlmacen {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    // 📦 Listar todo el inventario con nombres de producto y almacén
    public function listar() {
        $sql = "SELECT ia.id,
                       p.nombre AS producto,
                       a.nombre AS almacen,
                       ia.codigo_barras,
                       ia.lote,
                       ia.fecha_vencimiento,
                       ia.fecha_ingreso,
                       ia.cantidad_actual,
                       ia.cantidad_minima,
                       ia.cantidad_maxima,
                       ia.observaciones,
                       ia.actualizado_en
                FROM inventario_almacen ia
                INNER JOIN productos p ON ia.producto_id = p.id
                INNER JOIN almacenes a ON ia.almacen_id = a.id
                ORDER BY ia.id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔍 Obtener un registro por ID
    public function obtener($id) {
        $sql = "SELECT * FROM inventario_almacen WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ➕ Insertar nuevo registro
    public function insertar($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        $sql = "INSERT INTO inventario_almacen
                (producto_id, almacen_id, codigo_barras, lote, fecha_vencimiento, fecha_ingreso,
                 cantidad_actual, cantidad_minima, cantidad_maxima, observaciones, usuario_modificador_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $resultado = $stmt->execute([
            $data['producto_id'],
            $data['almacen_id'],
            $data['codigo_barras'],
            $data['lote'],
            $data['fecha_vencimiento'],
            $data['fecha_ingreso'],
            $data['cantidad_actual'],
            $data['cantidad_minima'],
            $data['cantidad_maxima'],
            $data['observaciones'],
            $usuario_id
        ]);

        // 📝 Registrar movimiento de entrada inicial en bitácora
        if ($resultado) {
            $inventario_id = $this->conexion->getConexion()->lastInsertId();
            
            $this->registrarMovimiento([
                'producto_id' => $data['producto_id'],
                'almacen_id' => $data['almacen_id'],
                'cantidad_anterior' => 0,
                'cantidad_movimiento' => $data['cantidad_actual'],
                'cantidad_nueva' => $data['cantidad_actual'],
                'referencia_tipo' => 'entrada',
                'referencia_id' => $inventario_id,
                'usuario_id' => $usuario_id,
                'observaciones' => 'Registro inicial de inventario'
            ]);
        }

        return $resultado;
    }

    // ✏️ Actualizar registro existente
    public function actualizar($data) {
        session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        // 📊 Obtener cantidad anterior antes de actualizar
        $registroAnterior = $this->obtener($data['id']);
        $cantidad_anterior = $registroAnterior['cantidad_actual'];
        $cantidad_nueva = $data['cantidad_actual'];

        $sql = "UPDATE inventario_almacen SET
                    producto_id = ?, almacen_id = ?, codigo_barras = ?, lote = ?,
                    fecha_vencimiento = ?, fecha_ingreso = ?, cantidad_actual = ?,
                    cantidad_minima = ?, cantidad_maxima = ?, observaciones = ?,
                    usuario_modificador_id = ?
                WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $resultado = $stmt->execute([
            $data['producto_id'],
            $data['almacen_id'],
            $data['codigo_barras'],
            $data['lote'],
            $data['fecha_vencimiento'],
            $data['fecha_ingreso'],
            $cantidad_nueva,
            $data['cantidad_minima'],
            $data['cantidad_maxima'],
            $data['observaciones'],
            $usuario_id,
            $data['id']
        ]);

        // 📝 Registrar movimiento en bitácora si cambió la cantidad
        if ($resultado && $cantidad_anterior != $cantidad_nueva) {
            $diferencia = $cantidad_nueva - $cantidad_anterior;
            
            $this->registrarMovimiento([
                'producto_id' => $data['producto_id'],
                'almacen_id' => $data['almacen_id'],
                'cantidad_anterior' => $cantidad_anterior,
                'cantidad_movimiento' => abs($diferencia),
                'cantidad_nueva' => $cantidad_nueva,
                'referencia_tipo' => 'ajuste',
                'referencia_id' => $data['id'],
                'usuario_id' => $usuario_id,
                'observaciones' => 'Ajuste manual de inventario',
                'tipo_afectacion' => $diferencia > 0 ? 'suma' : 'resta'
            ]);
        }

        return $resultado;
    }

    // 🗑️ Eliminar registro
    public function eliminar($id) {
        $sql = "DELETE FROM inventario_almacen WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
    }

    // 🔸 Obtener lista de productos
    public function obtenerProductos() {
        $sql = "SELECT id, nombre FROM productos ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔸 Obtener lista de almacenes
    public function obtenerAlmacenes() {
        $sql = "SELECT id, nombre FROM almacenes ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 📝 Registrar movimiento en bitácora
    private function registrarMovimiento($datos) {
        // Obtener el tipo_movimiento_id según el tipo de afectación
        $tipo_movimiento_id = $this->obtenerTipoMovimiento(
            $datos['tipo_afectacion'] ?? ($datos['referencia_tipo'] == 'entrada' ? 'suma' : 'ninguna')
        );

        $sql = "INSERT INTO bitacora_inventario
                (producto_id, almacen_id, tipo_movimiento_id, cantidad_anterior,
                 cantidad_movimiento, cantidad_nueva, referencia_tipo, referencia_id,
                 usuario_id, observaciones)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([
            $datos['producto_id'],
            $datos['almacen_id'],
            $tipo_movimiento_id,
            $datos['cantidad_anterior'],
            $datos['cantidad_movimiento'],
            $datos['cantidad_nueva'],
            $datos['referencia_tipo'],
            $datos['referencia_id'],
            $datos['usuario_id'],
            $datos['observaciones']
        ]);
    }

    // 🔍 Obtener tipo_movimiento_id según tipo de afectación
    private function obtenerTipoMovimiento($tipo_afectacion) {
        $sql = "SELECT id FROM tipos_movimiento 
                WHERE tipo_afectacion = ? 
                AND afecta_inventario = 1 
                LIMIT 1";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$tipo_afectacion]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si no encuentra, devolver NULL (o puedes crear uno por defecto)
        return $resultado ? $resultado['id'] : null;
    }
}