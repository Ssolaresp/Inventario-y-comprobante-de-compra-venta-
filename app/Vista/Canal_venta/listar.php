<?php
require_once '../../Controlador/CanalesVentaControlador.php';
include '../../../includes/inicio.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit();
}

$controlador = new CanalesVentaControlador();
$canales = $controlador->listar();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between mb-3">
        <h4>Listado de Canales de Venta</h4>
        <a href="nuevo.php" class="btn btn-primary">+ Nuevo Canal</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Fecha Registro</th>
                <th>Creado</th>
               
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($canales as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['id_canal']) ?></td>
                <td><?= htmlspecialchars($c['codigo_canal']) ?></td>
                <td><?= htmlspecialchars($c['nombre_canal']) ?></td>
                <td><?= htmlspecialchars($c['descripcion']) ?></td>
                <td><?= htmlspecialchars($c['estado']) ?></td>
                <td><?= htmlspecialchars($c['fecha_registro']) ?></td>
                <td><?= htmlspecialchars($c['creado_en']) ?></td>
      
                <td>
                    <a href="editar.php?id=<?= $c['id_canal'] ?>" class="btn btn-sm btn-warning">Editar</a>
              </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

