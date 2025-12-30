<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/TransferenciasEstadosControlador.php';
require_once '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

$controlador = new TransferenciasEstadosControlador();
$estados = $controlador->listar();
?>

<h2>Estados de Transferencias</h2>
<a href="nuevo.php">+ Nuevo Estado</a>

<table border="1" cellpadding="5" cellspacing="0" style="width:100%; text-align:center;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Orden</th>
            <th>Creado</th>
            <th>Actualizado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($estados as $e): ?>
            <tr>
                <td><?= $e['id'] ?></td>
                <td><?= htmlspecialchars($e['nombre']) ?></td>
                <td><?= htmlspecialchars($e['descripcion']) ?></td>
                <td><?= htmlspecialchars($e['orden']) ?></td>
                <td><?= $e['creado_en'] ?></td>
                <td><?= $e['actualizado_en'] ?></td>
                <td>
                    <a href="editar.php?id=<?= $e['id'] ?>">Editar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($estados)): ?>
            <tr><td colspan="7">No hay estados registrados.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
