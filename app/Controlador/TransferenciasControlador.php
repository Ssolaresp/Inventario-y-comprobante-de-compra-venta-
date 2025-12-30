<?php
require_once __DIR__ . '/../Modelo/Transferencias.php';

class TransferenciasControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new Transferencias();
    }

    /**
     * Obtiene el ID del usuario de la sesión
     */
    private function obtenerUsuarioId() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario_id'])) {
            throw new Exception("No hay sesión activa. Por favor inicia sesión.");
        }
        
        return $_SESSION['usuario_id'];
    }

    /**
     * Lista transferencias filtradas por permisos del usuario
     */
    public function listar() {
        $usuario_id = $this->obtenerUsuarioId();
        return $this->modelo->listar($usuario_id);
    }

    public function obtener($id) {
        $usuario_id = $this->obtenerUsuarioId();
        return $this->modelo->obtener($id, $usuario_id);
    }

    public function obtenerDetalle($id) {
        return $this->modelo->obtenerDetalle($id);
    }

    public function crear($data, $detalle) {
        if (empty($detalle)) {
            throw new Exception("Debes agregar al menos un producto a la transferencia");
        }
        
        if (empty($data['almacen_origen_id']) || empty($data['almacen_destino_id'])) {
            throw new Exception("Debes seleccionar almacén de origen y destino");
        }

        $usuario_id = $this->obtenerUsuarioId();
        return $this->modelo->insertar($data, $detalle, $usuario_id);
    }

    public function actualizar($id, $data, $detalle) {
        if (empty($detalle)) {
            throw new Exception("Debes agregar al menos un producto a la transferencia");
        }
        
        if (empty($data['almacen_origen_id']) || empty($data['almacen_destino_id'])) {
            throw new Exception("Debes seleccionar almacén de origen y destino");
        }

        $usuario_id = $this->obtenerUsuarioId();
        return $this->modelo->actualizar($id, $data, $detalle, $usuario_id);
    }

    public function autorizar($id) {
        $usuario_id = $this->obtenerUsuarioId();
        return $this->modelo->autorizar($id, $usuario_id);
    }

    public function enviar($id) {
        $usuario_id = $this->obtenerUsuarioId();
        return $this->modelo->enviar($id, $usuario_id);
    }

    public function recibir($id, $cantidades_recibidas) {
        $usuario_id = $this->obtenerUsuarioId();
        return $this->modelo->recibir($id, $cantidades_recibidas, $usuario_id);
    }

    public function cancelar($id) {
        return $this->modelo->cancelar($id);
    }

    /**
     * Obtiene solo los almacenes asignados al usuario actual
     */
    public function obtenerAlmacenes() {
        $usuario_id = $this->obtenerUsuarioId();
        return $this->modelo->obtenerAlmacenesUsuario($usuario_id);
    }

    public function obtenerProductos() {
        return $this->modelo->obtenerProductos();
    }

    public function generarNumeroTransferencia() {
        return $this->modelo->generarNumeroTransferencia();
    }

    public function obtenerStockDisponible($producto_id, $almacen_id) {
        return $this->modelo->obtenerStockDisponible($producto_id, $almacen_id);
    }

    /**
     * Obtiene datos completos de una transferencia para PDF
     */
    public function obtenerParaPDF($id) {
        $usuario_id = $this->obtenerUsuarioId();
        
        // Obtener transferencia con validación de acceso
        $transferencia = $this->modelo->obtener($id, $usuario_id);
        
        if (!$transferencia) {
            throw new Exception("Transferencia no encontrada o no tienes permisos");
        }
        
        // Obtener detalle de productos
        $detalle = $this->modelo->obtenerDetalle($id);
        
        // Obtener nombres de usuarios para firmas
        $transferencia = $this->modelo->obtenerConUsuarios($id);
        
        return [
            'transferencia' => $transferencia,
            'detalle' => $detalle
        ];
    }
}