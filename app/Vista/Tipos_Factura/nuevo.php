<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../Controlador/TiposFacturaControlador.php';
require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(2);

$controlador = new TiposFacturaControlador();
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = $controlador->guardar($_POST);
    if ($resultado['success']) {
        header('Location: listar.php');
        exit;
    } else {
        $error = $resultado['message'];
    }
}

$estados = $controlador->obtenerEstados();

include '../../../includes/sidebar.php';
?>

<h2>Nuevo Tipo de Factura</h2>

<?php if ($error): ?>
    <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px;">
        ⚠️ <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="post">
    <fieldset style="margin-bottom: 20px; padding: 15px;">
        <legend><strong>Información Básica</strong></legend>
        
        <label>Código: <span style="color: red;">*</span></label><br>
        <input type="text" name="codigo" required maxlength="20" placeholder="Ej: FAC, FCF, NCR" 
               style="text-transform: uppercase; width: 200px;" 
               value="<?= htmlspecialchars($_POST['codigo'] ?? '') ?>"><br>
        <small>Código único para identificar el tipo (mayúsculas)</small><br><br>

        <label>Nombre: <span style="color: red;">*</span></label><br>
        <input type="text" name="nombre" required maxlength="100" placeholder="Ej: Factura de Venta" 
               style="width: 400px;"
               value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"><br><br>

        <label>Descripción:</label><br>
        <textarea name="descripcion" rows="3" style="width: 400px;" 
                  placeholder="Descripción detallada del tipo de factura"><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea><br><br>
    </fieldset>

    <fieldset style="margin-bottom: 20px; padding: 15px;">
        <legend><strong>Configuración de Serie</strong></legend>
        
        <label>Prefijo: <span style="color: red;">*</span></label><br>
        <input type="text" name="prefijo" required maxlength="10" placeholder="Ej: A, B, FAC" 
               style="text-transform: uppercase; width: 150px;"
               value="<?= htmlspecialchars($_POST['prefijo'] ?? '') ?>"><br>
        <small>Prefijo para la numeración (Ej: A001-00000001)</small><br><br>

        <label>Serie Actual Inicial:</label><br>
        <input type="number" name="serie_actual" min="1" value="1" style="width: 150px;"><br>
        <small>Número de serie inicial (por defecto: 1)</small><br><br>
    </fieldset>

    <fieldset style="margin-bottom: 20px; padding: 15px;">
        <legend><strong>Opciones de Validación</strong></legend>
        
        <label>
            <input type="checkbox" name="requiere_nit" value="1" 
                   <?= isset($_POST['requiere_nit']) ? 'checked' : '' ?>>
            Requiere NIT del cliente
        </label><br>
        <small>Marcar si este tipo de factura requiere obligatoriamente el NIT del cliente</small><br><br>

        <label>
            <input type="checkbox" name="afecta_inventario" value="1" checked>
            Afecta Inventario
        </label><br>
        <small>Marcar si este tipo de factura debe descontar del inventario</small><br><br>

        <label>
            <input type="checkbox" name="afecta_cuentas" value="1" checked>
            Afecta Cuentas por Cobrar
        </label><br>
        <small>Marcar si este tipo de factura debe registrarse en cuentas por cobrar</small><br><br>
    </fieldset>

    <fieldset style="margin-bottom: 20px; padding: 15px;">
        <legend><strong>Estado</strong></legend>
        
        <label>Estado: <span style="color: red;">*</span></label><br>
        <select name="estado_id" required style="width: 200px;">
            <?php foreach ($estados as $e): ?>
                <option value="<?= $e['id'] ?>" <?= (isset($_POST['estado_id']) && $_POST['estado_id'] == $e['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>
    </fieldset>

    <button type="submit" style="background-color: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px;">
        💾 Guardar
    </button>
    <a href="listar.php" style="padding: 10px 20px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px;">
        ↩️ Cancelar
    </a>
</form>

<style>
    fieldset { border: 1px solid #ddd; border-radius: 4px; }
    legend { padding: 0 10px; font-weight: bold; }
    label { font-weight: bold; }
    input[type="text"], input[type="number"], textarea, select { 
        padding: 8px; 
        border: 1px solid #ddd; 
        border-radius: 4px; 
        margin-top: 5px;
    }
    small { color: #666; font-style: italic; }
</style>