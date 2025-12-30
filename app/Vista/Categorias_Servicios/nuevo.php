<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/CategoriasServiciosControlador.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(2);

$controlador = new CategoriasServiciosControlador();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->guardar($_POST);
    header('Location: listar.php');
    exit;
}

$estados = $controlador->obtenerEstados();
$codigo = $controlador->obtenerSiguienteCodigo();

include '../../../includes/sidebar.php';
?>

<h2>Nueva Categoría de Servicio</h2>
<form method="post">
    <label>Código:</label><br>
    <input type="text" name="codigo" value="<?= htmlspecialchars($codigo) ?>" readonly><br><br>

    <label>Nombre:</label><br>
    <input type="text" name="nombre" required style="width: 400px;"><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" rows="3" style="width: 400px;"></textarea><br><br>

    <label>Estado:</label><br>
    <select name="estado_id" required>
        <?php foreach ($estados as $e): ?>
            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">💾 Guardar</button>
    <a href="listar.php">↩️ Cancelar</a>
</form>
