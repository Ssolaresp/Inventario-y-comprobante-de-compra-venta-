<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



require_once '../../controlador/RolesControlador.php';
require_once '../../../includes/sidebar.php'; 

require_once __DIR__ . '/../../../includes/permisos.php';

verificarAcceso(1);

$controlador = new RolesControlador();
$rol = $controlador->obtener($_GET['id']);
?>

<?php if ($rol): ?>
    <h2>Editar Rol</h2>
    <a href="listar.php">← Volver al Listado</a>

    <table border="1" cellpadding="5" cellspacing="0" style="margin-top:10px;">
        <tr>
            <td colspan="2">
                <form method="POST" action="guardar.php">
                    <input type="hidden" name="id" value="<?= $rol['id'] ?>">

                    <table cellpadding="5" cellspacing="0">
                        <tr>
                            <td><label for="nombre">Nombre:</label></td>
                            <td><input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($rol['nombre']) ?>" required></td>
                        </tr>
                        <tr>
                            <td><label for="descripcion">Descripción:</label></td>
                            <td><textarea name="descripcion" id="descripcion" rows="3" cols="30"><?= htmlspecialchars($rol['descripcion']) ?></textarea></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align:center;">
                                <button type="submit">Guardar Cambios</button>
                                <a href="listar.php">Cancelar</a>
                            </td>
                        </tr>
                    </table>
                </form>
            </td>
        </tr>
    </table>
<?php else: ?>
    <p>Rol no encontrado.</p>
    <a href="listar.php">Volver</a>
<?php endif; ?>
