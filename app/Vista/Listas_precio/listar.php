<?php
require_once '../../Controlador/ListaPrecioDetalleControlador.php';
require_once '../../../includes/sidebar.php'; 

$controlador = new ListaPrecioDetalleControlador();
$listas = $controlador->listarCabecera();

// Mostrar mensajes
if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'guardado') {
    echo '<p style="color: green;">✅ Registro guardado exitosamente.</p>';
}
if (isset($_GET['error'])) {
    echo '<p style="color: red;">❌ Error: ' . htmlspecialchars($_GET['error']) . '</p>';
}
?>

<h2>💵 Listado de Listas de Precios</h2>
<a href="nuevo.php">➕ Nueva Lista de Precios</a><br><br>

<?php if (empty($listas)): ?>
    <p>No hay listas de precios registradas.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%; text-align:center;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Moneda</th>
                <th>Vigente Desde</th>
                <th>Vigente Hasta</th>
                <th>Estado</th>
                <th>Productos</th>
                <th>Actualizado en</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($listas as $l): ?>
                <tr>
                    <td><?= htmlspecialchars($l['id'] ?? '') ?></td>
                    <td><?= htmlspecialchars($l['nombre'] ?? '') ?></td>
                    <td><?= htmlspecialchars($l['descripcion'] ?? '') ?></td>
                    <td><?= htmlspecialchars($l['moneda_simbolo'] ?? '') ?> <?= htmlspecialchars($l['moneda_codigo'] ?? '') ?></td>
                    <td><?= htmlspecialchars($l['vigente_desde'] ?? '') ?></td>
                    <td><?= htmlspecialchars($l['vigente_hasta'] ?? '') ?></td>
                    <td><?= htmlspecialchars($l['estado'] ?? '') ?></td>
                    <td><?= htmlspecialchars($l['cantidad_productos'] ?? '0') ?></td>
                    <td><?= htmlspecialchars($l['actualizado_en'] ?? '') ?></td>
                    <td>
                        <a href="detalle.php?id=<?= $l['id'] ?>">📝 Gestionar Detalle</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>