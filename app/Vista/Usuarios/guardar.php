<?php
require_once '../../Controlador/UsuariosControlador.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador = new UsuariosControlador();

    $data = [
        'id' => $_POST['id'] ?? null,
        'nombre' => $_POST['nombre'],
        'correo' => $_POST['correo'],
        'contrasena' => $_POST['contrasena'] ?? '',
        'estado_id' => $_POST['estado_id']
    ];

    $controlador->guardar($data);

    header('Location: listar.php');
    exit;
}
