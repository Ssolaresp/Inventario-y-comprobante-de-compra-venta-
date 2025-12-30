<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/ProductosControlador.php';
include '../../../includes/sidebar.php'; 
require_once __DIR__ . '/../../../includes/permisos.php';
verificarAcceso(2);

$controlador = new ProductosControlador();
$estados = $controlador->obtenerEstados();
$categorias = $controlador->obtenerCategorias();
$unidades = $controlador->obtenerUnidadesMedida();
$proveedores = $controlador->obtenerProveedores();
$codigoNuevo = $controlador->obtenerSiguienteCodigo();
?>

<h2>Nuevo Producto</h2>

<form method="POST" action="guardar.php" enctype="multipart/form-data">
    <label>Código:</label><br>
    <input type="text" name="codigo" value="<?= htmlspecialchars($codigoNuevo) ?>" readonly><br><br>

    <label>Nombre: *</label><br>
    <input type="text" name="nombre" required><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" rows="4"></textarea><br><br>

    <label>Categoría: *</label><br>
    <select name="categoria_id" required>
        <option value="">Seleccione una categoría</option>
        <?php foreach($categorias as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Unidad de Medida: *</label><br>
    <select name="unidad_medida_id" required>
        <option value="">Seleccione una unidad</option>
        <?php foreach($unidades as $u): ?>
            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Proveedor:</label><br>
    <select name="proveedor_id">
        <option value="">Seleccione un proveedor (opcional)</option>
        <?php foreach($proveedores as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Peso (kg):</label><br>
    <input type="number" step="0.01" name="peso" min="0"><br><br>

    <label>Imagen:</label><br>
    <input type="file" name="imagen" accept="image/*" onchange="previsualizarImagen(this)"><br>
    <img id="preview" style="max-width: 200px; margin-top: 10px; display: none; border: 2px solid green; padding: 5px;"><br><br>

    <label>Estado: *</label><br>
    <select name="estado_id" required>
        <option value="">Seleccione un estado</option>
        <?php foreach($estados as $e): ?>
            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">💾 Guardar Producto</button>
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