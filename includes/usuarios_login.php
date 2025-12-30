<?php
session_start();

require_once __DIR__ . '/../app/Controlador/UsuariosLoginControlador.php';
/*require_once __DIR__ . '/../modelo/BitacoraSesion.php';*/
require_once __DIR__ . '/../app/Modelo/BitacoraSesion.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    $controlador = new UsuariosLoginControlador();
    $usuario = $controlador->verificarCredenciales($correo, $contrasena);

    if ($usuario) {
        // Guardar en sesión
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_correo'] = $usuario['correo'];

        // Obtener IP y navegador
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido';

        // Registrar sesión en bitácora con manejo de errores
        try {
            $bitacora = new BitacoraSesion();
            $bitacora->registrarIngreso($usuario['id'], $ip, $navegador);
        } catch (Exception $e) {
            // Puedes registrar en log o mostrar mensaje para debugging
            error_log($e->getMessage());
            // No bloqueamos el login, solo seguimos
        }

        // Redirigir
        header('Location: ../index.php');
        exit;
    } else {
        $_SESSION['error'] = 'Correo o contraseña incorrectos';
        header('Location: login.php');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
