<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class Transferencias {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
        $this->verificarEstados();
    }

    /**
     * Verifica que existan los estados necesarios, si no, los crea
     */
    private function verificarEstados() {
        $estadosNecesarios = [
            ['nombre' => 'Solicitada', 'descripcion' => 'Transferencia creada y pendiente de autorización', 'orden' => 1],
            ['nombre' => 'Autorizada', 'descripcion' => 'Transferencia autorizada y lista para envío', 'orden' => 2],
            ['nombre' => 'Enviada', 'descripcion' => 'Productos enviados del almacén origen', 'orden' => 3],
            ['nombre' => 'Recibida', 'descripcion' => 'Productos recibidos en almacén destino', 'orden' => 4],
            ['nombre' => 'Cancelada', 'descripcion' => 'Transferencia cancelada', 'orden' => 5]
        ];

        foreach ($estadosNecesarios as $estado) {
            $sql = "SELECT id FROM transferencias_estados WHERE nombre = ?";
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $stmt->execute([$estado['nombre']]);
            
            if (!$stmt->fetch()) {
                $sql = "INSERT INTO transferencias_estados (nombre, descripcion, orden, creado_en, actualizado_en) 
                        VALUES (?, ?, ?, NOW(), NOW())";
                $stmt = $this->conexion->getConexion()->prepare($sql);
                $stmt->execute([$estado['nombre'], $estado['descripcion'], $estado['orden']]);
            }
        }
    }

    /**
     * Verifica si el usuario tiene acceso a un almacén específico
     */
    private function usuarioTieneAccesoAlmacen($usuario_id, $almacen_id) {
        $sql = "SELECT COUNT(*) as total FROM usuario_almacen 
                WHERE usuario_id = ? AND almacen_id = ? AND estado_id = 1";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$usuario_id, $almacen_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }

    /**
     * Obtiene los almacenes asignados a un usuario
     */
    public function obtenerAlmacenesUsuario($usuario_id) {
        $sql = "SELECT a.id, a.codigo, a.nombre 
                FROM almacenes a
                INNER JOIN usuario_almacen ua ON a.id = ua.almacen_id
                WHERE ua.usuario_id = ? 
                AND a.estado_id = 1 
                AND ua.estado_id = 1
                ORDER BY a.nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listar($usuario_id = null) {
        if ($usuario_id) {
            $sql = "SELECT DISTINCT t.id,
                           t.numero_transferencia,
                           ao.nombre AS almacen_origen,
                           ad.nombre AS almacen_destino,
                           t.fecha_solicitud,
                           t.fecha_autorizacion,
                           t.fecha_envio,
                           t.fecha_recepcion,
                           te.nombre AS estado,
                           us.nombre AS usuario_solicita,
                           t.observaciones,
                           t.almacen_origen_id,
                           t.almacen_destino_id
                    FROM transferencias t
                    INNER JOIN almacenes ao ON t.almacen_origen_id = ao.id
                    INNER JOIN almacenes ad ON t.almacen_destino_id = ad.id
                    INNER JOIN transferencias_estados te ON t.transferencia_estado_id = te.id
                    LEFT JOIN usuarios us ON t.usuario_solicita_id = us.id
                    INNER JOIN usuario_almacen ua ON (
                        ua.almacen_id = t.almacen_origen_id OR ua.almacen_id = t.almacen_destino_id
                    )
                    WHERE ua.usuario_id = ?
                    AND ua.estado_id = 1
                    ORDER BY t.id DESC";
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $stmt->execute([$usuario_id]);
        } else {
            // Listado completo (solo admins)
            $sql = "SELECT t.id,
                           t.numero_transferencia,
                           ao.nombre AS almacen_origen,
                           ad.nombre AS almacen_destino,
                           t.fecha_solicitud,
                           t.fecha_autorizacion,
                           t.fecha_envio,
                           t.fecha_recepcion,
                           te.nombre AS estado,
                           us.nombre AS usuario_solicita,
                           t.observaciones,
                           t.almacen_origen_id,
                           t.almacen_destino_id
                    FROM transferencias t
                    INNER JOIN almacenes ao ON t.almacen_origen_id = ao.id
                    INNER JOIN almacenes ad ON t.almacen_destino_id = ad.id
                    INNER JOIN transferencias_estados te ON t.transferencia_estado_id = te.id
                    LEFT JOIN usuarios us ON t.usuario_solicita_id = us.id
                    ORDER BY t.id DESC";
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id, $usuario_id = null) {
        $sql = "SELECT t.*,
                       ao.nombre AS almacen_origen,
                       ad.nombre AS almacen_destino,
                       te.nombre AS estado
                FROM transferencias t
                INNER JOIN almacenes ao ON t.almacen_origen_id = ao.id
                INNER JOIN almacenes ad ON t.almacen_destino_id = ad.id
                INNER JOIN transferencias_estados te ON t.transferencia_estado_id = te.id
                WHERE t.id = ?";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        $transferencia = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si se proporciona usuario_id, validar que tenga acceso
        if ($transferencia && $usuario_id) {
            $tieneAccesoOrigen = $this->usuarioTieneAccesoAlmacen($usuario_id, $transferencia['almacen_origen_id']);
            $tieneAccesoDestino = $this->usuarioTieneAccesoAlmacen($usuario_id, $transferencia['almacen_destino_id']);
            
            // Debe tener acceso a al menos uno de los dos almacenes
            if (!$tieneAccesoOrigen && !$tieneAccesoDestino) {
                throw new Exception("No tienes permisos para acceder a esta transferencia");
            }
            
            // ✅ AGREGAR FLAGS para saber qué puede hacer el usuario
            $transferencia['puede_editar'] = $tieneAccesoOrigen && $transferencia['estado'] === 'Solicitada';
            $transferencia['puede_autorizar'] = $tieneAccesoOrigen && $transferencia['estado'] === 'Solicitada';
            $transferencia['puede_enviar'] = $tieneAccesoOrigen && $transferencia['estado'] === 'Autorizada';
            
            // ✅ CRÍTICO: Solo puede recibir si:
            // 1. Tiene acceso al almacén DESTINO
            // 2. El estado es 'Enviada'
            // 3. NO es el mismo usuario que solicitó la transferencia
            $transferencia['puede_recibir'] = $tieneAccesoDestino 
                                            && $transferencia['estado'] === 'Enviada'
                                            && $transferencia['usuario_solicita_id'] != $usuario_id;
            
            $transferencia['puede_cancelar'] = $tieneAccesoOrigen && in_array($transferencia['estado'], ['Solicitada', 'Autorizada']);
        }
        
        return $transferencia;
    }

    public function obtenerDetalle($transferencia_id) {
        $sql = "SELECT td.id,
                       td.transferencia_id,
                       td.producto_id,
                       p.nombre AS producto,
                       p.codigo AS codigo_producto,
                       td.cantidad,
                       td.cantidad_recibida,
                       td.observaciones
                FROM transferencias_detalle td
                INNER JOIN productos p ON td.producto_id = p.id
                WHERE td.transferencia_id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$transferencia_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertar($data, $detalle, $usuario_id) {
        try {
            $this->conexion->getConexion()->beginTransaction();

            // Validar que origen y destino sean diferentes
            if ($data['almacen_origen_id'] == $data['almacen_destino_id']) {
                throw new Exception("El almacén de origen y destino no pueden ser iguales");
            }

            // ✅ VALIDAR ACCESO DEL USUARIO A LOS ALMACENES
            if (!$this->usuarioTieneAccesoAlmacen($usuario_id, $data['almacen_origen_id'])) {
                throw new Exception("No tienes permisos para realizar transferencias desde el almacén de origen seleccionado");
            }

            if (!$this->usuarioTieneAccesoAlmacen($usuario_id, $data['almacen_destino_id'])) {
                throw new Exception("No tienes permisos para realizar transferencias hacia el almacén de destino seleccionado");
            }

            // Generar número de transferencia
            $numero_transferencia = $this->generarNumeroTransferencia();
            
            // Obtener ID del estado "Solicitada"
            $estado_id = $this->obtenerEstadoPorNombre('Solicitada');
            
            if (!$estado_id) {
                throw new Exception("Error crítico: No se pudo obtener el estado 'Solicitada'. Contacte al administrador.");
            }

            // Insertar transferencia principal
            $sql = "INSERT INTO transferencias
                    (numero_transferencia, almacen_origen_id, almacen_destino_id,
                     fecha_solicitud, transferencia_estado_id, observaciones, usuario_solicita_id,
                     creado_en, actualizado_en)
                    VALUES (?, ?, ?, NOW(), ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $resultado = $stmt->execute([
                $numero_transferencia,
                $data['almacen_origen_id'],
                $data['almacen_destino_id'],
                $estado_id,
                $data['observaciones'] ?? '',
                $usuario_id
            ]);

            if (!$resultado) {
                throw new Exception("Error al insertar la transferencia principal");
            }

            $transferencia_id = $this->conexion->getConexion()->lastInsertId();

            // Insertar detalle de productos
            foreach ($detalle as $item) {
                // Validar stock disponible
                if (!$this->validarStock($item['producto_id'], $data['almacen_origen_id'], $item['cantidad'])) {
                    $producto = $this->obtenerNombreProducto($item['producto_id']);
                    $stock = $this->obtenerStockDisponible($item['producto_id'], $data['almacen_origen_id']);
                    throw new Exception("Stock insuficiente para '{$producto}'. Disponible: {$stock}, Solicitado: {$item['cantidad']}");
                }

                $sql = "INSERT INTO transferencias_detalle
                        (transferencia_id, producto_id, cantidad, observaciones)
                        VALUES (?, ?, ?, ?)";
                
                $stmt = $this->conexion->getConexion()->prepare($sql);
                $resultado = $stmt->execute([
                    $transferencia_id,
                    $item['producto_id'],
                    $item['cantidad'],
                    $item['observaciones'] ?? ''
                ]);

                if (!$resultado) {
                    throw new Exception("Error al insertar el detalle del producto ID: {$item['producto_id']}");
                }
            }

            $this->conexion->getConexion()->commit();
            return $transferencia_id;

        } catch (Exception $e) {
            $this->conexion->getConexion()->rollBack();
            throw $e;
        }
    }

    public function actualizar($id, $data, $detalle, $usuario_id) {
        try {
            $this->conexion->getConexion()->beginTransaction();

            // Obtener transferencia actual
            $transferencia = $this->obtener($id);
            
            if (!$transferencia) {
                throw new Exception("Transferencia no encontrada");
            }

            // Solo se puede editar en estado "Solicitada"
            if ($transferencia['estado'] != 'Solicitada') {
                throw new Exception("Solo se pueden editar transferencias en estado 'Solicitada'");
            }

            // Validar que origen y destino sean diferentes
            if ($data['almacen_origen_id'] == $data['almacen_destino_id']) {
                throw new Exception("El almacén de origen y destino no pueden ser iguales");
            }

            // ✅ VALIDAR ACCESO DEL USUARIO A LOS ALMACENES
            if (!$this->usuarioTieneAccesoAlmacen($usuario_id, $data['almacen_origen_id'])) {
                throw new Exception("No tienes permisos para realizar transferencias desde el almacén de origen seleccionado");
            }

            if (!$this->usuarioTieneAccesoAlmacen($usuario_id, $data['almacen_destino_id'])) {
                throw new Exception("No tienes permisos para realizar transferencias hacia el almacén de destino seleccionado");
            }

            // Actualizar datos generales
            $sql = "UPDATE transferencias SET
                        almacen_origen_id = ?,
                        almacen_destino_id = ?,
                        observaciones = ?,
                        actualizado_en = NOW()
                    WHERE id = ?";
            
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $stmt->execute([
                $data['almacen_origen_id'],
                $data['almacen_destino_id'],
                $data['observaciones'] ?? '',
                $id
            ]);

            // Eliminar detalle anterior
            $sql = "DELETE FROM transferencias_detalle WHERE transferencia_id = ?";
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $stmt->execute([$id]);

            // Insertar nuevo detalle
            foreach ($detalle as $item) {
                // Validar stock disponible
                if (!$this->validarStock($item['producto_id'], $data['almacen_origen_id'], $item['cantidad'])) {
                    $producto = $this->obtenerNombreProducto($item['producto_id']);
                    $stock = $this->obtenerStockDisponible($item['producto_id'], $data['almacen_origen_id']);
                    throw new Exception("Stock insuficiente para '{$producto}'. Disponible: {$stock}, Solicitado: {$item['cantidad']}");
                }

                $sql = "INSERT INTO transferencias_detalle
                        (transferencia_id, producto_id, cantidad, observaciones)
                        VALUES (?, ?, ?, ?)";
                
                $stmt = $this->conexion->getConexion()->prepare($sql);
                $stmt->execute([
                    $id,
                    $item['producto_id'],
                    $item['cantidad'],
                    $item['observaciones'] ?? ''
                ]);
            }

            $this->conexion->getConexion()->commit();
            return true;

        } catch (Exception $e) {
            $this->conexion->getConexion()->rollBack();
            throw $e;
        }
    }

    public function autorizar($id, $usuario_id) {
        $transferencia = $this->obtener($id);
        
        if (!$transferencia) {
            throw new Exception("Transferencia no encontrada");
        }

        if ($transferencia['estado'] != 'Solicitada') {
            throw new Exception("Solo se puede autorizar una transferencia en estado 'Solicitada'");
        }

        // ✅ VALIDAR ACCESO A ALMACENES
        if (!$this->usuarioTieneAccesoAlmacen($usuario_id, $transferencia['almacen_origen_id'])) {
            throw new Exception("No tienes permisos para autorizar transferencias desde este almacén");
        }

        $estado_id = $this->obtenerEstadoPorNombre('Autorizada');

        $sql = "UPDATE transferencias SET
                    transferencia_estado_id = ?,
                    fecha_autorizacion = NOW(),
                    usuario_autoriza_id = ?,
                    actualizado_en = NOW()
                WHERE id = ?";
        
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([$estado_id, $usuario_id, $id]);
    }

    public function enviar($id, $usuario_id) {
        try {
            $this->conexion->getConexion()->beginTransaction();

            $transferencia = $this->obtener($id);
            
            if (!$transferencia) {
                throw new Exception("Transferencia no encontrada");
            }

            if ($transferencia['estado'] != 'Autorizada') {
                throw new Exception("La transferencia debe estar 'Autorizada' para poder enviarse");
            }

            // ✅ VALIDAR ACCESO AL ALMACÉN DE ORIGEN
            if (!$this->usuarioTieneAccesoAlmacen($usuario_id, $transferencia['almacen_origen_id'])) {
                throw new Exception("No tienes permisos para enviar transferencias desde este almacén");
            }

            $detalle = $this->obtenerDetalle($id);

            // Descontar inventario del almacén de origen
            foreach ($detalle as $item) {
                $this->afectarInventario(
                    $item['producto_id'],
                    $transferencia['almacen_origen_id'],
                    -$item['cantidad'],
                    'transferencia',
                    $id,
                    $usuario_id,
                    "Envío transferencia {$transferencia['numero_transferencia']}"
                );
            }

            // Actualizar estado a "Enviada"
            $estado_id = $this->obtenerEstadoPorNombre('Enviada');
            
            $sql = "UPDATE transferencias SET
                        transferencia_estado_id = ?,
                        fecha_envio = NOW(),
                        usuario_envia_id = ?,
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

    public function recibir($id, $cantidades_recibidas, $usuario_id) {
        try {
            $this->conexion->getConexion()->beginTransaction();

            $transferencia = $this->obtener($id);
            
            if (!$transferencia) {
                throw new Exception("Transferencia no encontrada");
            }

            if ($transferencia['estado'] != 'Enviada') {
                throw new Exception("La transferencia debe estar 'Enviada' para poder recibirla");
            }

            // ✅ VALIDAR ACCESO AL ALMACÉN DE DESTINO
            if (!$this->usuarioTieneAccesoAlmacen($usuario_id, $transferencia['almacen_destino_id'])) {
                throw new Exception("No tienes permisos para recibir transferencias en este almacén");
            }

            // ✅ CRÍTICO: Validar que NO sea el mismo usuario que solicitó
            if ($transferencia['usuario_solicita_id'] == $usuario_id) {
                throw new Exception("No puedes recibir una transferencia que tú mismo creaste. Debe ser recibida por otro usuario del almacén destino");
            }

            $detalle = $this->obtenerDetalle($id);

            foreach ($detalle as $item) {
                $cantidad_recibida = isset($cantidades_recibidas[$item['id']]) 
                    ? $cantidades_recibidas[$item['id']] 
                    : $item['cantidad'];

                // Actualizar cantidad recibida en detalle
                $sql = "UPDATE transferencias_detalle SET cantidad_recibida = ? WHERE id = ?";
                $stmt = $this->conexion->getConexion()->prepare($sql);
                $stmt->execute([$cantidad_recibida, $item['id']]);

                // Sumar al inventario destino
                $this->afectarInventario(
                    $item['producto_id'],
                    $transferencia['almacen_destino_id'],
                    $cantidad_recibida,
                    'transferencia',
                    $id,
                    $usuario_id,
                    "Recepción transferencia {$transferencia['numero_transferencia']}"
                );
            }

            // Actualizar estado a "Recibida"
            $estado_id = $this->obtenerEstadoPorNombre('Recibida');
            
            $sql = "UPDATE transferencias SET
                        transferencia_estado_id = ?,
                        fecha_recepcion = NOW(),
                        usuario_recibe_id = ?,
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
        $transferencia = $this->obtener($id);
        
        if (!$transferencia) {
            throw new Exception("Transferencia no encontrada");
        }

        if ($transferencia['estado'] != 'Solicitada' && $transferencia['estado'] != 'Autorizada') {
            throw new Exception("Solo se puede cancelar si está en estado 'Solicitada' o 'Autorizada'");
        }

        $estado_id = $this->obtenerEstadoPorNombre('Cancelada');
        
        $sql = "UPDATE transferencias SET 
                    transferencia_estado_id = ?,
                    actualizado_en = NOW()
                WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([$estado_id, $id]);
    }

    public function obtenerAlmacenes() {
        $sql = "SELECT id, codigo, nombre FROM almacenes WHERE estado_id = 1 ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProductos() {
        $sql = "SELECT id, codigo, nombre FROM productos WHERE estado_id = 1 ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
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

    public function generarNumeroTransferencia() {
        $sql = "SELECT numero_transferencia FROM transferencias ORDER BY id DESC LIMIT 1";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ultimo) {
            $numero = intval(substr($ultimo['numero_transferencia'], 6)) + 1;
        } else {
            $numero = 1;
        }

        return 'TRANS-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    // ==================== MÉTODOS PRIVADOS ====================

    private function validarStock($producto_id, $almacen_id, $cantidad) {
        return $this->obtenerStockDisponible($producto_id, $almacen_id) >= $cantidad;
    }

    private function obtenerEstadoPorNombre($nombre) {
        $sql = "SELECT id FROM transferencias_estados WHERE nombre = ? LIMIT 1";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$nombre]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado['id'] : null;
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

        if ($inventario) {
            $sql = "UPDATE inventario_almacen SET cantidad_actual = ? WHERE id = ?";
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $stmt->execute([$cantidad_nueva, $inventario['id']]);
        } else {
            if ($cantidad_movimiento > 0) {
                $sql = "INSERT INTO inventario_almacen (producto_id, almacen_id, cantidad_actual) 
                        VALUES (?, ?, ?)";
                $stmt = $this->conexion->getConexion()->prepare($sql);
                $stmt->execute([$producto_id, $almacen_id, $cantidad_nueva]);
            } else {
                throw new Exception("No existe inventario para este producto en el almacén especificado");
            }
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
        $stmt->execute([
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
    }


    // ==========================================
// AGREGA ESTE MÉTODO A LA CLASE Transferencias
// (después del método obtener() existente)
// ==========================================

/**
 * Obtiene una transferencia con los nombres completos de todos los usuarios involucrados
 */
public function obtenerConUsuarios($id) {
    $sql = "SELECT t.*,
                   ao.nombre AS almacen_origen,
                   ad.nombre AS almacen_destino,
                   te.nombre AS estado,
                   us.nombre AS usuario_solicita,
                   ua.nombre AS usuario_autoriza,
                   ue.nombre AS usuario_envia,
                   ur.nombre AS usuario_recibe
            FROM transferencias t
            INNER JOIN almacenes ao ON t.almacen_origen_id = ao.id
            INNER JOIN almacenes ad ON t.almacen_destino_id = ad.id
            INNER JOIN transferencias_estados te ON t.transferencia_estado_id = te.id
            LEFT JOIN usuarios us ON t.usuario_solicita_id = us.id
            LEFT JOIN usuarios ua ON t.usuario_autoriza_id = ua.id
            LEFT JOIN usuarios ue ON t.usuario_envia_id = ue.id
            LEFT JOIN usuarios ur ON t.usuario_recibe_id = ur.id
            WHERE t.id = ?";
    
    $stmt = $this->conexion->getConexion()->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


}