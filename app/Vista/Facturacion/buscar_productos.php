<?php
require_once '../../Controlador/FacturasControlador.php';

header('Content-Type: application/json');

try {
    $termino = $_GET['termino'] ?? '';
    $almacen_id = $_GET['almacen_id'] ?? null;
    
    if (strlen($termino) < 2) {
        echo json_encode([
            'success' => true,
            'items' => []
        ]);
        exit;
    }
    
    if (!$almacen_id) {
        throw new Exception("Almacén no especificado");
    }

    $controlador = new FacturasControlador();
    $productos = $controlador->buscarProductos($termino, $almacen_id);
    
    echo json_encode([
        'success' => true,
        'items' => $productos
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'items' => []
    ]);
}