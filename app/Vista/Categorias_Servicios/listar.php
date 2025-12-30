<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/CategoriasServiciosControlador.php';
include '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(3);

$controlador = new CategoriasServiciosControlador();
$categorias = $controlador->listar();
?>

<h2>Categorías de Servicios</h2>
<a href="nuevo.php">➕ Nueva Categoría</a>
<a href="../servicios/listar.php" style="margin-left: 10px;">↩️ Volver a Servicios</a><br><br>

<?php if (empty($categorias)): ?>
    <p>No hay categorías registradas.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%; text-align:center;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Creado</th>
                <th>Actualizado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categorias as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['id']) ?></td>
                    <td><?= htmlspecialchars($c['codigo']) ?></td>
                    <td><?= htmlspecialchars($c['nombre']) ?></td>
                    <td><?= htmlspecialchars($c['descripcion']) ?></td>
                    <td><?= htmlspecialchars($c['estado']) ?></td>
                    <td><?= htmlspecialchars($c['creado_en']) ?></td>
                    <td><?= htmlspecialchars($c['actualizado_en']) ?></td>
                    <td>
                        <a href="editar.php?id=<?= $c['id'] ?>">✏️ Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>