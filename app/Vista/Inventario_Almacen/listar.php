<?php



if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



require_once '../../Controlador/InventarioAlmacenControlador.php';
include '../../../includes/sidebar.php';

require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(3);


$controlador = new InventarioAlmacenControlador();
$inventario = $controlador->listar();
?>

<h2>📦 Listado de Inventario por Almacén</h2>
<a href="nuevo.php">➕ Nuevo Registro</a><br><br>

<?php if (empty($inventario)): ?>
    <p>No hay registros en el inventario.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%; text-align:center;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Producto</th>
                <th>Almacén</th>
                <th>Código de Barras</th>
                <th>Lote</th>
                <th>F. Vencimiento</th>
                <th>F. Ingreso</th>
                <th>Cant. Actual</th>
                <th>Cant. Mínima</th>
                <th>Cant. Máxima</th>
                <th>Observaciones</th>
                <th>Actualizado en</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inventario as $i): ?>
                <tr>
                    <td><?= htmlspecialchars($i['id'] ?? '') ?></td>
                    <td><?= htmlspecialchars($i['producto'] ?? '') ?></td>
                    <td><?= htmlspecialchars($i['almacen'] ?? '') ?></td>
                    <td><?= htmlspecialchars($i['codigo_barras'] ?? '') ?></td>
                    <td><?= htmlspecialchars($i['lote'] ?? '') ?></td>
                    <td><?= htmlspecialchars($i['fecha_vencimiento'] ?? '') ?></td>
                    <td><?= htmlspecialchars($i['fecha_ingreso'] ?? '') ?></td>
                    <td><?= htmlspecialchars($i['cantidad_actual'] ?? '') ?></td>
                    <td><?= htmlspecialchars($i['cantidad_minima'] ?? '') ?></td>
                    <td><?= htmlspecialchars($i['cantidad_maxima'] ?? '') ?></td>
                    <td><?= htmlspecialchars($i['observaciones'] ?? '') ?></td>
                    <td><?= htmlspecialchars($i['actualizado_en'] ?? '') ?></td>
                    <td>
                        <a href="editar.php?id=<?= $i['id'] ?>">✏️ Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>