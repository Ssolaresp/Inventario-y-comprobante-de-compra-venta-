<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../Controlador/DepartamentosControlador.php';
include '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';
verificarAcceso(6);

$controlador = new DepartamentosControlador();
$codigo = $controlador->obtenerSiguienteCodigo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->guardar($_POST);
    header('Location: listar.php');
    exit;
}
?>

<h2>Nuevo Departamento</h2>
<form method="post">
    <label>Código:</label><br>
    <input type="text" value="<?= htmlspecialchars($codigo) ?>" readonly><br><br>

    <label>Nombre del Departamento:</label><br>
    <input type="text" name="nombre_departamento" required maxlength="100"><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" rows="3" maxlength="255"></textarea><br><br>

    <label>Estado:</label><br>
    <select name="estado_id" required>
        <option value="1" selected>Activo</option>
        <option value="2">Inactivo</option>
    </select><br><br>

    <button type="submit">💾 Guardar</button>
    <a href="listar.php">↩️ Cancelar</a>
</form>