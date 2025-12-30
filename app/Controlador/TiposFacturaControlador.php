<?php
require_once __DIR__ . '/../Modelo/TiposFactura.php';

class TiposFacturaControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new TipoFactura();
    }

    public function listar() {
        return $this->modelo->listar();
    }

    public function obtener($id) {
        return $this->modelo->obtener($id);
    }

    public function obtenerActivos() {
        return $this->modelo->obtenerActivos();
    }

    public function guardar($data) {
        // Validar que el código no exista
        if ($this->modelo->verificarCodigoExiste($data['codigo'], $data['id'] ?? null)) {
            return ['success' => false, 'message' => 'El código ya existe'];
        }

        // Validar que el prefijo no exista
        if ($this->modelo->verificarPrefijoExiste($data['prefijo'], $data['id'] ?? null)) {
            return ['success' => false, 'message' => 'El prefijo ya existe'];
        }

        if (empty($data['id'])) {
            $resultado = $this->modelo->insertar($data);
        } else {
            $resultado = $this->modelo->actualizar($data);
        }

        return ['success' => $resultado, 'message' => $resultado ? 'Guardado correctamente' : 'Error al guardar'];
    }

    public function eliminar($id) {
        $resultado = $this->modelo->eliminar($id);
        if (!$resultado) {
            return ['success' => false, 'message' => 'No se puede eliminar. Tiene series de facturación asociadas.'];
        }
        return ['success' => true, 'message' => 'Eliminado correctamente'];
    }

    public function obtenerEstados() {
        return $this->modelo->obtenerEstados();
    }

    public function actualizarSerieActual($id, $nueva_serie) {
        return $this->modelo->actualizarSerieActual($id, $nueva_serie);
    }
}
