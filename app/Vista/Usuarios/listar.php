<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once '../../Controlador/UsuariosControlador.php';
 include '../../../includes/sidebar.php'; 
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

$controlador = new UsuariosControlador();
$usuarios = $controlador->listar();
?>

<h2>Listado de Usuarios</h2>
<a href="nuevo.php">+ Nuevo Usuario</a>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Estado</th>
            <th>Creado</th>
            <th>Actualizado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['nombre']) ?></td>
                <td><?= htmlspecialchars($u['correo']) ?></td>
                <td><?= htmlspecialchars($u['estado']) ?></td>
                <td><?= $u['creado_en'] ?></td>
                <td><?= $u['actualizado_en'] ?></td>
                <td>
                    <a href="editar.php?id=<?= $u['id'] ?>">Editar</a> </td>
            </tr>
        <?php endforeach; ?>
        <?php if(empty($usuarios)): ?>
            <tr><td colspan="7" style="text-align:center;">No hay usuarios registrados.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
