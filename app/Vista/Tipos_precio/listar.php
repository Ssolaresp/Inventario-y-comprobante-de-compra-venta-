<?php
require_once '../../Controlador/TipoPrecioControlador.php';
require_once '../../../includes/sidebar.php'; 
$controlador = new TipoPrecioControlador();
$tipos = $controlador->listar();

// Mostrar mensajes
if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'guardado') {
    echo '<p style="color: green;">✅ Registro guardado exitosamente.</p>';
}
if (isset($_GET['error'])) {
    echo '<p style="color: red;">❌ Error: ' . htmlspecialchars($_GET['error']) . '</p>';
}
?>

<h2>💰 Listado de Tipos de Precio</h2>
<a href="nuevo.php">➕ Nuevo Tipo de Precio</a><br><br>

<?php if (empty($tipos)): ?>
    <p>No hay tipos de precio registrados.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%; text-align:center;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Creado en</th>
                <th>Actualizado en</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tipos as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['id'] ?? '') ?></td>
                    <td><?= htmlspecialchars($t['nombre'] ?? '') ?></td>
                    <td><?= htmlspecialchars($t['descripcion'] ?? '') ?></td>
                    <td><?= htmlspecialchars($t['estado'] ?? '') ?></td>
                    <td><?= htmlspecialchars($t['creado_en'] ?? '') ?></td>
                    <td><?= htmlspecialchars($t['actualizado_en'] ?? '') ?></td>
                    <td>
                        <a href="editar.php?id=<?= $t['id'] ?>">✏️ Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>