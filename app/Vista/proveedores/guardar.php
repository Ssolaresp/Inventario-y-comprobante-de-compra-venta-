<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../Controlador/ProveedoresControlador.php';

try {
    $controlador = new ProveedoresControlador();

    $data = [
        'id' => $_POST['id'] ?? null,
        'nombre' => $_POST['nombre'] ?? '',
        'telefono' => $_POST['telefono'] ?? '',
        'email' => $_POST['email'] ?? '',
        'direccion' => $_POST['direccion'] ?? '',
        'nit' => $_POST['nit'] ?? '',
        'estado_id' => $_POST['estado_id'] ?? 1
    ];

    $controlador->guardar($data);

    header('Location: listar.php');
    exit;

} catch (Exception $e) {
    echo "<h3>🔥 Error al guardar proveedor</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<a href='javascript:history.back()'>← Regresar al formulario</a>";
}