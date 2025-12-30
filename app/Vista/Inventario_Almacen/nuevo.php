<?php



if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



require_once '../../Controlador/InventarioAlmacenControlador.php';
include '../../../includes/sidebar.php';

require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(2);


$controlador = new InventarioAlmacenControlador();
$productos = $controlador->obtenerProductos();
$almacenes = $controlador->obtenerAlmacenes();
?>

<h2>➕ Nuevo Registro de Inventario</h2>
<form action="guardar.php" method="POST">
    <label>Producto:</label><br>
    <select name="producto_id" required>
        <option value="">-- Seleccione --</option>
        <?php foreach ($productos as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre'] ?? '') ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Almacén:</label><br>
    <select name="almacen_id" required>
        <option value="">-- Seleccione --</option>
        <?php foreach ($almacenes as $a): ?>
            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nombre'] ?? '') ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Código de Barras:</label><br>
    <input type="text" name="codigo_barras" maxlength="100"><br><br>

    <label>Lote:</label><br>
    <input type="text" name="lote" maxlength="50"><br><br>

    <label>Fecha de Vencimiento:</label><br>
    <input type="date" name="fecha_vencimiento"><br><br>

    <label>Fecha de Ingreso:</label><br>
    <input type="datetime-local" name="fecha_ingreso"><br><br>

    <label>Cantidad Actual:</label><br>
    <input type="number" step="0.01" name="cantidad_actual" required><br><br>

    <label>Cantidad Mínima:</label><br>
    <input type="number" step="0.01" name="cantidad_minima"><br><br>

    <label>Cantidad Máxima:</label><br>
    <input type="number" step="0.01" name="cantidad_maxima"><br><br>

    <label>Observaciones:</label><br>
    <textarea name="observaciones" rows="3"></textarea><br><br>

    <button type="submit">💾 Guardar</button>
</form>

<br>
<a href="listar.php">⬅️ Volver al listado</a>