<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../Controlador/MunicipiosControlador.php';
include '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';
verificarAcceso(6);

$controlador = new MunicipiosControlador();
$departamentos = $controlador->obtenerDepartamentos();

// Obtener el departamento seleccionado del filtro
$departamento_filtro = isset($_GET['departamento']) ? intval($_GET['departamento']) : 0;

// Si hay filtro, obtener solo municipios de ese departamento, sino obtener todos
if ($departamento_filtro > 0) {
    require_once '../../Controlador/ClientesControlador.php';
    $clienteControlador = new ClientesControlador();
    $municipios = $clienteControlador->obtenerMunicipios($departamento_filtro);
    
    // Agregar nombre del departamento a cada municipio
    $departamentoInfo = null;
    foreach ($departamentos as $d) {
        if ($d['id_departamento'] == $departamento_filtro) {
            $departamentoInfo = $d;
            break;
        }
    }
    
    if ($departamentoInfo && !empty($municipios)) {
        foreach ($municipios as &$m) {
            $m['nombre_departamento'] = $departamentoInfo['nombre_departamento'];
            $m['estado'] = 'Activo'; // Valor por defecto
        }
    }
} else {
    $municipios = $controlador->listar();
}
?>

<h2>Municipios</h2>
<a href="nuevo.php">➕ Nuevo Municipio</a>
<br><br>

<!-- FILTRO POR DEPARTAMENTO -->
<div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <form method="get" style="display: flex; align-items: center; gap: 10px;">
        <label><strong>Filtrar por Departamento:</strong></label>
        <select name="departamento" onchange="this.form.submit()" style="padding: 5px;">
            <option value="0">-- Todos los departamentos --</option>
            <?php foreach ($departamentos as $d): ?>
                <option value="<?= $d['id_departamento'] ?>" 
                    <?= $departamento_filtro == $d['id_departamento'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($d['nombre_departamento']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($departamento_filtro > 0): ?>
            <a href="listar.php" style="padding: 5px 10px; background: #dc3545; color: white; text-decoration: none; border-radius: 3px;">✖ Limpiar filtro</a>
        <?php endif; ?>
    </form>
</div>

<table border="1" cellpadding="10" cellspacing="0" style="width: 100%;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Departamento</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th>Fecha Creación</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($municipios)): ?>
            <tr>
                <td colspan="8" style="text-align:center;">
                    <?php if ($departamento_filtro > 0): ?>
                        No hay municipios registrados para este departamento
                    <?php else: ?>
                        No hay municipios registrados
                    <?php endif; ?>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($municipios as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['id_municipio']) ?></td>
                    <td><?= htmlspecialchars($m['codigo_municipio'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($m['nombre_municipio']) ?></td>
                    <td><?= htmlspecialchars($m['nombre_departamento']) ?></td>
                    <td><?= htmlspecialchars($m['descripcion'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($m['estado'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($m['creado_en'] ?? 'N/A') ?></td>
                    <td>
                        <a href="editar.php?id=<?= $m['id_municipio'] ?>">✏️ Editar</a>
                 </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php if (!empty($municipios)): ?>
    <p style="margin-top: 15px; color: #666;">
        <strong>Total de municipios mostrados:</strong> <?= count($municipios) ?>
        <?php if ($departamento_filtro > 0): ?>
            (Filtrado por departamento)
        <?php endif; ?>
    </p>
<?php endif; ?>