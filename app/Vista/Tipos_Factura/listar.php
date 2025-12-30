<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/TiposFacturaControlador.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(3);

$controlador = new TiposFacturaControlador();
$tipos = $controlador->listar();

include '../../../includes/sidebar.php';
?>

<h2>Tipos de Factura</h2>
<a href="nuevo.php">➕ Nuevo Tipo de Factura</a><br><br>

<?php if (empty($tipos)): ?>
    <p>No hay tipos de factura registrados.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%; text-align:center;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Prefijo</th>
                <th>Serie Actual</th>
                <th>Requiere NIT</th>
                <th>Afecta Inventario</th>
                <th>Afecta Cuentas</th>
                <th>Estado</th>
                <th>Creado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tipos as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['id']) ?></td>
                    <td><strong><?= htmlspecialchars($t['codigo']) ?></strong></td>
                    <td><?= htmlspecialchars($t['nombre']) ?></td>
                    <td><?= htmlspecialchars($t['descripcion']) ?></td>
                    <td><strong><?= htmlspecialchars($t['prefijo']) ?></strong></td>
                    <td><?= htmlspecialchars($t['serie_actual']) ?></td>
                    <td><?= $t['requiere_nit'] ? '✓ Sí' : '✗ No' ?></td>
                    <td><?= $t['afecta_inventario'] ? '✓ Sí' : '✗ No' ?></td>
                    <td><?= $t['afecta_cuentas'] ? '✓ Sí' : '✗ No' ?></td>
                    <td><?= htmlspecialchars($t['estado']) ?></td>
                    <td><?= htmlspecialchars($t['creado_en']) ?></td>
                    <td>
                        <a href="editar.php?id=<?= $t['id'] ?>">✏️ Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<style>
    table { border-collapse: collapse; }
    th { background-color: #f4f4f4; font-weight: bold; }
    td, th { padding: 8px; border: 1px solid #ddd; }
    tr:hover { background-color: #f9f9f9; }
</style>
