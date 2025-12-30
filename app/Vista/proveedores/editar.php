<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/ProveedoresControlador.php';
include '../../../includes/sidebar.php'; 
require_once __DIR__ . '/../../../includes/permisos.php';
verificarAcceso(2);

$controlador = new ProveedoresControlador();
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<h3>⚠️ Error: Proveedor no encontrado</h3>";
    echo "<a href='listar.php'>← Volver al listado</a>";
    exit;
}

$proveedor = $controlador->obtener($id);
if (!$proveedor) {
    echo "<h3>⚠️ Error: El proveedor no existe</h3>";
    echo "<a href='listar.php'>← Volver al listado</a>";
    exit;
}

$estados = $controlador->obtenerEstados();
?>

<h2>Editar Proveedor</h2>

<form method="POST" action="guardar.php">
    <input type="hidden" name="id" value="<?= $proveedor['id'] ?>">

    <label>Código:</label><br>
    <input type="text" value="<?= htmlspecialchars($proveedor['codigo']) ?>" readonly><br>
    <small>El código no se puede modificar</small><br><br>

    <label>Nombre: *</label><br>
    <input type="text" name="nombre" value="<?= htmlspecialchars($proveedor['nombre']) ?>" required><br><br>

    <label>Teléfono:</label><br>
    <input type="text" name="telefono" value="<?= htmlspecialchars($proveedor['telefono']) ?>"><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($proveedor['email']) ?>"><br><br>

    <label>NIT:</label><br>
    <input type="text" name="nit" value="<?= htmlspecialchars($proveedor['nit']) ?>"><br><br>

    <label>Dirección:</label><br>
    <textarea name="direccion" rows="3"><?= htmlspecialchars($proveedor['direccion']) ?></textarea><br><br>

    <label>Estado: *</label><br>
    <select name="estado_id" required>
        <option value="">Seleccione un estado</option>
        <?php foreach($estados as $e): ?>
            <option value="<?= $e['id'] ?>" <?= $proveedor['estado_id']==$e['id']?'selected':'' ?>>
                <?= htmlspecialchars($e['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">💾 Actualizar Proveedor</button>
    <a href="listar.php">❌ Cancelar</a>
</form>