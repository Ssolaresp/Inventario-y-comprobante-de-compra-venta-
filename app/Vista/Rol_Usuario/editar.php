<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



require_once '../../Controlador/RolUsuarioControlador.php';
require_once '../../../includes/sidebar.php'; 

require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

$controlador = new RolUsuarioControlador();

if (!isset($_GET['id'])) {
    echo "ID no especificado.";
    exit;
}

$asignacion = $controlador->obtener($_GET['id']);
if (!$asignacion) {
    echo "Asignación no encontrada.";
    exit;
}

$usuarios = $controlador->obtenerUsuarios();
$roles = $controlador->obtenerRoles();
?>

<h2>Editar Asignación de Rol</h2>

<form method="POST" action="guardar.php">
    <input type="hidden" name="id" value="<?= htmlspecialchars($asignacion['id']) ?>">

    <label>Usuario:</label><br>
    <select name="usuario_id" required>
        <?php foreach ($usuarios as $u): ?>
            <option value="<?= $u['id'] ?>" <?= $u['id'] == $asignacion['usuario_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($u['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Rol:</label><br>
    <select name="rol_id" required>
        <?php foreach ($roles as $r): ?>
            <option value="<?= $r['id'] ?>" <?= $r['id'] == $asignacion['rol_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($r['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Guardar Cambios</button>
    <a href="listar.php">Cancelar</a>
</form>
