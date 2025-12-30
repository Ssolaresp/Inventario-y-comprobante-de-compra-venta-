<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../Controlador/ClientesControlador.php';
include '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';
verificarAcceso(6);

$controlador = new ClientesControlador();
$estados = $controlador->obtenerEstados();
$canales = $controlador->obtenerCanales();
$departamentos = $controlador->obtenerDepartamentos();
$codigo = $controlador->obtenerSiguienteCodigo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controlador->guardar($_POST);
    header('Location: listar.php');
    exit;
}
?>

<h2>Nuevo Cliente</h2>
<form method="post" id="frmCliente">
    <label>Código:</label><br>
    <input type="text" name="codigo_cliente" value="<?= htmlspecialchars($codigo) ?>" readonly><br><br>

    <label>Tipo:</label><br>
    <select name="id_tipo_cliente" required onchange="toggleTipo(this.value)">
        <option value="">-- Seleccione --</option>
        <option value="1">Persona Natural</option>
        <option value="2">Persona Jurídica</option>
    </select><br><br>

    <div id="natural" style="display:none;">
        <label>Primer Nombre:</label><br>
        <input type="text" name="primer_nombre"><br><br>
        <label>Segundo Nombre:</label><br>
        <input type="text" name="segundo_nombre"><br><br>
        <label>Primer Apellido:</label><br>
        <input type="text" name="primer_apellido"><br><br>
        <label>Segundo Apellido:</label><br>
        <input type="text" name="segundo_apellido"><br><br>
        <label>DPI:</label><br>
        <input type="text" name="dpi"><br><br>
    </div>

    <div id="juridica" style="display:none;">
        <label>Razón Social:</label><br>
        <input type="text" name="razon_social"><br><br>
    </div>

    <label>NIT:</label><br>
    <input type="text" name="nit" required><br><br>

    <label>Teléfono:</label><br>
    <input type="text" name="telefono"><br><br>

    <label>Correo:</label><br>
    <input type="email" name="correo"><br><br>

    <label>Dirección:</label><br>
    <textarea name="direccion" rows="2"></textarea><br><br>

    <label>Departamento:</label><br>
    <select name="id_departamento" id="departamento" required onchange="cargarMunicipios(this.value)">
        <option value="">-- Seleccione --</option>
        <?php foreach ($departamentos as $d): ?>
            <option value="<?= $d['id_departamento'] ?>"><?= htmlspecialchars($d['nombre_departamento']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Municipio:</label><br>
    <select name="id_municipio" id="municipio" required>
        <option value="">-- Seleccione departamento primero --</option>
    </select><br><br>

    <label>Canal de Venta:</label><br>
    <select name="id_canal">
        <option value="">-- Seleccione --</option>
        <?php foreach ($canales as $c): ?>
            <option value="<?= $c['id_canal'] ?>"><?= htmlspecialchars($c['nombre_canal']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Estado:</label><br>
    <select name="id_estado" required>
        <?php foreach ($estados as $e): ?>
            <option value="<?= $e['id_estado'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">💾 Guardar</button>
    <a href="listar.php">↩️ Cancelar</a>
</form>

<script>
function toggleTipo(val) {
    document.getElementById('natural').style.display = (val == 1) ? 'block' : 'none';
    document.getElementById('juridica').style.display = (val == 2) ? 'block' : 'none';
}

function cargarMunicipios(idDepartamento) {
    const selectMunicipio = document.getElementById('municipio');
    
    if (!idDepartamento || idDepartamento === '') {
        selectMunicipio.innerHTML = '<option value="">-- Seleccione departamento primero --</option>';
        selectMunicipio.disabled = false;
        return;
    }
    
    selectMunicipio.innerHTML = '<option value="">Cargando...</option>';
    selectMunicipio.disabled = true;
    
    // Construir URL con parámetro
    const url = 'ajax/municipios.php?id=' + encodeURIComponent(idDepartamento);
    console.log('Cargando municipios desde:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (!response.ok) {
                throw new Error('Error HTTP: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            
            // Verificar si hay error en la respuesta
            if (data.error) {
                throw new Error(data.error);
            }
            
            let html = '<option value="">-- Seleccione --</option>';
            
            if (data && Array.isArray(data) && data.length > 0) {
                data.forEach(m => {
                    html += `<option value="${m.id_municipio}">${m.nombre_municipio}</option>`;
                });
            } else {
                html = '<option value="">No hay municipios disponibles</option>';
            }
            
            selectMunicipio.innerHTML = html;
            selectMunicipio.disabled = false;
        })
        .catch(error => {
            console.error('Error completo:', error);
            selectMunicipio.innerHTML = '<option value="">Error al cargar</option>';
            selectMunicipio.disabled = false;
            
            // Mostrar alerta con detalles del error
            alert('Error al cargar municipios:\n\n' + error.message + '\n\nRevisa la consola del navegador (F12) para más detalles.');
        });
}
</script>