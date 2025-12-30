<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/FacturasControlador.php';

$id = $_GET['id'] ?? null;
$formato = $_GET['formato'] ?? 'normal'; // 'normal' o 'ticket'

if (!$id) {
    die("ID no especificado");
}

$controlador = new FacturasControlador();
$factura = $controlador->obtener($id);
$detalle = $controlador->obtenerDetalle($id);

if (!$factura) {
    die("Factura no encontrada");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura <?= htmlspecialchars($factura['numero_factura']) ?></title>
    <style>
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            margin: 20px;
        }

        /* ========== ESTILO NORMAL (Hoja completa) ========== */
        <?php if ($formato === 'normal'): ?>
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .empresa {
            font-size: 18px;
            font-weight: bold;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .totales {
            margin-left: auto;
            width: 300px;
        }
        .total-final {
            font-size: 14px;
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 5px;
        }
        <?php else: ?>
        /* ========== ESTILO TICKET (80mm angosto) ========== */
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            body {
                width: 80mm;
                margin: 0 auto;
            }
        }
        
        body {
            max-width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            font-size: 11px;
        }
        
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        
        .empresa {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .header div {
            font-size: 10px;
            line-height: 1.3;
        }
        
        .factura-titulo {
            text-align: center;
            margin: 10px 0;
            font-weight: bold;
            font-size: 12px;
        }
        
        .info-cliente {
            font-size: 10px;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }
        
        .info-cliente div {
            margin-bottom: 2px;
        }
        
        .detalle-item {
            border-bottom: 1px dotted #ccc;
            padding: 5px 0;
            font-size: 10px;
        }
        
        .item-nombre {
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .item-linea {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
        }
        
        .totales-ticket {
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 8px;
            font-size: 11px;
        }
        
        .total-linea {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .total-final-ticket {
            font-size: 13px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        
        .observaciones-ticket {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #000;
            font-size: 9px;
        }
        
        .footer-ticket {
            text-align: center;
            margin-top: 15px;
            font-size: 9px;
            color: #666;
            border-top: 1px dashed #000;
            padding-top: 8px;
        }
        
        .separador {
            text-align: center;
            margin: 10px 0;
            font-size: 10px;
        }
        <?php endif; ?>
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 15px; margin: 5px; cursor: pointer;">
            🖨️ Imprimir
        </button>
        <button onclick="window.location.href='?id=<?= $id ?>&formato=<?= $formato === 'normal' ? 'ticket' : 'normal' ?>'" 
                style="padding: 8px 15px; margin: 5px; cursor: pointer;">
            🔄 Cambiar a <?= $formato === 'normal' ? 'Ticket' : 'Normal' ?>
        </button>
        <button onclick="window.close()" style="padding: 8px 15px; margin: 5px; cursor: pointer;">
            ❌ Cerrar
        </button>
        <hr>
    </div>

    <?php if ($formato === 'normal'): ?>
        <!-- ========== FORMATO NORMAL ========== -->
        <div class="header">
            <div class="empresa">TU EMPRESA S.A.</div>
            <div>NIT: 12345678-9</div>
            <div>Dirección: Tu dirección aquí</div>
            <div>Teléfono: (502) 1234-5678</div>
        </div>

        <h2 style="text-align: center; margin: 20px 0;">
            <?= htmlspecialchars($factura['tipo_factura_nombre']) ?><br>
            No. <?= htmlspecialchars($factura['numero_factura']) ?>
        </h2>

        <div class="info-grid">
            <div>
                <strong>Cliente:</strong><br>
                <?= htmlspecialchars($factura['cliente_nombre']) ?><br>
                <strong>NIT:</strong> <?= htmlspecialchars($factura['cliente_nit']) ?><br>
                <strong>Dirección:</strong> <?= htmlspecialchars($factura['cliente_direccion'] ?? 'N/A') ?><br>
                <strong>Teléfono:</strong> <?= htmlspecialchars($factura['cliente_telefono'] ?? 'N/A') ?>
            </div>
            <div>
                <strong>Fecha Emisión:</strong> <?= date('d/m/Y', strtotime($factura['fecha_emision'])) ?><br>
                <?php if ($factura['fecha_vencimiento']): ?>
                    <strong>Fecha Vencimiento:</strong> <?= date('d/m/Y', strtotime($factura['fecha_vencimiento'])) ?><br>
                <?php endif; ?>
                <strong>Forma de Pago:</strong> <?= htmlspecialchars($factura['forma_pago_nombre']) ?><br>
                <?php if ($factura['orden_compra']): ?>
                    <strong>Orden de Compra:</strong> <?= htmlspecialchars($factura['orden_compra']) ?><br>
                <?php endif; ?>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Cant.</th>
                    <th>Descripción</th>
                    <th style="width: 12%;">Precio Unit.</th>
                    <th style="width: 10%;">Desc.</th>
                    <th style="width: 12%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalle as $item): ?>
                    <tr>
                        <td class="text-right"><?= number_format($item['cantidad'], 2) ?></td>
                        <td>
                            <?= htmlspecialchars($item['nombre_item'] ?? $item['descripcion']) ?>
                            <?php if ($item['codigo_item']): ?>
                                <br><small>Cód: <?= htmlspecialchars($item['codigo_item']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">Q <?= number_format($item['precio_unitario'], 2) ?></td>
                        <td class="text-right">
                            <?= $item['descuento_monto'] > 0 ? 'Q ' . number_format($item['descuento_monto'], 2) : '-' ?>
                        </td>
                        <td class="text-right"><strong>Q <?= number_format($item['total'], 2) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totales">
            <table>
                <tr>
                    <td><strong>Subtotal:</strong></td>
                    <td class="text-right">Q <?= number_format($factura['subtotal'], 2) ?></td>
                </tr>
                <tr>
                    <td><strong>Descuentos:</strong></td>
                    <td class="text-right">Q <?= number_format($factura['total_descuento'], 2) ?></td>
                </tr>
                <tr>
                    <td><strong>Impuestos:</strong></td>
                    <td class="text-right">Q <?= number_format($factura['total_impuestos'], 2) ?></td>
                </tr>
                <tr class="total-final">
                    <td><strong>TOTAL:</strong></td>
                    <td class="text-right"><strong>Q <?= number_format($factura['total'], 2) ?></strong></td>
                </tr>
            </table>
        </div>

        <?php if (!empty($factura['observaciones'])): ?>
            <div style="margin-top: 20px; padding: 10px; border: 1px solid #ccc; background-color: #f9f9f9;">
                <strong>Observaciones:</strong><br>
                <?= nl2br(htmlspecialchars($factura['observaciones'])) ?>
            </div>
        <?php endif; ?>

        <div style="margin-top: 40px; text-align: center; font-size: 10px; color: #666;">
            Documento generado el <?= date('d/m/Y H:i') ?> por <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Sistema') ?>
        </div>

    <?php else: ?>
        <!-- ========== FORMATO TICKET (80mm) ========== -->
        <div class="header">
            <div class="empresa">TU EMPRESA S.A.</div>
            <div>NIT: 12345678-9</div>
            <div>Tu dirección aquí</div>
            <div>Tel: (502) 1234-5678</div>
        </div>

        <div class="factura-titulo">
            <?= htmlspecialchars($factura['tipo_factura_nombre']) ?><br>
            No. <?= htmlspecialchars($factura['numero_factura']) ?>
        </div>

        <div class="info-cliente">
            <div><strong>Cliente:</strong> <?= htmlspecialchars($factura['cliente_nombre']) ?></div>
            <div><strong>NIT:</strong> <?= htmlspecialchars($factura['cliente_nit']) ?></div>
            <div><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($factura['fecha_emision'])) ?></div>
            <div><strong>Pago:</strong> <?= htmlspecialchars($factura['forma_pago_nombre']) ?></div>
            <?php if ($factura['orden_compra']): ?>
                <div><strong>O/C:</strong> <?= htmlspecialchars($factura['orden_compra']) ?></div>
            <?php endif; ?>
        </div>

        <div class="separador">================================</div>

        <?php foreach ($detalle as $item): ?>
            <div class="detalle-item">
                <div class="item-nombre">
                    <?= htmlspecialchars($item['nombre_item'] ?? $item['descripcion']) ?>
                </div>
                <?php if ($item['codigo_item']): ?>
                    <div style="font-size: 9px; color: #666;">
                        Cód: <?= htmlspecialchars($item['codigo_item']) ?>
                    </div>
                <?php endif; ?>
                <div class="item-linea">
                    <span><?= number_format($item['cantidad'], 2) ?> x Q<?= number_format($item['precio_unitario'], 2) ?></span>
                    <span><strong>Q<?= number_format($item['total'], 2) ?></strong></span>
                </div>
                <?php if ($item['descuento_monto'] > 0): ?>
                    <div style="font-size: 9px; color: #666;">
                        Descuento: -Q<?= number_format($item['descuento_monto'], 2) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="totales-ticket">
            <div class="total-linea">
                <span>Subtotal:</span>
                <span>Q<?= number_format($factura['subtotal'], 2) ?></span>
            </div>
            <?php if ($factura['total_descuento'] > 0): ?>
                <div class="total-linea">
                    <span>Descuentos:</span>
                    <span>-Q<?= number_format($factura['total_descuento'], 2) ?></span>
                </div>
            <?php endif; ?>
            <div class="total-linea">
                <span>Impuestos:</span>
                <span>Q<?= number_format($factura['total_impuestos'], 2) ?></span>
            </div>
            <div class="total-linea total-final-ticket">
                <span>TOTAL:</span>
                <span>Q<?= number_format($factura['total'], 2) ?></span>
            </div>
        </div>

        <?php if (!empty($factura['observaciones'])): ?>
            <div class="observaciones-ticket">
                <strong>Observaciones:</strong><br>
                <?= nl2br(htmlspecialchars($factura['observaciones'])) ?>
            </div>
        <?php endif; ?>

        <div class="footer-ticket">
            <div>¡Gracias por su compra!</div>
            <div style="margin-top: 5px;">
                <?= date('d/m/Y H:i') ?>
            </div>
            <div>
                Atendió: <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Sistema') ?>
            </div>
        </div>

        <div style="text-align: center; margin-top: 10px; font-size: 8px;">
            ================================
        </div>

    <?php endif; ?>
</body>
</html>