<?php
require_once __DIR__ . '/../Modelo/EntradasAlmacen.php';

class EntradasAlmacenControlador {
    private $modelo;

    public function __construct() {
        $this->modelo = new EntradasAlmacen();
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
            throw new Exception("Debes agregar al menos un producto a la entrada");
        }
        
        if (empty($data['almacen_id']) || empty($data['tipo_entrada_id'])) {
            throw new Exception("Debes seleccionar almacén y tipo de entrada");
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

    public function obtenerTiposEntrada() {
        return $this->modelo->obtenerTiposEntrada();
    }

    public function generarNumeroEntrada() {
        return $this->modelo->generarNumeroEntrada();
    }

    public function obtenerPrecioProducto($producto_id) {
        if (empty($producto_id)) {
            throw new Exception("ID de producto no especificado");
        }
        return $this->modelo->obtenerPrecioProducto($producto_id);
    }



    public function obtenerProductosPorProveedor($proveedor_id = null) {
    return $this->modelo->obtenerProductos($proveedor_id);
}

public function obtenerProveedores() {
    return $this->modelo->obtenerProveedores();
}



// Agregar este método en la clase EntradasAlmacenControlador

/**
 * Obtiene los datos completos de una entrada para generar PDF
 * Incluye nombres de usuarios y toda la información necesaria
 * 
 * @param int $id ID de la entrada
 * @return array Datos completos de la entrada
 * @throws Exception Si no se encuentra la entrada o ID inválido
 */
public function obtenerParaPDF($id) {
    if (empty($id)) {
        throw new Exception("ID de entrada no especificado");
    }
    
    // Obtener entrada completa con nombres de usuarios
    $entrada = $this->modelo->obtenerConUsuarios($id);
    
    if (!$entrada) {
        throw new Exception("Entrada no encontrada");
    }
    
    return $entrada;
}




}