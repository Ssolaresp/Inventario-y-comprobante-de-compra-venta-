<?php
require_once '../../Controlador/ListaPrecioDetalleControlador.php';
require_once '../../../includes/sidebar.php'; 

$controlador = new ListaPrecioDetalleControlador();
$id = $_GET['id'] ?? null;
$lista_id = $_GET['lista_id'] ?? null;
$detalle = $controlador->obtenerDetalle($id);
$productos = $controlador->obtenerProductos();
$tiposPrecios = $controlador->obtenerTiposPrecios();

if (!$detalle) {
    echo "<p>Detalle no encontrado.</p>";
    exit;
}
?>

<h2>✏️ Editar Producto en Lista</h2>
<form action="guardar_detalle.php" method="POST">
    <input type="hidden" name="id" value="<?= htmlspecialchars($detalle['id'] ?? '') ?>">
    <input type="hidden" name="lista_precio_id" value="<?= htmlspecialchars($detalle['lista_precio_id'] ?? '') ?>">

    <label>Producto:</label><br>
    <select name="producto_id" required>
        <?php foreach ($productos as $p): ?>
            <option value="<?= $p['id'] ?>" <?= ($detalle['producto_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['codigo'] ?? '') ?> - <?= htmlspecialchars($p['nombre'] ?? '') ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Tipo de Precio:</label><br>
    <select name="tipo_precio_id" required>
        <?php foreach ($tiposPrecios as $tp): ?>
            <option value="<?= $tp['id'] ?>" <?= ($detalle['tipo_precio_id'] ?? '') == $tp['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($tp['nombre'] ?? '') ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Precio:</label><br>
    <input type="number" step="0.01" name="precio" value="<?= htmlspecialchars($detalle['precio'] ?? '') ?>" required><br><br>

    <button type="submit">💾 Actualizar</button>
</form>

<br>
<a href="detalle.php?id=<?= $lista_id ?>">⬅️ Volver al detalle</a>