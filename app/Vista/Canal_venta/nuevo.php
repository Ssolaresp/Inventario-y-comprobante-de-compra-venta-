<?php
require_once '../../Controlador/CanalesVentaControlador.php';
include '../../../includes/inicio.php';

$controlador = new CanalesVentaControlador();
$estados = $controlador->obtenerEstados();
$codigo = $controlador->obtenerSiguienteCodigo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->guardar($_POST);
    header('Location: listar.php');
    exit();
}
?>

<div class="container mt-4">
    <h4>Nuevo Canal de Venta</h4>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Código</label>
            <input type="text" class="form-control" name="codigo_canal" value="<?= $codigo ?>" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Nombre del Canal</label>
            <input type="text" class="form-control" name="nombre_canal" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Estado</label>
            <select class="form-select" name="estado_id" required>
                <option value="">Seleccione...</option>
                <?php foreach ($estados as $e): ?>
                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="listar.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>


