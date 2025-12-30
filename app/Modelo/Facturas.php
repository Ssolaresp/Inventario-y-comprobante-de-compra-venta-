<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class Factura {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    // ==================== LISTAR Y CONSULTAR ====================
    


   


    public function listar($filtros = []) {
    $sql = "SELECT f.id, f.numero_factura, f.fecha_emision, f.fecha_vencimiento,
                   tf.nombre AS tipo_factura, tf.prefijo,
                   CONCAT(COALESCE(c.razon_social, CONCAT(c.primer_nombre, ' ', c.primer_apellido))) AS cliente,
                   c.nit,
                   f.subtotal, f.total_descuento, f.total_impuestos, f.total,
                   ef.nombre AS estado, ef.color AS estado_color,
                   fp.nombre AS forma_pago,
                   f.saldo_pendiente,
                   a.nombre AS almacen,
                   u.nombre AS vendedor,
                   f.creado_en
            FROM facturas f
            INNER JOIN tipos_factura tf ON f.tipo_factura_id = tf.id
            INNER JOIN clientes c ON f.cliente_id = c.id_cliente
            INNER JOIN estados_factura ef ON f.estado_factura_id = ef.id
            INNER JOIN formas_pago fp ON f.forma_pago_id = fp.id
            LEFT JOIN almacenes a ON f.almacen_id = a.id
            LEFT JOIN usuarios u ON f.vendedor_usuario_id = u.id
            WHERE 1=1";
    
    $params = [];
    
    if (!empty($filtros['estado_id'])) {
        $sql .= " AND f.estado_factura_id = ?";
        $params[] = $filtros['estado_id'];
    }
    
    if (!empty($filtros['fecha_desde'])) {
        $sql .= " AND f.fecha_emision >= ?";
        $params[] = $filtros['fecha_desde'];
    }
    
    if (!empty($filtros['fecha_hasta'])) {
        $sql .= " AND f.fecha_emision <= ?";
        $params[] = $filtros['fecha_hasta'];
    }
    
    if (!empty($filtros['cliente_id'])) {
        $sql .= " AND f.cliente_id = ?";
        $params[] = $filtros['cliente_id'];
    }
    
    $sql .= " ORDER BY f.id DESC LIMIT 100";
    
    $stmt = $this->conexion->getConexion()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}




    public function obtener($id) {
    $sql = "SELECT f.*, 
                   tf.nombre AS tipo_factura_nombre, tf.requiere_nit,
                   CONCAT(COALESCE(c.razon_social, CONCAT(c.primer_nombre, ' ', c.primer_apellido))) AS cliente_nombre,
                   c.nit AS cliente_nit, c.telefono AS cliente_telefono, 
                   c.direccion AS cliente_direccion,
                   ef.nombre AS estado_nombre, ef.codigo AS estado_codigo,
                   fp.nombre AS forma_pago_nombre,
                   a.nombre AS almacen_nombre,
                   u.nombre AS vendedor_nombre
            FROM facturas f
            INNER JOIN tipos_factura tf ON f.tipo_factura_id = tf.id
            INNER JOIN clientes c ON f.cliente_id = c.id_cliente
            INNER JOIN estados_factura ef ON f.estado_factura_id = ef.id
            INNER JOIN formas_pago fp ON f.forma_pago_id = fp.id
            LEFT JOIN almacenes a ON f.almacen_id = a.id
            LEFT JOIN usuarios u ON f.vendedor_usuario_id = u.id
            WHERE f.id = ?";
    $stmt = $this->conexion->getConexion()->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}




    public function obtenerDetalle($factura_id) {
        $sql = "SELECT fd.*,
                       CASE 
                           WHEN fd.tipo_item = 'producto' THEN p.nombre
                           WHEN fd.tipo_item = 'servicio' THEN s.nombre
                       END AS nombre_item,
                       i.nombre AS impuesto_nombre
                FROM facturas_detalle fd
                LEFT JOIN productos p ON fd.tipo_item = 'producto' AND fd.item_id = p.id
                LEFT JOIN servicios s ON fd.tipo_item = 'servicio' AND fd.item_id = s.id
                LEFT JOIN impuestos i ON fd.impuesto_id = i.id
                WHERE fd.factura_id = ?
                ORDER BY fd.numero_linea ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$factura_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==================== GENERAR NÚMERO DE FACTURA ====================
    




    public function generarNumeroFactura($serie_id) {
    try {
        $conn = $this->conexion->getConexion();
        
        // NO iniciar transacción aquí, usar la del método padre
        
        // Obtener y bloquear la serie
        $sql = "SELECT serie, numero_actual FROM series_facturacion 
                WHERE id = ? FOR UPDATE";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$serie_id]);
        $serie_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$serie_data) {
            throw new Exception("Serie no encontrada");
        }
        
        $numero_siguiente = $serie_data['numero_actual'] + 1;
        $numero_factura = $serie_data['serie'] . '-' . str_pad($numero_siguiente, 8, '0', STR_PAD_LEFT);
        
        // Actualizar el número actual
        $sql = "UPDATE series_facturacion SET numero_actual = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$numero_siguiente, $serie_id]);
        
        return $numero_factura;
        
    } catch (Exception $e) {
        throw $e;
    }
}

    // ==================== CREAR FACTURA ====================




public function crear($data, $detalle) {
    try {
        $conn = $this->conexion->getConexion();
        $conn->beginTransaction();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        
        // Generar número de factura
        $numero_factura = $this->generarNumeroFactura($data['serie_id']);
        
        // Calcular fecha de vencimiento
        $fecha_vencimiento = null;
        if ($data['dias_credito'] > 0) {
            $fecha_vencimiento = date('Y-m-d', strtotime($data['fecha_emision'] . ' + ' . $data['dias_credito'] . ' days'));
        }
        
        // Obtener estado "Emitida"
        $sql = "SELECT id FROM estados_factura WHERE codigo = 'EMI' LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $estado_id = $stmt->fetchColumn();
        
        if (!$estado_id) {
            throw new Exception("No se encontró el estado 'Emitida'");
        }
        
        // Insertar factura
        $sql = "INSERT INTO facturas 
                (tipo_factura_id, numero_factura, serie_id, cliente_id, fecha_emision, 
                 fecha_vencimiento, subtotal, total_descuento, total_impuestos, total,
                 forma_pago_id, dias_credito, saldo_pendiente, almacen_id, 
                 vendedor_usuario_id, orden_compra, referencia_interna, 
                 observaciones, estado_factura_id, usuario_crea_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $resultado = $stmt->execute([
            $data['tipo_factura_id'],
            $numero_factura,
            $data['serie_id'],
            $data['cliente_id'],
            $data['fecha_emision'],
            $fecha_vencimiento,
            $data['subtotal'],
            $data['total_descuento'],
            $data['total_impuestos'],
            $data['total'],
            $data['forma_pago_id'],
            $data['dias_credito'],
            $data['total'], // Saldo pendiente inicial = total
            $data['almacen_id'],
            $data['vendedor_usuario_id'] ?? $usuario_id,
            $data['orden_compra'] ?? null,
            $data['referencia_interna'] ?? null,
            $data['observaciones'] ?? null,
            $estado_id,
            $usuario_id
        ]);
        
        if (!$resultado) {
            throw new Exception("Error al insertar la factura");
        }
        
        $factura_id = $conn->lastInsertId();
        
        // Insertar detalle y descontar inventario
        foreach ($detalle as $index => $item) {
            $this->insertarDetalle($factura_id, $index + 1, $item);
            
            // Descontar inventario solo si es producto
            if ($item['tipo_item'] === 'producto') {
                $this->descontarInventario(
                    $item['item_id'],
                    $data['almacen_id'],
                    $item['cantidad'],
                    $usuario_id
                );
            }
        }
        
        $conn->commit();
        
        return ['success' => true, 'factura_id' => $factura_id, 'numero_factura' => $numero_factura];
        
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        return ['success' => false, 'message' => $e->getMessage()];
    }
}




    private function insertarDetalle($factura_id, $numero_linea, $item) {
        $sql = "INSERT INTO facturas_detalle 
                (factura_id, numero_linea, tipo_item, item_id, codigo_item, descripcion,
                 cantidad, unidad_medida, precio_unitario, descuento_porcentaje, descuento_monto,
                 subtotal, impuesto_id, impuesto_porcentaje, impuesto_monto, total,
                 inventario_almacen_id, lote, fecha_vencimiento)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([
            $factura_id,
            $numero_linea,
            $item['tipo_item'],
            $item['item_id'],
            $item['codigo_item'],
            $item['descripcion'],
            $item['cantidad'],
            $item['unidad_medida'] ?? 'UND',
            $item['precio_unitario'],
            $item['descuento_porcentaje'] ?? 0,
            $item['descuento_monto'] ?? 0,
            $item['subtotal'],
            $item['impuesto_id'] ?? null,
            $item['impuesto_porcentaje'] ?? 0,
            $item['impuesto_monto'] ?? 0,
            $item['total'],
            $item['inventario_almacen_id'] ?? null,
            $item['lote'] ?? null,
            $item['fecha_vencimiento'] ?? null
        ]);
    }

    // ==================== INVENTARIO ====================
    
    private function descontarInventario($producto_id, $almacen_id, $cantidad, $usuario_id) {
        // Verificar stock disponible usando FIFO (First In First Out)
        $sql = "SELECT id, cantidad_actual, lote 
                FROM inventario_almacen 
                WHERE producto_id = ? AND almacen_id = ? AND cantidad_actual > 0
                ORDER BY fecha_ingreso ASC";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$producto_id, $almacen_id]);
        $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $cantidad_restante = $cantidad;
        
        foreach ($lotes as $lote) {
            if ($cantidad_restante <= 0) break;
            
            $descontar = min($cantidad_restante, $lote['cantidad_actual']);
            $nueva_cantidad = $lote['cantidad_actual'] - $descontar;
            
            // Actualizar inventario
            $sql = "UPDATE inventario_almacen 
                    SET cantidad_actual = ?, usuario_modificador_id = ?
                    WHERE id = ?";
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $stmt->execute([$nueva_cantidad, $usuario_id, $lote['id']]);
            
            $cantidad_restante -= $descontar;
        }
        
        if ($cantidad_restante > 0) {
            throw new Exception("Stock insuficiente. Faltan $cantidad_restante unidades");
        }
    }

    public function verificarStock($producto_id, $almacen_id, $cantidad_requerida) {
        $sql = "SELECT SUM(cantidad_actual) as stock_total 
                FROM inventario_almacen 
                WHERE producto_id = ? AND almacen_id = ?";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$producto_id, $almacen_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stock_disponible = $result['stock_total'] ?? 0;
        
        return [
            'disponible' => $stock_disponible >= $cantidad_requerida,
            'stock_actual' => $stock_disponible,
            'faltante' => max(0, $cantidad_requerida - $stock_disponible)
        ];
    }

    // ==================== ANULAR FACTURA ====================
    



// ==================== ANULAR FACTURA ====================
// Reemplaza el método anular() en tu archivo Modelo/Facturas.php

public function anular($factura_id, $motivo) {
    try {
        $conn = $this->conexion->getConexion();
        $conn->beginTransaction();
        
        // ✅ Asegurar que la sesión esté iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // ✅ Validar que el usuario esté autenticado
        if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
            throw new Exception("Error de autenticación: No se pudo identificar al usuario");
        }
        
        $usuario_id = $_SESSION['usuario_id'];
        
        // Obtener factura con bloqueo
        $sql = "SELECT f.*, 
                       ef.codigo AS estado_codigo,
                       a.id AS almacen_id
                FROM facturas f
                INNER JOIN estados_factura ef ON f.estado_factura_id = ef.id
                LEFT JOIN almacenes a ON f.almacen_id = a.id
                WHERE f.id = ?
                FOR UPDATE";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$factura_id]);
        $factura = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$factura) {
            throw new Exception("Factura no encontrada");
        }
        
        // Verificar que no esté anulada
        if ($factura['estado_codigo'] === 'ANU') {
            throw new Exception("La factura ya está anulada");
        }
        
        // Verificar si tiene pagos registrados
        $sql = "SELECT COUNT(*) as total_pagos, 
                       COALESCE(SUM(monto), 0) as monto_total_pagado
                FROM facturas_pagos 
                WHERE factura_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$factura_id]);
        $pagos_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($pagos_info['total_pagos'] > 0 && $pagos_info['monto_total_pagado'] > 0) {
            throw new Exception("No se puede anular una factura con pagos registrados. Total pagado: Q" . number_format($pagos_info['monto_total_pagado'], 2));
        }
        
        // Obtener estado "Anulada"
        $sql = "SELECT id FROM estados_factura WHERE codigo = 'ANU' LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $estado_anulada_id = $stmt->fetchColumn();
        
        if (!$estado_anulada_id) {
            throw new Exception("No se encontró el estado 'Anulada' en el sistema");
        }
        
        // Obtener detalle de la factura
        $sql = "SELECT fd.*, fd.item_id, fd.cantidad, fd.lote
                FROM facturas_detalle fd
                WHERE fd.factura_id = ?
                ORDER BY fd.numero_linea";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$factura_id]);
        $detalle = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Devolver inventario para cada producto
        $productos_devueltos = [];
        foreach ($detalle as $item) {
            if ($item['tipo_item'] === 'producto' && $factura['almacen_id']) {
                $this->devolverInventario(
                    $item['item_id'],
                    $factura['almacen_id'],
                    $item['cantidad'],
                    $item['lote'],
                    $usuario_id
                );
                $productos_devueltos[] = $item['item_id'];
            }
        }
        
        // Actualizar factura con todos los datos de anulación
        $sql = "UPDATE facturas 
                SET estado_factura_id = ?, 
                    motivo_anulacion = ?, 
                    fecha_anulacion = NOW(), 
                    usuario_anula_id = ?,
                    saldo_pendiente = 0
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $resultado = $stmt->execute([
            $estado_anulada_id, 
            $motivo, 
            $usuario_id, 
            $factura_id
        ]);
        
        if (!$resultado) {
            throw new Exception("Error al actualizar el estado de la factura");
        }
        
        // Registrar en log de auditoría (opcional)
        $this->registrarAuditoriaAnulacion($factura_id, $usuario_id, $motivo);
        
        $conn->commit();
        
        return [
            'success' => true, 
            'message' => 'Factura anulada correctamente',
            'productos_devueltos' => count($productos_devueltos),
            'numero_factura' => $factura['numero_factura'] ?? ''
        ];
        
    } catch (Exception $e) {
        if ($conn && $conn->inTransaction()) {
            $conn->rollBack();
        }
        return [
            'success' => false, 
            'message' => 'Error al anular factura: ' . $e->getMessage()
        ];
    }
}

