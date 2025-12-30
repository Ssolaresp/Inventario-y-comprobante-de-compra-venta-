<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/ServiciosControlador.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(2);

$controlador = new ServiciosControlador();

$id = $_GET['id'] ?? null;
$servicio = $id ? $controlador->obtener($id) : null;

if (!$servicio) {
    echo "<p>Servicio no encontrado.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->guardar($_POST);
    header('Location: listar.php');
    exit;
}

$estados = $controlador->obtenerEstados();
$categorias = $controlador->obtenerCategorias();

include '../../../includes/sidebar.php';
?>

<h2>Editar Servicio</h2>
<form method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($servicio['id']) ?>">

    <label>Código:</label><br>
    <input type="text" value="<?= htmlspecialchars($servicio['codigo']) ?>" readonly><br><br>

    <label>Nombre:</label><br>
    <input type="text" name="nombre" value="<?= htmlspecialchars($servicio['nombre']) ?>" required style="width: 400px;"><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" rows="3" style="width: 400px;"><?= htmlspecialchars($servicio['descripcion']) ?></textarea><br><br>

    <label>Precio Base:</label><br>
    <input type="number" name="precio_base" step="0.01" min="0" value="<?= htmlspecialchars($servicio['precio_base']) ?>" required><br><br>

    <label>
        <input type="checkbox" name="aplica_iva" value="1" <?= $servicio['aplica_iva'] ? 'checked' : '' ?>> Aplica IVA
    </label><br><br>

    <label>Porcentaje IVA:</label><br>
    <input type="number" name="porcentaje_iva" step="0.01" value="<?= htmlspecialchars($servicio['porcentaje_iva']) ?>" min="0" max="100"><br><br>

    <label>Categoría:</label><br>
    <select name="categoria_servicio_id">
        <option value="">-- Seleccione --</option>
        <?php foreach ($categorias as $c): ?>
            <option value="<?= $c['id'] ?>" <?= ($c['id'] == $servicio['categoria_servicio_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Estado:</label><br>
    <select name="estado_id" required>
        <?php foreach ($estados as $e): ?>
            <option value="<?= $e['id'] ?>" <?= ($e['id'] == $servicio['estado_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($e['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">💾 Actualizar</button>
    <a href="listar.php">↩️ Cancelar</a>
</form>
