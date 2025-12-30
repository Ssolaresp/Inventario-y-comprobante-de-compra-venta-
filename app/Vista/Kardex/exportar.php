<?php
// NO BORRES - Debe ser lo primero
ob_start();
session_start();

if (!isset($_SESSION['usuario_id'])) {
    ob_end_clean();
    header('Location: ../../login.php');
    exit;
}

require_once '../../Controlador/BitacoraInventarioControlador.php';

// Obtener filtros
$filtros = [
    'producto_id'     => $_GET['producto_id'] ?? '',
    'almacen_id'      => $_GET['almacen_id'] ?? '',
    'referencia_tipo' => $_GET['referencia_tipo'] ?? '',
    'fecha_desde'     => $_GET['fecha_desde'] ?? '',
    'fecha_hasta'     => $_GET['fecha_hasta'] ?? '',
    'limite'          => $_GET['limite'] ?? ''
];

try {
    $controlador = new BitacoraInventarioControlador();
    
    // Limpiar buffer
    ob_end_clean();
    
    // Exportar
    $controlador->exportarKardex($filtros);
    
} catch (Exception $e) {
    ob_end_clean();
    header('Location: kardex.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>