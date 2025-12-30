<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/SalidasAlmacenControlador.php';
require_once '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

// Ajusta el ID del permiso que corresponda al módulo de salidas
verificarAcceso(1);

$controlador = new SalidasAlmacenControlador();
$almacenes = $controlador->obtenerAlmacenes();
$productos = $controlador->obtenerProductos();
$tipos_salida = $controlador->obtenerTiposSalida();

$usuario_actual = $_SESSION['nombre_usuario'] ?? 'Desconocido';
$numero_salida = $controlador->generarNumeroSalida();
?>

<!-- ################  ESTILOS  ################ -->
<style>
    .producto-item {
        border: 1px solid #ddd;
        padding: 10px;
        margin-bottom: 10px;
        background-color: #f9f9f9;
    }
    table {
        width: 100%;
        margin-top: 10px;
    }
    table td {
        padding: 5px;
    }
    .precio-info, .stock-info {
        display: block;
        margin-top: 3px;
        font-size: 0.85em;
    }
    .precio-input:disabled, .cantidad-input:disabled {
        background-color: #f0f0f0;
        cursor: wait;
    }
</style>

<!-- ################  TÍTULO  ################ -->
<h2>➕ Nueva Salida de Almacén</h2>

<!-- ################  INFO GENERAL  ################ -->
<div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 20px; background-color: #f9f9f9;">
    <strong>Número de Salida:</strong> <?= htmlspecialchars($numero_salida) ?><br>
    <strong>Usuario Registra:</strong> <?= htmlspecialchars($usuario_actual) ?><br>
    <strong>Fecha de Registro:</strong> <?= date('d/m/Y H:i') ?><br>
    <strong>Estado Inicial:</strong> Registrada
</div>

