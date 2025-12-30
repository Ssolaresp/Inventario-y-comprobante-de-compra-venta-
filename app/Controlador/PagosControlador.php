<?php
require_once __DIR__ . '/../Modelo/Pagos.php';

class PagosControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new Pagos();
    }

    public function listarFacturasPendientes($filtros = []) {
        return $this->modelo->listarFacturasPendientes($filtros);
    }

    public function obtenerFactura($factura_id) {
        return $this->modelo->obtenerFactura($factura_id);
    }

    public function registrarPago($factura_id, $data) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // ⭐ SOLUCIÓN: Buscar el ID en $_SESSION['id'] primero
        $usuario_id = $_SESSION['id'] ?? $_SESSION['usuario_id'] ?? null;
        
        if (!$usuario_id) {
            return [
                'success' => false, 
                'message' => 'Error: Usuario no autenticado. Por favor inicie sesión nuevamente.'
            ];
        }
        
        $data['usuario_registra_id'] = $usuario_id;

        if (empty($data['monto']) || empty($data['forma_pago_id']) || empty($data['fecha_pago'])) {
            return [
                'success' => false, 
                'message' => 'Faltan datos obligatorios: Monto, Forma de Pago y Fecha.'
            ];
        }

        if ($data['monto'] <= 0) {
            return [
                'success' => false, 
                'message' => 'El monto debe ser mayor a cero.'
            ];
        }

        return $this->modelo->registrarPago($factura_id, $data);
    }

    public function obtenerPagosFactura($factura_id) {
        return $this->modelo->obtenerPagosFactura($factura_id);
    }

    public function obtenerHistorialFacturas($filtros = []) {
        return $this->modelo->obtenerHistorialFacturas($filtros);
    }

    public function obtenerFormasPago() {
        return $this->modelo->obtenerFormasPago();
    }

    public function obtenerClientes() {
        return $this->modelo->obtenerClientes();
    }

    public function obtenerEstadosFactura() {
        return $this->modelo->obtenerEstadosFactura();
    }

    public function obtenerEstadisticasPagos() {
        return $this->modelo->obtenerEstadisticasPagos();
    }
}