<?php
// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../Controlador/PagosControlador.php';
require_once __DIR__ . '/../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    die('<div class="alert alert-danger">❌ Error: Sesión inválida.</div>');
}

$controlador = new PagosControlador();

// Obtener filtros
$filtro_cliente = $_GET['cliente_id'] ?? '';
$filtro_fecha_desde = $_GET['fecha_desde'] ?? '';
$filtro_fecha_hasta = $_GET['fecha_hasta'] ?? '';
$filtro_vencidas = $_GET['vencidas'] ?? '';

// Obtener datos
$facturas = $controlador->listarFacturasPendientes([
    'cliente_id' => $filtro_cliente,
    'fecha_desde' => $filtro_fecha_desde,
    'fecha_hasta' => $filtro_fecha_hasta,
    'vencidas' => $filtro_vencidas
]);

$clientes = $controlador->obtenerClientes();
$estadisticas = $controlador->obtenerEstadisticasPagos();

// Mensaje de éxito
$mensaje = $_GET['mensaje'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos Pendientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .factura-vencida { background-color: #fff3cd; }
        .badge-dias-vencidos { font-size: 0.75rem; }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3">
                <i class="fas fa-money-bill-wave text-warning"></i> 
                Facturas Pendientes de Pago
            </h2>
            <div class="d-flex gap-2 mb-3">
                <a href="listar.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a Facturas
                </a>
                <a href="historial_pagos.php" class="btn btn-outline-info">
                    <i class="fas fa-history"></i> Historial de Pagos
                </a>
            </div>
        </div>
    </div>

    <!-- Mensaje de éxito -->
    <?php if ($mensaje === 'pago_registrado'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> 
            <strong>¡Pago registrado exitosamente!</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Tarjetas de Resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Facturas Pendientes</h6>
                    <h2 class="text-warning mb-0">
                        <?= $estadisticas['total_facturas_pendientes'] ?? 0 ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total a Cobrar</h6>
                    <h4 class="text-info mb-0">
                        Q <?= number_format($estadisticas['monto_total_pendiente'] ?? 0, 2) ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h6 class="text-muted">Saldo Pendiente</h6>
                    <h4 class="text-danger mb-0">
                        Q <?= number_format($estadisticas['saldo_total_pendiente'] ?? 0, 2) ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Cobrado</h6>
                    <h4 class="text-success mb-0">
                        Q <?= number_format($estadisticas['total_cobrado'] ?? 0, 2) ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Facturas Vencidas -->
    <?php if (($estadisticas['facturas_vencidas'] ?? 0) > 0): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Atención:</strong> Hay <?= $estadisticas['facturas_vencidas'] ?> factura(s) vencida(s) 
            con un saldo de Q <?= number_format($estadisticas['saldo_vencido'], 2) ?>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filtros</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Cliente</label>
                    <select name="cliente_id" class="form-select">
                        <option value="">-- Todos --</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $filtro_cliente == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Desde</label>
                    <input type="date" name="fecha_desde" class="form-control" value="<?= $filtro_fecha_desde ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control" value="<?= $filtro_fecha_hasta ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="vencidas" class="form-select">
                        <option value="">Todas</option>
                        <option value="1" <?= $filtro_vencidas === '1' ? 'selected' : '' ?>>Solo Vencidas</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="pagos.php" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Facturas -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-list"></i> Listado de Facturas (<?= count($facturas) ?>)
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Factura</th>
                            <th>Cliente</th>
                            <th>Fecha Emisión</th>
                            <th>Vencimiento</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Pagado</th>
                            <th class="text-end">Saldo</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($facturas)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No hay facturas pendientes de pago
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($facturas as $f): ?>
                                <?php 
                                $es_vencida = !empty($f['fecha_vencimiento']) && $f['dias_vencidos'] > 0;
                                $clase_fila = $es_vencida ? 'factura-vencida' : '';
                                ?>
                                <tr class="<?= $clase_fila ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($f['numero_factura']) ?></strong>
                                        <?php if ($es_vencida): ?>
                                            <br><span class="badge bg-danger badge-dias-vencidos">
                                                Vencida hace <?= $f['dias_vencidos'] ?> días
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($f['cliente_nombre']) ?><br>
                                        <small class="text-muted">NIT: <?= htmlspecialchars($f['cliente_nit']) ?></small>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($f['fecha_emision'])) ?></td>
                                    <td>
                                        <?php if (!empty($f['fecha_vencimiento'])): ?>
                                            <?= date('d/m/Y', strtotime($f['fecha_vencimiento'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Contado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">Q <?= number_format($f['total'], 2) ?></td>
                                    <td class="text-end text-success">
                                        Q <?= number_format($f['total_pagado'], 2) ?>
                                        <?php if ($f['cantidad_pagos'] > 0): ?>
                                            <br><small class="text-muted">(<?= $f['cantidad_pagos'] ?> pago(s))</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-danger">
                                            Q <?= number_format($f['saldo_pendiente'], 2) ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: <?= $f['estado_color'] ?>;">
                                            <?= htmlspecialchars($f['estado']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="registrar_pago.php?id=<?= $f['id'] ?>" 
                                           class="btn btn-success btn-sm" 
                                           title="Registrar Pago">
                                            <i class="fas fa-hand-holding-usd"></i> Pagar
                                        </a>
                                        <a href="ver.php?id=<?= $f['id'] ?>" 
                                           class="btn btn-info btn-sm" 
                                           title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>