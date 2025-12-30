<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../Controlador/DepartamentosControlador.php';
require_once __DIR__ . '/../../../includes/permisos.php';
verificarAcceso(6);

$controlador = new DepartamentosControlador();
$id = $_GET['id'] ?? 0;

if ($id > 0) {
    $resultado = $controlador->eliminar($id);
    
    if ($resultado === false) {
        echo "<script>
            alert('No se puede eliminar este departamento porque tiene municipios asociados.');
            window.location.href = 'listar.php';
        </script>";
    } else {
        header('Location: listar.php');
    }
} else {
    header('Location: listar.php');
}
exit;