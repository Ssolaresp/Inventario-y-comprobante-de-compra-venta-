<?php
require_once '../../Controlador/TipoPrecioControlador.php';
include '../../../includes/inicio.php';

$controlador = new TipoPrecioControlador();
$estados = $controlador->obtenerEstados();
?>

<h2>➕ Nuevo Tipo de Precio</h2>
<form action="guardar.php" method="POST">
    <label>Nombre:</label><br>
    <input type="text" name="nombre" maxlength="50" required><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" rows="3"></textarea><br><br>

    <label>Estado:</label><br>
    <select name="estado_id" required>
        <option value="">-- Seleccione --</option>
        <?php foreach ($estados as $e): ?>
            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre'] ?? '') ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">💾 Guardar</button>
</form>

<br>
<a href="listar.php">⬅️ Volver al listado</a>