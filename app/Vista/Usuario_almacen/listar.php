<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../controlador/UsuarioAlmacenControlador.php';
require_once '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

// Verifica acceso al módulo (1 = nivel requerido, ajústalo si es necesario)
verificarAcceso(1);

$controlador = new UsuarioAlmacenControlador();
$registros = $controlador->listar();
?>

<h2>Asignación de Usuarios a Almacenes</h2>
<a href="nuevo.php">+ Nueva asignación</a>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Almacén</th>
            <th>Estado</th>
            <th>Creado</th>
            <th>Actualizado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($registros as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['id']) ?></td>
                <td><?= htmlspecialchars($r['usuario']) ?></td>
                <td><?= htmlspecialchars($r['almacen']) ?></td>
                <td><?= htmlspecialchars($r['estado']) ?></td>
                <td><?= htmlspecialchars($r['creado_en']) ?></td>
                <td><?= htmlspecialchars($r['actualizado_en']) ?></td>
                <td>
                    <a href="editar.php?id=<?= $r['id'] ?>">Editar</a>
             
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if (empty($registros)): ?>
            <tr><td colspan="7">No hay asignaciones registradas.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
