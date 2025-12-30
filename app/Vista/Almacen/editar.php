<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once '../../Controlador/AlmacenesControlador.php';
include '../../../includes/sidebar.php';

require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(2);


$controlador = new AlmacenesControlador();
$estados = $controlador->obtenerEstados();
$usuarios = $controlador->obtenerUsuariosResponsables();

$id = $_GET['id'] ?? null;
$almacen = $id ? $controlador->obtener($id) : null;

if (!$almacen) {
    echo "<p>Almacén no encontrado.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->guardar($_POST);
    header('Location: listar.php');
    exit;
}
?>

<h2>Editar Almacén</h2>
<form method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($almacen['id']) ?>">

    <label>Código:</label><br>
    <input type="text" name="codigo" value="<?= htmlspecialchars($almacen['codigo']) ?>" readonly><br><br>

    <label>Nombre:</label><br>
    <input type="text" name="nombre" value="<?= htmlspecialchars($almacen['nombre']) ?>" required><br><br>

    <label>Ubicación:</label><br>
    <textarea name="ubicacion" rows="2"><?= htmlspecialchars($almacen['ubicacion']) ?></textarea><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" rows="2"><?= htmlspecialchars($almacen['descripcion']) ?></textarea><br><br>

    <label>Responsable:</label><br>
    <select name="responsable_usuario_id">
        <option value="">-- Seleccione --</option>
        <?php foreach ($usuarios as $u): ?>
            <option value="<?= $u['id'] ?>" <?= ($u['id'] == $almacen['responsable_usuario_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($u['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Estado:</label><br>
    <select name="estado_id" required>
        <?php foreach ($estados as $e): ?>
            <option value="<?= $e['id'] ?>" <?= ($e['id'] == $almacen['estado_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($e['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">💾 Actualizar</button>
    <a href="listar.php">↩️ Cancelar</a>
</form>
