<?php
require_once '../../Controlador/CanalesVentaControlador.php';
include '../../../includes/inicio.php';

$controlador = new CanalesVentaControlador();
$estados = $controlador->obtenerEstados();

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];
$canal = $controlador->obtener($id);

if (!$canal) {
    header('Location: listar.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST['id_canal'] = $id;
    $controlador->guardar($_POST);
    header('Location: listar.php');
    exit();
}
?>

<div class="container mt-4">
    <h4>Editar Canal de Venta</h4>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Código</label>
            <input type="text" class="form-control" name="codigo_canal" value="<?= htmlspecialchars($canal['codigo_canal']) ?>" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Nombre del Canal</label>
            <input type="text" class="form-control" name="nombre_canal" value="<?= htmlspecialchars($canal['nombre_canal']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" rows="3"><?= htmlspecialchars($canal['descripcion']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Estado</label>
            <select class="form-select" name="estado_id" required>
                <?php foreach ($estados as $e): ?>
                    <option value="<?= $e['id'] ?>" <?= $canal['estado_id'] == $e['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="listar.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>


