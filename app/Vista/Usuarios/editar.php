<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



require_once '../../Controlador/UsuariosControlador.php';
 include '../../../includes/sidebar.php'; 
 require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

$controlador = new UsuariosControlador();

if (!isset($_GET['id'])) {
    echo "ID no especificado.";
    exit;
}

$usuario = $controlador->obtener($_GET['id']);
if (!$usuario) {
    echo "Usuario no encontrado.";
    exit;
}

$estados = $controlador->obtenerEstados();
?>

<h2>Editar Usuario</h2>

<form method="POST" action="guardar.php">
    <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id']) ?>">

    <label>Nombre:</label><br>
    <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required><br><br>

    <label>Correo:</label><br>
    <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required><br><br>

    <label>Contraseña (dejar vacío para no cambiar):</label><br>
    <input type="password" name="contrasena"><br><br>

    <label>Estado:</label><br>
    <select name="estado_id" required>
        <?php foreach ($estados as $e): ?>
            <option value="<?= $e['id'] ?>" <?= $e['id'] == $usuario['estado_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($e['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Guardar Cambios</button>
    <a href="listar.php">Cancelar</a>
</form>
