<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: includes/login.php');
    exit;
}

require_once 'app/Controlador/DashboardControlador.php';

$dashboard = new DashboardControlador();

/* =====  CONTADORES REALES  ===== */
$totalUsuariosActivos    = $dashboard->totalUsuariosActivos();
$totalAlmacenesActivos   = $dashboard->totalAlmacenesActivos();
$totalEntradasHoy        = $dashboard->totalEntradasHoy();
$totalSalidasHoy         = $dashboard->totalSalidasHoy();
$totalTransferenciasHoy  = $dashboard->totalTransferenciasHoy();
$datosGrafico            = $dashboard->datosAlmacenesProductos();
$alertasVenc             = $dashboard->alertasVencimientos();

/* =====  SEGURIDAD  ===== */
$nombre    = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Invitado', ENT_QUOTES, 'UTF-8');
$usuario_id = filter_var($_SESSION['usuario_id'] ?? 0, FILTER_VALIDATE_INT);
if (!$usuario_id || $usuario_id <= 0) {
    session_destroy();
    header('Location: includes/login.php');
    exit;
}

/* =====  HEADERS DE SEGURIDAD  ===== */
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema profesional de gestión de inventario y administración">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard - Sistema Profesional</title>

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Chart.js para gráfico -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Estilos profesionales -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        :root {
            --sidebar-bg: #0e1b2f;
            --sidebar-hover: #1f3a5f;
            --accent-gold: #c9a227;
            --body-bg: #f4f6f9;
            --text-light: #ffffff;
            --text-dark: #2e2e2e;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-dark);
        }
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background-color: var(--sidebar-bg);
            overflow-y: auto;
            transition: transform 0.3s ease;
            box-shadow: 2px 0 8px rgba(0,0,0,0.15);
        }
        #sidebar.hide { transform: translateX(-100%); }
        #sidebar .logo {
            text-align: center;
            padding: 24px 10px;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-light);
            border-bottom: 1px solid #1f3a5f;
        }
        #sidebar .nav-link {
            color: var(--text-light);
            font-weight: 500;
            padding: 10px 16px;
            margin: 4px 0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            transition: background 0.25s, border-left 0.25s;
            border-left: 4px solid transparent;
        }
        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            background-color: var(--sidebar-hover);
            border-left: 4px solid var(--accent-gold);
        }
        #sidebar .nav-link i {
            margin-right: 10px;
            color: var(--accent-gold);
        }
        #sidebar .collapse { padding-left: 20px; }
        #content {
            margin-left: 260px;
            padding: 40px;
            transition: margin-left 0.3s ease;
        }
        #sidebar.hide + #content { margin-left: 0; }
        #btn-toggle {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1055;
            background-color: var(--sidebar-bg);
            border: none;
            color: var(--text-light);
            font-size: 1.4rem;
            padding: 8px 14px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.25);
        }
        #btn-toggle:hover { background-color: var(--sidebar-hover); }
        h1, h2, h3 { color: var(--sidebar-bg); font-weight: 700; }
        #graficoAlmacenes { max-height: 300px; }
    </style>
</head>
<body>