// Método auxiliar para devolver inventario
private function devolverInventario($producto_id, $almacen_id, $cantidad, $lote, $usuario_id) {
    $conn = $this->conexion->getConexion();
    
    if ($lote) {
        // Devolver al lote específico si existe
        $sql = "UPDATE inventario_almacen 
                SET cantidad_actual = cantidad_actual + ?, 
                    usuario_modificador_id = ?,
                    fecha_modificacion = NOW()
                WHERE producto_id = ? 
                AND almacen_id = ? 
                AND lote = ?";
        $stmt = $conn->prepare($sql);
        $resultado = $stmt->execute([$cantidad, $usuario_id, $producto_id, $almacen_id, $lote]);
        
        // Si el lote no existe, crear nuevo registro
        if ($stmt->rowCount() === 0) {
            $sql = "INSERT INTO inventario_almacen 
                    (producto_id, almacen_id, cantidad_actual, lote, 
                     fecha_ingreso, usuario_modificador_id)
                    VALUES (?, ?, ?, ?, NOW(), ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$producto_id, $almacen_id, $cantidad, $lote, $usuario_id]);
        }
    } else {
        // Sin lote específico, crear nuevo registro o actualizar el más reciente
        $sql = "SELECT id FROM inventario_almacen 
                WHERE producto_id = ? AND almacen_id = ? AND lote IS NULL 
                ORDER BY fecha_ingreso DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$producto_id, $almacen_id]);
        $inventario_id = $stmt->fetchColumn();
        
        if ($inventario_id) {
            // Actualizar registro existente
            $sql = "UPDATE inventario_almacen 
                    SET cantidad_actual = cantidad_actual + ?,
                        usuario_modificador_id = ?,
                        fecha_modificacion = NOW()
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$cantidad, $usuario_id, $inventario_id]);
        } else {
            // Crear nuevo registro
            $sql = "INSERT INTO inventario_almacen 
                    (producto_id, almacen_id, cantidad_actual, fecha_ingreso, usuario_modificador_id)
                    VALUES (?, ?, ?, NOW(), ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$producto_id, $almacen_id, $cantidad, $usuario_id]);
        }
    }
}

