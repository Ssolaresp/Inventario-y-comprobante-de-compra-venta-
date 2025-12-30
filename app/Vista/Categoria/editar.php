<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



require_once '../../Controlador/CategoriasControlador.php';
include '../../../includes/sidebar.php'; 

require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(2);


$controlador = new CategoriasControlador();

if (!isset($_GET['id'])) {
    echo "ID no especificado.";
    exit;
}

$categoria = $controlador->obtener($_GET['id']);
if (!$categoria) {
    echo "Categoría no encontrada.";
    exit;
}

$estados = $controlador->obtenerEstados();
$categoriasPadre = $controlador->obtenerCategoriasPadre();
?>

<h2>Editar Categoría</h2>

<form method="POST" action="guardar.php">
    <input type="hidden" name="id" value="<?= htmlspecialchars($categoria['id']) ?>">

    <label>Código:</label><br>
    <input type="text" name="codigo" value="<?= htmlspecialchars($categoria['codigo']) ?>" readonly><br><br>

    <label>Nombre:</label><br>
    <input type="text" name="nombre" value="<?= htmlspecialchars($categoria['nombre']) ?>" required><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion"><?= htmlspecialchars($categoria['descripcion']) ?></textarea><br><br>

    <label>Categoría Padre:</label><br>
    <select name="categoria_padre_id">
        <option value="">(Ninguna)</option>
        <?php foreach ($categoriasPadre as $cp): ?>
            <option value="<?= $cp['id'] ?>" <?= $cp['id'] == $categoria['categoria_padre_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cp['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Estado:</label><br>
    <select name="estado_id" required>
        <?php foreach ($estados as $e): ?>
            <option value="<?= $e['id'] ?>" <?= $e['id'] == $categoria['estado_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($e['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Guardar Cambios</button>
    <a href="listar.php">Cancelar</a>
</form>