<!-- Botón hamburguesa -->
<button id="btn-toggle" onclick="toggleSidebar()" aria-label="Alternar menú lateral">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar -->
<nav id="sidebar" class="p-3" aria-label="Navegación principal">
    <div class="logo">
        <i class="bi bi-lightning-charge-fill" aria-hidden="true"></i> Sistema PRO
    </div>

    <ul class="nav flex-column mt-3">
        <li><a class="nav-link active" href="index.php"><i class="bi bi-house-door" aria-hidden="true"></i>Inicio</a></li>

        <!-- Administración -->
        <li class="nav-item">
            <a class="nav-link dropdown-toggle" data-bs-toggle="collapse" href="#menuAdmin" role="button" aria-expanded="false" aria-controls="menuAdmin">
                <i class="bi bi-gear" aria-hidden="true"></i>Administración
            </a>
            <div class="collapse" id="menuAdmin">
                <ul class="nav flex-column">
                    <li><a class="nav-link" href="/zenith/app/vista/Usuarios/listar.php"><i class="bi bi-people" aria-hidden="true"></i>Usuarios</a></li>
                    <li><a class="nav-link" href="/zenith/app/vista/Roles/listar.php"><i class="bi bi-person-badge" aria-hidden="true"></i>Roles</a></li>
                    <li><a class="nav-link" href="/zenith/app/vista/Rol_Usuario/listar.php"><i class="bi bi-arrow-repeat" aria-hidden="true"></i>Roles por Usuario</a></li>
                    <li><a class="nav-link" href="/zenith/app/vista/Estados/listar.php"><i class="bi bi-check-circle" aria-hidden="true"></i>Estados</a></li>
                    <li><a class="nav-link" href="/zenith/app/vista/Usuario_almacen/listar.php"><i class="bi bi-link-45deg" aria-hidden="true"></i>Usuarios Almacén</a></li>
                </ul>
            </div>
        </li>

        <!-- Inventario -->
        <li class="nav-item">
            <a class="nav-link dropdown-toggle" data-bs-toggle="collapse" href="#menuInventario" role="button" aria-expanded="false" aria-controls="menuInventario">
                <i class="bi bi-box" aria-hidden="true"></i>Inventario
            </a>
            <div class="collapse" id="menuInventario">
                <ul class="nav flex-column">
                    <li><a class="nav-link" href="/zenith/app/vista/Categoria/listar.php"><i class="bi bi-tags" aria-hidden="true"></i>Categorías</a></li>
                    <li><a class="nav-link" href="/zenith/app/vista/Unidad_medida/listar.php"><i class="bi bi-rulers" aria-hidden="true"></i>Unidades de Medida</a></li>
                    <li><a class="nav-link" href="/zenith/app/vista/Productos/listar.php"><i class="bi bi-box-seam" aria-hidden="true"></i>Productos</a></li>
                    <li><a class="nav-link" href="/zenith/app/vista/Almacen/listar.php"><i class="bi bi-building" aria-hidden="true"></i>Almacenes</a></li>
                    <li><a class="nav-link" href="/zenith/app/vista/Inventario_Almacen/listar.php"><i class="bi bi-boxes" aria-hidden="true"></i>Inventario por Almacén</a></li>
                </ul>
            </div>
        </li>

        <!-- Movimientos -->
        <li class="nav-item">
            <a class="nav-link dropdown-toggle" data-bs-toggle="collapse" href="#menuMovimientos" role="button" aria-expanded="false" aria-controls="menuMovimientos">
                <i class="bi bi-arrow-left-right" aria-hidden="true"></i>Movimientos
            </a>
            <div class="collapse" id="menuMovimientos">
                <ul class="nav flex-column">
                    <li><a class="nav-link" href="/zenith/app/vista/Entradas/listar.php"><i class="bi bi-box-arrow-in-down-left" aria-hidden="true"></i>Entradas</a></li>
                    <li><a class="nav-link" href="/zenith/app/vista/Salidas/listar.php"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>Salidas</a></li>
                    <li><a class="nav-link" href="/zenith/app/vista/transferencias/listar.php"><i class="bi bi-arrow-left-right" aria-hidden="true"></i>Transferencias</a></li>
                    <li><a class="nav-link" href="/zenith/app/vista/Kardex/listar.php"><i class="bi bi-journal-text" aria-hidden="true"></i>Kardex</a></li>
                </ul>
            </div>
        </li>

        <!-- Precios -->
        <li class="nav-item">
            <a class="nav-link dropdown-toggle" data-bs-toggle="collapse" href="#menuPrecios" role="button" aria-expanded="false" aria-controls="menuPrecios">
                <i class="bi bi-tag" aria-hidden="true"></i>Precios
            </a>
            <div class="collapse" id="menuPrecios">
                <ul class="nav flex-column">
                    <li><a class="nav-link" href="/zenith/app/vista/Monedas/listar.php"><i class="bi bi-currency-exchange" aria-hidden="true"></i>Monedas</a></li>
                    <li><a class="nav-link" href="/zenith/app/vista/Tipos_precio/listar.php"><i class="bi bi-tags" aria-hidden="true"></i>Tipos de Precio</a></li>
                    <li><a class="nav-link" href="/zenith/app/vista/Listas_precio/listar.php"><i class="bi bi-card-list" aria-hidden="true"></i>Listas de Precios</a></li>
                </ul>
            </div>
        </li>

        <!-- Clientes y Ventas -->
        <li class="nav-item">
            <a class="nav-link dropdown-toggle" data-bs-toggle="collapse" href="#menuClientes" role="button" aria-expanded="false" aria-controls="menuClientes">
                <i class="bi bi-people-fill" aria-hidden="true"></i>Clientes y Ventas
            </a>
            <div class="collapse" id="menuClientes">
                <ul class="nav flex-column">
                    <li><a class="nav-link" href="/zenith/app/vista/Clientes/listar.php"><i class="bi bi-person-lines-fill" aria-hidden="true"></i>Clientes</a></li>
                    <li><a class="nav-link" href="/zenith/app/vista/Canal_venta/listar.php"><i class="bi bi-shop" aria-hidden="true"></i>Canales de Venta</a></li>
                    <li><a class="nav-link" href="/zenith/app/vista/Facturacion/listar.php"><i class="bi bi-receipt" aria-hidden="true"></i>Facturación</a></li>
                </ul>
            </div>
        </li>

        <!-- Servicios -->
        <li class="nav-item">
            <a class="nav-link dropdown-toggle" data-bs-toggle="collapse" href="#menuServicios" role="button" aria-expanded="false" aria-controls="menuServicios">
                <i class="bi bi-tools" aria-hidden="true"></i>Servicios
            </a>
            <div class="collapse" id="menuServicios">
                <ul class="nav flex-column">
                    <li><a class="nav-link" href="/zenith/app/vista/Categorias_Servicios/listar.php"><i class="bi bi-grid" aria-hidden="true"></i>Categorías de Servicios</a></li>
                    <li><a class="nav-link" href="/zenith/app/vista/Servicios/listar.php"><i class="bi bi-wrench" aria-hidden="true"></i>Servicios</a></li>
                </ul>
            </div>
        </li>

        <!-- Proveedores -->
        <li class="nav-item">
            <a class="nav-link" href="/zenith/app/vista/Proveedores/listar.php">
                <i class="bi bi-truck" aria-hidden="true"></i>Proveedores
            </a>
        </li>

        <!-- Ubicaciones -->
        <li class="nav-item">
            <a class="nav-link dropdown-toggle" data-bs-toggle="collapse" href="#menuUbicaciones" role="button" aria-expanded="false" aria-controls="menuUbicaciones">
                <i class="bi bi-geo-alt" aria-hidden="true"></i>Ubicaciones
            </a>
            <div class="collapse" id="menuUbicaciones">
                <ul class="nav flex-column">
                    <li><a class="nav-link" href="/zenith/app/vista/Departamentos/listar.php"><i class="bi bi-map" aria-hidden="true"></i>Departamentos</a></li>
                    <li><a class="nav-link" href="/zenith/app/vista/Municipios/listar.php"><i class="bi bi-pin-map" aria-hidden="true"></i>Municipios</a></li>
                </ul>
            </div>
        </li>

        <!-- Cerrar sesión -->
        <li class="nav-item mt-4">
            <a class="nav-link text-warning" href="includes/logout.php">
                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>Cerrar sesión
            </a>
        </li>
    </ul>
