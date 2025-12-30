<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/FacturasControlador.php';
require_once '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

$controlador = new FacturasControlador();

// Capturar filtros
$filtros = [
    'estado_id' => $_GET['estado_id'] ?? '',
    'fecha_desde' => $_GET['fecha_desde'] ?? '',
    'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
    'cliente_id' => $_GET['cliente_id'] ?? ''
];

$facturas = $controlador->listar($filtros);
$estados = $controlador->obtenerEstados();
$clientes = $controlador->obtenerClientes();

$mensaje = $_GET['mensaje'] ?? '';
$error = $_GET['error'] ?? '';
?>

<h2>📄 Listado de Facturas</h2>
<a href="nuevo.php">➕ Nueva Factura</a>

<!-- Filtros -->
<div style="background-color: #f0f0f0; padding: 10px; margin: 20px 0; border: 1px solid #ccc;">
    <form method="GET" action="">
        <strong>Filtros:</strong><br><br>
        
        <label>Estado:</label>
        <select name="estado_id">
            <option value="">Todos</option>
            <?php foreach ($estados as $e): ?>
                <option value="<?= $e['id'] ?>" <?= $filtros['estado_id'] == $e['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <label>Desde:</label>
        <input type="date" name="fecha_desde" value="<?= htmlspecialchars($filtros['fecha_desde']) ?>">
        
        <label>Hasta:</label>
        <input type="date" name="fecha_hasta" value="<?= htmlspecialchars($filtros['fecha_hasta']) ?>">
        
        <label>Cliente:</label>
        <select name="cliente_id">
            <option value="">Todos</option>
            <?php foreach ($clientes as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $filtros['cliente_id'] == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit">🔍 Filtrar</button>
        <a href="listar.php">🔄 Limpiar</a>
    </form>
</div>

<table border="1" cellpadding="5" cellspacing="0" style="width: 100%;">
    <thead>
        <tr>
            <th>No. Factura</th>
            <th>Tipo</th>
            <th>Cliente</th>
            <th>NIT</th>
            <th>Fecha Emisión</th>
            <th>Vencimiento</th>
            <th>Total</th>
            <th>Saldo Pend.</th>
            <th>Estado</th>
            <th>Forma Pago</th>
            <th>Vendedor</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($facturas)): ?>
            <tr>
                <td colspan="12" style="text-align: center;">No hay facturas registradas.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($facturas as $f): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($f['numero_factura']) ?></strong></td>
                    <td><?= htmlspecialchars($f['tipo_factura']) ?></td>
                    <td><?= htmlspecialchars($f['cliente']) ?></td>
                    <td><?= htmlspecialchars($f['nit']) ?></td>
                    <td><?= date('d/m/Y', strtotime($f['fecha_emision'])) ?></td>
                    <td><?= $f['fecha_vencimiento'] ? date('d/m/Y', strtotime($f['fecha_vencimiento'])) : 'N/A' ?></td>
                    <td style="text-align: right;">Q <?= number_format($f['total'], 2) ?></td>
                    <td style="text-align: right;">Q <?= number_format($f['saldo_pendiente'], 2) ?></td>
                    <td>
                        <span style="background-color: <?= htmlspecialchars($f['estado_color']) ?>; color: white; padding: 3px 8px; border-radius: 3px; font-size: 0.85em;">
                            <?= htmlspecialchars($f['estado']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($f['forma_pago']) ?></td>
                    <td><?= htmlspecialchars($f['vendedor']) ?></td>
                    <td>
                        <a href="ver.php?id=<?= $f['id'] ?>">👁️ Ver</a>
                        
                        <?php if ($f['estado'] !== 'Anulada'): ?>
                            <a href="procesar.php?accion=anular&id=<?= $f['id'] ?>"
                               onclick="return confirm('¿Anular esta factura? Esta acción devolverá el inventario.')">❌ Anular</a>
                        <?php endif; ?>
                        
                        <?php if ($f['saldo_pendiente'] > 0 && $f['estado'] !== 'Anulada'): ?>
                            <a href="pagos.php?id=<?= $f['id'] ?>">💰 Pago</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php if ($mensaje): ?>
    <script>
        alert("<?php
            switch ($mensaje) {
                case 'creada': echo '✅ Factura creada exitosamente'; break;
                case 'anulada': echo '✅ Factura anulada correctamente'; break;
                case 'pago_registrado': echo '✅ Pago registrado correctamente'; break;
                default: echo '✅ Operación exitosa';
            }
        ?>");
    </script>
<?php endif; ?>

<?php if ($error): ?>
    <script>alert("❌ Error: <?= htmlspecialchars($error) ?>");</script>
<?php endif; ?>