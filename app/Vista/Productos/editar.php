<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/ProductosControlador.php';
include '../../../includes/sidebar.php'; 
require_once __DIR__ . '/../../../includes/permisos.php';
verificarAcceso(2);

$controlador = new ProductosControlador();
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<h3>⚠️ Error: Producto no encontrado</h3>";
    echo "<a href='listar.php'>← Volver al listado</a>";
    exit;
}

$producto = $controlador->obtener($id);
if (!$producto) {
    echo "<h3>⚠️ Error: El producto no existe</h3>";
    echo "<a href='listar.php'>← Volver al listado</a>";
    exit;
}

$estados = $controlador->obtenerEstados();
$categorias = $controlador->obtenerCategorias();
$unidades = $controlador->obtenerUnidadesMedida();
$proveedores = $controlador->obtenerProveedores();
?>

<h2>Editar Producto</h2>

<form method="POST" action="guardar.php" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $producto['id'] ?>">
    <input type="hidden" name="imagen_actual" value="<?= htmlspecialchars($producto['imagen_url']) ?>">

    <label>Código:</label><br>
    <input type="text" value="<?= htmlspecialchars($producto['codigo']) ?>" readonly><br>
    <small>El código no se puede modificar</small><br><br>

    <label>Nombre: *</label><br>
    <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" rows="4"><?= htmlspecialchars($producto['descripcion']) ?></textarea><br><br>

    <label>Categoría: *</label><br>
    <select name="categoria_id" required>
        <option value="">Seleccione una categoría</option>
        <?php foreach($categorias as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $producto['categoria_id']==$c['id']?'selected':'' ?>>
                <?= htmlspecialchars($c['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Unidad de Medida: *</label><br>
    <select name="unidad_medida_id" required>
        <option value="">Seleccione una unidad</option>
        <?php foreach($unidades as $u): ?>
            <option value="<?= $u['id'] ?>" <?= $producto['unidad_medida_id']==$u['id']?'selected':'' ?>>
                <?= htmlspecialchars($u['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Proveedor:</label><br>
    <select name="proveedor_id">
        <option value="">Seleccione un proveedor (opcional)</option>
        <?php foreach($proveedores as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $producto['proveedor_id']==$p['id']?'selected':'' ?>>
                <?= htmlspecialchars($p['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Peso (kg):</label><br>
    <input type="number" step="0.01" name="peso" value="<?= $producto['peso'] ?>" min="0"><br><br>

    <label>Imagen actual:</label><br>
    <?php if(!empty($producto['imagen_url'])): ?>
        <img src="../../../<?= htmlspecialchars($producto['imagen_url']) ?>" style="max-width: 200px; border: 1px solid #ddd; padding: 5px;"><br>
        <small>Si subes una nueva imagen, esta será reemplazada</small><br><br>
    <?php else: ?>
        <small>No hay imagen registrada</small><br><br>
    <?php endif; ?>

    <label>Nueva imagen (opcional):</label><br>
    <input type="file" name="imagen" accept="image/jpeg,image/png,image/jpg,image/gif" onchange="previsualizarImagen(this)"><br>
    <img id="preview" style="max-width: 200px; margin-top: 10px; display: none; border: 2px solid green; padding: 5px;"><br><br>

    <label>Estado: *</label><br>
    <select name="estado_id" required>
        <option value="">Seleccione un estado</option>
        <?php foreach($estados as $e): ?>
            <option value="<?= $e['id'] ?>" <?= $producto['estado_id']==$e['id']?'selected':'' ?>>
                <?= htmlspecialchars($e['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">💾 Actualizar Producto</button>
    <a href="listar.php">❌ Cancelar</a>
</form>

<script>
function previsualizarImagen(input) {
    const preview = document.getElementById('preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}
</script>