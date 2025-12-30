<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/ServiciosControlador.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(2);

$controlador = new ServiciosControlador();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->guardar($_POST);
    header('Location: listar.php');
    exit;
}

$estados = $controlador->obtenerEstados();
$categorias = $controlador->obtenerCategorias();
$codigo = $controlador->obtenerSiguienteCodigo();

include '../../../includes/sidebar.php';
?>

<h2>Nuevo Servicio</h2>
<form method="post">
    <label>Código:</label><br>
    <input type="text" name="codigo" value="<?= htmlspecialchars($codigo) ?>" readonly><br><br>

    <label>Nombre:</label><br>
    <input type="text" name="nombre" required style="width: 400px;"><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" rows="3" style="width: 400px;"></textarea><br><br>

    <label>Precio Base:</label><br>
    <input type="number" name="precio_base" step="0.01" min="0" required><br><br>

    <label>
        <input type="checkbox" name="aplica_iva" value="1" checked> Aplica IVA
    </label><br><br>

    <label>Porcentaje IVA:</label><br>
    <input type="number" name="porcentaje_iva" step="0.01" value="12.00" min="0" max="100"><br><br>

    <label>Categoría:</label><br>
    <select name="categoria_servicio_id">
        <option value="">-- Seleccione --</option>
        <?php foreach ($categorias as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Estado:</label><br>
    <select name="estado_id" required>
        <?php foreach ($estados as $e): ?>
            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">💾 Guardar</button>
    <a href="listar.php">↩️ Cancelar</a>
</form>
