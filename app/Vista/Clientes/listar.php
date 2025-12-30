<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../Controlador/ClientesControlador.php';
include '../../../includes/sidebar.php';
require_once __DIR__ . '/../../../includes/permisos.php';
verificarAcceso(6); // Ajusta el rol que corresponda

$controlador = new ClientesControlador();
$clientes = $controlador->listar();
?>

<h2>Listado de Clientes</h2>
<a href="nuevo.php">➕ Nuevo Cliente</a><br><br>

<?php if (empty($clientes)): ?>
    <p>No hay clientes registrados.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0" style="width:100%; text-align:center;">
        <thead>
            <tr>
                <th>ID</th><th>Código</th><th>Nombre / Razón Social</th>
                <th>NIT</th><th>DPI</th><th>Teléfono</th><th>Correo</th>
                <th>Departamento</th><th>Municipio</th><th>Estado</th><th>Canal</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clientes as $c):
                // Evitamos nulls
                $nombre = trim($c['razon_social'] ?? '') !== ''
                          ? $c['razon_social']
                          : trim(($c['primer_nombre']   ?? '') . ' ' .
                                 ($c['segundo_nombre']  ?? '') . ' ' .
                                 ($c['primer_apellido'] ?? '') . ' ' .
                                 ($c['segundo_apellido']?? ''));
            ?>
                <tr>
                    <td><?= $c['id_cliente'] ?></td>
                    <td><?= $c['codigo_cliente'] ?></td>
                    <td><?= htmlspecialchars($nombre) ?></td>
                    <td><?= $c['nit'] ?></td>
                    <td><?= $c['dpi'] ?: '—' ?></td>
                    <td><?= $c['telefono'] ?></td>
                    <td><?= $c['correo'] ?></td>
                    <td><?= $c['nombre_departamento'] ?: '—' ?></td>
                    <td><?= $c['nombre_municipio'] ?: '—' ?></td>
                    <td><?= $c['estado'] ?></td>
                    <td><?= $c['nombre_canal'] ?: '—' ?></td>
                    <td>
                        <a href="editar.php?id=<?= $c['id_cliente'] ?>">✏️ Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>