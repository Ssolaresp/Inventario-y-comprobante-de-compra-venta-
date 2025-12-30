<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../Controlador/ClientesControlador.php';
include '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';
verificarAcceso(6);

$controlador = new ClientesControlador();
$id = $_GET['id'] ?? 0;
$cliente = $controlador->obtener($id);

if (!$cliente) {
    header('Location: listar.php');
    exit;
}

$estados   = $controlador->obtenerEstados();
$canales   = $controlador->obtenerCanales();
$departamentos = $controlador->obtenerDepartamentos();
$municipios = [];
if (!empty($cliente['id_departamento'])) {
    $municipios = $controlador->obtenerMunicipios($cliente['id_departamento']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST['id_cliente'] = $id;
    $controlador->guardar($_POST);
    header('Location: listar.php');
    exit;
}
?>

<h2>Editar Cliente</h2>
<form method="post" id="frmCliente">
    <label>Código:</label><br>
    <input type="text" value="<?= htmlspecialchars($cliente['codigo_cliente']) ?>" readonly><br><br>

    <label>Tipo:</label><br>
    <select name="id_tipo_cliente" required onchange="toggleTipo(this.value)">
        <option value="">-- Seleccione --</option>
        <option value="1" <?= $cliente['id_tipo_cliente'] == 1 ? 'selected' : '' ?>>Persona Natural</option>
        <option value="2" <?= $cliente['id_tipo_cliente'] == 2 ? 'selected' : '' ?>>Persona Jurídica</option>
    </select><br><br>

    <!-- PERSONA NATURAL -->
    <div id="natural" style="display:<?= $cliente['id_tipo_cliente'] == 1 ? 'block' : 'none' ?>;">
        <label>Primer Nombre:</label><br>
        <input type="text" name="primer_nombre" value="<?= htmlspecialchars($cliente['primer_nombre'] ?? '') ?>"><br><br>
        <label>Segundo Nombre:</label><br>
        <input type="text" name="segundo_nombre" value="<?= htmlspecialchars($cliente['segundo_nombre'] ?? '') ?>"><br><br>
        <label>Primer Apellido:</label><br>
        <input type="text" name="primer_apellido" value="<?= htmlspecialchars($cliente['primer_apellido'] ?? '') ?>"><br><br>
        <label>Segundo Apellido:</label><br>
        <input type="text" name="segundo_apellido" value="<?= htmlspecialchars($cliente['segundo_apellido'] ?? '') ?>"><br><br>
        <label>DPI:</label><br>
        <input type="text" name="dpi" value="<?= htmlspecialchars($cliente['dpi'] ?? '') ?>"><br><br>
    </div>

    <!-- PERSONA JURÍDICA -->
    <div id="juridica" style="display:<?= $cliente['id_tipo_cliente'] == 2 ? 'block' : 'none' ?>;">
        <label>Razón Social:</label><br>
        <input type="text" name="razon_social" value="<?= htmlspecialchars($cliente['razon_social'] ?? '') ?>"><br><br>
    </div>

    <!-- DATOS COMUNES -->
    <label>NIT:</label><br>
    <input type="text" name="nit" value="<?= htmlspecialchars($cliente['nit']) ?>" required><br><br>

    <label>Teléfono:</label><br>
    <input type="text" name="telefono" value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>"><br><br>

    <label>Correo:</label><br>
    <input type="email" name="correo" value="<?= htmlspecialchars($cliente['correo'] ?? '') ?>"><br><br>

    <label>Dirección:</label><br>
    <textarea name="direccion" rows="2"><?= htmlspecialchars($cliente['direccion'] ?? '') ?></textarea><br><br>

    <!-- DEPARTAMENTO -->
    <label>Departamento:</label><br>
    <select name="id_departamento" id="departamento" required onchange="cargarMunicipios(this.value)">
        <option value="">-- Seleccione --</option>
        <?php foreach ($departamentos as $d): ?>
            <option value="<?= $d['id_departamento'] ?>" 
                <?= $cliente['id_departamento'] == $d['id_departamento'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($d['nombre_departamento']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <!-- MUNICIPIO -->
    <label>Municipio:</label><br>
    <select name="id_municipio" id="municipio" required>
        <option value="">-- Seleccione --</option>
        <?php foreach ($municipios as $m): ?>
            <option value="<?= $m['id_municipio'] ?>" 
                <?= $cliente['id_municipio'] == $m['id_municipio'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($m['nombre_municipio']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <!-- CANAL -->
    <label>Canal de Venta:</label><br>
    <select name="id_canal">
        <option value="">-- Seleccione --</option>
        <?php foreach ($canales as $c): ?>
            <option value="<?= $c['id_canal'] ?>" 
                <?= $cliente['id_canal'] == $c['id_canal'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['nombre_canal']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <!-- ESTADO -->
    <label>Estado:</label><br>
    <select name="id_estado" required>
        <?php foreach ($estados as $e): ?>
            <option value="<?= $e['id_estado'] ?>" 
                <?= $cliente['id_estado'] == $e['id_estado'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($e['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">💾 Actualizar</button>
    <a href="listar.php">↩️ Cancelar</a>
</form>

<script>
// Mostrar/ocultar bloques según tipo de cliente
function toggleTipo(val) {
    const natural = document.getElementById('natural');
    const juridica = document.getElementById('juridica');
    
    natural.style.display = (val == 1) ? 'block' : 'none';
    juridica.style.display = (val == 2) ? 'block' : 'none';
}

// Cargar municipios según departamento seleccionado
function cargarMunicipios(idDepartamento) {
    const selectMunicipio = document.getElementById('municipio');
    const municipioActual = selectMunicipio.value; // Guardar selección actual
    
    // Si no hay departamento seleccionado
    if (!idDepartamento || idDepartamento === '') {
        selectMunicipio.innerHTML = '<option value="">-- Seleccione departamento primero --</option>';
        selectMunicipio.disabled = false;
        return;
    }
    
    // Mostrar estado de carga
    selectMunicipio.innerHTML = '<option value="">Cargando...</option>';
    selectMunicipio.disabled = true;
    
    // Realizar petición AJAX
    fetch('ajax/municipios.php?id=' + encodeURIComponent(idDepartamento))
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en el servidor: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Municipios recibidos:', data);
            
            let opciones = '<option value="">-- Seleccione --</option>';
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            if (data && data.length > 0) {
                data.forEach(municipio => {
                    const selected = (municipio.id_municipio == municipioActual) ? 'selected' : '';
                    opciones += `<option value="${municipio.id_municipio}" ${selected}>${municipio.nombre_municipio}</option>`;
                });
            } else {
                opciones = '<option value="">No hay municipios disponibles</option>';
            }
            
            selectMunicipio.innerHTML = opciones;
            selectMunicipio.disabled = false;
        })
        .catch(error => {
            console.error('Error al cargar municipios:', error);
            selectMunicipio.innerHTML = '<option value="">Error al cargar municipios</option>';
            selectMunicipio.disabled = false;
            alert('Error al cargar los municipios. Por favor, intente nuevamente.\n\nDetalle: ' + error.message);
        });
}
</script>