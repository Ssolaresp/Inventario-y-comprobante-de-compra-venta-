<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/BitacoraInventarioControlador.php';
include '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

$producto_id = filter_input(INPUT_GET, 'producto_id', FILTER_VALIDATE_INT);
$almacen_id = filter_input(INPUT_GET, 'almacen_id', FILTER_VALIDATE_INT);

if (!$producto_id) {
    header('Location: listar.php?error=producto_invalido');
    exit;
}

$controlador = new BitacoraInventarioControlador();

try {
    $kardex = $controlador->obtenerKardexPorProducto($producto_id, $almacen_id);
    $producto_info = $controlador->obtenerInfoProducto($producto_id, $almacen_id);
} catch (Exception $e) {
    $error = $e->getMessage();
    $kardex = [];
    $producto_info = null;
}
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📊 Historial de Producto</h2>
        <a href="listar.php" class="btn btn-secondary">← Volver al Kardex</a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($producto_info): ?>
    <!-- Información del Producto -->
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">📦 Información del Producto</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Código:</strong><br>
                    <span class="text-primary fs-4"><?= htmlspecialchars($producto_info['codigo']) ?></span>
                </div>
                <div class="col-md-5">
                    <strong>Nombre:</strong><br>
                    <span class="fs-5"><?= htmlspecialchars($producto_info['nombre']) ?></span>
                </div>
                <div class="col-md-2">
                    <strong>Stock Actual:</strong><br>
                    <span class="badge bg-success fs-5"><?= number_format($producto_info['stock_actual'], 2) ?></span>
                </div>
                <div class="col-md-2">
                    <strong>Almacén:</strong><br>
                    <span><?= htmlspecialchars($producto_info['almacen'] ?? 'Todos') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas del Producto -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h3 class="text-success">📈 <?= number_format($producto_info['total_entradas'], 2) ?></h3>
                    <p class="mb-0">Total Entradas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h3 class="text-danger">📉 <?= number_format($producto_info['total_salidas'], 2) ?></h3>
                    <p class="mb-0">Total Salidas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h3 class="text-info">🔄 <?= number_format($producto_info['total_movimientos']) ?></h3>
                    <p class="mb-0">Total Movimientos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h3 class="text-warning">📅 <?= $producto_info['ultimo_movimiento'] ? date('d/m/Y', strtotime($producto_info['ultimo_movimiento'])) : 'N/A' ?></h3>
                    <p class="mb-0">Último Movimiento</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Historial de Movimientos -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">📋 Historial de Movimientos</h5>
        </div>
        <div class="card-body">
            <?php if (empty($kardex)): ?>
                <div class="text-center py-5 text-muted">
                    <div class="mb-2" style="font-size:48px">📭</div>
                    <p class="lead">No hay movimientos registrados para este producto</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Almacén</th>
                                <th>Tipo Movimiento</th>
                                <th class="text-center">Stock Anterior</th>
                                <th class="text-center">Movimiento</th>
                                <th class="text-center">Stock Nuevo</th>
                                <th>Referencia</th>
                                <th>Usuario</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kardex as $m): 
                                $colorMov = $m['tipo_afectacion'] == 'suma' ? 'success' : 'danger';
                                $signo = $m['tipo_afectacion'] == 'suma' ? '+' : '-';
                            ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i:s', strtotime($m['fecha_movimiento'])) ?></td>
                                    <td><?= htmlspecialchars($m['almacen']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $colorMov ?>">
                                            <?= htmlspecialchars($m['tipo_movimiento']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?= number_format($m['cantidad_anterior'], 2) ?></td>
                                    <td class="text-center">
                                        <strong class="text-<?= $colorMov ?>">
                                            <?= $signo ?><?= number_format($m['cantidad_movimiento'], 2) ?>
                                        </strong>
                                    </td>
                                    <td class="text-center">
                                        <strong><?= number_format($m['cantidad_nueva'], 2) ?></strong>
                                    </td>
                                    <td>
                                        <small>
                                            <?= htmlspecialchars(ucfirst($m['referencia_tipo'])) ?> 
                                            #<?= $m['referencia_id'] ?>
                                        </small>
                                    </td>
                                    <td><?= htmlspecialchars($m['usuario'] ?? 'N/A') ?></td>
                                    <td><small><?= htmlspecialchars($m['observaciones']) ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>