<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../Controlador/DepartamentosControlador.php';
include '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';
verificarAcceso(6);

$controlador = new DepartamentosControlador();
$id = $_GET['id'] ?? 0;
$departamento = $controlador->obtener($id);

if (!$departamento) {
    header('Location: listar.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST['id_departamento'] = $id;
    $controlador->guardar($_POST);
    header('Location: listar.php');
    exit;
}
?>

<h2>Editar Departamento</h2>
<form method="post">
    <label>Código:</label><br>
    <input type="text" value="<?= htmlspecialchars($departamento['codigo_departamento']) ?>" readonly><br><br>

    <label>Nombre del Departamento:</label><br>
    <input type="text" name="nombre_departamento" value="<?= htmlspecialchars($departamento['nombre_departamento']) ?>" required maxlength="100"><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" rows="3" maxlength="255"><?= htmlspecialchars($departamento['descripcion'] ?? '') ?></textarea><br><br>

    <label>Estado:</label><br>
    <select name="estado_id" required>
        <option value="1" <?= $departamento['estado_id'] == 1 ? 'selected' : '' ?>>Activo</option>
        <option value="2" <?= $departamento['estado_id'] == 2 ? 'selected' : '' ?>>Inactivo</option>
    </select><br><br>

    <button type="submit">💾 Actualizar</button>
    <a href="listar.php">↩️ Cancelar</a>
</form>