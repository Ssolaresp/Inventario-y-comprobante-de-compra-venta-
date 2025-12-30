<?php
require_once '../../Controlador/FacturasControlador.php';

header('Content-Type: application/json');

try {
    $termino = $_GET['termino'] ?? '';
    
    if (strlen($termino) < 2) {
        echo json_encode([
            'success' => true,
            'items' => []
        ]);
        exit;
    }

    $controlador = new FacturasControlador();
    $servicios = $controlador->buscarServicios($termino);
    
    echo json_encode([
        'success' => true,
        'items' => $servicios
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'items' => []
    ]);
}