// Método opcional para auditoría
private function registrarAuditoriaAnulacion($factura_id, $usuario_id, $motivo) {
    try {
        $sql = "INSERT INTO auditoria_facturas 
                (factura_id, accion, usuario_id, motivo, fecha, ip_address)
                VALUES (?, 'ANULACION', ?, ?, NOW(), ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'DESCONOCIDA';
        $stmt->execute([$factura_id, $usuario_id, $motivo, $ip]);
    } catch (Exception $e) {
        // Si falla el registro de auditoría, no interrumpir el proceso
        error_log("Error en auditoría: " . $e->getMessage());
    }
}





// ===================================================================
// Archivo: Modelo/Facturas.php
// Función: registrarPago
// ===================================================================

public function registrarPago($factura_id, $data) {
    $conn = $this->conexion->getConexion();
    
    try {
        $conn->beginTransaction();

        // Tomamos el usuario correctamente
        $usuario_id = $data['usuario_registra_id'] ?? null;

        if (!$usuario_id) {
            throw new Exception("No se pudo identificar al usuario que registra el pago.");
        }
        
        // Obtener número de pago
        $sql = "SELECT COALESCE(MAX(numero_pago), 0) + 1 AS siguiente 
                FROM facturas_pagos WHERE factura_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$factura_id]);
        $numero_pago = $stmt->fetchColumn();
        
        // Insertar pago
        $sql = "INSERT INTO facturas_pagos 
                (factura_id, numero_pago, fecha_pago, forma_pago_id, monto, 
                 referencia, observaciones, usuario_registra_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $factura_id,
            $numero_pago,
            $data['fecha_pago'],
            $data['forma_pago_id'],
            $data['monto'],
            $data['referencia'] ?? null,
            $data['observaciones'] ?? null,
            $usuario_id
        ]);

        // Actualizar saldo
        $this->actualizarSaldoFactura($factura_id);

        $conn->commit();
        return ['success' => true, 'message' => 'Pago registrado correctamente'];

    } catch (Exception $e) {
        if ($conn && $conn->inTransaction()) {
            $conn->rollBack();
        }
        return ['success' => false, 'message' => $e->getMessage()];
    }
}


