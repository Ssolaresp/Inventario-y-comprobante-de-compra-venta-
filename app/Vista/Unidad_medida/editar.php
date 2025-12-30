<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once '../../Controlador/UnidadesMedidaControlador.php';
include '../../../includes/sidebar.php'; 

require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(2);


$controlador = new UnidadesMedidaControlador();

if (!isset($_GET['id'])) {
    echo "ID no especificado.";
    exit;
}

$unidad = $controlador->obtener($_GET['id']);
if (!$unidad) {
    echo "Unidad no encontrada.";
    exit;
}

$estados = $controlador->obtenerEstados();
?>

<h2>Editar Unidad de Medida</h2>

<form method="POST" action="guardar.php">
    <input type="hidden" name="id" value="<?= htmlspecialchars($unidad['id']) ?>">

    <label>Nombre:</label><br>
    <input type="text" name="nombre" value="<?= htmlspecialchars($unidad['nombre']) ?>" required><br><br>

    <label>Abreviatura:</label><br>
    <input type="text" name="abreviatura" value="<?= htmlspecialchars($unidad['abreviatura']) ?>" maxlength="10" required><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion"><?= htmlspecialchars($unidad['descripcion']) ?></textarea><br><br>

    <label>Estado:</label><br>
    <select name="estado_id" required>
        <?php foreach ($estados as $e): ?>
            <option value="<?= $e['id'] ?>" <?= $e['id'] == $unidad['estado_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($e['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Guardar Cambios</button>
    <a href="listar.php">Cancelar</a>
</form>
