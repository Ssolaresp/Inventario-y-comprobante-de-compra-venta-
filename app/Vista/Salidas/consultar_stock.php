<?php
require_once '../../Controlador/SalidasAlmacenControlador.php';

header('Content-Type: application/json');

try {
    $producto_id = $_GET['producto_id'] ?? null;
    $almacen_id = $_GET['almacen_id'] ?? null;
    
    if (!$producto_id || !$almacen_id) {
        throw new Exception("Producto o almacén no especificado");
    }

    $controlador = new SalidasAlmacenControlador();
    $stock = $controlador->obtenerStockDisponible($producto_id, $almacen_id);
    
    echo json_encode([
        'success' => true,
        'stock' => $stock
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'stock' => 0
    ]);
}