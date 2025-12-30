<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

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
            margin: 0;
            padding: 0;
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
            z-index: 1050;
        }

        #sidebar.hide {
            transform: translateX(-100%);
        }

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

        #sidebar .collapse {
            padding-left: 20px;
        }

        #content {
            margin-left: 260px;
            padding: 40px;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }

        #sidebar.hide ~ #content {
            margin-left: 0;
        }

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

        #btn-toggle:hover {
            background-color: var(--sidebar-hover);
        }

        h1, h2, h3 {
            color: var(--sidebar-bg);
            font-weight: 700;
        }

        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.show {
                transform: translateX(0);
            }

            #content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<!-- Botón hamburguesa -->
<button id="btn-toggle" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar -->
<nav id="sidebar" class="p-3">
    <div class="logo">
        <i class="bi bi-lightning-charge-fill"></i> Sistema PRO
    </div>

    <ul class="nav flex-column mt-3">
        <li><a class="nav-link" href="/zenith/index.php"><i class="bi bi-house-door"></i>Inicio</a></li>

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
            <a class="nav-link text-warning" href="/zenith/includes/logout.php">
                <i class="bi bi-box-arrow-right"></i>Cerrar sesión
            </a>
        </li>
    </ul>
</nav>

<!-- Aquí comienza el contenido -->
<div id="content">

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('show');
        sidebar.classList.toggle('hide');
    }

    // Marcar enlace activo
    const links = document.querySelectorAll('#sidebar .nav-link');
    links.forEach(link => {
        if (link.href === window.location.href) {
            link.classList.add('active');
            const parent = link.closest('.collapse');
            if (parent) {
                parent.classList.add('show');
            }
        }
    });
</script>