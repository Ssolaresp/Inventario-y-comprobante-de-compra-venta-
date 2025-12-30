<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/ProveedoresControlador.php';
include '../../../includes/sidebar.php'; 
require_once __DIR__ . '/../../../includes/permisos.php';
verificarAcceso(2);

$controlador = new ProveedoresControlador();
$estados = $controlador->obtenerEstados();
$codigoNuevo = $controlador->obtenerSiguienteCodigo();
?>

<h2>Nuevo Proveedor</h2>

<form method="POST" action="guardar.php">
    <label>Código:</label><br>
    <input type="text" name="codigo" value="<?= htmlspecialchars($codigoNuevo) ?>" readonly><br><br>

    <label>Nombre: *</label><br>
    <input type="text" name="nombre" required><br><br>

    <label>Teléfono:</label><br>
    <input type="text" name="telefono"><br><br>

    <label>Email:</label><br>
    <input type="email" name="email"><br><br>

    <label>NIT:</label><br>
    <input type="text" name="nit"><br><br>

    <label>Dirección:</label><br>
    <textarea name="direccion" rows="3"></textarea><br><br>

    <label>Estado: *</label><br>
    <select name="estado_id" required>
        <option value="">Seleccione un estado</option>
        <?php foreach($estados as $e): ?>
            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">💾 Guardar Proveedor</button>
    <a href="listar.php">❌ Cancelar</a>
</form>