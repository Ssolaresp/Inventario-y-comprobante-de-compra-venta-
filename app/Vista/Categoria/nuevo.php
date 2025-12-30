<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



require_once '../../Controlador/CategoriasControlador.php';
include '../../../includes/sidebar.php'; 
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(2);


$controlador = new CategoriasControlador();
$estados = $controlador->obtenerEstados();
$categoriasPadre = $controlador->obtenerCategoriasPadre();

// 🔹 Obtener el siguiente código desde el modelo a través del controlador
$codigoNuevo = $controlador->obtenerSiguienteCodigo();
?>

<h2>Nueva Categoría</h2>

<form method="POST" action="guardar.php">
    <label>Código:</label><br>
    <input type="text" name="codigo" value="<?= htmlspecialchars($codigoNuevo) ?>" readonly><br><br>

    <label>Nombre:</label><br>
    <input type="text" name="nombre" required><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion"></textarea><br><br>

    <label>Categoría Padre:</label><br>
    <select name="categoria_padre_id">
        <option value="">(Ninguna)</option>
        <?php foreach ($categoriasPadre as $cp): ?>
            <option value="<?= $cp['id'] ?>"><?= htmlspecialchars($cp['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Estado:</label><br>
    <select name="estado_id" required>
        <option value="">Seleccione un estado</option>
        <?php foreach ($estados as $e): ?>
            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Guardar</button>
    <a href="listar.php">Cancelar</a>
</form>
