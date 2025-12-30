<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}





require_once '../../controlador/RolesControlador.php';
require_once '../../../includes/sidebar.php'; 

require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

$controlador = new RolesControlador();
$roles = $controlador->listar();
?>

<h2>Listado de Roles</h2>
<a href="nuevo.php">+ Nuevo Rol</a>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Creado</th>
            <th>Actualizado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($roles as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['nombre']) ?></td>
                <td><?= htmlspecialchars($r['descripcion']) ?></td>
                <td><?= $r['creado_en'] ?></td>
                <td><?= $r['actualizado_en'] ?></td>
                <td>
                    <a href="editar.php?id=<?= $r['id'] ?>">Editar</a> 
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($roles)): ?>
            <tr><td colspan="6">No hay roles registrados.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
