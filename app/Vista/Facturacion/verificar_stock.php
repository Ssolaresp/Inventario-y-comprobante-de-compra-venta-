<?php
require_once '../../Controlador/FacturasControlador.php';

header('Content-Type: application/json');

try {
    $producto_id = $_GET['producto_id'] ?? null;
    $almacen_id = $_GET['almacen_id'] ?? null;
    $cantidad = $_GET['cantidad'] ?? 1;
    
    if (!$producto_id || !$almacen_id) {
        throw new Exception("Producto o almacén no especificado");
    }

    $controlador = new FacturasControlador();
    $resultado = $controlador->verificarStock($producto_id, $almacen_id, $cantidad);
    
    echo json_encode([
        'success' => true,
        'disponible' => $resultado['disponible'],
        'stock_actual' => $resultado['stock_actual'],
        'faltante' => $resultado['faltante']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}