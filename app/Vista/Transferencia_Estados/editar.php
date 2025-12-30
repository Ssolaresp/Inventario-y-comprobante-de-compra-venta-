<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/TransferenciasEstadosControlador.php';
require_once '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

$controlador = new TransferenciasEstadosControlador();

$id = $_GET['id'] ?? null;
$estado = $id ? $controlador->obtener($id) : null;

if (!$estado) {
    echo "<p>Estado no encontrado.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->guardar($_POST);
    header('Location: listar.php');
    exit;
}
?>

<h2>Editar Estado de Transferencia</h2>
<form method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($estado['id']) ?>">

    <label>Nombre:</label><br>
    <input type="text" name="nombre" value="<?= htmlspecialchars($estado['nombre']) ?>" required><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" rows="3"><?= htmlspecialchars($estado['descripcion']) ?></textarea><br><br>

    <label>Orden:</label><br>
    <input type="number" name="orden" min="0" value="<?= htmlspecialchars($estado['orden']) ?>" required><br><br>

    <button type="submit">💾 Actualizar</button>
    <a href="listar.php">↩️ Cancelar</a>
</form>
