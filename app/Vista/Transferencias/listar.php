<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../Controlador/TransferenciasControlador.php';
require_once '../../../includes/sidebar.php';

$controlador = new TransferenciasControlador();

try {
    $transferencias   = $controlador->listar();
    $almacenesUsuario = $controlador->obtenerAlmacenes();
} catch (Exception $e) {
    $transferencias = $almacenesUsuario = [];
    $error = $e->getMessage();
}

$mensaje = $_GET['mensaje'] ?? '';
$error   = $_GET['error']   ?? ($error ?? '');
?>

<div class="container mt-4">
    <h2 class="mb-4">📦 Transferencias entre Almacenes</h2>

    <!-- Panel de almacenes -->
    <?php if (!empty($almacenesUsuario)): ?>
        <div class="card border-primary mb-4">
            <div class="card-header bg-primary text-white d-flex align-items-center">
                <span class="me-2">🏢</span> Tus Almacenes Asignados
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($almacenesUsuario as $a): ?>
                        <span class="badge bg-light text-dark border px-3 py-2">
                            <?= htmlspecialchars($a['codigo']) ?> - <?= htmlspecialchars($a['nombre']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
                <p class="mb-0 mt-2 text-muted"><small>ℹ️ Solo puedes crear y ver transferencias entre estos almacenes.</small></p>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">⚠️ No tienes almacenes asignados. Contacta al administrador.</div>
    <?php endif; ?>

    <!-- Mensajes -->
    <?php foreach (['guardado'=>'success','actualizada'=>'success','autorizada'=>'success',
                     'enviada'=>'success','recibida'=>'success','cancelada'=>'warning'] as $key=>$type):
            if ($mensaje === $key): ?>
            <div class="alert alert-<?= $type ?>">✅ <strong>¡Éxito!</strong> Transferencia <?= str_replace('_',' ',$key) ?>.</div>
    <?php endif; endforeach; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger">❌ <strong>Error:</strong> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Estadísticas rápidas -->
    <?php if (!empty($transferencias)):
          $total = count($transferencias);
          $stats = array_count_values(array_column($transferencias,'estado'));
    ?>
        <div class="row mb-4 text-center">
            <?php foreach (['Total'=>$total,'Solicitadas'=>$stats['Solicitada']??0,
                            'Enviadas'=>$stats['Enviada']??0,'Recibidas'=>$stats['Recibida']??0] as $label=>$num): ?>
                <div class="col">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <div class="h4 mb-0 text-primary"><?= $num ?></div>
                            <small class="text-muted"><?= $label ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Botón nueva -->
    <div class="mb-3">
        <?php if (!empty($almacenesUsuario)): ?>
            <a href="nuevo.php" class="btn btn-primary">➕ Nueva Transferencia</a>
        <?php else: ?>
            <button class="btn btn-primary" disabled title="Sin almacenes asignados">➕ Nueva Transferencia</button>
        <?php endif; ?>
    </div>

    <!-- Listado -->
    <?php if (empty($transferencias)): ?>
        <div class="text-center py-5 text-muted">
            <div class="mb-2" style="font-size:48px">📭</div>
            <p class="lead">No hay transferencias disponibles</p>
            <p><?= empty($almacenesUsuario)?'Necesitas almacenes asignados.':'Las transferencias aparecerán aquí.' ?></p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th><th>Número</th><th>Almacén Origen</th><th>Almacén Destino</th>
                        <th>Estado</th><th>Fecha Solicitud</th><th>Usuario Solicita</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transferencias as $t):
                          $est = htmlspecialchars($t['estado']);
                          $badge = match(strtolower($est)){
                              'solicitada'=>'warning','autorizada'=>'info','enviada'=>'secondary',
                              'recibida'=>'success','cancelada'=>'danger',default=>'secondary'};
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($t['id']) ?></td>
                            <td><span class="fw-bold text-primary"><?= htmlspecialchars($t['numero_transferencia']) ?></span></td>
                            <td><?= htmlspecialchars($t['almacen_origen']) ?></td>
                            <td><?= htmlspecialchars($t['almacen_destino']) ?></td>
                            <td><span class="badge bg-<?= $badge ?>"><?= $est ?></span></td>
                            <td><?= date('d/m/Y H:i', strtotime($t['fecha_solicitud'])) ?></td>
                            <td><?= htmlspecialchars($t['usuario_solicita'] ?? 'N/A') ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="ver.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                        👁️
                                    </a>
                                    <?php if ($t['estado'] === 'Solicitada'): ?>
                                        <a href="editar.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                            ✏️
                                        </a>
                                    <?php endif; ?>
                                    <a href="imprimir.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-success" title="Imprimir PDF" target="_blank">
                                        🖨️
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>