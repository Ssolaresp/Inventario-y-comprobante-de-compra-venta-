<?php
require_once '../../Controlador/TransferenciasControlador.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

header('Content-Type: application/json');

try {
    $producto_id = $_GET['producto_id'] ?? null;
    $almacen_id = $_GET['almacen_id'] ?? null;

    if (!$producto_id || !$almacen_id) {
        throw new Exception("Parámetros incompletos");
    }

    $controlador = new TransferenciasControlador();
    $stock = $controlador->obtenerStockDisponible($producto_id, $almacen_id);

    echo json_encode([
        'success' => true,
        'stock' => $stock,
        'producto_id' => $producto_id,
        'almacen_id' => $almacen_id
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}