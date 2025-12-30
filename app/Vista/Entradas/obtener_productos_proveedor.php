<?php
header('Content-Type: application/json');
session_start();

require_once '../../Controlador/EntradasAlmacenControlador.php';

try {
    $proveedor_id = $_GET['proveedor_id'] ?? null;
    
    if (!$proveedor_id) {
        echo json_encode(['success' => false, 'error' => 'Proveedor no especificado']);
        exit;
    }

    $controlador = new EntradasAlmacenControlador();
    $productos = $controlador->obtenerProductosPorProveedor($proveedor_id);

    echo json_encode([
        'success' => true,
        'productos' => $productos
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}