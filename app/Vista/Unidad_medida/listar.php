<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once '../../Controlador/UnidadesMedidaControlador.php';
include '../../../includes/sidebar.php'; 
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(2);


$controlador = new UnidadesMedidaControlador();
$unidades = $controlador->listar();
?>

<h2>Listado de Unidades de Medida</h2>
<a href="nuevo.php">+ Nueva Unidad</a>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Abreviatura</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th>Creado</th>
            <th>Actualizado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($unidades as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['nombre']) ?></td>
                <td><?= htmlspecialchars($u['abreviatura']) ?></td>
                <td><?= htmlspecialchars($u['descripcion']) ?></td>
                <td><?= htmlspecialchars($u['estado']) ?></td>
                <td><?= $u['creado_en'] ?></td>
                <td><?= $u['actualizado_en'] ?></td>
                <td>
                    <a href="editar.php?id=<?= $u['id'] ?>">Editar</a>
                    <!-- Puedes agregar eliminar si quieres -->
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if(empty($unidades)): ?>
            <tr><td colspan="8" style="text-align:center;">No hay unidades registradas.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
