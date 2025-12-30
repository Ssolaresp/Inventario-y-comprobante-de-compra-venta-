<?php
// Evita el error de headers ya enviados
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controlador/UsuarioAlmacenControlador.php';
require_once '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';

// Verifica acceso al módulo (1 = nivel requerido)
verificarAcceso(1);

$controlador = new UsuarioAlmacenControlador();
$usuarios = $controlador->obtenerUsuarios();
$almacenes = $controlador->obtenerAlmacenes();
$estados = $controlador->obtenerEstados();

$id = $_GET['id'] ?? null;
$registro = $controlador->obtener($id);

if (!$registro) {
    echo "<p>Registro no encontrado.</p>";
    ob_end_flush();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    $data['id'] = $id;
    $controlador->guardar($data);
    // Redirección segura
    header('Location: listar.php');
    ob_end_flush();
    exit;
}
?>

<h2>Editar Asignación de Usuario a Almacén</h2>

<form method="POST">
    <table border="0" cellpadding="5" cellspacing="0">
        <tr>
            <td><label>Usuario:</label></td>
            <td>
                <select name="usuario_id" required>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $u['id'] == $registro['usuario_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <tr>
            <td><label>Almacén:</label></td>
            <td>
                <select name="almacen_id" required>
                    <?php foreach ($almacenes as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= $a['id'] == $registro['almacen_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <tr>
            <td><label>Estado:</label></td>
            <td>
                <select name="estado_id" required>
                    <?php foreach ($estados as $e): ?>
                        <option value="<?= $e['id'] ?>" <?= $e['id'] == $registro['estado_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>

        <tr>
            <td colspan="2" align="center">
                <button type="submit">Actualizar</button>
                <a href="listar.php">Cancelar</a>
            </td>
        </tr>
    </table>
</form>

<?php
// Cierra el buffer de salida
ob_end_flush();
?>
