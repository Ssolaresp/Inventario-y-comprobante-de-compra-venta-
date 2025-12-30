<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/EntradasAlmacenControlador.php';
require_once '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(6); // Ajusta el rol que corresponda

$controlador = new EntradasAlmacenControlador();
$entradas = $controlador->listar();

$mensaje = $_GET['mensaje'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="container mt-4">
    <h2 class="mb-4">📥 Entradas de Almacén</h2>

    <!-- Mensajes -->
    <?php if ($mensaje): ?>
        <div class="alert alert-success alert-dismissible fade show">
            ✅ <strong>¡Éxito!</strong> 
            <?php
                echo match($mensaje) {
                    'creada' => 'Entrada creada exitosamente',
                    'autorizada' => 'Entrada autorizada y procesada',
                    'cancelada' => 'Entrada cancelada',
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

    <!-- Botón nueva entrada -->
    <div class="mb-3">
        <a href="nuevo.php" class="btn btn-success">➕ Nueva Entrada</a>
    </div>

    <!-- Tabla -->
    <?php if (empty($entradas)): ?>
        <div class="text-center py-5 text-muted">
            <div class="mb-2" style="font-size:48px">📭</div>
            <p class="lead">No hay entradas registradas</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-success">
                    <tr>
                        <th>ID</th>
                        <th>Número</th>
                        <th>Almacén</th>
                        <th>Tipo Entrada</th>
                        <th>Fecha</th>
                        <th>Doc. Ref.</th>
                        <th>Estado</th>
                        <th>Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entradas as $e): 
                        $badge = match($e['estado']) {
                            'Registrada' => 'warning',
                            'Autorizada' => 'success',
                            'Cancelada' => 'danger',
                            default => 'secondary'
                        };
                    ?>
                        <tr>
                            <td><?= $e['id'] ?></td>
                            <td><span class="fw-bold text-success"><?= htmlspecialchars($e['numero_entrada']) ?></span></td>
                            <td><?= htmlspecialchars($e['almacen']) ?></td>
                            <td><?= htmlspecialchars($e['tipo_entrada']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($e['fecha_entrada'])) ?></td>
                            <td><?= htmlspecialchars($e['documento_referencia']) ?></td>
                            <td><span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($e['estado']) ?></span></td>
                            <td><?= htmlspecialchars($e['usuario_registra']) ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="ver.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver">👁️</a>
                                    
                                    <?php if ($e['estado'] === 'Registrada'): ?>
                                        <a href="procesar.php?accion=autorizar&id=<?= $e['id'] ?>" 
                                           class="btn btn-sm btn-outline-success" 
                                           title="Autorizar"
                                           onclick="return confirm('¿Autorizar esta entrada?')">✅</a>
                                        
                                        <a href="procesar.php?accion=cancelar&id=<?= $e['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           title="Cancelar"
                                           onclick="return confirm('¿Cancelar esta entrada?')">❌</a>
                                    <?php endif; ?>
                                    
                                    <a href="imprimir.php?id=<?= $e['id'] ?>" 
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