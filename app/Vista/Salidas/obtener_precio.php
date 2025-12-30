<?php
require_once '../../Controlador/SalidasAlmacenControlador.php';

header('Content-Type: application/json');

try {
    $producto_id = $_GET['producto_id'] ?? null;
    
    if (!$producto_id) {
        throw new Exception("Producto no especificado");
    }

    $controlador = new SalidasAlmacenControlador();
    $resultado = $controlador->obtenerPrecioProducto($producto_id);
    
    echo json_encode([
        'success' => true,
        'precio' => $resultado['precio'],
        'tipo_precio' => $resultado['tipo_precio'],
        'encontrado' => $resultado['encontrado']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}