<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



require_once '../../Controlador/CategoriasControlador.php';
include '../../../includes/sidebar.php'; 
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(2);


$controlador = new CategoriasControlador();
$categorias = $controlador->listar();
?>

<h2>Listado de Categorías</h2>
<a href="nuevo.php">+ Nueva Categoría</a>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Categoría Padre</th>
            <th>Estado</th>
            <th>Creado</th>
            <th>Actualizado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categorias as $c): ?>
            <tr>
                <td><?= $c['id'] ?></td>
                <td><?= htmlspecialchars($c['codigo']) ?></td>
                <td><?= htmlspecialchars($c['nombre']) ?></td>
                <td><?= htmlspecialchars($c['descripcion']) ?></td>
                <td><?= htmlspecialchars($c['categoria_padre'] ?? '—') ?></td>
                <td><?= htmlspecialchars($c['estado']) ?></td>
                <td><?= $c['creado_en'] ?></td>
                <td><?= $c['actualizado_en'] ?></td>
                <td>
                    <a href="editar.php?id=<?= $c['id'] ?>">Editar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($categorias)): ?>
            <tr><td colspan="9" style="text-align:center;">No hay categorías registradas.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
