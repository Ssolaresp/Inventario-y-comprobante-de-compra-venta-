<!-- header.php -->
<nav class="navbar navbar-expand-lg navbar-dark topbar">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">Sistema PRO</span>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="topbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario'); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="/CRUD_USUARIOS/app/vista/Usuarios/perfil.php"><i class="bi bi-person"></i> Mi perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/CRUD_USUARIOS/includes/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Estilos topbar -->
<style>
    .topbar {
        background-color: #0e1b2f;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        position: sticky;
        top: 0;
        z-index: 1030;
    }

    .topbar .navbar-brand,
    .topbar .nav-link {
        color: #ffffff !important;
        font-weight: 500;
    }

    .topbar .nav-link:hover {
        color: #c9a227 !important;
    }

    .topbar .dropdown-menu {
        background-color: #1f3a5f;
        border: none;
    }

    .topbar .dropdown-item {
        color: #ffffff;
    }

    .topbar .dropdown-item:hover {
        background-color: #c9a227;
        color: #000;
    }
</style>