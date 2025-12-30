<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../Controlador/ProductosControlador.php';

try {
    $controlador = new ProductosControlador();

    $data = [
        'id' => $_POST['id'] ?? null,
        'nombre' => $_POST['nombre'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? '',
        'categoria_id' => $_POST['categoria_id'] ?? null,
        'unidad_medida_id' => $_POST['unidad_medida_id'] ?? null,
        'proveedor_id' => $_POST['proveedor_id'] ?: null,
        'peso' => $_POST['peso'] ?: null,
        'estado_id' => $_POST['estado_id'] ?? 1,
        'imagen_actual' => $_POST['imagen_actual'] ?? null
    ];

    $controlador->guardar($data);

    header('Location: listar.php');
    exit;

} catch (Exception $e) {
    echo "<h3>🔥 Error al guardar producto</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<a href='javascript:history.back()'>← Regresar al formulario</a>";
}