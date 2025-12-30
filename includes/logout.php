<?php
session_start();

require_once __DIR__ . '/../app/Modelo/BitacoraSesion.php';

if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];

    $bitacora = new BitacoraSesion();
    try {
        $bitacora->registrarSalida($usuario_id);
    } catch (Exception $e) {
        error_log($e->getMessage());
        // Aquí puedes manejar el error o continuar sin bloquear el logout
    }
}

// Destruir sesión
session_unset();
session_destroy();

// Redirigir a login
header('Location: login.php');
exit;