// ✅ Este método ya lo tienes, NO lo cambies:
private function actualizarSaldoFactura($factura_id) {
    $conn = $this->conexion->getConexion();
    
    // Calcular total pagado
    $sql = "SELECT COALESCE(SUM(monto), 0) as total_pagado 
            FROM facturas_pagos WHERE factura_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$factura_id]);
    $total_pagado = $stmt->fetchColumn();
    
    // Obtener total de factura
    $sql = "SELECT total FROM facturas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$factura_id]);
    $total = $stmt->fetchColumn();
    
    $saldo = $total - $total_pagado;
    
    // Determinar estado
    $estado_codigo = 'EMI';
    if ($saldo <= 0) {
        $estado_codigo = 'PAG';
    } elseif ($total_pagado > 0) {
        $estado_codigo = 'PAR';
    }
    
    // Obtener ID del estado
    $sql = "SELECT id FROM estados_factura WHERE codigo = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$estado_codigo]);
    $estado_id = $stmt->fetchColumn();
    
    if (!$estado_id) {
        throw new Exception("No se encontró el estado con código: $estado_codigo");
    }
    
    // Actualizar factura
    $sql = "UPDATE facturas SET saldo_pendiente = ?, estado_factura_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$saldo, $estado_id, $factura_id]);
}

    public function obtenerPagos($factura_id) {
        $sql = "SELECT fp.*, 
                       forma.nombre as forma_pago_nombre, 
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


   // ==================== PAGOS ====================



    // ==================== LISTAS DE PRECIOS ====================
    
    public function obtenerListasPrecios($fecha = null) {
        $fecha = $fecha ?? date('Y-m-d');
        
        $sql = "SELECT id, nombre, descripcion, moneda_id,
                       vigente_desde, vigente_hasta
                FROM listas_precios 
                WHERE estado_id = 1
                AND (vigente_desde IS NULL OR vigente_desde <= ?)
                AND (vigente_hasta IS NULL OR vigente_hasta >= ?)
                ORDER BY nombre";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$fecha, $fecha]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    /*



    */

public function obtenerPrecioProducto($producto_id, $lista_precio_id = null, $fecha = null) {
    $fecha = $fecha ?? date('Y-m-d');
    
    // SIEMPRE usar tipo_precio_id = 2 (Precio de Venta)
    $tipo_precio_venta = 2;
    
    // Si hay lista de precios especificada, buscar ahí primero
    if ($lista_precio_id) {
        $sql = "SELECT lpd.precio, tp.nombre as tipo_precio, lp.nombre as lista_nombre
                FROM listas_precios_detalle lpd
                INNER JOIN listas_precios lp ON lpd.lista_precio_id = lp.id
                INNER JOIN tipos_precio tp ON lpd.tipo_precio_id = tp.id
                WHERE lpd.producto_id = ? 
                AND lpd.lista_precio_id = ?
                AND lpd.tipo_precio_id = ?
                AND lp.estado_id = 1
                AND tp.estado_id = 1
                AND (lp.vigente_desde IS NULL OR lp.vigente_desde <= ?)
                AND (lp.vigente_hasta IS NULL OR lp.vigente_hasta >= ?)
                LIMIT 1";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$producto_id, $lista_precio_id, $tipo_precio_venta, $fecha, $fecha]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado) {
            return [
                'encontrado' => true,
                'precio' => $resultado['precio'],
                'tipo_precio' => $resultado['tipo_precio'],
                'lista_nombre' => $resultado['lista_nombre']
            ];
        }
    }
    
    // Si no se encontró o no hay lista, buscar en cualquier lista vigente con tipo_precio_id = 2
    $sql = "SELECT lpd.precio, tp.nombre as tipo_precio, lp.nombre as lista_nombre
            FROM listas_precios_detalle lpd
            INNER JOIN listas_precios lp ON lpd.lista_precio_id = lp.id
            INNER JOIN tipos_precio tp ON lpd.tipo_precio_id = tp.id
            WHERE lpd.producto_id = ?
            AND lpd.tipo_precio_id = ?
            AND lp.estado_id = 1
            AND tp.estado_id = 1
            AND (lp.vigente_desde IS NULL OR lp.vigente_desde <= ?)
            AND (lp.vigente_hasta IS NULL OR lp.vigente_hasta >= ?)
            ORDER BY lp.id ASC
            LIMIT 1";
    
    $stmt = $this->conexion->getConexion()->prepare($sql);
    $stmt->execute([$producto_id, $tipo_precio_venta, $fecha, $fecha]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado) {
        return [
            'encontrado' => true,
            'precio' => $resultado['precio'],
            'tipo_precio' => $resultado['tipo_precio'],
            'lista_nombre' => $resultado['lista_nombre']
        ];
    }
    
    return [
        'encontrado' => false,
        'precio' => 0,
        'tipo_precio' => null,
        'lista_nombre' => null
    ];
}


/*


    */


    public function obtenerPreciosProductoPorLista($producto_id, $lista_precio_id) {
    // SIEMPRE usar tipo_precio_id = 2 (Precio de Venta)
    $tipo_precio_venta = 2;
    
    $sql = "SELECT lpd.precio, tp.nombre as tipo_precio, tp.id as tipo_precio_id
            FROM listas_precios_detalle lpd
            INNER JOIN tipos_precio tp ON lpd.tipo_precio_id = tp.id
            WHERE lpd.producto_id = ? 
            AND lpd.lista_precio_id = ?
            AND lpd.tipo_precio_id = ?
            AND tp.estado_id = 1
            LIMIT 1";
    
    $stmt = $this->conexion->getConexion()->prepare($sql);
    $stmt->execute([$producto_id, $lista_precio_id, $tipo_precio_venta]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // ==================== CATÁLOGOS ====================
    
    public function obtenerTiposFactura() {
        $sql = "SELECT id, codigo, nombre, prefijo, requiere_nit 
                FROM tipos_factura WHERE estado_id = 1 ORDER BY nombre";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSeriesPorTipo($tipo_factura_id) {
        $sql = "SELECT id, serie, numero_actual 
                FROM series_facturacion 
                WHERE tipo_factura_id = ? AND estado_id = 1 
                ORDER BY serie";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$tipo_factura_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerClientes() {
        $sql = "SELECT id_cliente as id, 
                       CONCAT(COALESCE(razon_social, CONCAT(primer_nombre, ' ', primer_apellido))) as nombre,
                       nit, telefono, direccion
                FROM clientes WHERE id_estado = 1 ORDER BY razon_social, primer_nombre";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerFormasPago() {
        $sql = "SELECT id, codigo, nombre, dias_credito 
                FROM formas_pago WHERE estado_id = 1 ORDER BY nombre";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerAlmacenes() {
        $sql = "SELECT id, codigo, nombre FROM almacenes WHERE estado_id = 1 ORDER BY nombre";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerImpuestos() {
        $sql = "SELECT id, codigo, nombre, porcentaje, tipo 
                FROM impuestos WHERE estado_id = 1 ORDER BY nombre";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEstados() {
        $sql = "SELECT id, codigo, nombre, color FROM estados_factura ORDER BY orden";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar productos con stock
    public function buscarProductos($termino, $almacen_id) {
        $sql = "SELECT p.id, p.codigo, p.nombre, p.descripcion,
                       SUM(ia.cantidad_actual) as stock_total,
                       um.nombre as unidad_medida
                FROM productos p
                LEFT JOIN inventario_almacen ia ON p.id = ia.producto_id AND ia.almacen_id = ?
                LEFT JOIN unidades_medida um ON p.unidad_medida_id = um.id
                WHERE p.estado_id = 1 
                AND (p.codigo LIKE ? OR p.nombre LIKE ?)
                GROUP BY p.id
                HAVING stock_total > 0
                LIMIT 20";
        
        $termino_busqueda = "%$termino%";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$almacen_id, $termino_busqueda, $termino_busqueda]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar servicios
    public function buscarServicios($termino) {
        $sql = "SELECT id, codigo, nombre, descripcion, precio_base, 
                       aplica_iva, porcentaje_iva
                FROM servicios
                WHERE estado_id = 1 
                AND (codigo LIKE ? OR nombre LIKE ?)
                LIMIT 20";
        
        $termino_busqueda = "%$termino%";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$termino_busqueda, $termino_busqueda]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}