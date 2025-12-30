<?php
require_once __DIR__ . '/../../controlador/MonedasControlador.php';
require_once '../../../includes/sidebar.php'; 
$controlador = new MonedasControlador();
$id = $_GET['id'] ?? null;
$moneda = $controlador->obtener($id);
$estados = $controlador->obtenerEstados();
?>

<div class="container mt-4">
    <h2 class="mb-4">Editar Moneda</h2>

    <form action="guardar.php" method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($moneda['id']) ?>">

        <div class="mb-3">
            <label for="codigo" class="form-label">Código</label>
            <input type="text" name="codigo" id="codigo" class="form-control" 
                   value="<?= htmlspecialchars($moneda['codigo']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="form-control" 
                   value="<?= htmlspecialchars($moneda['nombre']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="simbolo" class="form-label">Símbolo</label>
            <input type="text" name="simbolo" id="simbolo" class="form-control" 
                   value="<?= htmlspecialchars($moneda['simbolo']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="estado_id" class="form-label">Estado</label>
            <select name="estado_id" id="estado_id" class="form-select" required>
                <?php foreach ($estados as $estado): ?>
                    <option value="<?= htmlspecialchars($estado['id']) ?>"
                        <?= $estado['id'] == $moneda['estado_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($estado['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="listar.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
