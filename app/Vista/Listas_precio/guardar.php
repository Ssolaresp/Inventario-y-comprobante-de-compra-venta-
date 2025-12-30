<?php
require_once '../../Controlador/ListaPrecioControlador.php';

$controlador = new ListaPrecioControlador();

try {
    // Recoger datos del formulario
    $data = [
        'id' => $_POST['id'] ?? null,
        'nombre' => $_POST['nombre'] ?? null,
        'descripcion' => $_POST['descripcion'] ?? null,
        'moneda_id' => $_POST['moneda_id'] ?? null,
        'vigente_desde' => $_POST['vigente_desde'] ?? null,
        'vigente_hasta' => $_POST['vigente_hasta'] ?? null,
        'estado_id' => $_POST['estado_id'] ?? null
    ];

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