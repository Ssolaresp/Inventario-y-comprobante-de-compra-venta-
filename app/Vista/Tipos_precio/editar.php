<?php
require_once '../../Controlador/TipoPrecioControlador.php';
require_once '../../../includes/sidebar.php'; 

$controlador = new TipoPrecioControlador();
$id = $_GET['id'] ?? null;
$registro = $controlador->obtener($id);
$estados = $controlador->obtenerEstados();

if (!$registro) {
    echo "<p>Registro no encontrado.</p>";
    exit;
}
?>

<h2>✏️ Editar Tipo de Precio</h2>
<form action="guardar.php" method="POST">
    <input type="hidden" name="id" value="<?= htmlspecialchars($registro['id'] ?? '') ?>">

    <label>Nombre:</label><br>
    <input type="text" name="nombre" maxlength="50" value="<?= htmlspecialchars($registro['nombre'] ?? '') ?>" required><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" rows="3"><?= htmlspecialchars($registro['descripcion'] ?? '') ?></textarea><br><br>

    <label>Estado:</label><br>
    <select name="estado_id" required>
        <?php foreach ($estados as $e): ?>
            <option value="<?= $e['id'] ?>" <?= ($registro['estado_id'] ?? '') == $e['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($e['nombre'] ?? '') ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">💾 Actualizar</button>
</form>

<br>
<a href="listar.php">⬅️ Volver al listado</a>