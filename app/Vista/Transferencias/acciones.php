<?php
require_once '../../Controlador/TransferenciasControlador.php';
include '../../../includes/inicio.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: listar.php");
    exit();
}

$controlador = new TransferenciasControlador();

try {
    $accion = $_POST['accion'] ?? null;
    $id = $_POST['id'] ?? null;

    if (!$accion || !$id) {
        throw new Exception("Datos incompletos para realizar la acción");
    }

    switch ($accion) {
        case 'autorizar':
            $controlador->autorizar($id);
            header("Location: ver.php?id={$id}&mensaje=autorizada");
            break;

        case 'enviar':
            $controlador->enviar($id);
            header("Location: ver.php?id={$id}&mensaje=enviada");
            break;

        case 'recibir':
            $cantidades = $_POST['cantidades'] ?? [];
            $controlador->recibir($id, $cantidades);
            header("Location: ver.php?id={$id}&mensaje=recibida");
            break;

        case 'cancelar':
            $controlador->cancelar($id);
            header("Location: listar.php?mensaje=cancelada");
            break;

        default:
            throw new Exception("Acción no válida");
    }

} catch (Exception $e) {
    $id = $_POST['id'] ?? '';
    if ($id) {
        header("Location: ver.php?id={$id}&error=" . urlencode($e->getMessage()));
    } else {
        header("Location: listar.php?error=" . urlencode($e->getMessage()));
    }
    exit();
}