<?php
require_once '../../Controlador/SalidasAlmacenControlador.php';

$controlador = new SalidasAlmacenControlador();

try {
    $accion = $_GET['accion'] ?? '';
    $id = $_GET['id'] ?? null;

    if (!$id) {
        throw new Exception("ID no especificado");
    }

    switch ($accion) {
        case 'autorizar':
            $controlador->autorizar($id);
            header('Location: listar.php?mensaje=autorizada');
            break;

        case 'cancelar':
            $controlador->cancelar($id);
            header('Location: listar.php?mensaje=cancelada');
            break;

        default:
            throw new Exception("Acción no válida");
    }

    exit;

} catch (Exception $e) {
    header('Location: listar.php?error=' . urlencode($e->getMessage()));
    exit;
}