<?php
require_once __DIR__ . '/../../controlador/MonedasControlador.php';
$controlador = new MonedasControlador();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id' => $_POST['id'] ?? null,
        'codigo' => $_POST['codigo'] ?? '',
        'nombre' => $_POST['nombre'] ?? '',
        'simbolo' => $_POST['simbolo'] ?? '',
        'estado_id' => $_POST['estado_id'] ?? 1
    ];

    $controlador->guardar($data);

    // Redirigir al listado después de guardar
    header('Location: listar.php');
    exit;
} else {
    // Si accede directamente a guardar.php sin POST
    header('Location: listar.php');
    exit;
}
