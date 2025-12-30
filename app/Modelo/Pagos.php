<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class Pagos {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    // ==================== LISTAR FACTURAS PENDIENTES ====================
    
    public function listarFacturasPendientes($filtros = []) {
        $sql = "SELECT f.id, f.numero_factura, f.fecha_emision, f.fecha_vencimiento,
                       tf.nombre AS tipo_factura, tf.prefijo,
                       CONCAT(COALESCE(c.razon_social, CONCAT(c.primer_nombre, ' ', c.primer_apellido))) AS cliente_nombre,
                       c.nit AS cliente_nit,
                       f.subtotal, f.total_descuento, f.total_impuestos, f.total,
                       f.saldo_pendiente,
                       ef.nombre AS estado, ef.color AS estado_color,
                       (f.total - f.saldo_pendiente) AS total_pagado,
                       DATEDIFF(CURDATE(), f.fecha_vencimiento) AS dias_vencidos,
                       (SELECT COUNT(*) FROM facturas_pagos WHERE factura_id = f.id) AS cantidad_pagos
                FROM facturas f
                INNER JOIN tipos_factura tf ON f.tipo_factura_id = tf.id
                INNER JOIN clientes c ON f.cliente_id = c.id_cliente
                INNER JOIN estados_factura ef ON f.estado_factura_id = ef.id
                WHERE f.saldo_pendiente > 0
                AND ef.codigo != 'ANU'";
        
        $params = [];
        
        if (!empty($filtros['cliente_id'])) {
            $sql .= " AND f.cliente_id = ?";
            $params[] = $filtros['cliente_id'];
        }
        
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND f.fecha_emision >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND f.fecha_emision <= ?";
            $params[] = $filtros['fecha_hasta'];
        }

        if (!empty($filtros['vencidas'])) {
            $sql .= " AND f.fecha_vencimiento < CURDATE()";
        }
        
        $sql .= " ORDER BY f.fecha_vencimiento ASC, f.id DESC";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== OBTENER DETALLE DE FACTURA ====================
    
    public function obtenerFactura($factura_id) {
        $sql = "SELECT f.*, 
                       tf.nombre AS tipo_factura_nombre, tf.prefijo,
                       CONCAT(COALESCE(c.razon_social, CONCAT(c.primer_nombre, ' ', c.primer_apellido))) AS cliente_nombre,
                       c.nit AS cliente_nit, c.telefono AS cliente_telefono, 
                       c.direccion AS cliente_direccion,
                       ef.nombre AS estado_nombre, ef.codigo AS estado_codigo,
                       (f.total - f.saldo_pendiente) AS total_pagado
                FROM facturas f
                INNER JOIN tipos_factura tf ON f.tipo_factura_id = tf.id
                INNER JOIN clientes c ON f.cliente_id = c.id_cliente
                INNER JOIN estados_factura ef ON f.estado_factura_id = ef.id
                WHERE f.id = ?";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$factura_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ==================== REGISTRAR PAGO ====================
    
    public function registrarPago($factura_id, $data) {
        $conn = $this->conexion->getConexion();
        
        try {
            $conn->beginTransaction();

            // 1. Validar que la factura exista y tenga saldo pendiente
            $factura = $this->obtenerFactura($factura_id);
            
            if (!$factura) {
                throw new Exception("Factura no encontrada");
            }

            if ($factura['saldo_pendiente'] <= 0) {
                throw new Exception("Esta factura no tiene saldo pendiente");
            }

            if ($factura['estado_codigo'] === 'ANU') {
                throw new Exception("No se puede registrar pago en una factura anulada");
            }

            // 2. Validar que el monto no exceda el saldo
            if ($data['monto'] > $factura['saldo_pendiente']) {
                throw new Exception("El monto del pago (Q " . number_format($data['monto'], 2) . 
                                  ") no puede ser mayor al saldo pendiente (Q " . 
                                  number_format($factura['saldo_pendiente'], 2) . ")");
            }

            // 3. Obtener número de pago consecutivo
            $sql = "SELECT COALESCE(MAX(numero_pago), 0) + 1 AS siguiente 
                    FROM facturas_pagos WHERE factura_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$factura_id]);
            $numero_pago = $stmt->fetchColumn();
            
            // 4. Insertar el pago
            $sql = "INSERT INTO facturas_pagos 
                    (factura_id, numero_pago, fecha_pago, forma_pago_id, monto, 
                     referencia, observaciones, usuario_registra_id, creado_en)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $factura_id,
                $numero_pago,
                $data['fecha_pago'],
                $data['forma_pago_id'],
                $data['monto'],
                $data['referencia'] ?? null,
                $data['observaciones'] ?? null,
                $data['usuario_registra_id']
            ]);

            $pago_id = $conn->lastInsertId();

            // 5. Actualizar saldo de la factura
            $this->actualizarSaldoFactura($factura_id, $conn);

            $conn->commit();
            
            return [
                'success' => true, 
                'message' => 'Pago registrado correctamente',
                'pago_id' => $pago_id,
                'numero_pago' => $numero_pago
            ];

        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ==================== ACTUALIZAR SALDO Y ESTADO DE FACTURA ====================
    
    private function actualizarSaldoFactura($factura_id, $conn) {
        // 1. Calcular total pagado
        $sql = "SELECT COALESCE(SUM(monto), 0) as total_pagado 
                FROM facturas_pagos WHERE factura_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$factura_id]);
        $total_pagado = $stmt->fetchColumn();
        
        // 2. Obtener total de factura
        $sql = "SELECT total FROM facturas WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$factura_id]);
        $total = $stmt->fetchColumn();
        
        // 3. Calcular saldo pendiente
        $saldo = $total - $total_pagado;
        
        // 4. Determinar estado según saldo
        if ($saldo <= 0) {
            $estado_codigo = 'PAG'; // Pagada
        } elseif ($total_pagado > 0) {
            $estado_codigo = 'PAR'; // Parcialmente Pagada
        } else {
            $estado_codigo = 'EMI'; // Emitida (sin pagos)
        }
        
        // 5. Obtener ID del estado
        $sql = "SELECT id FROM estados_factura WHERE codigo = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$estado_codigo]);
        $estado_id = $stmt->fetchColumn();
        
        if (!$estado_id) {
            throw new Exception("No se encontró el estado con código: $estado_codigo");
        }
        
        // 6. Actualizar factura
        $sql = "UPDATE facturas 
                SET saldo_pendiente = ?, 
                    estado_factura_id = ?, 
                    actualizado_en = NOW() 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$saldo, $estado_id, $factura_id]);
    }

    // ==================== OBTENER PAGOS DE UNA FACTURA ====================
    
    public function obtenerPagosFactura($factura_id) {
        $sql = "SELECT fp.*, 
                       forma.nombre as forma_pago_nombre,
                       forma.codigo as forma_pago_codigo,
                       u.nombre as usuario_nombre
                FROM facturas_pagos fp
                INNER JOIN formas_pago forma ON fp.forma_pago_id = forma.id
                INNER JOIN usuarios u ON fp.usuario_registra_id = u.id
                WHERE fp.factura_id = ?
                ORDER BY fp.numero_pago ASC";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$factura_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== HISTORIAL DE TODAS LAS FACTURAS ====================
    
    public function obtenerHistorialFacturas($filtros = []) {
        $sql = "SELECT f.id, f.numero_factura, f.fecha_emision, f.fecha_vencimiento,
                       tf.nombre AS tipo_factura,
                       CONCAT(COALESCE(c.razon_social, CONCAT(c.primer_nombre, ' ', c.primer_apellido))) AS cliente_nombre,
                       c.nit AS cliente_nit,
                       f.total,
                       (f.total - f.saldo_pendiente) AS total_pagado,
                       f.saldo_pendiente,
                       ef.nombre AS estado, ef.color AS estado_color, ef.codigo AS estado_codigo,
                       (SELECT COUNT(*) FROM facturas_pagos WHERE factura_id = f.id) AS cantidad_pagos,
                       DATEDIFF(CURDATE(), f.fecha_vencimiento) AS dias_vencidos
                FROM facturas f
                INNER JOIN tipos_factura tf ON f.tipo_factura_id = tf.id
                INNER JOIN clientes c ON f.cliente_id = c.id_cliente
                INNER JOIN estados_factura ef ON f.estado_factura_id = ef.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['estado_codigo'])) {
            $sql .= " AND ef.codigo = ?";
            $params[] = $filtros['estado_codigo'];
        }
        
        if (!empty($filtros['cliente_id'])) {
            $sql .= " AND f.cliente_id = ?";
            $params[] = $filtros['cliente_id'];
        }
        
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND f.fecha_emision >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND f.fecha_emision <= ?";
            $params[] = $filtros['fecha_hasta'];
        }
        
        $sql .= " ORDER BY f.fecha_emision DESC, f.id DESC LIMIT 500";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== CATÁLOGOS ====================
    
    public function obtenerFormasPago() {
        $sql = "SELECT id, codigo, nombre, requiere_referencia, dias_credito 
                FROM formas_pago WHERE estado_id = 1 ORDER BY nombre";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerClientes() {
        $sql = "SELECT id_cliente as id, 
                       CONCAT(COALESCE(razon_social, CONCAT(primer_nombre, ' ', primer_apellido))) as nombre,
                       nit
                FROM clientes WHERE id_estado = 1 ORDER BY razon_social, primer_nombre";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEstadosFactura() {
        $sql = "SELECT id, codigo, nombre, color FROM estados_factura ORDER BY orden";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== ESTADÍSTICAS ====================
    
    public function obtenerEstadisticasPagos() {
        $sql = "SELECT 
                    COUNT(DISTINCT f.id) as total_facturas_pendientes,
                    SUM(f.total) as monto_total_pendiente,
                    SUM(f.saldo_pendiente) as saldo_total_pendiente,
                    SUM(f.total - f.saldo_pendiente) as total_cobrado,
                    COUNT(DISTINCT CASE WHEN f.fecha_vencimiento < CURDATE() THEN f.id END) as facturas_vencidas,
                    SUM(CASE WHEN f.fecha_vencimiento < CURDATE() THEN f.saldo_pendiente ELSE 0 END) as saldo_vencido
                FROM facturas f
                INNER JOIN estados_factura ef ON f.estado_factura_id = ef.id
                WHERE f.saldo_pendiente > 0 AND ef.codigo != 'ANU'";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}