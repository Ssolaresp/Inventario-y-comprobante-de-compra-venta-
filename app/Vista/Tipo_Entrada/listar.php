<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/TiposEntradaControlador.php';
require_once '../../../includes/sidebar.php'; 
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

$controlador = new TiposEntradaControlador();
$tipos = $controlador->listar();
?>

<h2>Tipos de Entrada</h2>
<a href="nuevo.php">+ Nuevo Tipo</a>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Autorización</th>
            <th>Estado</th>
            <th>Creado</th>
            <th>Actualizado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tipos as $t): ?>
            <tr>
                <td><?= $t['id'] ?></td>
                <td><?= htmlspecialchars($t['nombre']) ?></td>
                <td><?= htmlspecialchars($t['descripcion']) ?></td>
                <td><?= $t['requiere_autorizacion'] ? 'Sí' : 'No' ?></td>
                <td><?= htmlspecialchars($t['estado']) ?></td>
                <td><?= $t['creado_en'] ?></td>
                <td><?= $t['actualizado_en'] ?></td>
                <td>
                    <a href="editar.php?id=<?= $t['id'] ?>">Editar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($tipos)): ?>
            <tr><td colspan="8">No hay tipos de entrada registrados.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
