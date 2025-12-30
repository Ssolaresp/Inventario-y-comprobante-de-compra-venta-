<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/ProveedoresControlador.php';
include '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';
verificarAcceso(3);

$controlador = new ProveedoresControlador();
$proveedores = $controlador->listar();
?>

<h2>Listado de Proveedores</h2>
<a href="nuevo.php">➕ Nuevo Proveedor</a><br><br>

<?php if (empty($proveedores)): ?>
    <p>No hay proveedores registrados.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%; text-align:center;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>NIT</th>
                <th>Dirección</th>
                <th>Estado</th>
                <th>Creado</th>
                <th>Actualizado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($proveedores as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['id']) ?></td>
                    <td><?= htmlspecialchars($p['codigo']) ?></td>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['telefono'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['email'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['nit'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['direccion'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['estado']) ?></td>
                    <td><?= $p['creado_en'] ?></td>
                    <td><?= $p['actualizado_en'] ?></td>
                    <td>
                        <a href="editar.php?id=<?= $p['id'] ?>">✏️ Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>