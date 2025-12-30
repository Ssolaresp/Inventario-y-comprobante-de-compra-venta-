<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/BitacoraInventarioControlador.php';
include '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

$controlador = new BitacoraInventarioControlador();

try {
    $resumen = $controlador->obtenerResumenStock();
} catch (Exception $e) {
    $error = $e->getMessage();
    $resumen = [];
}

// Agrupar por producto
$productos_agrupados = [];
foreach ($resumen as $item) {
    $pid = $item['producto_id'];
    if (!isset($productos_agrupados[$pid])) {
        $productos_agrupados[$pid] = [
            'codigo' => $item['codigo_producto'],
            'nombre' => $item['producto'],
            'almacenes' => [],
            'total' => 0
        ];
    }
    $productos_agrupados[$pid]['almacenes'][] = [
        'almacen' => $item['almacen'],
        'codigo_almacen' => $item['codigo_almacen'],
        'stock' => $item['stock_actual']
    ];
    $productos_agrupados[$pid]['total'] += $item['stock_actual'];
}
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📦 Resumen de Stock por Producto</h2>
        <a href="kardex.php" class="btn btn-secondary">← Volver al Kardex</a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Tarjetas de Resumen -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h3 class="text-primary"><?= count($productos_agrupados) ?></h3>
                    <p class="mb-0">Productos Diferentes</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h3 class="text-success"><?= count($resumen) ?></h3>
                    <p class="mb-0">Ubicaciones Totales</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h3 class="text-info"><?= number_format(array_sum(array_column($productos_agrupados, 'total')), 2) ?></h3>
                    <p class="mb-0">Unidades Totales</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Stock -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">📊 Stock por Producto y Almacén</h5>
        </div>
        <div class="card-body">
            <?php if (empty($productos_agrupados)): ?>
                <div class="text-center py-5 text-muted">
                    <div class="mb-2" style="font-size:48px">📭</div>
                    <p class="lead">No hay stock registrado</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Almacenes</th>
                                <th class="text-center">Total General</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos_agrupados as $pid => $prod): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($prod['codigo']) ?></strong></td>
                                    <td><?= htmlspecialchars($prod['nombre']) ?></td>
                                    <td>
                                        <?php foreach ($prod['almacenes'] as $alm): ?>
                                            <span class="badge bg-secondary me-1">
                                                <?= htmlspecialchars($alm['codigo_almacen']) ?>: 
                                                <?= number_format($alm['stock'], 2) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success fs-6">
                                            <?= number_format($prod['total'], 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="ver_producto.php?producto_id=<?= $pid ?>" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Ver historial">
                                            📊 Ver Kardex
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>