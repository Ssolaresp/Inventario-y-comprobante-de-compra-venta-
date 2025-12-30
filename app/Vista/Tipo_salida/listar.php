<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/TiposSalidaControlador.php';
require_once '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

$controlador = new TiposSalidaControlador();
$tipos = $controlador->listar();
?>

<h2>Listado de Tipos de Salida</h2>
<a href="nuevo.php">+ Nuevo Tipo</a><br><br>

<?php if (empty($tipos)): ?>
    <p>No hay tipos de salida registrados.</p>
<?php else: ?>
    <table border="1" cellpadding="5" cellspacing="0" style="width:100%; text-align:center;">
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
        </tbody>
    </table>
<?php endif; ?>
