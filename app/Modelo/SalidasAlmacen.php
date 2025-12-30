<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class SalidasAlmacen {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
        $this->verificarEstados();
        $this->verificarTiposSalida();
    }

    /**
     * Verifica que existan los estados necesarios, si no, los crea
     */
    private function verificarEstados() {
        $estadosNecesarios = [
            ['nombre' => 'Registrada', 'descripcion' => 'Salida registrada pendiente de autorización'],
            ['nombre' => 'Autorizada', 'descripcion' => 'Salida autorizada y procesada'],
            ['nombre' => 'Cancelada', 'descripcion' => 'Salida cancelada']
        ];

        foreach ($estadosNecesarios as $estado) {
            $sql = "SELECT id FROM estados WHERE nombre = ? LIMIT 1";
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $stmt->execute([$estado['nombre']]);
            
            if (!$stmt->fetch()) {
                $sql = "INSERT INTO estados (nombre, descripcion) VALUES (?, ?)";
                $stmt = $this->conexion->getConexion()->prepare($sql);
                $stmt->execute([$estado['nombre'], $estado['descripcion']]);
            }
        }
    }

    /**
     * Verifica que existan los tipos de salida básicos
     */
    private function verificarTiposSalida() {
        $tiposNecesarios = [
            ['nombre' => 'Venta', 'descripcion' => 'Salida por venta a cliente', 'requiere_autorizacion' => 0],
            ['nombre' => 'Devolución Proveedor', 'descripcion' => 'Devolución de mercadería a proveedor', 'requiere_autorizacion' => 1],
            ['nombre' => 'Ajuste Inventario', 'descripcion' => 'Ajuste negativo de inventario', 'requiere_autorizacion' => 1],
            ['nombre' => 'Merma', 'descripcion' => 'Salida por merma o pérdida', 'requiere_autorizacion' => 1],
            ['nombre' => 'Transferencia Salida', 'descripcion' => 'Salida por transferencia entre almacenes', 'requiere_autorizacion' => 0],
            ['nombre' => 'Consumo Interno', 'descripcion' => 'Consumo interno de productos', 'requiere_autorizacion' => 1]
        ];

        $estadoActivo = $this->obtenerEstadoPorNombre('Activo') ?? 1;

        foreach ($tiposNecesarios as $tipo) {
            $sql = "SELECT id FROM tipos_salida WHERE nombre = ? LIMIT 1";
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $stmt->execute([$tipo['nombre']]);
            
            if (!$stmt->fetch()) {
                $sql = "INSERT INTO tipos_salida (nombre, descripcion, requiere_autorizacion, estado_id, creado_en, actualizado_en) 
                        VALUES (?, ?, ?, ?, NOW(), NOW())";
                $stmt = $this->conexion->getConexion()->prepare($sql);
                $stmt->execute([$tipo['nombre'], $tipo['descripcion'], $tipo['requiere_autorizacion'], $estadoActivo]);
            }
        }
    }


