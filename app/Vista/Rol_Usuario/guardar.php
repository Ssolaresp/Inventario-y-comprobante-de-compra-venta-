<?php
require_once '../../Controlador/RolUsuarioControlador.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador = new RolUsuarioControlador();

    $data = [
        'id' => $_POST['id'] ?? null,
        'usuario_id' => $_POST['usuario_id'],
        'rol_id' => $_POST['rol_id'],
    ];

    $controlador->guardar($data);

    header('Location: listar.php');
    exit;
}
