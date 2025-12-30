<?php
require_once '../../Controlador/ListaPrecioDetalleControlador.php';

$controlador = new ListaPrecioDetalleControlador();

try {
    $data = [
        'id' => $_POST['id'] ?? null,
        'nombre' => $_POST['nombre'] ?? null,
        'descripcion' => $_POST['descripcion'] ?? null,
        'moneda_id' => $_POST['moneda_id'] ?? null,
        'vigente_desde' => $_POST['vigente_desde'] ?? null,
        'vigente_hasta' => $_POST['vigente_hasta'] ?? null,
        'estado_id' => $_POST['estado_id'] ?? null
    ];

    $lista_id = $controlador->guardarCabecera($data);

    // Si es nuevo, redirige a detalle para agregar productos
    // Si es actualización, vuelve al detalle
    header('Location: detalle.php?id=' . $lista_id . '&mensaje=guardado');
    exit;

} catch (Exception $e) {
    header('Location: listar.php?error=' . urlencode($e->getMessage()));
    exit;
}