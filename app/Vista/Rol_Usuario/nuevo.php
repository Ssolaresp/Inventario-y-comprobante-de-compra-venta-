<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}




require_once '../../Controlador/RolUsuarioControlador.php';
require_once '../../../includes/sidebar.php'; 

require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);


$controlador = new RolUsuarioControlador();

$usuarios = $controlador->obtenerUsuarios();
$roles = $controlador->obtenerRoles();
?>

<h2>Asignar Nuevo Rol a Usuario</h2>

<form method="POST" action="guardar.php">
    <label>Usuario:</label><br>
    <select name="usuario_id" required>
        <option value="">Seleccione un usuario</option>
        <?php foreach ($usuarios as $u): ?>
            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Rol:</label><br>
    <select name="rol_id" required>
        <option value="">Seleccione un rol</option>
        <?php foreach ($roles as $r): ?>
            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Asignar Rol</button>
    <a href="listar.php">Cancelar</a>
</form>
