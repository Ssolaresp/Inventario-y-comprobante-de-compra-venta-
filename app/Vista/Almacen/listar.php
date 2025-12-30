<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



require_once '../../Controlador/AlmacenesControlador.php';
include '../../../includes/sidebar.php';

require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(3);


$controlador = new AlmacenesControlador();
$almacenes = $controlador->listar();
?>

<h2>Listado de Almacenes</h2>
<a href="nuevo.php">➕ Nuevo Almacén</a><br><br>

<?php if (empty($almacenes)): ?>
    <p>No hay almacenes registrados.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%; text-align:center;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Ubicación</th>
                <th>Descripción</th>
                <th>Responsable</th>
                <th>Estado</th>
                <th>Creado</th>
                <th>Actualizado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($almacenes as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['id']) ?></td>
                    <td><?= htmlspecialchars($a['codigo']) ?></td>
                    <td><?= htmlspecialchars($a['nombre']) ?></td>
                    <td><?= htmlspecialchars($a['ubicacion']) ?></td>
                    <td><?= htmlspecialchars($a['descripcion']) ?></td>
                    <td><?= htmlspecialchars($a['responsable'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($a['estado']) ?></td>
                    <td><?= htmlspecialchars($a['creado_en']) ?></td>
                    <td><?= htmlspecialchars($a['actualizado_en']) ?></td>
                    <td>
                        <a href="editar.php?id=<?= $a['id'] ?>">✏️ Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
