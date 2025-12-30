<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class Producto {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    public function listar() {
        $sql = "SELECT p.id, p.codigo, p.nombre, p.descripcion, 
                       c.nombre AS categoria, 
                       u.nombre AS unidad_medida,
                       p.peso, p.imagen_url,
                       prov.nombre AS proveedor,
                       e.nombre AS estado,
                       p.creado_en, p.actualizado_en
                FROM productos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                INNER JOIN unidades_medida u ON p.unidad_medida_id = u.id
                INNER JOIN bitacora_estados e ON p.estado_id = e.id
                LEFT JOIN proveedores prov ON p.proveedor_id = prov.id
                ORDER BY p.id DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id) {
        $sql = "SELECT * FROM productos WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function generarCodigo() {
        $sql = "SELECT MAX(id) AS ultimo_id FROM productos";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $ultimo = $resultado['ultimo_id'] ?? 0;
        $nuevo = $ultimo + 1;
        return 'PROD-' . str_pad($nuevo, 4, '0', STR_PAD_LEFT);
    }

    public function obtenerSiguienteCodigo() {
        return $this->generarCodigo();
    }

    public function insertar($data) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        $codigo = $this->generarCodigo();
        $imagen_url = null;

        if (!empty($_FILES['imagen']['name'])) {
            $ruta = '../../../assets/img/productos/';
            if (!is_dir($ruta)) mkdir($ruta, 0777, true);

            $nombreArchivo = uniqid() . '_' . basename($_FILES['imagen']['name']);
            $destino = $ruta . $nombreArchivo;

            if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Error al subir imagen: " . $_FILES['imagen']['error']);
            }

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
                $imagen_url = 'assets/img/productos/' . $nombreArchivo;
            } else {
                throw new Exception("No se pudo mover la imagen a: $destino");
            }
        }

        $sql = "INSERT INTO productos 
                (codigo, nombre, descripcion, categoria_id, unidad_medida_id, proveedor_id, peso, imagen_url, estado_id, usuario_creador_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $codigo,
            $data['nombre'],
            $data['descripcion'],
            $data['categoria_id'],
            $data['unidad_medida_id'],
            $data['proveedor_id'] ?: null,
            $data['peso'] ?: null,
            $imagen_url,
            $data['estado_id'],
            $usuario_id
        ]);
    }

    public function actualizar($data) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        $imagen_url = $data['imagen_actual'] ?? null;

        if (!empty($_FILES['imagen']['name'])) {
            $ruta = '../../../assets/img/productos/';
            if (!is_dir($ruta)) mkdir($ruta, 0777, true);

            $nombreArchivo = uniqid() . '_' . basename($_FILES['imagen']['name']);
            $destino = $ruta . $nombreArchivo;

            if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Error al subir imagen: " . $_FILES['imagen']['error']);
            }

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
                if (!empty($imagen_url) && file_exists('../../../' . $imagen_url)) {
                    unlink('../../../' . $imagen_url);
                }
                $imagen_url = 'assets/img/productos/' . $nombreArchivo;
            } else {
                throw new Exception("No se pudo mover la imagen a: $destino");
            }
        }

        $sql = "UPDATE productos SET
                    nombre = ?, descripcion = ?, categoria_id = ?, unidad_medida_id = ?,
                    proveedor_id = ?, peso = ?, imagen_url = ?, estado_id = ?, usuario_modificador_id = ?
                WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['categoria_id'],
            $data['unidad_medida_id'],
            $data['proveedor_id'] ?: null,
            $data['peso'] ?: null,
            $imagen_url,
            $data['estado_id'],
            $usuario_id,
            $data['id']
        ]);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM productos WHERE id = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
    }

    public function obtenerEstados() {
        $sql = "SELECT id, nombre FROM bitacora_estados ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCategorias() {
        $sql = "SELECT id, nombre FROM categorias ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerUnidadesMedida() {
        $sql = "SELECT id, nombre FROM unidades_medida ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProveedores() {
        $sql = "SELECT id, nombre FROM proveedores WHERE estado_id = 1 ORDER BY nombre ASC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}