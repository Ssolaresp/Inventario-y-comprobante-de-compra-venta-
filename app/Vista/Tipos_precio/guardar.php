<?php
require_once '../../Controlador/TipoPrecioControlador.php';

$controlador = new TipoPrecioControlador();

try {
    // Recoger datos del formulario
    $data = [
        'id' => $_POST['id'] ?? null,
        'nombre' => $_POST['nombre'] ?? null,
        'descripcion' => $_POST['descripcion'] ?? null,
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