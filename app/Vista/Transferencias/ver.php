<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../Controlador/TransferenciasControlador.php';
require_once '../../../includes/sidebar.php';

$controlador = new TransferenciasControlador();
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: listar.php?error=" . urlencode("ID de transferencia no válido"));
    exit();
}

try {
    $transferencia = $controlador->obtener($id);
    $detalle       = $controlador->obtenerDetalle($id);
    if (!$transferencia) { throw new Exception("Transferencia no encontrada"); }
} catch (Exception $e) {
    header("Location: listar.php?error=" . urlencode($e->getMessage()));
    exit();
}

$mensaje = $_GET['mensaje'] ?? '';
$error   = $_GET['error']   ?? '';
?>

<div class="container mt-4">
    <h2 class="mb-4">👁️ Ver Transferencia: <span class="text-primary"><?= htmlspecialchars($transferencia['numero_transferencia']) ?></span></h2>

    <?php if ($mensaje === 'actualizada'): ?>
        <div class="alert alert-success">✅ <strong>¡Éxito!</strong> Transferencia actualizada correctamente</div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger">❌ <strong>Error:</strong> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- INFORMACIÓN GENERAL -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">📋 Información General</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4"><strong>Estado</strong>
                    <?php $estado = htmlspecialchars($transferencia['estado']);
                          $badge  = match(strtolower($estado)){
                              'solicitada'=>'warning','autorizada'=>'info','enviada'=>'secondary',
                              'recibida'=>'success','cancelada'=>'danger',default=>'light'};
                    ?>
                    <br><span class="badge bg-<?= $badge ?>"><?= $estado ?></span>
                </div>
                <div class="col-md-4"><strong>Número</strong>
                    <br><span class="text-primary fw-bold"><?= htmlspecialchars($transferencia['numero_transferencia']) ?></span>
                </div>
                <div class="col-md-4"><strong>📤 Almacén Origen</strong>
                    <br><?= htmlspecialchars($transferencia['almacen_origen']) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>📥 Almacén Destino</strong>
                    <br><?= htmlspecialchars($transferencia['almacen_destino']) ?>
                </div>
                <div class="col-md-4"><strong>📅 Fecha Solicitud</strong>
                    <br><?= date('d/m/Y H:i', strtotime($transferencia['fecha_solicitud'])) ?>
                </div>
                <?php if ($transferencia['fecha_autorizacion']): ?>
                <div class="col-md-4"><strong>✅ Fecha Autorización</strong>
                    <br><?= date('d/m/Y H:i', strtotime($transferencia['fecha_autorizacion'])) ?>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($transferencia['fecha_envio']): ?>
            <div class="row mb-3">
                <div class="col-md-4"><strong>📤 Fecha Envío</strong>
                    <br><?= date('d/m/Y H:i', strtotime($transferencia['fecha_envio'])) ?>
                </div>
                <?php if ($transferencia['fecha_recepcion']): ?>
                <div class="col-md-4"><strong>📥 Fecha Recepción</strong>
                    <br><?= date('d/m/Y H:i', strtotime($transferencia['fecha_recepcion'])) ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php if ($transferencia['observaciones']): ?>
            <div class="row">
                <div class="col-12"><strong>📝 Observaciones</strong>
                    <br><?= nl2br(htmlspecialchars($transferencia['observaciones'])) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- PRODUCTOS -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">📦 Productos</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>Código</th><th>Producto</th><th>Cantidad Enviada</th>
                            <th>Cantidad Recibida</th><th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalle as $item):
                              $dif = $item['cantidad'] - ($item['cantidad_recibida'] ?? 0);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($item['codigo_producto']) ?></td>
                            <td><?= htmlspecialchars($item['producto']) ?></td>
                            <td><strong><?= number_format($item['cantidad'],2) ?></strong></td>
                            <td>
                                <?php if ($item['cantidad_recibida'] !== null): ?>
                                    <strong class="text-success"><?= number_format($item['cantidad_recibida'],2) ?></strong>
                                    <?php if ($dif != 0): ?>
                                        <br><span class="text-danger fw-bold">Dif: <?= number_format($dif,2) ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($item['observaciones'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ACCIONES - AHORA CON VALIDACIÓN DE PERMISOS -->
    <div class="card mb-4">
        <div class="card-body">
            <?php if ($transferencia['estado'] === 'Solicitada'): ?>
                <!-- Solo usuarios del almacén ORIGEN pueden autorizar, editar y cancelar -->
                <?php if ($transferencia['puede_autorizar']): ?>
                    <form action="acciones.php" method="POST" class="d-inline">
                        <input type="hidden" name="accion" value="autorizar">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button class="btn btn-success btn-sm" onclick="return confirm('¿Autorizar esta transferencia?')">✅ Autorizar</button>
                    </form>
                <?php endif; ?>
                
                <?php if ($transferencia['puede_editar']): ?>
                    <a href="editar.php?id=<?= $id ?>" class="btn btn-warning btn-sm">✏️ Editar</a>
                <?php endif; ?>
                
                <?php if ($transferencia['puede_cancelar']): ?>
                    <form action="acciones.php" method="POST" class="d-inline">
                        <input type="hidden" name="accion" value="cancelar">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button class="btn btn-danger btn-sm" onclick="return confirm('¿Cancelar esta transferencia?')">❌ Cancelar</button>
                    </form>
                <?php endif; ?>
                
                <!-- Si no tiene permisos, mostrar mensaje -->
                <?php if (!$transferencia['puede_autorizar'] && !$transferencia['puede_editar']): ?>
                    <div class="alert alert-info mb-0">
                        ℹ️ Esta transferencia debe ser autorizada por el almacén de origen.
                    </div>
                <?php endif; ?>

            <?php elseif ($transferencia['estado'] === 'Autorizada'): ?>
                <!-- Solo usuarios del almacén ORIGEN pueden enviar -->
                <?php if ($transferencia['puede_enviar']): ?>
                    <form action="acciones.php" method="POST" class="d-inline">
                        <input type="hidden" name="accion" value="enviar">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button class="btn btn-info btn-sm" onclick="return confirm('¿Enviar esta transferencia? Se restará del inventario origen.')">📤 Enviar</button>
                    </form>
                <?php endif; ?>
                
                <?php if ($transferencia['puede_cancelar']): ?>
                    <form action="acciones.php" method="POST" class="d-inline">
                        <input type="hidden" name="accion" value="cancelar">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button class="btn btn-danger btn-sm" onclick="return confirm('¿Cancelar esta transferencia?')">❌ Cancelar</button>
                    </form>
                <?php endif; ?>
                
                <?php if (!$transferencia['puede_enviar']): ?>
                    <div class="alert alert-info mb-0">
                        ℹ️ Esta transferencia debe ser enviada por el almacén de origen.
                    </div>
                <?php endif; ?>

            <?php elseif ($transferencia['estado'] === 'Enviada'): ?>
                <!-- ✅ SOLO usuarios del almacén DESTINO que NO sean el solicitante pueden recibir -->
                <?php if ($transferencia['puede_recibir']): ?>
                    <div class="alert alert-warning p-3">
                        <h5 class="mb-3">📥 Recibir Transferencia</h5>
                        <p class="mb-3">Ajusta las cantidades recibidas si es necesario. Por defecto se recibirán las cantidades enviadas.</p>
                        <form action="acciones.php" method="POST">
                            <input type="hidden" name="accion" value="recibir">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle">
                                    <thead class="table-light">
                                        <tr><th>Producto</th><th>Cantidad Enviada</th><th>Cantidad a Recibir</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($detalle as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['producto']) ?></td>
                                            <td><?= number_format($item['cantidad'],2) ?></td>
                                            <td>
                                                <input type="number" step="0.01" name="cantidades[<?= $item['id'] ?>]"
                                                       value="<?= htmlspecialchars($item['cantidad']) ?>"
                                                       max="<?= htmlspecialchars($item['cantidad']) ?>" min="0" required
                                                       class="form-control form-control-sm d-inline" style="width:120px">
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button class="btn btn-success btn-sm" onclick="return confirm('¿Confirmar recepción? Se sumará al inventario destino.')">📥 Confirmar Recepción</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <?php if ($transferencia['usuario_solicita_id'] == $_SESSION['usuario_id']): ?>
                            ℹ️ No puedes recibir una transferencia que tú mismo creaste. Debe ser recibida por otro usuario del almacén destino.
                        <?php else: ?>
                            ℹ️ Esta transferencia está en tránsito. Solo el almacén de destino puede recibirla.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php elseif ($transferencia['estado'] === 'Recibida'): ?>
                <div class="alert alert-success mb-0">
                    ✅ Transferencia completada exitosamente
                </div>

            <?php elseif ($transferencia['estado'] === 'Cancelada'): ?>
                <div class="alert alert-danger mb-0">
                    ❌ Esta transferencia ha sido cancelada
                </div>
            <?php endif; ?>

            <a href="listar.php" class="btn btn-secondary btn-sm mt-2">⬅️ Volver al Listado</a>
        </div>
    </div>
</div>