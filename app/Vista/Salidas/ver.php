<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/SalidasAlmacenControlador.php';
require_once '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

// Ajusta el ID del permiso que corresponda al módulo de salidas
verificarAcceso(1);

$controlador = new SalidasAlmacenControlador();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: listar.php?error=ID no especificado');
    exit;
}

try {
    $salida   = $controlador->obtener($id);
    if (!$salida) {
        throw new Exception("Salida no encontrada");
    }
    $detalle  = $controlador->obtenerDetalle($id);
} catch (Exception $e) {
    header('Location: listar.php?error=' . urlencode($e->getMessage()));
    exit;
}

// Cálculo de totales
$total_productos = count($detalle);
$total_cantidad  = 0;
$total_valor     = 0;

foreach ($detalle as $item) {
    $total_cantidad += $item['cantidad'];
    $total_valor    += $item['subtotal'];
}
?>

<!-- ################  ESTILOS  ################ -->
<style>
    .info-box{
        border:1px solid #ddd;
        padding:15px;
        margin-bottom:20px;
        background:#f9f9f9;
        border-radius:4px;
    }
    .info-row{margin-bottom:8px;}
    table{
        width:100%;
        border-collapse:collapse;
        margin-top:20px;
    }
    th,td{
        border:1px solid #ddd;
        padding:8px;
        text-align:left;
    }
    th{background:#f2f2f2;}
    .total-row{font-weight:bold;background:#f2f2f2;}
    .btn{
        padding:5px 10px;
        margin:2px;
        text-decoration:none;
        border:1px solid #333;
        display:inline-block;
        color:#000;
    }
</style>

<!-- ################  TÍTULO  ################ -->
<h2>📤 Detalle de Salida #<?= $salida['id'] ?></h2>

<!-- ################  DATOS GENERALES  ################ -->
<div class="info-box">
    <div class="info-row"><strong>Número de Salida:</strong> <?= htmlspecialchars($salida['numero_salida']) ?></div>
    <div class="info-row"><strong>Tipo de Salida:</strong> <?= htmlspecialchars($salida['tipo_salida']) ?></div>
    <div class="info-row"><strong>Almacén:</strong> <?= htmlspecialchars($salida['almacen']) ?></div>
    <div class="info-row"><strong>Estado:</strong> <?= htmlspecialchars($salida['estado']) ?></div>
    <div class="info-row"><strong>Fecha de Salida:</strong> <?= date('d/m/Y H:i', strtotime($salida['fecha_salida'])) ?></div>
    <?php if ($salida['fecha_autorizacion']): ?>
        <div class="info-row"><strong>Fecha de Autorización:</strong> <?= date('d/m/Y H:i', strtotime($salida['fecha_autorizacion'])) ?></div>
    <?php endif; ?>
    <div class="info-row"><strong>Documento de Referencia:</strong> <?= htmlspecialchars($salida['documento_referencia'] ?? '') ?></div>
    <div class="info-row"><strong>Motivo:</strong> <?= htmlspecialchars($salida['motivo'] ?? '') ?></div>
</div>

<!-- ################  PRODUCTOS  ################ -->
<h3>📦 Productos</h3>
<table>
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
        <?php if (empty($detalle)): ?>
            <tr><td colspan="6" style="text-align:center;">No hay productos en esta salida</td></tr>
        <?php else: ?>
            <?php foreach ($detalle as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['codigo_producto'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['producto'] ?? '') ?></td>
                    <td><?= number_format($item['cantidad'], 2) ?></td>
                    <td>Q <?= number_format($item['precio_unitario'], 2) ?></td>
                    <td>Q <?= number_format($item['subtotal'], 2) ?></td>
                    <td><?= htmlspecialchars($item['observaciones'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="2">TOTALES</td>
                <td><?= number_format($total_cantidad, 2) ?></td>
                <td></td>
                <td>Q <?= number_format($total_valor, 2) ?></td>
                <td></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<br>

<!-- ################  BOTONES DE ACCIÓN  ################ -->
<?php if ($salida['estado'] == 'Registrada'): ?>
    <a href="procesar.php?accion=autorizar&id=<?= $salida['id'] ?>"
       class="btn"
       onclick="return confirm('¿Autorizar esta salida? Se afectará el inventario.')">✅ Autorizar</a>

    <a href="procesar.php?accion=cancelar&id=<?= $salida['id'] ?>"
       class="btn"
       onclick="return confirm('¿Cancelar esta salida?')">❌ Cancelar</a>
<?php endif; ?>

<a href="listar.php" class="btn">⬅️ Volver al listado</a>