<?php
require_once __DIR__ . '/../Conexion/Conexion.php';

class Cliente {
    private $conexion;

    public function __construct() {
        $this->conexion = new Conexion();
    }

    /* ---------- LISTAR ---------- */
    public function listar() {
        $sql = "SELECT c.id_cliente,
                       c.codigo_cliente,
                       c.primer_nombre,
                       c.segundo_nombre,
                       c.primer_apellido,
                       c.segundo_apellido,
                       c.razon_social,
                       c.nit,
                       c.dpi,
                       c.telefono,
                       c.correo,
                       c.direccion,
                       d.nombre_departamento,
                       m.nombre_municipio,
                       ce.nombre AS estado,
                       cv.nombre_canal,
                       c.fecha_registro,
                       c.creado_en,
                       c.actualizado_en
                FROM clientes c
                LEFT JOIN departamentos d  ON c.id_departamento = d.id_departamento
                LEFT JOIN municipios   m  ON c.id_municipio   = m.id_municipio
                INNER JOIN cli_estados ce ON c.id_estado      = ce.id_estado
                LEFT JOIN canales_venta cv ON c.id_canal      = cv.id_canal
                ORDER BY c.id_cliente DESC";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ---------- OBTENER 1 ---------- */
    public function obtener($id) {
        $sql = "SELECT * FROM clientes WHERE id_cliente = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ---------- GENERAR CÓDIGO ---------- */
    private function generarCodigo() {
        $sql = "SELECT MAX(id_cliente) AS ultimo FROM clientes";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        $ultimo = $stmt->fetch(PDO::FETCH_ASSOC)['ultimo'] ?? 0;
        return 'CLI-' . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
    
    public function obtenerSiguienteCodigo() {
        return $this->generarCodigo();
    }

    /* ---------- INSERTAR ---------- */
    public function insertar($data) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        $codigo = $this->generarCodigo();

        $sql = "INSERT INTO clientes
                (codigo_cliente, id_tipo_cliente, primer_nombre, segundo_nombre,
                 primer_apellido, segundo_apellido, razon_social, nit, dpi,
                 telefono, correo, direccion, id_departamento, id_municipio,
                 id_estado, id_canal, usuario_registra_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $codigo,
            $data['id_tipo_cliente'],
            $data['primer_nombre']      ?: null,
            $data['segundo_nombre']     ?: null,
            $data['primer_apellido']    ?: null,
            $data['segundo_apellido']   ?: null,
            $data['razon_social']       ?: null,
            $data['nit'],
            $data['dpi']                ?: null,
            $data['telefono'],
            $data['correo'],
            $data['direccion'],
            $data['id_departamento']    ?: null,
            $data['id_municipio']       ?: null,
            $data['id_estado'],
            $data['id_canal']           ?: null,
            $usuario_id
        ]);
    }

    /* ---------- ACTUALIZAR ---------- */
    public function actualizar($data) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        $sql = "UPDATE clientes SET
                    id_tipo_cliente   = ?,
                    primer_nombre     = ?,
                    segundo_nombre    = ?,
                    primer_apellido   = ?,
                    segundo_apellido  = ?,
                    razon_social      = ?,
                    nit               = ?,
                    dpi               = ?,
                    telefono          = ?,
                    correo            = ?,
                    direccion         = ?,
                    id_departamento   = ?,
                    id_municipio      = ?,
                    id_estado         = ?,
                    id_canal          = ?,
                    usuario_modifica_id = ?,
                    actualizado_en    = NOW()
                WHERE id_cliente = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([
            $data['id_tipo_cliente'],
            $data['primer_nombre']    ?: null,
            $data['segundo_nombre']   ?: null,
            $data['primer_apellido']  ?: null,
            $data['segundo_apellido'] ?: null,
            $data['razon_social']     ?: null,
            $data['nit'],
            $data['dpi']              ?: null,
            $data['telefono'],
            $data['correo'],
            $data['direccion'],
            $data['id_departamento']  ?: null,
            $data['id_municipio']     ?: null,
            $data['id_estado'],
            $data['id_canal']         ?: null,
            $usuario_id,
            $data['id_cliente']
        ]);
    }

    /* ---------- ELIMINAR ---------- */
    public function eliminar($id) {
        $sql = "DELETE FROM clientes WHERE id_cliente = ?";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        return $stmt->execute([$id]);
    }

    /* ---------- COMBOS ---------- */
    public function obtenerEstados() {
        $sql = "SELECT id_estado, nombre FROM cli_estados ORDER BY nombre";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerCanales() {
        $sql = "SELECT id_canal, nombre_canal FROM canales_venta ORDER BY nombre_canal";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerDepartamentos() {
        $sql = "SELECT id_departamento, nombre_departamento FROM departamentos ORDER BY nombre_departamento";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerMunicipios($idDepa) {
        // CORREGIDO: Usar departamento_id según estructura de la BD
        $sql = "SELECT id_municipio, nombre_municipio
                FROM municipios
                WHERE departamento_id = ?
                ORDER BY nombre_municipio";
        $stmt = $this->conexion->getConexion()->prepare($sql);
        $stmt->execute([$idDepa]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}