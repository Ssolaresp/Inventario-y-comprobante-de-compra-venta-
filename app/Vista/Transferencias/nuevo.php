<?php
require_once '../../Controlador/TransferenciasControlador.php';
include '../../../includes/inicio.php';

$controlador = new TransferenciasControlador();
$almacenes   = $controlador->obtenerAlmacenes();
$productos   = $controlador->obtenerProductos();
/*
session_start();

*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario_actual = $_SESSION['nombre_usuario'] ?? 'Desconocido';

// ✅ Usamos el método público del controlador
$numero_transferencia = $controlador->generarNumeroTransferencia();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Transferencia</title>
    <style>
        .producto-item {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
        }
        .stock-info {
            margin-left: 10px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>

<h2>➕ Nueva Transferencia</h2>

<div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 20px; background-color: #f9f9f9;">
    <strong>Número de Transferencia:</strong>
    <span style="color: #007bff;"><?= htmlspecialchars($numero_transferencia) ?></span><br>
    <strong>Usuario Solicita:</strong> <?= htmlspecialchars($usuario_actual) ?><br>
    <strong>Fecha de Solicitud:</strong> <?= date('d/m/Y H:i') ?><br>
    <strong>Estado Inicial:</strong> <span style="color: orange;">Solicitada</span>
</div>

<form action="guardar.php" method="POST" id="formTransferencia">
    <h3>📋 Datos Generales</h3>

    <label>Almacén Origen:</label><br>
    <select name="almacen_origen_id" id="almacen_origen" required>
        <option value="">-- Seleccione --</option>
        <?php foreach ($almacenes as $a): ?>
            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['codigo'] . ' - ' . $a['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Almacén Destino:</label><br>
    <select name="almacen_destino_id" id="almacen_destino" required>
        <option value="">-- Seleccione --</option>
        <?php foreach ($almacenes as $a): ?>
            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['codigo'] . ' - ' . $a['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Observaciones:</label><br>
    <textarea name="observaciones" rows="3"></textarea><br><br>

    <hr>

    <h3>📦 Productos a Transferir</h3>
    <div id="productos-container">
        <div class="producto-item">
            <label>Producto:</label>
            <select name="productos[0][producto_id]" class="producto-select" required>
                <option value="">-- Seleccione --</option>
                <?php foreach ($productos as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['codigo'] . ' - ' . $p['nombre']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Cantidad:</label>
            <input type="number" step="0.01" name="productos[0][cantidad]" class="cantidad-input" required min="0.01">
            <span class="stock-info"></span>

            <label>Observaciones:</label>
            <input type="text" name="productos[0][observaciones]">
            <br><br>
        </div>
    </div>

    <button type="button" onclick="agregarProducto()">➕ Agregar Producto</button>
    <br><br>

    <button type="submit">💾 Crear Transferencia</button>
</form>

<br>
<a href="listar.php">⬅️ Volver al listado</a>

<script>
let contadorProductos = 1;

function agregarProducto() {
    const container = document.getElementById('productos-container');
    const nuevoProducto = `
        <div class="producto-item">
            <label>Producto:</label>
            <select name="productos[${contadorProductos}][producto_id]" class="producto-select" required>
                <option value="">-- Seleccione --</option>
                <?php foreach ($productos as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['codigo'] . ' - ' . $p['nombre']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Cantidad:</label>
            <input type="number" step="0.01" name="productos[${contadorProductos}][cantidad]" class="cantidad-input" required min="0.01">
            <span class="stock-info"></span>

            <label>Observaciones:</label>
            <input type="text" name="productos[${contadorProductos}][observaciones]">

            <button type="button" onclick="this.parentElement.remove()">❌ Eliminar</button>
            <br><br>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', nuevoProducto);
    contadorProductos++;
    aplicarListeners();
}

function aplicarListeners() {
    const almacenOrigen = document.getElementById('almacen_origen');
    const productosSelects = document.querySelectorAll('.producto-select');
    
    productosSelects.forEach(select => {
        select.addEventListener('change', function() {
            const productoId = this.value;
            const almacenId = almacenOrigen.value;
            const stockSpan = this.parentElement.querySelector('.stock-info');
            
            if (productoId && almacenId) {
                fetch(`consultar_stock.php?producto_id=${productoId}&almacen_id=${almacenId}`)
                    .then(res => res.json())
                    .then(data => {
                        stockSpan.innerHTML = `<strong style="color: ${data.stock > 0 ? 'green' : 'red'};">Stock disponible: ${data.stock}</strong>`;
                    });
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', aplicarListeners);
</script>

</body>
</html>