<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/TiposEntradaControlador.php';
require_once '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

$controlador = new TiposEntradaControlador();
$estados = $controlador->obtenerEstados();

$id = $_GET['id'] ?? null;
$tipo = $id ? $controlador->obtener($id) : null;

if (!$tipo) {
    echo "<p>Tipo de entrada no encontrado.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->guardar($_POST);
    header('Location: listar.php');
    exit;
}
?>

<h2>Editar Tipo de Entrada</h2>

<form method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($tipo['id']) ?>">

    <div>
        <label>Nombre:</label><br>
        <input type="text" name="nombre" value="<?= htmlspecialchars($tipo['nombre']) ?>" required>
    </div>
    <br>
    <div>
        <label>Descripción:</label><br>
        <textarea name="descripcion" rows="3"><?= htmlspecialchars($tipo['descripcion']) ?></textarea>
    </div>
    <br>
    <div>
        <label>¿Requiere Autorización?</label><br>
        <input type="checkbox" name="requiere_autorizacion" value="1" <?= $tipo['requiere_autorizacion'] ? 'checked' : '' ?>>
    </div>
    <br>
    <div>
        <label>Estado:</label><br>
        <select name="estado_id" required>
            <?php foreach ($estados as $e): ?>
                <option value="<?= $e['id'] ?>" <?= $e['id'] == $tipo['estado_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <br>
    <div>
        <button type="submit">💾 Actualizar</button>
        <a href="listar.php">↩️ Cancelar</a>
    </div>
</form>
