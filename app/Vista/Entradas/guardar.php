<?php
require_once '../../Controlador/EntradasAlmacenControlador.php';

$controlador = new EntradasAlmacenControlador();

try {
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new Exception("Método no permitido");
    }

    // Capturar datos principales
    $data = [
        'tipo_entrada_id' => $_POST['tipo_entrada_id'] ?? null,
        'almacen_id' => $_POST['almacen_id'] ?? null,
        'documento_referencia' => $_POST['documento_referencia'] ?? '',
        'motivo' => $_POST['motivo'] ?? ''
    ];

    // Capturar detalle de productos
    $detalle = [];
    if (isset($_POST['productos']) && is_array($_POST['productos'])) {
        foreach ($_POST['productos'] as $producto) {
            if (!empty($producto['producto_id'])) {
                $detalle[] = [
                    'producto_id' => $producto['producto_id'],
                    'cantidad' => $producto['cantidad'] ?? 0,
                    'precio_unitario' => $producto['precio_unitario'] ?? 0,
                    'observaciones' => $producto['observaciones'] ?? ''
                ];
            }
        }
    }

    // Crear la entrada
    $entrada_id = $controlador->crear($data, $detalle);

    header('Location: listar.php?mensaje=creada');
    exit;

} catch (Exception $e) {
    header('Location: nuevo.php?error=' . urlencode($e->getMessage()));
    exit;
}