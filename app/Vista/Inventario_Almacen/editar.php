<?php



if (session_status() === PHP_SESSION_NONE) {
    session_start();
}




require_once '../../Controlador/InventarioAlmacenControlador.php';
include '../../../includes/sidebar.php';

require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);


$controlador = new InventarioAlmacenControlador();
$id = $_GET['id'] ?? null;
$registro = $controlador->obtener($id);
$productos = $controlador->obtenerProductos();
$almacenes = $controlador->obtenerAlmacenes();

if (!$registro) {
    echo "<p>Registro no encontrado.</p>";
    exit;
}
?>

<h2>✏️ Editar Registro de Inventario</h2>
<form action="guardar.php" method="POST">
    <input type="hidden" name="id" value="<?= htmlspecialchars($registro['id'] ?? '') ?>">

    <label>Producto:</label><br>
    <select name="producto_id" required>
        <?php foreach ($productos as $p): ?>
            <option value="<?= $p['id'] ?>" <?= ($registro['producto_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['nombre'] ?? '') ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Almacén:</label><br>
    <select name="almacen_id" required>
        <?php foreach ($almacenes as $a): ?>
            <option value="<?= $a['id'] ?>" <?= ($registro['almacen_id'] ?? '') == $a['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($a['nombre'] ?? '') ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Código de Barras:</label><br>
    <input type="text" name="codigo_barras" value="<?= htmlspecialchars($registro['codigo_barras'] ?? '') ?>"><br><br>

    <label>Lote:</label><br>
    <input type="text" name="lote" value="<?= htmlspecialchars($registro['lote'] ?? '') ?>"><br><br>

    <label>Fecha de Vencimiento:</label><br>
    <input type="date" name="fecha_vencimiento" value="<?= htmlspecialchars($registro['fecha_vencimiento'] ?? '') ?>"><br><br>

    <label>Fecha de Ingreso:</label><br>
    <input type="datetime-local" name="fecha_ingreso" value="<?= !empty($registro['fecha_ingreso']) ? str_replace(' ', 'T', $registro['fecha_ingreso']) : '' ?>"><br><br>

    <label>Cantidad Actual:</label><br>
    <input type="number" step="0.01" name="cantidad_actual" value="<?= htmlspecialchars($registro['cantidad_actual'] ?? '') ?>"><br><br>

    <label>Cantidad Mínima:</label><br>
    <input type="number" step="0.01" name="cantidad_minima" value="<?= htmlspecialchars($registro['cantidad_minima'] ?? '') ?>"><br><br>

    <label>Cantidad Máxima:</label><br>
    <input type="number" step="0.01" name="cantidad_maxima" value="<?= htmlspecialchars($registro['cantidad_maxima'] ?? '') ?>"><br><br>

    <label>Observaciones:</label><br>
    <textarea name="observaciones" rows="3"><?= htmlspecialchars($registro['observaciones'] ?? '') ?></textarea><br><br>

    <button type="submit">💾 Actualizar</button>
</form>

<br>
<a href="listar.php">⬅️ Volver al listado</a>