<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../Controlador/MunicipiosControlador.php';
include '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';
verificarAcceso(6);

$controlador = new MunicipiosControlador();
$id = $_GET['id'] ?? 0;
$municipio = $controlador->obtener($id);

if (!$municipio) {
    header('Location: listar.php');
    exit;
}

$departamentos = $controlador->obtenerDepartamentos();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST['id_municipio'] = $id;
    $controlador->guardar($_POST);
    header('Location: listar.php');
    exit;
}
?>

<h2>Editar Municipio</h2>
<form method="post">
    <label>Código:</label><br>
    <input type="text" value="<?= htmlspecialchars($municipio['codigo_municipio']) ?>" readonly><br><br>

    <label>Nombre del Municipio:</label><br>
    <input type="text" name="nombre_municipio" value="<?= htmlspecialchars($municipio['nombre_municipio']) ?>" required maxlength="100"><br><br>

    <label>Departamento:</label><br>
    <select name="departamento_id" required>
        <option value="">-- Seleccione --</option>
        <?php foreach ($departamentos as $d): ?>
            <option value="<?= $d['id_departamento'] ?>" 
                <?= $municipio['departamento_id'] == $d['id_departamento'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($d['nombre_departamento']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" rows="3" maxlength="255"><?= htmlspecialchars($municipio['descripcion'] ?? '') ?></textarea><br><br>

    <label>Estado:</label><br>
    <select name="estado_id" required>
        <option value="1" <?= $municipio['estado_id'] == 1 ? 'selected' : '' ?>>Activo</option>
        <option value="2" <?= $municipio['estado_id'] == 2 ? 'selected' : '' ?>>Inactivo</option>
    </select><br><br>

    <button type="submit">💾 Actualizar</button>
    <a href="listar.php">↩️ Cancelar</a>
</form>