<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/FacturasControlador.php';
require_once '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

$controlador = new FacturasControlador();
$tipos_factura = $controlador->obtenerTiposFactura();
$clientes = $controlador->obtenerClientes();
$formas_pago = $controlador->obtenerFormasPago();
$almacenes = $controlador->obtenerAlmacenes();
$impuestos = $controlador->obtenerImpuestos();
$listas_precios = $controlador->obtenerListasPrecios();

$usuario_actual = $_SESSION['nombre_usuario'] ?? 'Desconocido';
?>

<!-- ################  ESTILOS  ################ -->
<style>
    .item-linea {
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
    .info-item {
        display: block;
        margin-top: 3px;
        font-size: 0.85em;
    }
    .input-disabled {
        background-color: #f0f0f0;
        cursor: wait;
    }
    .totales-section {
        background-color: #f5f5f5;
        padding: 15px;
        border: 1px solid #ddd;
        margin-top: 20px;
    }
    .total-linea {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
    }
    .total-final {
        font-size: 1.2em;
        font-weight: bold;
        border-top: 2px solid #333;
        margin-top: 10px;
        padding-top: 10px;
    }
    .lista-precio-info {
        background-color: #e7f3ff;
        border: 1px solid #2196F3;
        padding: 10px;
        margin: 10px 0;
        border-radius: 5px;
    }
</style>

<!-- ################  TÍTULO  ################ -->
<h2>📄 Nueva Factura</h2>

<!-- ################  INFO GENERAL  ################ -->
<div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 20px; background-color: #f9f9f9;">
    <strong>Usuario Registra:</strong> <?= htmlspecialchars($usuario_actual) ?><br>
    <strong>Fecha de Registro:</strong> <?= date('d/m/Y H:i') ?><br>
    <strong>Estado Inicial:</strong> Emitida
</div>

<!-- ################  FORMULARIO  ################ -->
<form action="guardar.php" method="POST" id="formFactura">
    <h3>📋 Datos Generales</h3>

    <label>Tipo de Factura:</label><br>
    <select name="tipo_factura_id" id="tipo_factura" required>
        <option value="">-- Seleccione --</option>
        <?php foreach ($tipos_factura as $tipo): ?>
            <option value="<?= $tipo['id'] ?>" data-prefijo="<?= htmlspecialchars($tipo['prefijo']) ?>">
                <?= htmlspecialchars($tipo['nombre']) ?> (<?= htmlspecialchars($tipo['prefijo']) ?>)
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Serie:</label><br>
    <select name="serie_id" id="serie_factura" required disabled>
        <option value="">-- Primero seleccione tipo de factura --</option>
    </select><br><br>

    <label>Cliente:</label><br>
    <select name="cliente_id" id="cliente" required>
        <option value="">-- Seleccione --</option>
        <?php foreach ($clientes as $c): ?>
            <option value="<?= $c['id'] ?>" data-nit="<?= htmlspecialchars($c['nit']) ?>">
                <?= htmlspecialchars($c['nombre']) ?> - <?= htmlspecialchars($c['nit']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>📋 Lista de Precios:</label><br>
    <select name="lista_precio_id" id="lista_precio_id">
        <option value="">-- Sin lista de precios --</option>
        <?php foreach ($listas_precios as $lp): ?>
            <option value="<?= $lp['id'] ?>">
                <?= htmlspecialchars($lp['nombre']) ?>
                <?php if ($lp['vigente_desde'] || $lp['vigente_hasta']): ?>
                    (Vigente: <?= $lp['vigente_desde'] ? date('d/m/Y', strtotime($lp['vigente_desde'])) : 'Siempre' ?> 
                    - <?= $lp['vigente_hasta'] ? date('d/m/Y', strtotime($lp['vigente_hasta'])) : 'Siempre' ?>)
                <?php endif; ?>
            </option>
        <?php endforeach; ?>
    </select><br>
    <small style="color: #666;">💡 Si selecciona una lista, los precios se cargarán automáticamente</small><br><br>

    <label>Fecha de Emisión:</label><br>
    <input type="date" name="fecha_emision" id="fecha_emision" value="<?= date('Y-m-d') ?>" required><br><br>

    <label>Forma de Pago:</label><br>
    <select name="forma_pago_id" id="forma_pago" required>
        <option value="">-- Seleccione --</option>
        <?php foreach ($formas_pago as $fp): ?>
            <option value="<?= $fp['id'] ?>" data-dias="<?= $fp['dias_credito'] ?>">
                <?= htmlspecialchars($fp['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Días de Crédito:</label><br>
    <input type="number" name="dias_credito" id="dias_credito" value="0" min="0"><br><br>

    <label>Almacén:</label><br>
    <select name="almacen_id" id="almacen_id" required>
        <option value="">-- Seleccione --</option>
        <?php foreach ($almacenes as $a): ?>
            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['codigo'] . ' - ' . $a['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Orden de Compra:</label><br>
    <input type="text" name="orden_compra" placeholder="Opcional"><br><br>

    <label>Referencia Interna:</label><br>
    <input type="text" name="referencia_interna" placeholder="Opcional"><br><br>

    <label>Observaciones:</label><br>
    <textarea name="observaciones" rows="3"></textarea><br><br>

    <hr>

    <h3>🛒 Items de la Factura</h3>
    
    <div style="margin-bottom: 10px;">
        <button type="button" onclick="buscarItem('producto')">🔍 Buscar Producto</button>
        <button type="button" onclick="buscarItem('servicio')">🔍 Buscar Servicio</button>
    </div>

    <div id="items-container">
        <!-- Los items se agregarán dinámicamente aquí -->
    </div>

    <div class="totales-section">
        <h3>💰 Totales</h3>
        <div class="total-linea">
            <span>Subtotal:</span>
            <span id="subtotal_display">Q 0.00</span>
        </div>
        <div class="total-linea">
            <span>Descuentos:</span>
            <span id="descuentos_display">Q 0.00</span>
        </div>
        <div class="total-linea">
            <span>Impuestos:</span>
            <span id="impuestos_display">Q 0.00</span>
        </div>
        <div class="total-linea total-final">
            <span>TOTAL:</span>
            <span id="total_display">Q 0.00</span>
        </div>
        
        <input type="hidden" name="subtotal" id="subtotal" value="0">
        <input type="hidden" name="total_descuento" id="total_descuento" value="0">
        <input type="hidden" name="total_impuestos" id="total_impuestos" value="0">
        <input type="hidden" name="total" id="total" value="0">
    </div>

    <br>
    <button type="submit">💾 Crear Factura</button>
</form>

<br>
<a href="listar.php">⬅️ Volver al listado</a>

<!-- Modal para buscar items -->
<div id="modalBuscar" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div style="background:white; width:80%; max-width:800px; margin:50px auto; padding:20px; border-radius:5px; max-height:80vh; overflow-y:auto;">
        <h3 id="modalTitulo">Buscar Item</h3>
        <input type="text" id="buscarInput" placeholder="Buscar por código o nombre..." style="width:100%; padding:10px; margin-bottom:10px;">
        <div id="resultadosBusqueda"></div>
        <button type="button" onclick="cerrarModal()">Cerrar</button>
    </div>
</div>

<!-- ################  JAVASCRIPT  ################ -->
<script>
let contadorItems = 0;
let tipoItemBuscar = '';

// Cargar series cuando cambia el tipo de factura
document.getElementById('tipo_factura').addEventListener('change', function() {
    const tipoId = this.value;
    const serieSelect = document.getElementById('serie_factura');
    
    if (!tipoId) {
        serieSelect.disabled = true;
        serieSelect.innerHTML = '<option value="">-- Primero seleccione tipo de factura --</option>';
        return;
    }
    
    fetch(`obtener_series.php?tipo_factura_id=${tipoId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                serieSelect.innerHTML = '<option value="">-- Seleccione una serie --</option>';
                data.series.forEach(serie => {
                    serieSelect.innerHTML += `<option value="${serie.id}">${serie.serie} (Actual: ${serie.numero_actual})</option>`;
                });
                serieSelect.disabled = false;
            }
        })
        .catch(error => console.error('Error:', error));
});

// Actualizar días de crédito cuando cambia forma de pago
document.getElementById('forma_pago').addEventListener('change', function() {
    const dias = this.options[this.selectedIndex].dataset.dias || 0;
    document.getElementById('dias_credito').value = dias;
});

// Buscar items
function buscarItem(tipo) {
    const almacenId = document.getElementById('almacen_id').value;
    if (!almacenId && tipo === 'producto') {
        alert('⚠️ Primero debe seleccionar un almacén');
        return;
    }
    
    tipoItemBuscar = tipo;
    document.getElementById('modalTitulo').textContent = tipo === 'producto' ? 'Buscar Producto' : 'Buscar Servicio';
    document.getElementById('modalBuscar').style.display = 'block';
    document.getElementById('buscarInput').value = '';
    document.getElementById('resultadosBusqueda').innerHTML = '';
    document.getElementById('buscarInput').focus();
}

function cerrarModal() {
    document.getElementById('modalBuscar').style.display = 'none';
}

document.getElementById('buscarInput').addEventListener('keyup', function(e) {
    const termino = this.value;
    if (termino.length < 2) {
        document.getElementById('resultadosBusqueda').innerHTML = '';
        return;
    }
    
    const almacenId = document.getElementById('almacen_id').value;
    
    const url = tipoItemBuscar === 'producto' 
        ? `buscar_productos.php?termino=${encodeURIComponent(termino)}&almacen_id=${almacenId}`
        : `buscar_servicios.php?termino=${encodeURIComponent(termino)}`;
    
    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.items.length > 0) {
                let html = '<table border="1" width="100%"><tr><th>Código</th><th>Nombre</th><th>Info</th><th>Acción</th></tr>';
                data.items.forEach(item => {
                    const info = tipoItemBuscar === 'producto' 
                        ? `Stock: ${item.stock_total || 0}`
                        : `Precio Base: Q ${parseFloat(item.precio_base || 0).toFixed(2)}`;
                    html += `<tr>
                        <td>${item.codigo}</td>
                        <td>${item.nombre}</td>
                        <td>${info}</td>
                        <td><button type="button" onclick='agregarItem(${JSON.stringify(item)})'>Agregar</button></td>
                    </tr>`;
                });
                html += '</table>';
                document.getElementById('resultadosBusqueda').innerHTML = html;
            } else {
                document.getElementById('resultadosBusqueda').innerHTML = '<p>No se encontraron resultados</p>';
            }
        });
});

function agregarItem(item) {
    const container = document.getElementById('items-container');
    const itemHtml = `
        <div class="item-linea" id="item_${contadorItems}">
            <input type="hidden" name="items[${contadorItems}][tipo_item]" value="${tipoItemBuscar}">
            <input type="hidden" name="items[${contadorItems}][item_id]" value="${item.id}">
            <input type="hidden" name="items[${contadorItems}][codigo_item]" value="${item.codigo}">
            
            <table>
                <tr>
                    <td width="30%">
                        <strong>${item.codigo}</strong><br>
                        ${item.nombre}
                        <input type="hidden" name="items[${contadorItems}][descripcion]" value="${item.nombre}">
                        <small class="precio-info-item" id="precio_info_${contadorItems}" style="display:block; margin-top:5px; color:#666;"></small>
                    </td>
                    <td width="10%">
                        <label>Cantidad:</label><br>
                        <input type="number" step="0.01" name="items[${contadorItems}][cantidad]" 
                               class="cantidad-item" data-index="${contadorItems}" required min="0.01" value="1">
                        ${tipoItemBuscar === 'producto' ? `<small class="info-item">Stock: ${item.stock_total || 0}</small>` : ''}
                    </td>
                    <td width="12%">
                        <label>Precio Unit:</label><br>
                        <input type="number" step="0.01" name="items[${contadorItems}][precio_unitario]" 
                               class="precio-item" data-index="${contadorItems}" required min="0" value="0">
                    </td>
                    <td width="10%">
                        <label>Desc %:</label><br>
                        <input type="number" step="0.01" name="items[${contadorItems}][descuento_porcentaje]" 
                               class="descuento-item" data-index="${contadorItems}" min="0" max="100" value="0">
                    </td>
                    <td width="15%">
                        <label>Impuesto:</label><br>
                        <select name="items[${contadorItems}][impuesto_id]" class="impuesto-item" data-index="${contadorItems}">
                            <option value="">Sin impuesto</option>
                            <?php foreach ($impuestos as $imp): ?>
                                <option value="<?= $imp['id'] ?>" data-porcentaje="<?= $imp['porcentaje'] ?>">
                                    <?= htmlspecialchars($imp['nombre']) ?> (<?= $imp['porcentaje'] ?>%)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td width="15%">
                        <label>Total Línea:</label><br>
                        <strong class="total-linea-display" id="total_linea_${contadorItems}">Q 0.00</strong>
                        <input type="hidden" name="items[${contadorItems}][subtotal]" class="subtotal-linea" id="subtotal_${contadorItems}" value="0">
                        <input type="hidden" name="items[${contadorItems}][descuento_monto]" class="descuento-monto" id="desc_monto_${contadorItems}" value="0">
                        <input type="hidden" name="items[${contadorItems}][impuesto_porcentaje]" class="impuesto-porcentaje" id="imp_porc_${contadorItems}" value="0">
                        <input type="hidden" name="items[${contadorItems}][impuesto_monto]" class="impuesto-monto" id="imp_monto_${contadorItems}" value="0">
                        <input type="hidden" name="items[${contadorItems}][total]" class="total-linea" id="total_${contadorItems}" value="0">
                    </td>
                    <td width="8%">
                        <button type="button" onclick="eliminarItem(${contadorItems})">❌</button>
                    </td>
                </tr>
            </table>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', itemHtml);
    
    // Cargar precio desde lista si aplica
    cargarPrecioItem(contadorItems, item.id);
    
    contadorItems++;
    cerrarModal();
    aplicarCalculos();
}

function cargarPrecioItem(index, itemId) {
    const listaPrecioId = document.getElementById('lista_precio_id').value;
    const fecha = document.getElementById('fecha_emision').value;
    const precioInput = document.querySelector(`input[name="items[${index}][precio_unitario]"]`);
    const precioInfo = document.getElementById(`precio_info_${index}`);
    
    if (!precioInfo) return;
    
    precioInfo.innerHTML = '<span style="color:#999;">⏳ Consultando precio...</span>';
    precioInput.disabled = true;
    
    const url = `obtener_precio.php?producto_id=${itemId}&lista_precio_id=${listaPrecioId || ''}&fecha=${fecha}`;
    
    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.encontrado) {
                precioInput.value = parseFloat(data.precio).toFixed(2);
                precioInfo.innerHTML = `<span style="color:green;">✓ Precio de: ${data.lista_nombre || 'Lista'} (${data.tipo_precio})</span>`;
            } else {
                precioInput.value = '0.00';
                precioInfo.innerHTML = '<span style="color:orange;">⚠ Sin precio en lista - Ingrese manualmente</span>';
            }
            precioInput.disabled = false;
            calcularLineaItem(index);
        })
        .catch(error => {
            console.error('Error:', error);
            precioInput.value = '0.00';
            precioInfo.innerHTML = '<span style="color:red;">✗ Error al obtener precio</span>';
            precioInput.disabled = false;
        });
}

function eliminarItem(index) {
    document.getElementById(`item_${index}`).remove();
    calcularTotales();
}

function aplicarCalculos() {
    document.querySelectorAll('.cantidad-item, .precio-item, .descuento-item, .impuesto-item').forEach(input => {
        input.addEventListener('change', function() {
            const index = this.dataset.index;
            calcularLineaItem(index);
        });
    });
}

function calcularLineaItem(index) {
    const cantidad = parseFloat(document.querySelector(`input[name="items[${index}][cantidad]"]`).value) || 0;
    const precioUnit = parseFloat(document.querySelector(`input[name="items[${index}][precio_unitario]"]`).value) || 0;
    const descPorc = parseFloat(document.querySelector(`input[name="items[${index}][descuento_porcentaje]"]`).value) || 0;
    const impuestoSelect = document.querySelector(`select[name="items[${index}][impuesto_id]"]`);
    const impPorc = impuestoSelect.options[impuestoSelect.selectedIndex]?.dataset.porcentaje || 0;
    
    // Subtotal antes de descuento
    const subtotalBruto = cantidad * precioUnit;
    
    // Descuento
    const descMonto = subtotalBruto * (descPorc / 100);
    
    // Subtotal después de descuento
    const subtotal = subtotalBruto - descMonto;
    
    // Impuesto
    const impMonto = subtotal * (impPorc / 100);
    
    // Total línea
    const total = subtotal + impMonto;
    
    // Actualizar campos ocultos
    document.getElementById(`subtotal_${index}`).value = subtotal.toFixed(2);
    document.getElementById(`desc_monto_${index}`).value = descMonto.toFixed(2);
    document.getElementById(`imp_porc_${index}`).value = impPorc;
    document.getElementById(`imp_monto_${index}`).value = impMonto.toFixed(2);
    document.getElementById(`total_${index}`).value = total.toFixed(2);
    
    // Actualizar display
    document.getElementById(`total_linea_${index}`).textContent = `Q ${total.toFixed(2)}`;
    
    calcularTotales();
}

function calcularTotales() {
    let subtotalGeneral = 0;
    let descuentosGeneral = 0;
    let impuestosGeneral = 0;
    let totalGeneral = 0;
    
    document.querySelectorAll('.subtotal-linea').forEach(input => {
        subtotalGeneral += parseFloat(input.value) || 0;
    });
    
    document.querySelectorAll('.descuento-monto').forEach(input => {
        descuentosGeneral += parseFloat(input.value) || 0;
    });
    
    document.querySelectorAll('.impuesto-monto').forEach(input => {
        impuestosGeneral += parseFloat(input.value) || 0;
    });
    
    document.querySelectorAll('.total-linea').forEach(input => {
        totalGeneral += parseFloat(input.value) || 0;
    });
    
    // Actualizar displays
    document.getElementById('subtotal_display').textContent = `Q ${subtotalGeneral.toFixed(2)}`;
    document.getElementById('descuentos_display').textContent = `Q ${descuentosGeneral.toFixed(2)}`;
    document.getElementById('impuestos_display').textContent = `Q ${impuestosGeneral.toFixed(2)}`;
    document.getElementById('total_display').textContent = `Q ${totalGeneral.toFixed(2)}`;
    
    // Actualizar campos ocultos
    document.getElementById('subtotal').value = subtotalGeneral.toFixed(2);
    document.getElementById('total_descuento').value = descuentosGeneral.toFixed(2);
    document.getElementById('total_impuestos').value = impuestosGeneral.toFixed(2);
    document.getElementById('total').value = totalGeneral.toFixed(2);
}

// Validación antes de enviar
document.getElementById('formFactura').addEventListener('submit', function(e) {
    const itemsCount = document.querySelectorAll('.item-linea').length;
    if (itemsCount === 0) {
        e.preventDefault();
        alert('❌ Debe agregar al menos un item a la factura');
        return false;
    }
});
</script>