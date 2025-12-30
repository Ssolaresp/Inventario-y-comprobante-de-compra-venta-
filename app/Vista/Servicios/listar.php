<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/ServiciosControlador.php';
include '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(3);

$controlador = new ServiciosControlador();
$servicios = $controlador->listar();
?>

<h2>Listado de Servicios</h2>
<a href="nuevo.php">➕ Nuevo Servicio</a>
<a href="../categorias_servicios/listar.php" style="margin-left: 10px;">📂 Categorías</a><br><br>

<?php if (empty($servicios)): ?>
    <p>No hay servicios registrados.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%; text-align:center;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio Base</th>
                <th>IVA</th>
                <th>%IVA</th>
                <th>Categoría</th>
                <th>Estado</th>
                <th>Creado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($servicios as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['id']) ?></td>
                    <td><?= htmlspecialchars($s['codigo']) ?></td>
                    <td><?= htmlspecialchars($s['nombre']) ?></td>
                    <td><?= htmlspecialchars($s['descripcion']) ?></td>
                    <td>Q <?= number_format($s['precio_base'], 2) ?></td>
                    <td><?= $s['aplica_iva'] ? '✓' : '✗' ?></td>
                    <td><?= $s['porcentaje_iva'] ?>%</td>
                    <td><?= htmlspecialchars($s['categoria'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($s['estado']) ?></td>
                    <td><?= htmlspecialchars($s['creado_en']) ?></td>
                    <td>
                        <a href="editar.php?id=<?= $s['id'] ?>">✏️ Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>