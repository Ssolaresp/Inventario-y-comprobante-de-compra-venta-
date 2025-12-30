<?php
require_once '../../Controlador/ListaPrecioDetalleControlador.php';
require_once '../../../includes/sidebar.php'; 

$controlador = new ListaPrecioDetalleControlador();
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "<p>ID de lista no especificado.</p>";
    exit;
}

$cabecera = $controlador->obtenerCabecera($id);
$detalles = $controlador->listarDetalle($id);
$monedas = $controlador->obtenerMonedas();
$estados = $controlador->obtenerEstados();
$productos = $controlador->obtenerProductos();
$tiposPrecios = $controlador->obtenerTiposPrecios();

if (!$cabecera) {
    echo "<p>Lista de precios no encontrada.</p>";
    exit;
}

// Mostrar mensajes
if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'guardado') {
    echo '<p style="color: green;">✅ Detalle guardado exitosamente.</p>';
}
if (isset($_GET['error'])) {
    echo '<p style="color: red;">❌ Error: ' . htmlspecialchars($_GET['error']) . '</p>';
}
?>

<h2>📝 Gestión de Lista de Precios</h2>

<!-- SECCIÓN CABECERA -->
<fieldset>
    <legend><strong>📋 Información General</strong></legend>
    <form action="guardar_cabecera.php" method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($cabecera['id'] ?? '') ?>">

        <label>Nombre:</label><br>
        <input type="text" name="nombre" maxlength="100" value="<?= htmlspecialchars($cabecera['nombre'] ?? '') ?>" required><br><br>

        <label>Descripción:</label><br>
        <textarea name="descripcion" rows="3"><?= htmlspecialchars($cabecera['descripcion'] ?? '') ?></textarea><br><br>

        <label>Moneda:</label><br>
        <select name="moneda_id" required>
            <?php foreach ($monedas as $m): ?>
                <option value="<?= $m['id'] ?>" <?= ($cabecera['moneda_id'] ?? '') == $m['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['simbolo'] ?? '') ?> 
                    <?= htmlspecialchars($m['codigo'] ?? '') ?> - 
                    <?= htmlspecialchars($m['nombre'] ?? '') ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Vigente Desde:</label><br>
        <input type="date" name="vigente_desde" value="<?= htmlspecialchars($cabecera['vigente_desde'] ?? '') ?>" required><br><br>

        <label>Vigente Hasta:</label><br>
        <input type="date" name="vigente_hasta" value="<?= htmlspecialchars($cabecera['vigente_hasta'] ?? '') ?>"><br><br>

        <label>Estado:</label><br>
        <select name="estado_id" required>
            <?php foreach ($estados as $e): ?>
                <option value="<?= $e['id'] ?>" <?= ($cabecera['estado_id'] ?? '') == $e['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['nombre'] ?? '') ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit">💾 Actualizar Cabecera</button>
    </form>
</fieldset>

<br>

<!-- SECCIÓN DETALLE -->
<fieldset>
    <legend><strong>🛒 Productos en la Lista</strong></legend>
    
    <!-- Formulario para agregar producto -->
    <form action="guardar_detalle.php" method="POST" style="background: #f0f0f0; padding: 10px; margin-bottom: 15px;">
        <input type="hidden" name="lista_precio_id" value="<?= htmlspecialchars($id) ?>">
        
        <strong>➕ Agregar Producto:</strong><br><br>
        
        <label>Producto:</label>
        <select name="producto_id" required>
            <option value="">-- Seleccione --</option>
            <?php foreach ($productos as $p): ?>
                <option value="<?= $p['id'] ?>">
                    <?= htmlspecialchars($p['codigo'] ?? '') ?> - <?= htmlspecialchars($p['nombre'] ?? '') ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <label>Tipo de Precio:</label>
        <select name="tipo_precio_id" required>
            <option value="">-- Seleccione --</option>
            <?php foreach ($tiposPrecios as $tp): ?>
                <option value="<?= $tp['id'] ?>"><?= htmlspecialchars($tp['nombre'] ?? '') ?></option>
            <?php endforeach; ?>
        </select>
        
        <label>Precio:</label>
        <input type="number" step="0.01" name="precio" required>
        
        <button type="submit">➕ Agregar</button>
    </form>

    <!-- Tabla de productos -->
    <?php if (empty($detalles)): ?>
        <p>No hay productos agregados a esta lista.</p>
    <?php else: ?>
        <table border="1" cellpadding="6" cellspacing="0" style="width:100%; text-align:center;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Producto</th>
                    <th>Tipo Precio</th>
                    <th>Precio</th>
                    <th>Actualizado en</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['id'] ?? '') ?></td>
                        <td><?= htmlspecialchars($d['producto_codigo'] ?? '') ?></td>
                        <td><?= htmlspecialchars($d['producto'] ?? '') ?></td>
                        <td><?= htmlspecialchars($d['tipo_precio'] ?? '') ?></td>
                        <td><?= htmlspecialchars($d['precio'] ?? '') ?></td>
                        <td><?= htmlspecialchars($d['actualizado_en'] ?? '') ?></td>
                        <td>
                            <a href="editar_detalle.php?id=<?= $d['id'] ?>&lista_id=<?= $id ?>">✏️ Editar</a> |
                            <a href="eliminar_detalle.php?id=<?= $d['id'] ?>&lista_id=<?= $id ?>" 
                               onclick="return confirm('¿Eliminar este producto?')">🗑️ Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</fieldset>

<br>
<a href="listar.php">⬅️ Volver al listado</a>