<!-- ################  FORMULARIO  ################ -->
<form action="guardar.php" method="POST" id="formSalida">
    <h3>📋 Datos Generales</h3>

    <label>Tipo de Salida:</label><br>
    <select name="tipo_salida_id" required>
        <option value="">-- Seleccione --</option>
        <?php foreach ($tipos_salida as $tipo): ?>
            <option value="<?= $tipo['id'] ?>">
                <?= htmlspecialchars($tipo['nombre']) ?>
                <?= $tipo['requiere_autorizacion'] ? '(Requiere autorización)' : '(Automática)' ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Almacén Origen:</label><br>
    <select name="almacen_id" id="almacen_origen" required>
        <option value="">-- Seleccione --</option>
        <?php foreach ($almacenes as $a): ?>
            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['codigo'] . ' - ' . $a['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Documento de Referencia:</label><br>
    <input type="text" name="documento_referencia" placeholder="Factura, orden, etc."><br><br>

    <label>Motivo:</label><br>
    <textarea name="motivo" rows="3"></textarea><br><br>

    <hr>

    <h3>📦 Productos a Sacar</h3>
    <div id="productos-container">
        <div class="producto-item">
            <table>
                <tr>
                    <td width="35%">
                        <label>Producto:</label><br>
                        <select name="productos[0][producto_id]" class="producto-select" required>
                            <option value="">-- Seleccione --</option>
                            <?php foreach ($productos as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['codigo'] . ' - ' . $p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td width="20%">
                        <label>Cantidad:</label><br>
                        <input type="number" step="0.01" name="productos[0][cantidad]" class="cantidad-input" required min="0.01">
                        <small class="stock-info"></small>
                    </td>
                    <td width="20%">
                        <label>Precio Unitario:</label><br>
                        <input type="number" step="0.01" name="productos[0][precio_unitario]" class="precio-input" required min="0">
                        <small class="precio-info"></small>
                    </td>
                    <td width="25%">
                        <label>Observaciones:</label><br>
                        <input type="text" name="productos[0][observaciones]">
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <button type="button" onclick="agregarProducto()">➕ Agregar Producto</button>
    <br><br>

    <button type="submit">💾 Registrar Salida</button>
</form>

<br>
<a href="listar.php">⬅️ Volver al listado</a>

<!-- ################  JAVASCRIPT  ################ -->
<script>
        let contadorProductos = 1;

        function agregarProducto() {
            const container = document.getElementById('productos-container');
            const nuevoProducto = `
                <div class="producto-item">
                    <table>
                        <tr>
                            <td width="35%">
                                <label>Producto:</label><br>
                                <select name="productos[${contadorProductos}][producto_id]" class="producto-select" required>
                                    <option value="">-- Seleccione --</option>
                                    <?php foreach ($productos as $p): ?>
                                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['codigo'] . ' - ' . $p['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td width="20%">
                                <label>Cantidad:</label><br>
                                <input type="number" step="0.01" name="productos[${contadorProductos}][cantidad]" class="cantidad-input" required min="0.01">
                                <small class="stock-info"></small>
                            </td>
                            <td width="20%">
                                <label>Precio Unitario:</label><br>
                                <input type="number" step="0.01" name="productos[${contadorProductos}][precio_unitario]" class="precio-input" required min="0">
                                <small class="precio-info"></small>
                            </td>
                            <td width="25%">
                                <label>Observaciones:</label><br>
                                <input type="text" name="productos[${contadorProductos}][observaciones]">
                            </td>
                        </tr>
                    </table>
                    <button type="button" onclick="this.parentElement.remove()">❌ Eliminar</button>
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
                const newSelect = select.cloneNode(true);
                select.parentNode.replaceChild(newSelect, select);
                
                newSelect.addEventListener('change', function () {
                    const productoId = this.value;
                    const almacenId = almacenOrigen.value;
                    const container = this.closest('.producto-item');
                    const precioInput = container.querySelector('.precio-input');
                    const cantidadInput = container.querySelector('.cantidad-input');
                    const precioInfo = container.querySelector('.precio-info');
                    const stockInfo = container.querySelector('.stock-info');
                    
                    if (!almacenId) {
                        alert('Primero selecciona un almacén');
                        this.value = '';
                        return;
                    }
                    
                    if (productoId) {
                        precioInfo.innerHTML = '<span style="color: #999;">Cargando precio...</span>';
                        stockInfo.innerHTML = '<span style="color: #999;">Consultando stock...</span>';
                        precioInput.disabled = true;
                        cantidadInput.disabled = true;
                        
                        fetch(`obtener_precio.php?producto_id=${productoId}`)
                            .then(res => res.json())
                            .then(data => {
                                if (data.success && data.encontrado) {
                                    precioInput.value = parseFloat(data.precio).toFixed(2);
                                    precioInfo.innerHTML = `<span style="color: green;">✓ Precio sugerido (${data.tipo_precio || 'Lista de precios'})</span>`;
                                } else {
                                    precioInput.value = '0.00';
                                    precioInfo.innerHTML = '<span style="color: orange;">⚠ Sin precio en lista</span>';
                                }
                                precioInput.disabled = false;
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                precioInput.value = '0.00';
                                precioInfo.innerHTML = '<span style="color: red;">✗ Error al obtener precio</span>';
                                precioInput.disabled = false;
                            });
                        
                        fetch(`consultar_stock.php?producto_id=${productoId}&almacen_id=${almacenId}`)
                            .then(res => res.json())
                            .then(data => {
                                const stock = data.stock || 0;
                                const color = stock > 0 ? 'green' : 'red';
                                stockInfo.innerHTML = `<strong style="color: ${color};">Stock disponible: ${stock}</strong>`;
                                cantidadInput.disabled = false;
                                if (stock > 0) {
                                    cantidadInput.max = stock;
                                    cantidadInput.focus();
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                stockInfo.innerHTML = '<span style="color: red;">✗ Error al consultar stock</span>';
                                cantidadInput.disabled = false;
                            });
                    } else {
                        precioInput.value = '';
                        cantidadInput.value = '';
                        precioInfo.innerHTML = '';
                        stockInfo.innerHTML = '';
                        precioInput.disabled = false;
                        cantidadInput.disabled = false;
                    }
                });
            });
        }

        document.addEventListener('DOMContentLoaded', aplicarListeners);
</script>