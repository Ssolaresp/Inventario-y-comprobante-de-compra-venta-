<?php
require_once '../../Controlador/InventarioAlmacenControlador.php';

$controlador = new InventarioAlmacenControlador();

try {
    // Recoger datos del formulario
    $data = [
        'id' => $_POST['id'] ?? null,
        'producto_id' => $_POST['producto_id'] ?? null,
        'almacen_id' => $_POST['almacen_id'] ?? null,
        'codigo_barras' => $_POST['codigo_barras'] ?? null,
        'lote' => $_POST['lote'] ?? null,
        'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? null,
        'fecha_ingreso' => $_POST['fecha_ingreso'] ?? null,
        'cantidad_actual' => $_POST['cantidad_actual'] ?? null,
        'cantidad_minima' => $_POST['cantidad_minima'] ?? null,
        'cantidad_maxima' => $_POST['cantidad_maxima'] ?? null,
        'observaciones' => $_POST['observaciones'] ?? null
    ];

    // Convertir fecha_ingreso de formato datetime-local a formato MySQL
    if (!empty($data['fecha_ingreso'])) {
        $data['fecha_ingreso'] = str_replace('T', ' ', $data['fecha_ingreso']);
    }

    // Guardar (insertar o actualizar)
    $controlador->guardar($data);

    // Redireccionar al listado con mensaje de éxito
    header('Location: listar.php?mensaje=guardado');
    exit;

} catch (Exception $e) {
    // En caso de error, redireccionar con mensaje de error
    header('Location: listar.php?error=' . urlencode($e->getMessage()));
    exit;
}