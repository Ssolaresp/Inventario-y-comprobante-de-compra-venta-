<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/CategoriasServiciosControlador.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(2);

$controlador = new CategoriasServiciosControlador();

$id = $_GET['id'] ?? null;
$categoria = $id ? $controlador->obtener($id) : null;

if (!$categoria) {
    echo "<p>Categoría no encontrada.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->guardar($_POST);
    header('Location: listar.php');
    exit;
}

$estados = $controlador->obtenerEstados();

include '../../../includes/sidebar.php';
?>

<h2>Editar Categoría de Servicio</h2>
<form method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($categoria['id']) ?>">

    <label>Código:</label><br>
    <input type="text" value="<?= htmlspecialchars($categoria['codigo']) ?>" readonly><br><br>

    <label>Nombre:</label><br>
    <input type="text" name="nombre" value="<?= htmlspecialchars($categoria['nombre']) ?>" required style="width: 400px;"><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" rows="3" style="width: 400px;"><?= htmlspecialchars($categoria['descripcion']) ?></textarea><br><br>

    <label>Estado:</label><br>
    <select name="estado_id" required>
        <?php foreach ($estados as $e): ?>
            <option value="<?= $e['id'] ?>" <?= ($e['id'] == $categoria['estado_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($e['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">💾 Actualizar</button>
    <a href="listar.php">↩️ Cancelar</a>
</form>