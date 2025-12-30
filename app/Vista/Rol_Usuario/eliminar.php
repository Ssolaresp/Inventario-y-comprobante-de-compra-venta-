<?php
require_once '../../Controlador/RolUsuarioControlador.php';

if (isset($_GET['id'])) {
    $controlador = new RolUsuarioControlador();
    $controlador->eliminar($_GET['id']);
}

header('Location: listar.php');
exit;
