<?php
require_once '../../Controlador/TransferenciasControlador.php';
include '../../../includes/inicio.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: nuevo.php");
    exit();
}

$controlador = new TransferenciasControlador();

try {
    // Preparar datos generales
    $data = [
        'almacen_origen_id' => $_POST['almacen_origen_id'] ?? null,
        'almacen_destino_id' => $_POST['almacen_destino_id'] ?? null,
        'observaciones' => $_POST['observaciones'] ?? ''
    ];

    // Preparar detalle de productos
    $detalle = [];
    if (isset($_POST['productos']) && is_array($_POST['productos'])) {
        foreach ($_POST['productos'] as $producto) {
            if (!empty($producto['producto_id']) && !empty($producto['cantidad'])) {
                $detalle[] = [
                    'producto_id' => $producto['producto_id'],
                    'cantidad' => $producto['cantidad'],
                    'observaciones' => $producto['observaciones'] ?? ''
                ];
            }
        }
    }

    // Crear la transferencia (valida permisos internamente)
    $transferencia_id = $controlador->crear($data, $detalle);
    
    header("Location: ver.php?id={$transferencia_id}&mensaje=guardado");
    exit();

} catch (Exception $e) {
    header("Location: nuevo.php?error=" . urlencode($e->getMessage()));
    exit();
}