<?php
require_once '../../Controlador/ListaPrecioDetalleControlador.php';
require_once '../../../includes/sidebar.php'; 

$controlador = new ListaPrecioDetalleControlador();
$monedas = $controlador->obtenerMonedas();
$estados = $controlador->obtenerEstados();
?>

<h2>➕ Nueva Lista de Precios</h2>
<form action="guardar_cabecera.php" method="POST">
    <label>Nombre:</label><br>
    <input type="text" name="nombre" maxlength="100" required><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" rows="3"></textarea><br><br>

    <label>Moneda:</label><br>
    <select name="moneda_id" required>
        <option value="">-- Seleccione --</option>
        <?php foreach ($monedas as $m): ?>
            <option value="<?= $m['id'] ?>">
                <?= htmlspecialchars($m['simbolo'] ?? '') ?> 
                <?= htmlspecialchars($m['codigo'] ?? '') ?> - 
                <?= htmlspecialchars($m['nombre'] ?? '') ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Vigente Desde:</label><br>
    <input type="date" name="vigente_desde" required><br><br>

    <label>Vigente Hasta:</label><br>
    <input type="date" name="vigente_hasta"><br><br>

    <label>Estado:</label><br>
    <select name="estado_id" required>
        <option value="">-- Seleccione --</option>
        <?php foreach ($estados as $e): ?>
            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre'] ?? '') ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">💾 Guardar y Agregar Productos</button>
</form>

<br>
<a href="listar.php">⬅️ Volver al listado</a>