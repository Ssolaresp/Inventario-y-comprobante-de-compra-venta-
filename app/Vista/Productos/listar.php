<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/ProductosControlador.php';
include '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';
verificarAcceso(3);

$controlador = new ProductosControlador();
$productos = $controlador->listar();
?>

<h2>Listado de Productos</h2>
<a href="nuevo.php">➕ Nuevo Producto</a><br><br>

<?php if (empty($productos)): ?>
    <p>No hay productos registrados.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%; text-align:center;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Unidad</th>
                <th>Proveedor</th>
                <th>Peso</th>
                <th>Imagen</th>
                <th>Estado</th>
                <th>Creado</th>
                <th>Actualizado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['id']) ?></td>
                    <td><?= htmlspecialchars($p['codigo']) ?></td>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['categoria']) ?></td>
                    <td><?= htmlspecialchars($p['unidad_medida']) ?></td>
                    <td><?= htmlspecialchars($p['proveedor'] ?? 'Sin proveedor') ?></td>
                    <td><?= $p['peso'] ?></td>
                    <td>
                        <?php if (!empty($p['imagen_url']) && file_exists('../../../' . $p['imagen_url'])): ?>
                            <img src="../../../<?= $p['imagen_url'] ?>" width="60" style="border-radius:4px;">
                        <?php else: ?>
                            <span style="color:#999;">Sin imagen</span>
                        <?php endif; ?>
                    </td>
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