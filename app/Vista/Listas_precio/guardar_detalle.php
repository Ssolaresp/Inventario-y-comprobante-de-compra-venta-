<?php
require_once '../../Controlador/ListaPrecioDetalleControlador.php';

$controlador = new ListaPrecioDetalleControlador();

try {
    $data = [
        'id' => $_POST['id'] ?? null,
        'lista_precio_id' => $_POST['lista_precio_id'] ?? null,
        'producto_id' => $_POST['producto_id'] ?? null,
        'tipo_precio_id' => $_POST['tipo_precio_id'] ?? null,
        'precio' => $_POST['precio'] ?? null
    ];

    $controlador->guardarDetalle($data);

    header('Location: detalle.php?id=' . $data['lista_precio_id'] . '&mensaje=guardado');
    exit;

} catch (Exception $e) {
    header('Location: detalle.php?id=' . $data['lista_precio_id'] . '&error=' . urlencode($e->getMessage()));
    exit;
}