</nav>

<!-- Contenido principal -->
<main id="content">
    <h1 class="mb-2">Bienvenido, <span id="nombreUsuario"><?= $nombre ?></span> 👋</h1>
    <p class="text-muted">Selecciona una opción del menú lateral para comenzar.</p>

    <!-- Estadísticas rápidas -->
    <div class="row mt-4" id="estadisticasRapidas">
        <!-- Usuarios activos -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-people display-6 text-primary"></i>
                    <h3 class="mt-2"><?= $totalUsuariosActivos ?></h3>
                    <p class="text-muted mb-0">Usuarios activos</p>
                </div>
            </div>
        </div>

        <!-- Almacenes activos -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-building display-6 text-success"></i>
                    <h3 class="mt-2"><?= $totalAlmacenesActivos ?></h3>
                    <p class="text-muted mb-0">Almacenes activos</p>
                </div>
            </div>
        </div>

        <!-- Entradas hoy -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-box-arrow-in-down-left display-6 text-info"></i>
                    <h3 class="mt-2"><?= $totalEntradasHoy ?></h3>
                    <p class="text-muted mb-0">Entradas hoy</p>
                </div>
            </div>
        </div>

        <!-- Salidas hoy -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-box-arrow-up-right display-6 text-warning"></i>
                    <h3 class="mt-2"><?= $totalSalidasHoy ?></h3>
                    <p class="text-muted mb-0">Salidas hoy</p>
                </div>
            </div>
        </div>

        <!-- Transferencias hoy -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-left-right display-6 text-secondary"></i>
                    <h3 class="mt-2"><?= $totalTransferenciasHoy ?></h3>
                    <p class="text-muted mb-0">Transferencias hoy</p>
                </div>
            </div>
        </div>

        <!-- Alerta vencimientos -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100 border-0 shadow-sm bg-danger text-white">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle display-6"></i>
                    <h3 class="mt-2"><?= count($alertasVenc) ?></h3>
                    <p class="mb-0">Productos próx. a vencer</p>
                    <span class="d-block small mt-1" data-bs-toggle="tooltip" data-bs-placement="bottom" 
                          title="<?= htmlspecialchars(
                                    implode(', ', array_map(
                                        function($a) {
                                            return $a['producto'] . ' (Alm: ' . $a['almacen'] . ', ' . $a['meses_para_vencer'] . ' m)';
                                        }, $alertasVenc))
                                  ) ?>">
                        Pasa el ratón para ver
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos almacenes -->
    <div class="row mt-5">
        <!-- Gráfico de barras horizontal -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Productos por almacén (barras horizontales)</h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoBarrasHorz"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico de circulo (dona) -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Productos por almacén (distribución)</h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoCirculo"></canvas>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<script>
    // Alternar sidebar
    function toggleSidebar(){
        const sb = document.getElementById('sidebar');
        sb.classList.toggle('hide');
        localStorage.setItem('sidebarState', sb.classList.contains('hide') ? 'hidden' : 'visible');
    }
    
    // Restaurar estado sidebar
    document.addEventListener('DOMContentLoaded', () => {
        if(localStorage.getItem('sidebarState') === 'hidden'){
            document.getElementById('sidebar').classList.add('hide');
        }
        
        // Activar tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Datos para gráficos
        const datos = <?= json_encode($datosGrafico) ?>;
        const labels = datos.map(d => d.nombre);
        const values = datos.map(d => d.total_productos);

        // Gráfico de barras horizontales
        new Chart(document.getElementById('graficoBarrasHorz'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Productos distintos',
                    data: values,
                    backgroundColor: '#0e1b2f'
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });

        // Gráfico circular (dona)
        new Chart(document.getElementById('graficoCirculo'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: [
                        '#0e1b2f','#1f3a5f','#c9a227','#f4c430','#7f8c8d'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    });
</script>
</body>
</html>