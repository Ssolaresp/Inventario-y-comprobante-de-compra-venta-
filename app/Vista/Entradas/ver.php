<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../Controlador/EntradasAlmacenControlador.php';
include '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';
verificarAcceso(6); // Ajusta el rol que corresponda

$controlador = new EntradasAlmacenControlador();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: listar.php?error=ID no especificado');
    exit;
}

try {
    $entrada = $controlador->obtener($id);
    if (!$entrada) throw new Exception("Entrada no encontrada");
    $detalle = $controlador->obtenerDetalle($id);
} catch (Exception $e) {
    header('Location: listar.php?error=' . urlencode($e->getMessage()));
    exit;
}

// Totales
$total_productos = count($detalle);
$total_cantidad  = array_sum(array_column($detalle, 'cantidad'));
$total_valor     = array_sum(array_column($detalle, 'subtotal'));
?>

<h2>📥 Detalle de Entrada #<?= $entrada['id'] ?></h2>

<!-- DATOS DE LA ENTRADA -->
<table border="1" cellpadding="6" cellspacing="0" style="width:100%; margin-bottom:20px;">
    <tr><td><strong>Número de Entrada:</strong></td><td><?= htmlspecialchars($entrada['numero_entrada']) ?></td></tr>
    <tr><td><strong>Tipo de Entrada:</strong></td><td><?= htmlspecialchars($entrada['tipo_entrada']) ?></td></tr>
    <tr><td><strong>Almacén:</strong></td><td><?= htmlspecialchars($entrada['almacen']) ?></td></tr>
    <tr><td><strong>Estado:</strong></td><td><?= htmlspecialchars($entrada['estado']) ?></td></tr>
    <tr><td><strong>Fecha de Entrada:</strong></td><td><?= date('d/m/Y H:i', strtotime($entrada['fecha_entrada'])) ?></td></tr>
    <?php if ($entrada['fecha_autorizacion']): ?>
        <tr><td><strong>Fecha de Autorización:</strong></td><td><?= date('d/m/Y H:i', strtotime($entrada['fecha_autorizacion'])) ?></td></tr>
    <?php endif; ?>
    <tr><td><strong>Documento de Referencia:</strong></td><td><?= htmlspecialchars($entrada['documento_referencia']) ?></td></tr>
    <tr><td><strong>Motivo:</strong></td><td><?= htmlspecialchars($entrada['motivo']) ?></td></tr>
</table>

<!-- PRODUCTOS -->
<h3>📦 Productos</h3>
<?php if (empty($detalle)): ?>
    <p>No hay productos en esta entrada.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%; text-align:center;">
        <thead>
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalle as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['codigo_producto']) ?></td>
                    <td><?= htmlspecialchars($item['producto']) ?></td>
                    <td><?= number_format($item['cantidad'], 2) ?></td>
                    <td>Q <?= number_format($item['precio_unitario'], 2) ?></td>
                    <td>Q <?= number_format($item['subtotal'], 2) ?></td>
                    <td><?= htmlspecialchars($item['observaciones']) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="font-weight:bold; background:#f2f2f2;">
                <td colspan="2">TOTALES</td>
                <td><?= number_format($total_cantidad, 2) ?></td>
                <td></td>
                <td>Q <?= number_format($total_valor, 2) ?></td>
                <td></td>
            </tr>
        </tbody>
    </table>
<?php endif; ?>

<br>

<!-- ACCIONES -->
<?php if ($entrada['estado'] === 'Registrada'): ?>
    <a href="procesar.php?accion=autorizar&id=<?= $entrada['id'] ?>"
       onclick="return confirm('¿Autorizar esta entrada? Se afectará el inventario.')">✅ Autorizar</a> |
    <a href="procesar.php?accion=cancelar&id=<?= $entrada['id'] ?>"
       onclick="return confirm('¿Cancelar esta entrada?')">❌ Cancelar</a> |
<?php endif; ?>
<a href="listar.php">⬅️ Volver al listado</a>