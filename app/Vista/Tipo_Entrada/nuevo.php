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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->guardar($_POST);
    header('Location: listar.php');
    exit;
}
?>

<h2>Nuevo Tipo de Entrada</h2>

<form method="post">
    <div>
        <label>Nombre:</label><br>
        <input type="text" name="nombre" required>
    </div>
    <br>
    <div>
        <label>Descripción:</label><br>
        <textarea name="descripcion" rows="3"></textarea>
    </div>
    <br>
    <div>
        <label>¿Requiere Autorización?</label><br>
        <input type="checkbox" name="requiere_autorizacion" value="1">
    </div>
    <br>
    <div>
        <label>Estado:</label><br>
        <select name="estado_id" required>
            <?php foreach ($estados as $e): ?>
                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <br>
    <div>
        <button type="submit">💾 Guardar</button>
        <a href="listar.php">↩️ Cancelar</a>
    </div>
</form>