public function listar() {
    // Asegurar que la sesión esté iniciada
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $usuario_id = isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null;
    if (empty($usuario_id)) {
        // O devolver vacío o lanzar excepción según tu lógica
        return []; // sin permisos porque no hay usuario en sesión
    }

    $sql = "SELECT sa.id,
                   sa.numero_salida,
                   a.nombre AS almacen,
                   ts.nombre AS tipo_salida,
                   sa.fecha_salida,
                   sa.fecha_autorizacion,
                   sa.documento_referencia,
                   e.nombre AS estado,
                   ur.nombre AS usuario_registra,
                   ua.nombre AS usuario_autoriza,
                   sa.motivo
            FROM salidas_almacen sa
            INNER JOIN almacenes a ON sa.almacen_id = a.id
            INNER JOIN tipos_salida ts ON sa.tipo_salida_id = ts.id
            INNER JOIN estados e ON sa.estado_id = e.id
            LEFT JOIN usuarios ur ON sa.usuario_registra_id = ur.id
            LEFT JOIN usuarios ua ON sa.usuario_autoriza_id = ua.id
            INNER JOIN usuario_almacen ua2 
                    ON ua2.almacen_id = sa.almacen_id
                   AND ua2.usuario_id = :usuario_id
                   AND ua2.estado_id = 1
            ORDER BY sa.id DESC";

    $stmt = $this->conexion->getConexion()->prepare($sql);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    /*
    public function listar() {
        $sql = "SELECT sa.id,
                       sa.numero_salida,
                       a.nombre AS almacen,
                       ts.nombre AS tipo_salida,
                       sa.fecha_salida,
                       sa.fecha_autorizacion,
                       sa.documento_referencia,
                       e.nombre AS estado,
                       ur.nombre AS usuario_registra,
                       ua.nombre AS usuario_autoriza,
                       sa.motivo
                FROM salidas_almacen sa
                INNER JOIN almacenes a ON sa.almacen_id = a.id
                INNER JOIN tipos_salida ts ON sa.tipo_salida_id = ts.id
                INNER JOIN estados e ON sa.estado_id = e.id
                LEFT JOIN usuarios ur ON sa.usuario_registra_id = ur.id
                LEFT JOIN usuarios ua ON sa.usuario_autoriza_id = ua.id
                ORDER BY sa.id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

*/


    public function obtener($id) {
        $sql = "SELECT sa.*,
                       a.nombre AS almacen,
                       ts.nombre AS tipo_salida,
                       ts.requiere_autorizacion,
                       e.nombre AS estado
                FROM salidas_almacen sa
                INNER JOIN almacenes a ON sa.almacen_id = a.id
                INNER JOIN tipos_salida ts ON sa.tipo_salida_id = ts.id
                INNER JOIN estados e ON sa.estado_id = e.id
                WHERE sa.id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerDetalle($salida_id) {
        $sql = "SELECT sd.id,
                       sd.salida_id,
                       sd.producto_id,
                       p.nombre AS producto,
                       p.codigo AS codigo_producto,
                       sd.cantidad,
                       sd.precio_unitario,
                       (sd.cantidad * sd.precio_unitario) AS subtotal,
                       sd.observaciones
                FROM salidas_detalle sd
                INNER JOIN productos p ON sd.producto_id = p.id
                WHERE sd.salida_id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$salida_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertar($data, $detalle, $usuario_id) {
        try {
            $this->conexion->getConexion()->beginTransaction();

            // Validaciones
            if (empty($detalle)) {
                throw new Exception("Debe agregar al menos un producto");
            }

            // Generar número de salida
            $numero_salida = $this->generarNumeroSalida();
            
            // Obtener estado "Registrada"
            $estado_id = $this->obtenerEstadoPorNombre('Registrada');
            
            if (!$estado_id) {
                throw new Exception("Error crítico: No se pudo obtener el estado 'Registrada'");
            }

            // Verificar si el tipo de salida requiere autorización
            $tipo_salida = $this->obtenerTipoSalida($data['tipo_salida_id']);
            $requiere_autorizacion = $tipo_salida['requiere_autorizacion'] ?? 1;

            // Validar stock disponible antes de continuar
            foreach ($detalle as $item) {
                $stock = $this->obtenerStockDisponible($item['producto_id'], $data['almacen_id']);
                if ($stock < $item['cantidad']) {
                    $producto = $this->obtenerNombreProducto($item['producto_id']);
                    throw new Exception("Stock insuficiente para '{$producto}'. Disponible: {$stock}, Solicitado: {$item['cantidad']}");
                }
            }

            // Insertar salida principal
            $sql = "INSERT INTO salidas_almacen
                    (numero_salida, almacen_id, tipo_salida_id, fecha_salida,
                     motivo, documento_referencia, usuario_registra_id, estado_id,
                     creado_en, actualizado_en)
                    VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $resultado = $stmt->execute([
                $numero_salida,
                $data['almacen_id'],
                $data['tipo_salida_id'],
                $data['motivo'] ?? '',
                $data['documento_referencia'] ?? '',
                $usuario_id,
                $estado_id
            ]);

            if (!$resultado) {
                throw new Exception("Error al insertar la salida principal");
            }

            $salida_id = $this->conexion->getConexion()->lastInsertId();

            // Insertar detalle de productos
            foreach ($detalle as $item) {
                // Validar precio unitario
                if ($item['precio_unitario'] < 0) {
                    throw new Exception("El precio unitario no puede ser negativo");
                }

                if ($item['cantidad'] <= 0) {
                    throw new Exception("La cantidad debe ser mayor a cero");
                }

                $sql = "INSERT INTO salidas_detalle
                        (salida_id, producto_id, cantidad, precio_unitario, observaciones)
                        VALUES (?, ?, ?, ?, ?)";
                
                $stmt = $this->conexion->getConexion()->prepare($sql);
                $resultado = $stmt->execute([
                    $salida_id,
                    $item['producto_id'],
                    $item['cantidad'],
                    $item['precio_unitario'],
                    $item['observaciones'] ?? ''
                ]);

                if (!$resultado) {
                    throw new Exception("Error al insertar el detalle del producto ID: {$item['producto_id']}");
                }
            }

            // Si NO requiere autorización, procesar automáticamente
            if ($requiere_autorizacion == 0) {
                $this->procesarSalida($salida_id, $usuario_id);
            }

            $this->conexion->getConexion()->commit();
            return $salida_id;

        } catch (Exception $e) {
            $this->conexion->getConexion()->rollBack();
            throw $e;
        }
    }

    public function autorizar($id, $usuario_id) {
        try {
            $this->conexion->getConexion()->beginTransaction();

            $salida = $this->obtener($id);
            
            if (!$salida) {
                throw new Exception("Salida no encontrada");
            }

            if ($salida['estado'] != 'Registrada') {
                throw new Exception("Solo se puede autorizar una salida en estado 'Registrada'");
            }

            // Validar stock nuevamente antes de procesar
            $detalle = $this->obtenerDetalle($id);
            foreach ($detalle as $item) {
                $stock = $this->obtenerStockDisponible($item['producto_id'], $salida['almacen_id']);
                if ($stock < $item['cantidad']) {
                    $producto = $item['producto'];
                    throw new Exception("Stock insuficiente para '{$producto}'. Disponible: {$stock}, Solicitado: {$item['cantidad']}");
                }
            }

            // Procesar la salida (afectar inventario)
            $this->procesarSalida($id, $usuario_id);

            // Actualizar estado y fecha de autorización
            $estado_id = $this->obtenerEstadoPorNombre('Autorizada');

            $sql = "UPDATE salidas_almacen SET
                        estado_id = ?,
                        fecha_autorizacion = NOW(),
                        usuario_autoriza_id = ?,
                        actualizado_en = NOW()
                    WHERE id = ?";
            
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $stmt->execute([$estado_id, $usuario_id, $id]);

            $this->conexion->getConexion()->commit();
            return true;

        } catch (Exception $e) {
            $this->conexion->getConexion()->rollBack();
            throw $e;
        }
    }

    public function cancelar($id) {
        $salida = $this->obtener($id);
        
        if (!$salida) {
            throw new Exception("Salida no encontrada");
        }

        if ($salida['estado'] != 'Registrada') {
            throw new Exception("Solo se puede cancelar si está en estado 'Registrada'");
        }

        $estado_id = $this->obtenerEstadoPorNombre('Cancelada');
        
        $sql = "UPDATE salidas_almacen SET 
                    estado_id = ?,
                    actualizado_en = NOW()
                WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([$estado_id, $id]);
    }

    public function obtenerAlmacenes() {
        $estadoActivo = $this->obtenerEstadoPorNombre('Activo') ?? 1;
        $sql = "SELECT id, codigo, nombre FROM almacenes WHERE estado_id = ? ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$estadoActivo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProductos() {
        $estadoActivo = $this->obtenerEstadoPorNombre('Activo') ?? 1;
        $sql = "SELECT id, codigo, nombre FROM productos WHERE estado_id = ? ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$estadoActivo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTiposSalida() {
        $estadoActivo = $this->obtenerEstadoPorNombre('Activo') ?? 1;
        $sql = "SELECT id, nombre, descripcion, requiere_autorizacion 
                FROM tipos_salida 
                WHERE estado_id = ? 
                ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$estadoActivo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerStockDisponible($producto_id, $almacen_id) {
        $sql = "SELECT cantidad_actual FROM inventario_almacen
                WHERE producto_id = ? AND almacen_id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$producto_id, $almacen_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado['cantidad_actual'] : 0;
    }

    public function generarNumeroSalida() {
        $sql = "SELECT numero_salida FROM salidas_almacen ORDER BY id DESC LIMIT 1";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ultimo) {
            $numero = intval(substr($ultimo['numero_salida'], 4)) + 1;
        } else {
            $numero = 1;
        }

        return 'SAL-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Obtiene el precio de venta de un producto desde la lista de precios vigente
     */
    public function obtenerPrecioProducto($producto_id) {
        // Buscar precio de venta en lista de precios vigente
        $sql = "SELECT lpd.precio, tp.nombre as tipo_precio
                FROM listas_precios_detalle lpd
                INNER JOIN listas_precios lp ON lpd.lista_precio_id = lp.id
                INNER JOIN tipos_precio tp ON lpd.tipo_precio_id = tp.id
                WHERE lpd.producto_id = ?
                AND lp.estado_id = ?
                AND (CURDATE() BETWEEN lp.vigente_desde AND lp.vigente_hasta 
                     OR lp.vigente_hasta IS NULL)
                ORDER BY 
                    CASE 
                        WHEN tp.nombre LIKE '%venta%' THEN 1
                        WHEN tp.nombre LIKE '%público%' THEN 2
                        WHEN tp.nombre LIKE '%cliente%' THEN 3
                        ELSE 4
                    END,
                    lpd.id DESC
                LIMIT 1";
        
        $estadoActivo = $this->obtenerEstadoPorNombre('Activo') ?? 1;
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$producto_id, $estadoActivo]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado) {
            return [
                'precio' => $resultado['precio'],
                'tipo_precio' => $resultado['tipo_precio'],
                'encontrado' => true
            ];
        }
        
        return [
            'precio' => 0,
            'tipo_precio' => null,
            'encontrado' => false
        ];
    }

    // ==================== MÉTODOS PRIVADOS ====================

    private function procesarSalida($salida_id, $usuario_id) {
        $salida = $this->obtener($salida_id);
        $detalle = $this->obtenerDetalle($salida_id);

        foreach ($detalle as $item) {
            // Restar del inventario (cantidad negativa)
            $this->afectarInventario(
                $item['producto_id'],
                $salida['almacen_id'],
                -$item['cantidad'],  // Negativo para restar
                'salida',
                $salida_id,
                $usuario_id,
                "Salida {$salida['numero_salida']} - {$salida['tipo_salida']}"
            );
        }
    }

    private function obtenerEstadoPorNombre($nombre) {
        $sql = "SELECT id FROM estados WHERE nombre = ? LIMIT 1";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$nombre]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado['id'] : null;
    }

    private function obtenerTipoSalida($tipo_salida_id) {
        $sql = "SELECT * FROM tipos_salida WHERE id = ? LIMIT 1";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$tipo_salida_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function obtenerNombreProducto($producto_id) {
        $sql = "SELECT nombre FROM productos WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$producto_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado['nombre'] : "Producto ID: $producto_id";
    }

    private function obtenerTipoMovimiento($tipo_afectacion) {
        $sql = "SELECT id FROM tipos_movimiento 
                WHERE tipo_afectacion = ? 
                AND afecta_inventario = 1 
                LIMIT 1";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$tipo_afectacion]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado['id'] : null;
    }

    private function afectarInventario($producto_id, $almacen_id, $cantidad_movimiento, $referencia_tipo, $referencia_id, $usuario_id, $observaciones) {
        // Buscar el registro de inventario
        $sql = "SELECT id, cantidad_actual FROM inventario_almacen
                WHERE producto_id = ? AND almacen_id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$producto_id, $almacen_id]);
        $inventario = $stmt->fetch(PDO::FETCH_ASSOC);

        $cantidad_anterior = $inventario ? $inventario['cantidad_actual'] : 0;
        $cantidad_nueva = $cantidad_anterior + $cantidad_movimiento;

        if ($cantidad_nueva < 0) {
            throw new Exception("No se puede dejar stock negativo. Stock actual: $cantidad_anterior");
        }

        // Actualizar inventario_almacen
        if ($inventario) {
            $sql = "UPDATE inventario_almacen 
                    SET cantidad_actual = ?, 
                        usuario_modificador_id = ?,
                        actualizado_en = NOW()
                    WHERE id = ?";
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $resultado = $stmt->execute([$cantidad_nueva, $usuario_id, $inventario['id']]);
            
            if (!$resultado) {
                throw new Exception("Error al actualizar el inventario en inventario_almacen");
            }
        } else {
            throw new Exception("No existe inventario para este producto en el almacén especificado");
        }

        // Registrar en bitácora
        $tipo_afectacion = $cantidad_movimiento > 0 ? 'suma' : 'resta';
        $tipo_movimiento_id = $this->obtenerTipoMovimiento($tipo_afectacion);

        $sql = "INSERT INTO bitacora_inventario
                (producto_id, almacen_id, tipo_movimiento_id, cantidad_anterior,
                 cantidad_movimiento, cantidad_nueva, referencia_tipo, referencia_id,
                 usuario_id, fecha_movimiento, observaciones)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $resultado = $stmt->execute([
            $producto_id,
            $almacen_id,
            $tipo_movimiento_id,
            $cantidad_anterior,
            abs($cantidad_movimiento),
            $cantidad_nueva,
            $referencia_tipo,
            $referencia_id,
            $usuario_id,
            $observaciones
        ]);
        
        if (!$resultado) {
            throw new Exception("Error al registrar en bitácora_inventario");
        }
    }


    // ==========================================
// AGREGA ESTE MÉTODO A LA CLASE SalidasAlmacen
// (después del método obtener() existente)
// ==========================================

/**
 * Obtiene una salida con los nombres completos de todos los usuarios involucrados
 */
public function obtenerConUsuarios($id) {
    $sql = "SELECT sa.*,
                   a.nombre AS almacen,
                   ts.nombre AS tipo_salida,
                   ts.requiere_autorizacion,
                   e.nombre AS estado,
                   ur.nombre AS usuario_registra,
                   ua.nombre AS usuario_autoriza
            FROM salidas_almacen sa
            INNER JOIN almacenes a ON sa.almacen_id = a.id
            INNER JOIN tipos_salida ts ON sa.tipo_salida_id = ts.id
            INNER JOIN estados e ON sa.estado_id = e.id
            LEFT JOIN usuarios ur ON sa.usuario_registra_id = ur.id
            LEFT JOIN usuarios ua ON sa.usuario_autoriza_id = ua.id
            WHERE sa.id = ?";
    
    $stmt = $this->conexion->getConexion()->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}



}