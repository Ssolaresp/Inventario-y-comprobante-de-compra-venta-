<?php
require_once '../../Controlador/FacturasControlador.php';

header('Content-Type: application/json');

try {
    $producto_id = $_GET['producto_id'] ?? null;
    $lista_precio_id = $_GET['lista_precio_id'] ?? null;
    $fecha = $_GET['fecha'] ?? date('Y-m-d');

    if (!$producto_id) {
        throw new Exception("Producto no especificado");
    }

    $controlador = new FacturasControlador();
    $resultado = $controlador->obtenerPrecioProducto($producto_id, $lista_precio_id, $fecha);

    echo json_encode([
        'success' => true,
        'encontrado' => $resultado['encontrado'],
        'precio' => $resultado['precio'],
        'tipo_precio' => $resultado['tipo_precio'],
        'lista_nombre' => $resultado['lista_nombre']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}