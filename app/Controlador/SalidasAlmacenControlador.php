<?php
require_once __DIR__ . '/../Modelo/SalidasAlmacen.php';

class SalidasAlmacenControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new SalidasAlmacen();
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

    public function listar() {
        return $this->modelo->listar();
    }

    public function obtener($id) {
        return $this->modelo->obtener($id);
    }

    public function obtenerDetalle($id) {
        return $this->modelo->obtenerDetalle($id);
    }

    public function crear($data, $detalle) {
        // Validaciones básicas
        if (empty($detalle)) {
            throw new Exception("Debes agregar al menos un producto a la salida");
        }
        
        if (empty($data['almacen_id']) || empty($data['tipo_salida_id'])) {
            throw new Exception("Debes seleccionar almacén y tipo de salida");
        }

        // Validar que todos los productos tengan precio y cantidad
        foreach ($detalle as $item) {
            if (empty($item['producto_id'])) {
                throw new Exception("Todos los productos deben estar seleccionados");
            }
            if (!isset($item['cantidad']) || $item['cantidad'] <= 0) {
                throw new Exception("Todas las cantidades deben ser mayores a cero");
            }
            if (!isset($item['precio_unitario']) || $item['precio_unitario'] < 0) {
                throw new Exception("Los precios unitarios no pueden ser negativos");
            }
        }

        // Obtener usuario de sesión
        $usuario_id = $this->obtenerUsuarioId();
        
        // Llamar al modelo
        return $this->modelo->insertar($data, $detalle, $usuario_id);
    }

    public function autorizar($id) {
        $usuario_id = $this->obtenerUsuarioId();
        return $this->modelo->autorizar($id, $usuario_id);
    }

    public function cancelar($id) {
        return $this->modelo->cancelar($id);
    }

    public function obtenerAlmacenes() {
        return $this->modelo->obtenerAlmacenes();
    }

    public function obtenerProductos() {
        return $this->modelo->obtenerProductos();
    }

    public function obtenerTiposSalida() {
        return $this->modelo->obtenerTiposSalida();
    }

    public function generarNumeroSalida() {
        return $this->modelo->generarNumeroSalida();
    }

    public function obtenerStockDisponible($producto_id, $almacen_id) {
        return $this->modelo->obtenerStockDisponible($producto_id, $almacen_id);
    }

    public function obtenerPrecioProducto($producto_id) {
        if (empty($producto_id)) {
            throw new Exception("ID de producto no especificado");
        }
        return $this->modelo->obtenerPrecioProducto($producto_id);
    }

    // ==========================================
// AGREGA ESTE MÉTODO A LA CLASE SalidasAlmacenControlador
// (al final de la clase, antes del cierre })
// ==========================================

/**
 * Obtiene datos completos de una salida para PDF
 */
public function obtenerParaPDF($id) {
    if (empty($id)) {
        throw new Exception("ID de salida no especificado");
    }
    
    // Obtener salida completa con nombres de usuarios
    $salida = $this->modelo->obtenerConUsuarios($id);
    
    if (!$salida) {
        throw new Exception("Salida no encontrada");
    }
    
    return $salida;
}


}