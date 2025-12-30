<?php
require_once __DIR__ . '/../../Controlador/TransferenciasControlador.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit();
}

$controlador = new TransferenciasControlador();
$id = $_POST['id'] ?? null;

if (!$id) {
    header("Location: listar.php?error=" . urlencode("ID de transferencia faltante"));
    exit();
}

try {
    $data = [
        'almacen_origen_id' => $_POST['almacen_origen_id'],
        'almacen_destino_id' => $_POST['almacen_destino_id'],
        'observaciones' => $_POST['observaciones'] ?? ''
    ];

    $detalle = [];
    foreach ($_POST['productos'] as $prod) {
        $detalle[] = [
            'producto_id' => $prod['producto_id'],
            'cantidad' => $prod['cantidad'],
            'observaciones' => $prod['observaciones'] ?? ''
        ];
    }

    $controlador->actualizar($id, $data, $detalle, $_SESSION['usuario_id']);

    // ✅ Redirige a ver.php con mensaje de éxito
    header("Location: ver.php?id=$id&success=" . urlencode("Transferencia actualizada correctamente."));
    exit();

} catch (Exception $e) {
    // ❌ Redirige a ver.php con mensaje de error
    header("Location: ver.php?id=$id&error=" . urlencode($e->getMessage()));
    exit();
}