<?php
require_once '../../controlador/RolesControlador.php';

$controlador = new RolesControlador();
$controlador->guardar($_POST);

header("Location: listar.php");
exit;
