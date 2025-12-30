<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



require_once '../../Controlador/EstadoControlador.php';
include '../../../includes/sidebar.php';

require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);



$controlador = new EstadoControlador();
$estados = $controlador->listar();
?>

<h2>📊 Listado de Estados</h2>

<?php if (empty($estados)): ?>
    <p>No hay estados registrados.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%; text-align:center;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Aplica A</th>
                <th>Creado en</th>
                <th>Actualizado en</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($estados as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['id'] ?? '') ?></td>
                    <td><?= htmlspecialchars($e['nombre'] ?? '') ?></td>
                    <td><?= htmlspecialchars($e['descripcion'] ?? '') ?></td>
                    <td><?= htmlspecialchars($e['aplica_a'] ?? '') ?></td>
                    <td><?= htmlspecialchars($e['creado_en'] ?? '') ?></td>
                    <td><?= htmlspecialchars($e['actualizado_en'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>