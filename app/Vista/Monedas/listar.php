<?php
require_once __DIR__ . '/../../controlador/MonedasControlador.php';
require_once '../../../includes/sidebar.php'; 

$controlador = new MonedasControlador();
$monedas = $controlador->listar();
?>

<div class="container mt-4">
    <h2 class="mb-4">Listado de Monedas</h2>
    <a href="nuevo.php" class="btn btn-primary mb-3">Nueva Moneda</a>

    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Símbolo</th>
                <th>Estado</th>
                <th>Creado en</th>
                <th>Actualizado en</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($monedas as $fila): ?>
                <tr>
                    <td><?= htmlspecialchars($fila['id'] ?? '') ?></td>
                    <td><?= htmlspecialchars($fila['codigo'] ?? '') ?></td>
                    <td><?= htmlspecialchars($fila['nombre'] ?? '') ?></td>
                    <td><?= htmlspecialchars($fila['simbolo'] ?? '') ?></td>
                    <td><?= htmlspecialchars($fila['estado_id'] ?? '') ?></td>
                    <td><?= htmlspecialchars($fila['creado_en'] ?? '') ?></td>
                    <td><?= htmlspecialchars($fila['actualizado_en'] ?? '') ?></td>
                    <td>
                        <a href="editar.php?id=<?= $fila['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                 </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
