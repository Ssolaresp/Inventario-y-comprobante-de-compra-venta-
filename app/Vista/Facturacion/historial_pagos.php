<?php
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
$filtro_estado = $_GET['estado_codigo'] ?? '';
$filtro_cliente = $_GET['cliente_id'] ?? '';
$filtro_fecha_desde = $_GET['fecha_desde'] ?? '';
$filtro_fecha_hasta = $_GET['fecha_hasta'] ?? '';

// Obtener datos
$facturas = $controlador->obtenerHistorialFacturas([
    'estado_codigo' => $filtro_estado,
    'cliente_id' => $filtro_cliente,
    'fecha_desde' => $filtro_fecha_desde,
    'fecha_hasta' => $filtro_fecha_hasta
]);

$clientes = $controlador->obtenerClientes();
$estados = $controlador->obtenerEstadosFactura();

// Calcular totales
$total_facturas = count($facturas);
$suma_total = array_sum(array_column($facturas, 'total'));
$suma_pagado = array_sum(array_column($facturas, 'total_pagado'));
$suma_saldo = array_sum(array_column($facturas, 'saldo_pendiente'));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pagos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .modal-pagos .table { font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3">
                <i class="fas fa-history text-info"></i> 
                Historial de Facturas y Pagos
            </h2>
            <div class="d-flex gap-2 mb-3">
                <a href="listar.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a Facturas
                </a>
                <a href="pagos.php" class="btn btn-outline-warning">
                    <i class="fas fa-money-bill-wave"></i> Facturas Pendientes
                </a>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Facturas</h6>
                    <h2 class="text-primary mb-0"><?= $total_facturas ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h6 class="text-muted">Monto Total</h6>
                    <h4 class="text-info mb-0">Q <?= number_format($suma_total, 2) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Pagado</h6>
                    <h4 class="text-success mb-0">Q <?= number_format($suma_pagado, 2) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h6 class="text-muted">Saldo Pendiente</h6>
                    <h4 class="text-warning mb-0">Q <?= number_format($suma_saldo, 2) ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filtros</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado_codigo" class="form-select">
                        <option value="">-- Todos --</option>
                        <?php foreach ($estados as $est): ?>
                            <option value="<?= $est['codigo'] ?>" <?= $filtro_estado == $est['codigo'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($est['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
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
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="historial_pagos.php" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Facturas -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="fas fa-list"></i> Historial de Facturas (<?= $total_facturas ?>)
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
                            <th class="text-center">Pagos</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($facturas)): ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No se encontraron facturas
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($facturas as $f): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($f['numero_factura']) ?></strong></td>
                                    <td>
                                        <?= htmlspecialchars($f['cliente_nombre']) ?><br>
                                        <small class="text-muted">NIT: <?= htmlspecialchars($f['cliente_nit']) ?></small>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($f['fecha_emision'])) ?></td>
                                    <td>
                                        <?php if (!empty($f['fecha_vencimiento'])): ?>
                                            <?= date('d/m/Y', strtotime($f['fecha_vencimiento'])) ?>
                                            <?php if ($f['dias_vencidos'] > 0 && $f['saldo_pendiente'] > 0): ?>
                                                <br><span class="badge bg-danger" style="font-size: 0.7rem;">
                                                    Vencida (<?= $f['dias_vencidos'] ?> días)
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Contado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">Q <?= number_format($f['total'], 2) ?></td>
                                    <td class="text-end text-success">
                                        Q <?= number_format($f['total_pagado'], 2) ?>
                                    </td>
                                    <td class="text-end <?= $f['saldo_pendiente'] > 0 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                        Q <?= number_format($f['saldo_pendiente'], 2) ?>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: <?= $f['estado_color'] ?>;">
                                            <?= htmlspecialchars($f['estado']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($f['cantidad_pagos'] > 0): ?>
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="verPagos(<?= $f['id'] ?>, '<?= htmlspecialchars($f['numero_factura']) ?>')">
                                                <i class="fas fa-list"></i> <?= $f['cantidad_pagos'] ?>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">Sin pagos</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($f['saldo_pendiente'] > 0 && $f['estado_codigo'] != 'ANU'): ?>
                                            <a href="registrar_pago.php?id=<?= $f['id'] ?>" 
                                               class="btn btn-success btn-sm">
                                                <i class="fas fa-hand-holding-usd"></i> Pagar
                                            </a>
                                        <?php endif; ?>
                                        <a href="ver.php?id=<?= $f['id'] ?>" 
                                           class="btn btn-info btn-sm">
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

<!-- Modal para Ver Pagos -->
<div class="modal fade" id="modalPagos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-pagos">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-list"></i> Historial de Pagos - <span id="modal-factura-numero"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-pagos-content">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function verPagos(facturaId, numeroFactura) {
    document.getElementById('modal-factura-numero').textContent = numeroFactura;
    const modal = new bootstrap.Modal(document.getElementById('modalPagos'));
    modal.show();
    
    // Cargar pagos vía AJAX (ruta relativa desde Vista/Facturacion/)
    fetch('../../Controlador/Facturacion/obtener_pagos.php?factura_id=' + facturaId)
        .then(response => response.json())
        .then(data => {
            let html = '';
            
            if (data.success && data.pagos.length > 0) {
                html = `
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Forma de Pago</th>
                                    <th>Referencia</th>
                                    <th class="text-end">Monto</th>
                                    <th>Usuario</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                data.pagos.forEach(pago => {
                    html += `
                        <tr>
                            <td>${pago.numero_pago}</td>
                            <td>${new Date(pago.fecha_pago).toLocaleDateString('es-GT')}</td>
                            <td>${pago.forma_pago_nombre}</td>
                            <td>${pago.referencia || '-'}</td>
                            <td class="text-end fw-bold">Q ${parseFloat(pago.monto).toFixed(2)}</td>
                            <td>${pago.usuario_nombre}</td>
                            <td>${pago.observaciones || '-'}</td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                html = '<div class="alert alert-info">No se encontraron pagos</div>';
            }
            
            document.getElementById('modal-pagos-content').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('modal-pagos-content').innerHTML = 
                '<div class="alert alert-danger">Error al cargar los pagos</div>';
        });
}
</script>
</body>
</html>