<?php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/PagosControlador.php';

if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión inválida']);
    exit;
}

$factura_id = $_GET['factura_id'] ?? null;

if (!$factura_id) {
    echo json_encode(['success' => false, 'message' => 'ID de factura no especificado']);
    exit;
}

try {
    $controlador = new PagosControlador();
    $pagos = $controlador->obtenerPagosFactura($factura_id);
    
    echo json_encode([
        'success' => true,
        'pagos' => $pagos
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>