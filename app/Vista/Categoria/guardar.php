<?php
require_once '../../Controlador/CategoriasControlador.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador = new CategoriasControlador();

    $data = [
        'id' => $_POST['id'] ?? null,
        'codigo' => $_POST['codigo'],
        'nombre' => $_POST['nombre'],
        'descripcion' => $_POST['descripcion'] ?? '',
        'categoria_padre_id' => $_POST['categoria_padre_id'] ?? null,
        'estado_id' => $_POST['estado_id']
    ];

    $controlador->guardar($data);

    header('Location: listar.php');
    exit;
}
