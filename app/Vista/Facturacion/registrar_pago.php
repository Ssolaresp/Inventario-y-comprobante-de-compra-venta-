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
/*
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {


*/


if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
    
    die('<div class="alert alert-danger">❌ Error: Sesión inválida.</div>');
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: pagos.php?error=ID no especificado');
    exit;
}

$controlador = new PagosControlador();
$factura = $controlador->obtenerFactura($id);
$formas_pago = $controlador->obtenerFormasPago();
$pagos_anteriores = $controlador->obtenerPagosFactura($id);

if (!$factura) {
    header('Location: pagos.php?error=Factura no encontrada');
    exit;
}

$saldo_pendiente = (float)$factura['saldo_pendiente'];

if ($saldo_pendiente <= 0) {
    header('Location: pagos.php?error=Esta factura ya está pagada');
    exit;
}

// Procesar el pago
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'fecha_pago' => $_POST['fecha_pago'] ?? date('Y-m-d'),
            'forma_pago_id' => $_POST['forma_pago_id'] ?? null,
            'monto' => (float)($_POST['monto'] ?? 0),
            'referencia' => $_POST['referencia'] ?? null,
            'observaciones' => $_POST['observaciones'] ?? null,
        ];
        
        $resultado = $controlador->registrarPago($id, $data);
        
        if ($resultado['success']) {
            header('Location: pagos.php?mensaje=pago_registrado');
            exit;
        } else {
            throw new Exception($resultado['message']);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Pago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="container mt-4">
    
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">💰 Registrar Pago de Factura</h2>
            <a href="pagos.php" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="fas fa-arrow-left"></i> Volver a Pagos
            </a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Error:</strong> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="row">
        
        <!-- Columna de Información -->
        <div class="col-md-5 mb-4">
            
            <!-- Información de la Factura -->
            <div class="card shadow-sm border-primary mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📄 Información de la Factura</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>No. Factura:</strong> <?= htmlspecialchars($factura['numero_factura']) ?></p>
                    <p class="mb-1"><strong>Cliente:</strong> <?= htmlspecialchars($factura['cliente_nombre']) ?></p>
                    <p class="mb-1"><strong>NIT:</strong> <?= htmlspecialchars($factura['cliente_nit']) ?></p>
                    <p class="mb-1"><strong>Fecha Emisión:</strong> <?= date('d/m/Y', strtotime($factura['fecha_emision'])) ?></p>
                    <p class="mb-1"><strong>Vencimiento:</strong> 
                        <?php if (!empty($factura['fecha_vencimiento'])): ?>
                            <?= date('d/m/Y', strtotime($factura['fecha_vencimiento'])) ?>
                        <?php else: ?>
                            <span class="text-muted">Contado</span>
                        <?php endif; ?>
                    </p>
                    <hr>
                    <p class="mb-1"><strong>Total Factura:</strong> Q <?= number_format($factura['total'], 2) ?></p>
                    <p class="mb-1 text-success"><strong>Total Pagado:</strong> Q <?= number_format($factura['total_pagado'], 2) ?></p>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Saldo Pendiente:</h5>
                        <h3 class="mb-0 text-danger fw-bold">
                            Q <?= number_format($saldo_pendiente, 2) ?>
                        </h3>
                    </div>
                </div>
            </div>

            <!-- Historial de Pagos -->
            <?php if (!empty($pagos_anteriores)): ?>
                <div class="card shadow-sm border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">📋 Historial de Pagos (<?= count($pagos_anteriores) ?>)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha</th>
                                        <th>Forma</th>
                                        <th class="text-end">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pagos_anteriores as $pago): ?>
                                        <tr>
                                            <td><?= $pago['numero_pago'] ?></td>
                                            <td><?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?></td>
                                            <td><?= htmlspecialchars($pago['forma_pago_nombre']) ?></td>
                                            <td class="text-end">Q <?= number_format($pago['monto'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- Formulario de Pago -->
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">💵 Registrar Nuevo Pago</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="formPago">
                        
                        <!-- Monto -->
                        <div class="form-group mb-3">
                            <label for="monto_pago"><strong>Monto a Pagar: <span class="text-danger">*</span></strong></label>
                            <input type="number" step="0.01" name="monto" id="monto_pago" 
                                class="form-control form-control-lg" required min="0.01" 
                                max="<?= $saldo_pendiente ?>" 
                                value="<?= number_format($saldo_pendiente, 2, '.', '') ?>"
                                placeholder="0.00">
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Máximo permitido: Q <?= number_format($saldo_pendiente, 2) ?>
                            </small>
                        </div>

                        <!-- Forma de Pago -->
                        <div class="form-group mb-3">
                            <label for="forma_pago_id"><strong>Forma de Pago: <span class="text-danger">*</span></strong></label>
                            <select name="forma_pago_id" id="forma_pago_id" class="form-select" required>
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($formas_pago as $fp): ?>
                                    <option value="<?= $fp['id'] ?>" data-requiere-ref="<?= $fp['requiere_referencia'] ?>">
                                        <?= htmlspecialchars($fp['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Fecha de Pago -->
                        <div class="form-group mb-3">
                            <label for="fecha_pago"><strong>Fecha de Pago: <span class="text-danger">*</span></strong></label>
                            <input type="date" name="fecha_pago" id="fecha_pago" 
                                class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        
                        <!-- Referencia -->
                        <div class="form-group mb-3">
                            <label for="referencia">
                                Referencia/No. de Documento:
                                <span id="ref_requerida" class="text-danger" style="display:none;">*</span>
                            </label>
                            <input type="text" name="referencia" id="referencia" 
                                class="form-control" placeholder="Ej: Cheque #12345, Transferencia #98765">
                            <small class="text-muted" id="ref_help"></small>
                        </div>
                        
                        <!-- Observaciones -->
                        <div class="form-group mb-4">
                            <label for="observaciones">Observaciones:</label>
                            <textarea name="observaciones" id="observaciones" rows="3" 
                                class="form-control" placeholder="Observaciones adicionales (opcional)"></textarea>
                        </div>
                        
                        <hr>
                        
                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="pagos.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save"></i> Registrar Pago
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const montoInput = document.getElementById('monto_pago');
    const formaPagoSelect = document.getElementById('forma_pago_id');
    const referenciaInput = document.getElementById('referencia');
    const refRequerida = document.getElementById('ref_requerida');
    const refHelp = document.getElementById('ref_help');
    const maxMonto = parseFloat(<?= $saldo_pendiente ?>);
    
    // Validación de monto
    montoInput.addEventListener('input', function() {
        let montoIngresado = parseFloat(this.value);
        
        if (isNaN(montoIngresado) || montoIngresado <= 0) {
            return;
        }

        if (montoIngresado > maxMonto) {
            alert('El monto no puede ser mayor al saldo pendiente (Q ' + maxMonto.toFixed(2) + ')');
            this.value = maxMonto.toFixed(2);
        }
    });

    // Mostrar/ocultar referencia según forma de pago
    formaPagoSelect.addEventListener('change', function() {
        const opcionSeleccionada = this.options[this.selectedIndex];
        const requiereRef = opcionSeleccionada.dataset.requiereRef === '1';
        
        if (requiereRef) {
            referenciaInput.required = true;
            refRequerida.style.display = 'inline';
            refHelp.textContent = 'Este método de pago requiere una referencia';
        } else {
            referenciaInput.required = false;
            refRequerida.style.display = 'none';
            refHelp.textContent = '';
        }
    });

    // Validación antes de enviar
    document.getElementById('formPago').addEventListener('submit', function(e) {
        const monto = parseFloat(montoInput.value);
        
        if (monto > maxMonto) {
            e.preventDefault();
            alert('El monto excede el saldo pendiente');
            return false;
        }
        
        if (monto <= 0) {
            e.preventDefault();
            alert('El monto debe ser mayor a cero');
            return false;
        }

        return confirm('¿Confirma que desea registrar este pago por Q ' + monto.toFixed(2) + '?');
    });
});
</script>
</body>
</html>