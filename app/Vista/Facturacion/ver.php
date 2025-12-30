<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/FacturasControlador.php';
require_once '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: listar.php?error=ID no especificado');
    exit;
}

$controlador = new FacturasControlador();
$factura = $controlador->obtener($id);
$detalle = $controlador->obtenerDetalle($id);
$pagos = $controlador->obtenerPagos($id);

if (!$factura) {
    header('Location: listar.php?error=Factura no encontrada');
    exit;
}
?>

<style>
    .factura-header {
        background-color: #f5f5f5;
        padding: 20px;
        border: 1px solid #ddd;
        margin-bottom: 20px;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    .info-item {
        padding: 5px;
    }
    .info-label {
        font-weight: bold;
        color: #555;
    }
    .estado-badge {
        display: inline-block;
        padding: 5px 15px;
        border-radius: 5px;
        color: white;
        font-weight: bold;
    }
    .totales-box {
        background-color: #f0f0f0;
        padding: 15px;
        border: 1px solid #ccc;
        margin-top: 20px;
    }
</style>

<h2>📄 Detalle de Factura</h2>
<a href="listar.php">⬅️ Volver al listado</a>

<div class="factura-header">
    <h3>Factura No. <?= htmlspecialchars($factura['numero_factura']) ?></h3>
    
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Tipo de Factura:</span><br>
            <?= htmlspecialchars($factura['tipo_factura_nombre']) ?>
        </div>
        
        <div class="info-item">
            <span class="info-label">Estado:</span><br>
            <span class="estado-badge" style="background-color: <?= htmlspecialchars($factura['estado_color'] ?? '#999') ?>">
                <?= htmlspecialchars($factura['estado_nombre']) ?>
            </span>
        </div>
        
        <div class="info-item">
            <span class="info-label">Cliente:</span><br>
            <?= htmlspecialchars($factura['cliente_nombre']) ?>
        </div>
        
        <div class="info-item">
            <span class="info-label">NIT:</span><br>
            <?= htmlspecialchars($factura['cliente_nit']) ?>
        </div>
        
        <div class="info-item">
            <span class="info-label">Fecha Emisión:</span><br>
            <?= date('d/m/Y', strtotime($factura['fecha_emision'])) ?>
        </div>
        
        <div class="info-item">
            <span class="info-label">Fecha Vencimiento:</span><br>
            <?= $factura['fecha_vencimiento'] ? date('d/m/Y', strtotime($factura['fecha_vencimiento'])) : 'N/A' ?>
        </div>
        
        <div class="info-item">
            <span class="info-label">Forma de Pago:</span><br>
            <?= htmlspecialchars($factura['forma_pago_nombre']) ?>
        </div>
        
        <div class="info-item">
            <span class="info-label">Almacén:</span><br>
            <?= htmlspecialchars($factura['almacen_nombre'] ?? 'N/A') ?>
        </div>
        
        <div class="info-item">
            <span class="info-label">Vendedor:</span><br>
            <?= htmlspecialchars($factura['vendedor_nombre'] ?? 'N/A') ?>
        </div>
        
        <div class="info-item">
            <span class="info-label">Orden de Compra:</span><br>
            <?= htmlspecialchars($factura['orden_compra'] ?? 'N/A') ?>
        </div>
    </div>
    
    <?php if (!empty($factura['observaciones'])): ?>
        <div class="info-item" style="margin-top: 10px;">
            <span class="info-label">Observaciones:</span><br>
            <?= nl2br(htmlspecialchars($factura['observaciones'])) ?>
        </div>
    <?php endif; ?>
</div>

<!-- Detalle de Items -->
<h3>📦 Items de la Factura</h3>
<table border="1" cellpadding="5" cellspacing="0" style="width: 100%;">
    <thead>
        <tr>
            <th>No.</th>
            <th>Código</th>
            <th>Descripción</th>
            <th>Tipo</th>
            <th>Cantidad</th>
            <th>Precio Unit.</th>
            <th>Descuento</th>
            <th>Subtotal</th>
            <th>Impuesto</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($detalle as $item): ?>
            <tr>
                <td><?= $item['numero_linea'] ?></td>
                <td><?= htmlspecialchars($item['codigo_item']) ?></td>
                <td><?= htmlspecialchars($item['nombre_item'] ?? $item['descripcion']) ?></td>
                <td><?= ucfirst($item['tipo_item']) ?></td>
                <td style="text-align: right;"><?= number_format($item['cantidad'], 2) ?></td>
                <td style="text-align: right;">Q <?= number_format($item['precio_unitario'], 2) ?></td>
                <td style="text-align: right;">
                    <?= $item['descuento_porcentaje'] > 0 ? number_format($item['descuento_porcentaje'], 2) . '%' : '-' ?><br>
                    <?= $item['descuento_monto'] > 0 ? 'Q ' . number_format($item['descuento_monto'], 2) : '' ?>
                </td>
                <td style="text-align: right;">Q <?= number_format($item['subtotal'], 2) ?></td>
                <td style="text-align: right;">
                    <?= $item['impuesto_porcentaje'] > 0 ? number_format($item['impuesto_porcentaje'], 2) . '%' : '-' ?><br>
                    <?= $item['impuesto_monto'] > 0 ? 'Q ' . number_format($item['impuesto_monto'], 2) : '' ?>
                </td>
                <td style="text-align: right;"><strong>Q <?= number_format($item['total'], 2) ?></strong></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Totales -->
<div class="totales-box">
    <div style="display: grid; grid-template-columns: 1fr auto; gap: 10px; max-width: 400px; margin-left: auto;">
        <div><strong>Subtotal:</strong></div>
        <div style="text-align: right;">Q <?= number_format($factura['subtotal'], 2) ?></div>
        
        <div><strong>Descuentos:</strong></div>
        <div style="text-align: right;">Q <?= number_format($factura['total_descuento'], 2) ?></div>
        
        <div><strong>Impuestos:</strong></div>
        <div style="text-align: right;">Q <?= number_format($factura['total_impuestos'], 2) ?></div>
        
        <div style="border-top: 2px solid #333; padding-top: 10px;"><strong>TOTAL:</strong></div>
        <div style="border-top: 2px solid #333; padding-top: 10px; text-align: right; font-size: 1.2em;">
            <strong>Q <?= number_format($factura['total'], 2) ?></strong>
        </div>
        
        <?php if ($factura['saldo_pendiente'] > 0): ?>
            <div style="color: red;"><strong>Saldo Pendiente:</strong></div>
            <div style="text-align: right; color: red;">
                <strong>Q <?= number_format($factura['saldo_pendiente'], 2) ?></strong>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Historial de Pagos -->
<?php if (!empty($pagos)): ?>
    <h3>💰 Historial de Pagos</h3>
    <table border="1" cellpadding="5" cellspacing="0" style="width: 100%;">
        <thead>
            <tr>
                <th>No. Pago</th>
                <th>Fecha</th>
                <th>Forma de Pago</th>
                <th>Monto</th>
                <th>Referencia</th>
                <th>Observaciones</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pagos as $pago): ?>
                <tr>
                    <td><?= $pago['numero_pago'] ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($pago['fecha_pago'])) ?></td>
                    <td><?= htmlspecialchars($pago['forma_pago_nombre']) ?></td>
                    <td style="text-align: right;"><strong>Q <?= number_format($pago['monto'], 2) ?></strong></td>
                    <td><?= htmlspecialchars($pago['referencia'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($pago['observaciones'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($pago['usuario_nombre']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Acciones -->
<div style="margin-top: 20px;">
    <?php if ($factura['estado_codigo'] !== 'ANU'): ?>
        <a href="procesar.php?accion=anular&id=<?= $id ?>" 
           onclick="return confirm('¿Anular esta factura? Se devolverá el inventario.')"
           style="background-color: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            ❌ Anular Factura
        </a>
        
        <?php if ($factura['saldo_pendiente'] > 0): ?>
            <a href="pagos.php?id=<?= $id ?>" 
               style="background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                💰 Registrar Pago
            </a>
        <?php endif; ?>
    <?php endif; ?>
    
    <a href="imprimir.php?id=<?= $id ?>" target="_blank"
       style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
        🖨️ Imprimir
    </a>
</div>