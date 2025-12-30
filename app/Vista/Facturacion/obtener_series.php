<?php
require_once '../../Controlador/FacturasControlador.php';

header('Content-Type: application/json');

try {
    $tipo_factura_id = $_GET['tipo_factura_id'] ?? null;
    
    if (!$tipo_factura_id) {
        throw new Exception("Tipo de factura no especificado");
    }

    $controlador = new FacturasControlador();
    $series = $controlador->obtenerSeriesPorTipo($tipo_factura_id);
    
    echo json_encode([
        'success' => true,
        'series' => $series
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}