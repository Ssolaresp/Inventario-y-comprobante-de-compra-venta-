<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



require_once '../../Controlador/UsuariosControlador.php';
 include '../../../includes/sidebar.php'; 

 require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

$controlador = new UsuariosControlador();
$estados = $controlador->obtenerEstados();
?>

<h2>Nuevo Usuario</h2>

<form method="POST" action="guardar.php">
    <label>Nombre:</label><br>
    <input type="text" name="nombre" required><br><br>

    <label>Correo:</label><br>
    <input type="email" name="correo" required><br><br>

    <label>Contraseña:</label><br>
    <input type="password" name="contrasena" required><br><br>

    <label>Estado:</label><br>
    <select name="estado_id" required>
        <option value="">Seleccione un estado</option>
        <?php foreach ($estados as $e): ?>
            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Guardar</button>
    <a href="listar.php">Cancelar</a>
</form>
