<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once '../../Controlador/UnidadesMedidaControlador.php';
include '../../../includes/sidebar.php'; 

require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(2);


$controlador = new UnidadesMedidaControlador();
$estados = $controlador->obtenerEstados();
?>

<h2>Nueva Unidad de Medida</h2>

<form method="POST" action="guardar.php">
    <label>Nombre:</label><br>
    <input type="text" name="nombre" required><br><br>

    <label>Abreviatura:</label><br>
    <input type="text" name="abreviatura" maxlength="10" required><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion"></textarea><br><br>

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
