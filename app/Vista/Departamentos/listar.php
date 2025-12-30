<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../Controlador/DepartamentosControlador.php';
include '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';
verificarAcceso(6);

$controlador = new DepartamentosControlador();
$departamentos = $controlador->listar();
?>

<h2>Departamentos</h2>
<a href="nuevo.php">➕ Nuevo Departamento</a>
<br><br>

<table border="1" cellpadding="10" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th>Fecha Creación</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($departamentos)): ?>
            <tr>
                <td colspan="7" style="text-align:center;">No hay departamentos registrados</td>
            </tr>
        <?php else: ?>
            <?php foreach ($departamentos as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['id_departamento']) ?></td>
                    <td><?= htmlspecialchars($d['codigo_departamento']) ?></td>
                    <td><?= htmlspecialchars($d['nombre_departamento']) ?></td>
                    <td><?= htmlspecialchars($d['descripcion'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($d['estado']) ?></td>
                    <td><?= htmlspecialchars($d['creado_en']) ?></td>
                    <td>
                        <a href="editar.php?id=<?= $d['id_departamento'] ?>">✏️ Editar</a>
                 </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>