<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}




require_once '../../Controlador/RolUsuarioControlador.php';
require_once '../../../includes/sidebar.php'; 

require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);



$controlador = new RolUsuarioControlador();
$datos = $controlador->listar();
?>

<h2>Listado de Roles por Usuario</h2>
<a href="nuevo.php">+ Asignar Nuevo Rol</a>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Asignado En</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($datos as $d): ?>
            <tr>
                <td><?= $d['id'] ?></td>
                <td><?= htmlspecialchars($d['usuario']) ?></td>
                <td><?= htmlspecialchars($d['rol']) ?></td>
                <td><?= $d['asignado_en'] ?></td>
                <td>
                    <a href="editar.php?id=<?= $d['id'] ?>">Editar</a> |
                    <a href="eliminar.php?id=<?= $d['id'] ?>" onclick="return confirm('¿Eliminar esta asignación?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($datos)): ?>
            <tr><td colspan="5" style="text-align:center;">No hay asignaciones registradas.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
