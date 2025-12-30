<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class EntradasAlmacen {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
        $this->verificarEstados();
        $this->verificarTiposEntrada();
    }

    /**
     * Verifica que existan los estados necesarios, si no, los crea
     */
    private function verificarEstados() {
        $estadosNecesarios = [
            ['nombre' => 'Registrada', 'descripcion' => 'Entrada registrada pendiente de autorización'],
            ['nombre' => 'Autorizada', 'descripcion' => 'Entrada autorizada y procesada'],
            ['nombre' => 'Cancelada', 'descripcion' => 'Entrada cancelada']
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
     * Verifica que existan los tipos de entrada básicos
     */
    private function verificarTiposEntrada() {
        $tiposNecesarios = [
            ['nombre' => 'Compra a Proveedor', 'descripcion' => 'Entrada por compra a proveedor', 'requiere_autorizacion' => 1],
            ['nombre' => 'Devolución Cliente', 'descripcion' => 'Entrada por devolución de cliente', 'requiere_autorizacion' => 1],
            ['nombre' => 'Ajuste Inventario', 'descripcion' => 'Ajuste positivo de inventario', 'requiere_autorizacion' => 1],
            ['nombre' => 'Producción Interna', 'descripcion' => 'Entrada por producción interna', 'requiere_autorizacion' => 0],
            ['nombre' => 'Transferencia Entrada', 'descripcion' => 'Entrada por transferencia entre almacenes', 'requiere_autorizacion' => 0]
        ];

        $estadoActivo = $this->obtenerEstadoPorNombre('Activo') ?? 1;

        foreach ($tiposNecesarios as $tipo) {
            $sql = "SELECT id FROM tipos_entrada WHERE nombre = ? LIMIT 1";
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $stmt->execute([$tipo['nombre']]);
            
            if (!$stmt->fetch()) {
                $sql = "INSERT INTO tipos_entrada (nombre, descripcion, requiere_autorizacion, estado_id, creado_en, actualizado_en) 
                        VALUES (?, ?, ?, ?, NOW(), NOW())";
                $stmt = $this->conexion->getConexion()->prepare($sql);
                $stmt->execute([$tipo['nombre'], $tipo['descripcion'], $tipo['requiere_autorizacion'], $estadoActivo]);
            }
        }
    }





    /*
    public function listar() {
        $sql = "SELECT ea.id,
                       ea.numero_entrada,
                       a.nombre AS almacen,
                       te.nombre AS tipo_entrada,
                       ea.fecha_entrada,
                       ea.fecha_autorizacion,
                       ea.documento_referencia,
                       e.nombre AS estado,
                       ur.nombre AS usuario_registra,
                       ua.nombre AS usuario_autoriza,
                       ea.motivo
                FROM entradas_almacen ea
                INNER JOIN almacenes a ON ea.almacen_id = a.id
                INNER JOIN tipos_entrada te ON ea.tipo_entrada_id = te.id
                INNER JOIN estados e ON ea.estado_id = e.id
                LEFT JOIN usuarios ur ON ea.usuario_registra_id = ur.id
                LEFT JOIN usuarios ua ON ea.usuario_autoriza_id = ua.id
                ORDER BY ea.id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


*/



public function listar() {
    $usuario_id = $_SESSION['usuario_id'];

    $sql = "SELECT ea.id,
                   ea.numero_entrada,
                   a.nombre AS almacen,
                   te.nombre AS tipo_entrada,
                   ea.fecha_entrada,
                   ea.fecha_autorizacion,
                   ea.documento_referencia,
                   e.nombre AS estado,
                   ur.nombre AS usuario_registra,
                   ua.nombre AS usuario_autoriza,
                   ea.motivo
            FROM entradas_almacen ea
            INNER JOIN almacenes a ON ea.almacen_id = a.id
            INNER JOIN tipos_entrada te ON ea.tipo_entrada_id = te.id
            INNER JOIN estados e ON ea.estado_id = e.id
            LEFT JOIN usuarios ur ON ea.usuario_registra_id = ur.id
            LEFT JOIN usuarios ua ON ea.usuario_autoriza_id = ua.id
            INNER JOIN usuario_almacen ua2 
                    ON ua2.almacen_id = ea.almacen_id
                   AND ua2.usuario_id = :usuario_id
                   AND ua2.estado_id = 1
            ORDER BY ea.id DESC";

    $stmt = $this->conexion->getConexion()->prepare($sql);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function obtener($id) {
        $sql = "SELECT ea.*,
                       a.nombre AS almacen,
                       te.nombre AS tipo_entrada,
                       te.requiere_autorizacion,
                       e.nombre AS estado
                FROM entradas_almacen ea
                INNER JOIN almacenes a ON ea.almacen_id = a.id
                INNER JOIN tipos_entrada te ON ea.tipo_entrada_id = te.id
                INNER JOIN estados e ON ea.estado_id = e.id
                WHERE ea.id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerDetalle($entrada_id) {
        $sql = "SELECT ed.id,
                       ed.entrada_id,
                       ed.producto_id,
                       p.nombre AS producto,
                       p.codigo AS codigo_producto,
                       ed.cantidad,
                       ed.precio_unitario,
                       (ed.cantidad * ed.precio_unitario) AS subtotal,
                       ed.observaciones
                FROM entradas_detalle ed
                INNER JOIN productos p ON ed.producto_id = p.id
                WHERE ed.entrada_id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$entrada_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertar($data, $detalle, $usuario_id) {
        try {
            $this->conexion->getConexion()->beginTransaction();

            // Validaciones
            if (empty($detalle)) {
                throw new Exception("Debe agregar al menos un producto");
            }

            // Generar número de entrada
            $numero_entrada = $this->generarNumeroEntrada();
            
            // Obtener estado "Registrada"
            $estado_id = $this->obtenerEstadoPorNombre('Registrada');
            
            if (!$estado_id) {
                throw new Exception("Error crítico: No se pudo obtener el estado 'Registrada'");
            }

            // Verificar si el tipo de entrada requiere autorización
            $tipo_entrada = $this->obtenerTipoEntrada($data['tipo_entrada_id']);
            $requiere_autorizacion = $tipo_entrada['requiere_autorizacion'] ?? 1;

            // Insertar entrada principal
            $sql = "INSERT INTO entradas_almacen
                    (numero_entrada, almacen_id, tipo_entrada_id, fecha_entrada,
                     motivo, documento_referencia, usuario_registra_id, estado_id,
                     creado_en, actualizado_en)
                    VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $resultado = $stmt->execute([
                $numero_entrada,
                $data['almacen_id'],
                $data['tipo_entrada_id'],
                $data['motivo'] ?? '',
                $data['documento_referencia'] ?? '',
                $usuario_id,
                $estado_id
            ]);

            if (!$resultado) {
                throw new Exception("Error al insertar la entrada principal");
            }

            $entrada_id = $this->conexion->getConexion()->lastInsertId();

            // Insertar detalle de productos
            foreach ($detalle as $item) {
                // Validar precio unitario
                if ($item['precio_unitario'] < 0) {
                    throw new Exception("El precio unitario no puede ser negativo");
                }

                if ($item['cantidad'] <= 0) {
                    throw new Exception("La cantidad debe ser mayor a cero");
                }

                $sql = "INSERT INTO entradas_detalle
                        (entrada_id, producto_id, cantidad, precio_unitario, observaciones)
                        VALUES (?, ?, ?, ?, ?)";
                
                $stmt = $this->conexion->getConexion()->prepare($sql);
                $resultado = $stmt->execute([
                    $entrada_id,
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
                $this->procesarEntrada($entrada_id, $usuario_id);
            }

            $this->conexion->getConexion()->commit();
            return $entrada_id;

        } catch (Exception $e) {
            $this->conexion->getConexion()->rollBack();
            throw $e;
        }
    }

    public function autorizar($id, $usuario_id) {
        try {
            $this->conexion->getConexion()->beginTransaction();

            $entrada = $this->obtener($id);
            
            if (!$entrada) {
                throw new Exception("Entrada no encontrada");
            }

            if ($entrada['estado'] != 'Registrada') {
                throw new Exception("Solo se puede autorizar una entrada en estado 'Registrada'");
            }

            // Procesar la entrada (afectar inventario)
            $this->procesarEntrada($id, $usuario_id);

            // Actualizar estado y fecha de autorización
            $estado_id = $this->obtenerEstadoPorNombre('Autorizada');

            $sql = "UPDATE entradas_almacen SET
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
        $entrada = $this->obtener($id);
        
        if (!$entrada) {
            throw new Exception("Entrada no encontrada");
        }

        if ($entrada['estado'] != 'Registrada') {
            throw new Exception("Solo se puede cancelar si está en estado 'Registrada'");
        }

        $estado_id = $this->obtenerEstadoPorNombre('Cancelada');
        
        $sql = "UPDATE entradas_almacen SET 
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

    /*
    public function 
    () {
        $estadoActivo = $this->obtenerEstadoPorNombre('Activo') ?? 1;
        $sql = "SELECT id, codigo, nombre FROM productos WHERE estado_id = ? ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$estadoActivo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
*/
  

public function obtenerProductos($proveedor_id = null) {
    $estadoActivo = $this->obtenerEstadoPorNombre('Activo') ?? 1;
    
    if ($proveedor_id) {
        $sql = "SELECT p.id, p.codigo, p.nombre 
                FROM productos p
                WHERE p.estado_id = ? 
                AND p.proveedor_id = ?
                ORDER BY p.nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$estadoActivo, $proveedor_id]);
    } else {
        $sql = "SELECT id, codigo, nombre 
                FROM productos 
                WHERE estado_id = ? 
                ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$estadoActivo]);
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    

public function obtenerProveedores() {
    $estadoActivo = $this->obtenerEstadoPorNombre('Activo') ?? 1;
    $sql = "SELECT id, codigo, nombre FROM proveedores WHERE estado_id = ? ORDER BY nombre ASC";
    $stmt = $this->conexion->getConexion()->prepare($sql);
    $stmt->execute([$estadoActivo]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function obtenerTiposEntrada() {
        $estadoActivo = $this->obtenerEstadoPorNombre('Activo') ?? 1;
        $sql = "SELECT id, nombre, descripcion, requiere_autorizacion 
                FROM tipos_entrada 
                WHERE estado_id = ? 
                ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$estadoActivo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generarNumeroEntrada() {
        $sql = "SELECT numero_entrada FROM entradas_almacen ORDER BY id DESC LIMIT 1";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ultimo) {
            $numero = intval(substr($ultimo['numero_entrada'], 4)) + 1;
        } else {
            $numero = 1;
        }

        return 'ENT-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Obtiene el precio de un producto desde la lista de precios vigente
     * Busca primero precio de compra, si no existe, trae el primero disponible
     */
    public function obtenerPrecioProducto($producto_id) {
        // Primero buscar en lista de precios vigente (precio de compra preferentemente)
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
                        WHEN tp.nombre LIKE '%compra%' THEN 1
                        WHEN tp.nombre LIKE '%costo%' THEN 2
                        ELSE 3
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
        
        // Si no se encontró en lista de precios, retornar 0
        return [
            'precio' => 0,
            'tipo_precio' => null,
            'encontrado' => false
        ];
    }

    // ==================== MÉTODOS PRIVADOS ====================

    private function procesarEntrada($entrada_id, $usuario_id) {
        $entrada = $this->obtener($entrada_id);
        $detalle = $this->obtenerDetalle($entrada_id);

        foreach ($detalle as $item) {
            $this->afectarInventario(
                $item['producto_id'],
                $entrada['almacen_id'],
                $item['cantidad'],
                'entrada',
                $entrada_id,
                $usuario_id,
                "Entrada {$entrada['numero_entrada']} - {$entrada['tipo_entrada']}"
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

    private function obtenerTipoEntrada($tipo_entrada_id) {
        $sql = "SELECT * FROM tipos_entrada WHERE id = ? LIMIT 1";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$tipo_entrada_id]);
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

        // Actualizar o insertar en inventario_almacen
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
            if ($cantidad_movimiento > 0) {
                $sql = "INSERT INTO inventario_almacen 
                        (producto_id, almacen_id, cantidad_actual, fecha_ingreso, usuario_modificador_id, actualizado_en) 
                        VALUES (?, ?, ?, NOW(), ?, NOW())";
                $stmt = $this->conexion->getConexion()->prepare($sql);
                $resultado = $stmt->execute([$producto_id, $almacen_id, $cantidad_nueva, $usuario_id]);
                
                if (!$resultado) {
                    throw new Exception("Error al crear el registro en inventario_almacen");
                }
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


    // Agregar este método en la clase EntradasAlmacen (después del método obtener())

/**
 * Obtiene una entrada con todos los datos incluyendo nombres completos de usuarios
 * Este método se usa específicamente para la generación de PDFs
 * 
 * @param int $id ID de la entrada
 * @return array|false Datos completos de la entrada con usuarios, false si no existe
 */
public function obtenerConUsuarios($id) {
    $sql = "SELECT ea.*,
                   a.nombre AS almacen,
                   te.nombre AS tipo_entrada,
                   te.requiere_autorizacion,
                   e.nombre AS estado,
                   ur.nombre AS usuario_registra,
                   ua.nombre AS usuario_autoriza
            FROM entradas_almacen ea
            INNER JOIN almacenes a ON ea.almacen_id = a.id
            INNER JOIN tipos_entrada te ON ea.tipo_entrada_id = te.id
            INNER JOIN estados e ON ea.estado_id = e.id
            LEFT JOIN usuarios ur ON ea.usuario_registra_id = ur.id
            LEFT JOIN usuarios ua ON ea.usuario_autoriza_id = ua.id
            WHERE ea.id = ?";
    
    $stmt = $this->conexion->getConexion()->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


}