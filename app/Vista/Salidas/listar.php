<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/SalidasAlmacenControlador.php';
require_once '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

$controlador = new SalidasAlmacenControlador();
$salidas = $controlador->listar();

$mensaje = $_GET['mensaje'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="container mt-4">
    <h2 class="mb-4">📤 Salidas de Almacén</h2>

    <!-- Mensajes -->
    <?php if ($mensaje): ?>
        <div class="alert alert-success alert-dismissible fade show">
            ✅ <strong>¡Éxito!</strong> 
            <?php
                echo match($mensaje) {
                    'creada' => 'Salida creada exitosamente',
                    'autorizada' => 'Salida autorizada y procesada',
                    'cancelada' => 'Salida cancelada',
                    default => 'Operación exitosa'
                };
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            ❌ <strong>Error:</strong> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Botón nueva salida -->
    <div class="mb-3">
        <a href="nuevo.php" class="btn btn-primary">➕ Nueva Salida</a>
    </div>

    <!-- Tabla -->
    <?php if (empty($salidas)): ?>
        <div class="text-center py-5 text-muted">
            <div class="mb-2" style="font-size:48px">📭</div>
            <p class="lead">No hay salidas registradas</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-danger">
                    <tr>
                        <th>ID</th>
                        <th>Número</th>
                        <th>Almacén</th>
                        <th>Tipo Salida</th>
                        <th>Fecha</th>
                        <th>Doc. Ref.</th>
                        <th>Estado</th>
                        <th>Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($salidas as $s): 
                        $badge = match($s['estado']) {
                            'Registrada' => 'warning',
                            'Autorizada' => 'success',
                            'Cancelada' => 'danger',
                            default => 'secondary'
                        };
                    ?>
                        <tr>
                            <td><?= $s['id'] ?></td>
                            <td><span class="fw-bold text-danger"><?= htmlspecialchars($s['numero_salida']) ?></span></td>
                            <td><?= htmlspecialchars($s['almacen']) ?></td>
                            <td><?= htmlspecialchars($s['tipo_salida']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($s['fecha_salida'])) ?></td>
                            <td><?= htmlspecialchars($s['documento_referencia']) ?></td>
                            <td><span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($s['estado']) ?></span></td>
                            <td><?= htmlspecialchars($s['usuario_registra']) ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="ver.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver">👁️</a>
                                    
                                    <?php if ($s['estado'] === 'Registrada'): ?>
                                        <a href="procesar.php?accion=autorizar&id=<?= $s['id'] ?>" 
                                           class="btn btn-sm btn-outline-success" 
                                           title="Autorizar"
                                           onclick="return confirm('¿Autorizar esta salida?')">✅</a>
                                        
                                        <a href="procesar.php?accion=cancelar&id=<?= $s['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           title="Cancelar"
                                           onclick="return confirm('¿Cancelar esta salida?')">❌</a>
                                    <?php endif; ?>
                                    
                                    <a href="imprimir.php?id=<?= $s['id'] ?>" 
                                       class="btn btn-sm btn-outline-info" 
                                       title="Imprimir PDF" 
                                       target="_blank">🖨️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>