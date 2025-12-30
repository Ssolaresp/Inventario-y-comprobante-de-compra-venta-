<?php
require_once __DIR__ . '/../Modelo/Facturas.php';

class FacturasControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new Factura();
    }

    public function listar($filtros = []) {
        return $this->modelo->listar($filtros);
    }

    public function obtener($id) {
        return $this->modelo->obtener($id);
    }

    public function obtenerDetalle($id) {
        return $this->modelo->obtenerDetalle($id);
    }

    public function crear($data, $detalle) {
        return $this->modelo->crear($data, $detalle);
    }

    public function anular($id, $motivo) {
        return $this->modelo->anular($id, $motivo);
    }

 
    
    /*
// ===================================================================
// Archivo: FacturasControlador.php
// Función: registrarPago
// ===================================================================

public function registrarPago($factura_id, $data) {
    // 1. Validación de Sesión y Obtención del ID de Usuario
    // Nos aseguramos de que la sesión esté iniciada y el ID exista.
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
        // Devolvemos un error claro si el usuario no está autenticado
        return ['success' => false, 'message' => 'Error de autenticación: El usuario no está logueado o la sesión ha expirado.'];
    }
    
    // Inyectamos el ID del usuario logueado en los datos que van al modelo
    $data['usuario_id'] = $_SESSION['usuario_id'];

    // 2. Validación de datos mínimos (Monto, Forma de Pago, Fecha)
    if (empty($data['monto']) || empty($data['forma_pago_id']) || empty($data['fecha_pago'])) {
        return ['success' => false, 'message' => 'Error: Faltan datos obligatorios para el pago (Monto, Forma de Pago, Fecha).'];
    }

    // 3. Llamar al modelo
    return $this->modelo->registrarPago($factura_id, $data);
}



*/



public function registrarPago($factura_id, $data) {
    // 1. Validación de Sesión y Obtención del ID de Usuario
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
        return ['success' => false, 'message' => 'Error de autenticación: El usuario no está logueado o la sesión ha expirado.'];
    }
    
    // Usamos el nombre correcto del campo según la tabla
    $data['usuario_registra_id'] = $_SESSION['usuario_id'];

    // 2. Validación de datos mínimos
    if (empty($data['monto']) || empty($data['forma_pago_id']) || empty($data['fecha_pago'])) {
        return ['success' => false, 'message' => 'Error: Faltan datos obligatorios para el pago (Monto, Forma de Pago, Fecha).'];
    }

    // 3. Llamar al modelo
    return $this->modelo->registrarPago($factura_id, $data);
}


    public function obtenerPagos($factura_id) {
        return $this->modelo->obtenerPagos($factura_id);
    }

    public function verificarStock($producto_id, $almacen_id, $cantidad) {
        return $this->modelo->verificarStock($producto_id, $almacen_id, $cantidad);
    }

    // Catálogos
    public function obtenerTiposFactura() {
        return $this->modelo->obtenerTiposFactura();
    }

    public function obtenerSeriesPorTipo($tipo_id) {
        return $this->modelo->obtenerSeriesPorTipo($tipo_id);
    }

    public function obtenerClientes() {
        return $this->modelo->obtenerClientes();
    }

    public function obtenerFormasPago() {
        return $this->modelo->obtenerFormasPago();
    }

    public function obtenerAlmacenes() {
        return $this->modelo->obtenerAlmacenes();
    }

    public function obtenerImpuestos() {
        return $this->modelo->obtenerImpuestos();
    }

    public function obtenerEstados() {
        return $this->modelo->obtenerEstados();
    }

    public function buscarProductos($termino, $almacen_id) {
        return $this->modelo->buscarProductos($termino, $almacen_id);
    }

    public function buscarServicios($termino) {
        return $this->modelo->buscarServicios($termino);
    }

    // Listas de Precios
    public function obtenerListasPrecios($fecha = null) {
        return $this->modelo->obtenerListasPrecios($fecha);
    }



    /*
    public function obtenerPrecioProducto($producto_id, $lista_precio_id = null, $fecha = null) {
        return $this->modelo->obtenerPrecioProducto($producto_id, $lista_precio_id, $fecha);
    }


    */


    public function obtenerPrecioProducto($producto_id, $lista_precio_id = null, $fecha = null) {
    return $this->modelo->obtenerPrecioProducto($producto_id, $lista_precio_id, $fecha);
}

    public function obtenerPreciosProductoPorLista($producto_id, $lista_precio_id) {
        return $this->modelo->obtenerPreciosProductoPorLista($producto_id, $lista_precio_id);
    }
}