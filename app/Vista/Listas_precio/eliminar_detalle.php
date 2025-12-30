<?php
require_once '../../Controlador/ListaPrecioDetalleControlador.php';

$controlador = new ListaPrecioDetalleControlador();

try {
    $id = $_GET['id'] ?? null;
    $lista_id = $_GET['lista_id'] ?? null;

    if ($id) {
        $controlador->eliminarDetalle($id);
    }

    header('Location: detalle.php?id=' . $lista_id . '&mensaje=guardado');
    exit;

} catch (Exception $e) {
    header('Location: detalle.php?id=' . $lista_id . '&error=' . urlencode($e->getMessage()));
    exit;
}