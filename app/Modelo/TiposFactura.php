<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class TipoFactura {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function listar() {
        $sql = "SELECT tf.id, tf.codigo, tf.nombre, tf.descripcion, tf.prefijo, 
                       tf.serie_actual, tf.requiere_nit, tf.afecta_inventario, 
                       tf.afecta_cuentas,
                       e.nombre AS estado,
                       tf.creado_en, tf.actualizado_en
                FROM tipos_factura tf
                INNER JOIN bitacora_estados e ON tf.estado_id = e.id
                ORDER BY tf.id ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id) {
        $sql = "SELECT * FROM tipos_factura WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerActivos() {
        $sql = "SELECT id, codigo, nombre, prefijo, requiere_nit 
                FROM tipos_factura 
                WHERE estado_id = 1 
                ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function verificarCodigoExiste($codigo, $id = null) {
        if ($id) {
            $sql = "SELECT COUNT(*) FROM tipos_factura WHERE codigo = ? AND id != ?";
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $stmt->execute([$codigo, $id]);
        } else {
            $sql = "SELECT COUNT(*) FROM tipos_factura WHERE codigo = ?";
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $stmt->execute([$codigo]);
        }
        return $stmt->fetchColumn() > 0;
    }

    public function verificarPrefijoExiste($prefijo, $id = null) {
        if ($id) {
            $sql = "SELECT COUNT(*) FROM tipos_factura WHERE prefijo = ? AND id != ?";
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $stmt->execute([$prefijo, $id]);
        } else {
            $sql = "SELECT COUNT(*) FROM tipos_factura WHERE prefijo = ?";
            $stmt = $this->conexion->getConexion()->prepare($sql);
            $stmt->execute([$prefijo]);
        }
        return $stmt->fetchColumn() > 0;
    }

    public function insertar($data) {
        $sql = "INSERT INTO tipos_factura 
                (codigo, nombre, descripcion, prefijo, serie_actual, requiere_nit, 
                 afecta_inventario, afecta_cuentas, estado_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            strtoupper($data['codigo']),
            $data['nombre'],
            $data['descripcion'],
            strtoupper($data['prefijo']),
            $data['serie_actual'] ?? 1,
            isset($data['requiere_nit']) ? 1 : 0,
            isset($data['afecta_inventario']) ? 1 : 0,
            isset($data['afecta_cuentas']) ? 1 : 0,
            $data['estado_id']
        ]);
    }

    public function actualizar($data) {
        $sql = "UPDATE tipos_factura SET
                    codigo = ?, nombre = ?, descripcion = ?, prefijo = ?, 
                    serie_actual = ?, requiere_nit = ?, 
                    afecta_inventario = ?, afecta_cuentas = ?, estado_id = ?
                WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            strtoupper($data['codigo']),
            $data['nombre'],
            $data['descripcion'],
            strtoupper($data['prefijo']),
            $data['serie_actual'] ?? 1,
            isset($data['requiere_nit']) ? 1 : 0,
            isset($data['afecta_inventario']) ? 1 : 0,
            isset($data['afecta_cuentas']) ? 1 : 0,
            $data['estado_id'],
            $data['id']
        ]);
    }

    public function eliminar($id) {
        // Verificar si tiene series de facturación asociadas
        $sql = "SELECT COUNT(*) FROM series_facturacion WHERE tipo_factura_id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        
        if ($stmt->fetchColumn() > 0) {
            return false; // No se puede eliminar si tiene series asociadas
        }

        $sql = "DELETE FROM tipos_factura WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function obtenerEstados() {
        $sql = "SELECT id, nombre FROM bitacora_estados ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarSerieActual($id, $nueva_serie) {
        $sql = "UPDATE tipos_factura SET serie_actual = ? WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([$nueva_serie, $id]);
    }
}