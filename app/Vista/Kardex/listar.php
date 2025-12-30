<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/BitacoraInventarioControlador.php';
include '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

$controlador = new BitacoraInventarioControlador();

// Filtros
$productos   = $controlador->obtenerProductos();
$almacenes   = $controlador->obtenerAlmacenes();
$tipos_ref   = $controlador->obtenerTiposReferencia();

$filtros = [
    'producto_id'     => $_GET['producto_id'] ?? '',
    'almacen_id'      => $_GET['almacen_id'] ?? '',
    'referencia_tipo' => $_GET['referencia_tipo'] ?? '',
    'fecha_desde'     => $_GET['fecha_desde'] ?? '',
    'fecha_hasta'     => $_GET['fecha_hasta'] ?? '',
    'limite'          => $_GET['limite'] ?? 100
];

// Datos
try {
    $kardex       = $controlador->obtenerKardex($filtros);
    $estadisticas = $controlador->obtenerEstadisticas($filtros['fecha_desde'], $filtros['fecha_hasta']);
} catch (Exception $e) {
    $error  = $e->getMessage();
    $kardex = $estadisticas = [];
}
?>

<div class="container-fluid mt-4">
    <h2 class="mb-4">📊 Kardex de Inventario</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            ❌ <strong>Error:</strong> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">🔍 Filtros de Búsqueda</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Producto</label>
                        <select name="producto_id" class="form-select">
                            <option value="">-- Todos los productos --</option>
                            <?php foreach ($productos as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $filtros['producto_id'] == $p['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['codigo'].' - '.$p['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Almacén</label>
                        <select name="almacen_id" class="form-select">
                            <option value="">-- Todos los almacenes --</option>
                            <?php foreach ($almacenes as $a): ?>
                                <option value="<?= $a['id'] ?>" <?= $filtros['almacen_id'] == $a['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($a['codigo'].' - '.$a['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Tipo Referencia</label>
                        <select name="referencia_tipo" class="form-select">
                            <option value="">-- Todos --</option>
                            <?php foreach ($tipos_ref as $tr): ?>
                                <option value="<?= $tr ?>" <?= $filtros['referencia_tipo'] == $tr ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucfirst($tr)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Fecha Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="<?= htmlspecialchars($filtros['fecha_desde']) ?>">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="<?= htmlspecialchars($filtros['fecha_hasta']) ?>">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Límite de registros</label>
                        <select name="limite" class="form-select">
                            <option value="100"  <?= $filtros['limite'] == 100  ? 'selected' : '' ?>>100</option>
                            <option value="500"  <?= $filtros['limite'] == 500  ? 'selected' : '' ?>>500</option>
                            <option value="1000" <?= $filtros['limite'] == 1000 ? 'selected' : '' ?>>1000</option>
                            <option value=""     <?= $filtros['limite'] === ''   ? 'selected' : '' ?>>Todos</option>
                        </select>
                    </div>

                    <div class="col-md-10">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary">🔍 Buscar</button>
                        <a href="?" class="btn btn-secondary">🔄 Limpiar</a>
                        <a href="exportar.php?<?= http_build_query($filtros) ?>" class="btn btn-success">📥 Exportar CSV</a>
                        <a href="resumen_stock.php" class="btn btn-info">📦 Resumen Stock</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas -->
    <?php if (!empty($estadisticas)): ?>
    <div class="row mb-4">
        <?php foreach ($estadisticas as $stat): 
            $icono = $stat['tipo_afectacion'] == 'suma' ? '📈' : '📉';
            $color = $stat['tipo_afectacion'] == 'suma' ? 'success' : 'danger';
        ?>
            <div class="col-md-3">
                <div class="card border-<?= $color ?>">
                    <div class="card-body">
                        <h6 class="text-<?= $color ?>"><?= $icono ?> <?= htmlspecialchars($stat['tipo_movimiento']) ?></h6>
                        <p class="mb-1"><strong><?= number_format($stat['total_movimientos']) ?></strong> movimientos</p>
                        <p class="mb-0 text-muted">Total: <?= number_format($stat['cantidad_total'], 2) ?> unidades</p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Tabla de Movimientos -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">📋 Movimientos de Inventario</h5>
        </div>
        <div class="card-body">
            <?php if (empty($kardex)): ?>
                <div class="text-center py-5 text-muted">
                    <div class="mb-2" style="font-size:48px">📭</div>
                    <p class="lead">No hay movimientos registrados con los filtros seleccionados</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Producto</th>
                                <th>Almacén</th>
                                <th>Tipo Mov.</th>
                                <th class="text-center">Stock Ant.</th>
                                <th class="text-center">Movimiento</th>
                                <th class="text-center">Stock Nuevo</th>
                                <th>Referencia</th>
                                <th>Usuario</th>
                                <th>Observaciones</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kardex as $m): 
                                $colorMov = $m['tipo_afectacion'] == 'suma' ? 'success' : 'danger';
                                $signo = $m['tipo_afectacion'] == 'suma' ? '+' : '-';
                            ?>
                                <tr>
                                    <td><small><?= date('d/m/Y H:i', strtotime($m['fecha_movimiento'])) ?></small></td>
                                    <td><small><?= htmlspecialchars($m['codigo_producto']) ?><br><strong><?= htmlspecialchars($m['producto']) ?></strong></small></td>
                                    <td><small><?= htmlspecialchars($m['codigo_almacen']) ?><br><?= htmlspecialchars($m['almacen']) ?></small></td>
                                    <td><span class="badge bg-<?= $colorMov ?>"><?= htmlspecialchars($m['tipo_movimiento']) ?></span></td>
                                    <td class="text-center"><?= number_format($m['cantidad_anterior'], 2) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $colorMov ?>">
                                            <?= $signo ?><?= number_format($m['cantidad_movimiento'], 2) ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><strong><?= number_format($m['cantidad_nueva'], 2) ?></strong></td>
                                    <td><small><?= htmlspecialchars(ucfirst($m['referencia_tipo'])) ?> #<?= $m['referencia_id'] ?></small></td>
                                    <td><small><?= htmlspecialchars($m['usuario'] ?? 'N/A') ?></small></td>
                                    <td><small><?= htmlspecialchars($m['observaciones']) ?></small></td>
                                    <td>
                                        <a href="ver_producto.php?producto_id=<?= $m['producto_id'] ?>&almacen_id=<?= $m['almacen_id'] ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Ver historial del producto">
                                            📊
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-muted">Mostrando <strong><?= count($kardex) ?></strong> movimiento(s).</p>
            <?php endif; ?>
        </div>
    </div>
</div>