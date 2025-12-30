<?php
ob_start(); // Evita el error de headers already sent

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';
require_once __DIR__ . '/../../controlador/UsuarioAlmacenControlador.php';

// Verifica acceso al módulo
verificarAcceso(1);

$controlador = new UsuarioAlmacenControlador();
$usuarios = $controlador->obtenerUsuarios();
$almacenes = $controlador->obtenerAlmacenes();
$estados = $controlador->obtenerEstados();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->guardar($_POST);
    header('Location: listar.php');
    exit;
}
?>

<div class="container mt-4">
    <h2 class="mb-4">Nueva Asignación de Usuario a Almacén</h2>

    <form method="POST" class="card p-4 shadow-sm">
        <div class="form-group mb-3">
            <label class="form-label">Usuario</label>
            <select name="usuario_id" class="form-control" required>
                <option value="">Seleccione...</option>
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Almacén</label>
            <select name="almacen_id" class="form-control" required>
                <option value="">Seleccione...</option>
                <?php foreach ($almacenes as $a): ?>
                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group mb-4">
            <label class="form-label">Estado</label>
            <select name="estado_id" class="form-control" required>
                <option value="">Seleccione...</option>
                <?php foreach ($estados as $e): ?>
                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="listar.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php
ob_end_flush();
?>
