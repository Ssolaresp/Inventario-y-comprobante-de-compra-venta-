<?php
require_once '../../Controlador/TransferenciasControlador.php';
include '../../../includes/inicio.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit();
}

$controlador = new TransferenciasControlador();

// Obtener ID de la transferencia
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: listar.php?error=" . urlencode("ID de transferencia no válido"));
    exit();
}

try {
    // Obtener datos de la transferencia
    $transferencia = $controlador->obtener($id);
    $detalle = $controlador->obtenerDetalle($id);
    
    if (!$transferencia) {
        throw new Exception("Transferencia no encontrada");
    }

    // Solo se puede editar si está en estado "Solicitada"
    if ($transferencia['estado'] != 'Solicitada') {
        header("Location: ver.php?id={$id}&error=" . urlencode("Solo se pueden editar transferencias en estado 'Solicitada'"));
        exit();
    }

    $almacenes = $controlador->obtenerAlmacenes();
    $productos = $controlador->obtenerProductos();
    
} catch (Exception $e) {
    header("Location: listar.php?error=" . urlencode($e->getMessage()));
    exit();
}

$usuario_actual = $_SESSION['nombre_usuario'] ?? 'Desconocido';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Transferencia</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header-info {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .header-info strong {
            display: inline-block;
            width: 200px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        select, input[type="number"], textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        textarea {
            resize: vertical;
            min-height: 60px;
        }
        .producto-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fafafa;
            border-radius: 5px;
            position: relative;
        }
        .producto-item:hover {
            background-color: #f5f5f5;
        }
        .producto-row {
            display: grid;
            grid-template-columns: 2fr 1fr 2fr;
            gap: 10px;
            margin-bottom: 10px;
        }
        .stock-info {
            margin-top: 5px;
            font-size: 0.9em;
            padding: 5px;
            border-radius: 3px;
        }
        .stock-ok {
            color: green;
            background-color: #e8f5e9;
        }
        .stock-bajo {
            color: orange;
            background-color: #fff3e0;
        }
        .stock-error {
            color: red;
            background-color: #ffebee;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            transition: background-color 0.3s;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .btn-remove {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            font-size: 12px;
        }
        .actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        h3 {
            color: #555;
            margin-top: 25px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>✏️ Editar Transferencia</h2>

    <div class="alert alert-warning">
        ⚠️ <strong>Importante:</strong> Solo se pueden editar transferencias en estado "Solicitada"
    </div>

    <div class="header-info">
        <strong>Número de Transferencia:</strong>
        <span style="color: #007bff; font-size: 1.1em;"><?= htmlspecialchars($transferencia['numero_transferencia']) ?></span><br>
        <strong>Usuario Solicita:</strong> <?= htmlspecialchars($usuario_actual) ?><br>
        <strong>Fecha de Solicitud:</strong> <?= date('d/m/Y H:i', strtotime($transferencia['fecha_solicitud'])) ?><br>
        <strong>Estado:</strong> <span style="color: orange; font-weight: bold;"><?= htmlspecialchars($transferencia['estado']) ?></span>
    </div>

    <form action="actualizar.php" method="POST" id="formTransferencia">
        <input type="hidden" name="id" value="<?= $id ?>">
        
        <h3>📋 Datos Generales</h3>

        <div class="form-group">
            <label>Almacén Origen:</label>
            <select name="almacen_origen_id" id="almacen_origen" required>
                <option value="">-- Seleccione --</option>
                <?php foreach ($almacenes as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= $a['id'] == $transferencia['almacen_origen_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['codigo'] . ' - ' . $a['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Almacén Destino:</label>
            <select name="almacen_destino_id" id="almacen_destino" required>
                <option value="">-- Seleccione --</option>
                <?php foreach ($almacenes as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= $a['id'] == $transferencia['almacen_destino_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['codigo'] . ' - ' . $a['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Observaciones:</label>
            <textarea name="observaciones" rows="3"><?= htmlspecialchars($transferencia['observaciones'] ?? '') ?></textarea>
        </div>

        <hr>

        <h3>📦 Productos a Transferir</h3>
        <div id="productos-container">
            <?php foreach ($detalle as $index => $item): ?>
            <div class="producto-item">
                <button type="button" class="btn btn-danger btn-sm btn-remove" onclick="eliminarProducto(this)">❌ Eliminar</button>
                
                <div class="producto-row">
                    <div class="form-group">
                        <label>Producto:</label>
                        <select name="productos[<?= $index ?>][producto_id]" class="producto-select" required>
                            <option value="">-- Seleccione --</option>
                            <?php foreach ($productos as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $p['id'] == $item['producto_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['codigo'] . ' - ' . $p['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Cantidad:</label>
                        <input type="number" step="0.01" name="productos[<?= $index ?>][cantidad]" 
                               class="cantidad-input" value="<?= $item['cantidad'] ?>" required min="0.01">
                    </div>

                    <div class="form-group">
                        <label>Observaciones:</label>
                        <input type="text" name="productos[<?= $index ?>][observaciones]" 
                               value="<?= htmlspecialchars($item['observaciones'] ?? '') ?>">
                    </div>
                </div>
                <div class="stock-info"></div>
            </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="btn btn-success" onclick="agregarProducto()">➕ Agregar Producto</button>

        <div class="actions">
            <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
            <a href="ver.php?id=<?= $id ?>" class="btn btn-secondary">🚫 Cancelar</a>
            <a href="listar.php" class="btn btn-secondary">⬅️ Volver al Listado</a>
        </div>
    </form>
</div>

<script>
let contadorProductos = <?= count($detalle) ?>;

function agregarProducto() {
    const container = document.getElementById('productos-container');
    const nuevoProducto = `
        <div class="producto-item">
            <button type="button" class="btn btn-danger btn-sm btn-remove" onclick="eliminarProducto(this)">❌ Eliminar</button>
            
            <div class="producto-row">
                <div class="form-group">
                    <label>Producto:</label>
                    <select name="productos[${contadorProductos}][producto_id]" class="producto-select" required>
                        <option value="">-- Seleccione --</option>
                        <?php foreach ($productos as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['codigo'] . ' - ' . $p['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Cantidad:</label>
                    <input type="number" step="0.01" name="productos[${contadorProductos}][cantidad]" 
                           class="cantidad-input" required min="0.01">
                </div>

                <div class="form-group">
                    <label>Observaciones:</label>
                    <input type="text" name="productos[${contadorProductos}][observaciones]">
                </div>
            </div>
            <div class="stock-info"></div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', nuevoProducto);
    contadorProductos++;
    aplicarListeners();
}

function eliminarProducto(btn) {
    if (confirm('¿Estás seguro de eliminar este producto?')) {
        btn.closest('.producto-item').remove();
    }
}

function aplicarListeners() {
    const almacenOrigen = document.getElementById('almacen_origen');
    const productosSelects = document.querySelectorAll('.producto-select');
    const cantidadInputs = document.querySelectorAll('.cantidad-input');
    
    // Listener para cambio de almacén origen
    almacenOrigen.addEventListener('change', function() {
        productosSelects.forEach(select => {
            if (select.value) {
                verificarStock(select);
            }
        });
    });

    // Listener para cambio de producto
    productosSelects.forEach(select => {
        select.addEventListener('change', function() {
            verificarStock(this);
        });
    });

    // Listener para cambio de cantidad
    cantidadInputs.forEach(input => {
        input.addEventListener('input', function() {
            const select = this.closest('.producto-item').querySelector('.producto-select');
            if (select.value) {
                verificarStock(select);
            }
        });
    });
}

function verificarStock(selectElement) {
    const productoId = selectElement.value;
    const almacenId = document.getElementById('almacen_origen').value;
    const stockSpan = selectElement.closest('.producto-item').querySelector('.stock-info');
    const cantidadInput = selectElement.closest('.producto-item').querySelector('.cantidad-input');
    const cantidadSolicitada = parseFloat(cantidadInput.value) || 0;
    
    if (productoId && almacenId) {
        fetch(`consultar_stock.php?producto_id=${productoId}&almacen_id=${almacenId}`)
            .then(res => res.json())
            .then(data => {
                const stock = parseFloat(data.stock);
                let clase = 'stock-ok';
                let mensaje = `✅ Stock disponible: ${stock}`;
                
                if (stock <= 0) {
                    clase = 'stock-error';
                    mensaje = `❌ Sin stock disponible`;
                } else if (cantidadSolicitada > stock) {
                    clase = 'stock-error';
                    mensaje = `❌ Stock insuficiente: ${stock} (Solicitado: ${cantidadSolicitada})`;
                } else if (stock < cantidadSolicitada * 2) {
                    clase = 'stock-bajo';
                    mensaje = `⚠️ Stock bajo: ${stock} (Solicitado: ${cantidadSolicitada})`;
                }
                
                stockSpan.className = `stock-info ${clase}`;
                stockSpan.innerHTML = mensaje;
            })
            .catch(error => {
                stockSpan.className = 'stock-info stock-error';
                stockSpan.innerHTML = '❌ Error al consultar stock';
            });
    } else {
        stockSpan.innerHTML = '';
    }
}

// Validación antes de enviar
document.getElementById('formTransferencia').addEventListener('submit', function(e) {
    const almacenOrigen = document.getElementById('almacen_origen').value;
    const almacenDestino = document.getElementById('almacen_destino').value;
    
    if (almacenOrigen === almacenDestino) {
        e.preventDefault();
        alert('❌ El almacén de origen y destino no pueden ser iguales');
        return false;
    }

    const productos = document.querySelectorAll('.producto-item');
    if (productos.length === 0) {
        e.preventDefault();
        alert('❌ Debes agregar al menos un producto');
        return false;
    }

    // Validar que no haya productos duplicados
    const productosIds = [];
    let duplicado = false;
    
    productos.forEach(item => {
        const select = item.querySelector('.producto-select');
        const id = select.value;
        if (productosIds.includes(id)) {
            duplicado = true;
        }
        productosIds.push(id);
    });

    if (duplicado) {
        e.preventDefault();
        alert('❌ No puedes agregar el mismo producto más de una vez');
        return false;
    }

    return true;
});

// Aplicar listeners al cargar
document.addEventListener('DOMContentLoaded', aplicarListeners);
</script>

</body>
</html>