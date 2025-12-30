<?php
/**
 * permisos.php  (verificarAcceso)
 * Entran los roles IGUALES o INFERIORES al requerido.
 * Si ya salió output hace un redirect vía JS para evitar
 * “Cannot modify header information”.
 */
require_once __DIR__ . '/../app/Conexion/Conexion.php';

function verificarAcceso(int $rolRequerido): bool
{
    /* ---------- 1. Sesión válida ---------- */
    if (empty($_SESSION['usuario_id'])) {
        redirigir('../../../includes/Sin_acceso.php');
    }

    /* ---------- 2. Consultamos el rol más bajo del usuario ---------- */
    $conexion = new Conexion();
    $db       = $conexion->getConexion();

    $stmt = $db->prepare("
        SELECT r.id AS rol_id
        FROM roles r
        JOIN rol_usuario ru ON ru.rol_id = r.id
        WHERE ru.usuario_id = :usuario_id
        ORDER BY r.id ASC
        LIMIT 1
    ");
    $stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
    $rolUsuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rolUsuario) {            // sin rol -> fuera
        redirigir('../../../includes/Sin_acceso.php');
    }

    $rolUsuarioId = (int) $rolUsuario['rol_id'];

    /* ---------- 3. Solo pasan roles <= al requerido ---------- */
    if ($rolUsuarioId > $rolRequerido) {
        redirigir('../../../includes/Sin_acceso.php');
    }

    return true;
}

/* -------------------------------------------------------------------- */
/* Helper: hace header() si aún se puede, sino redirección con JS      */
/* -------------------------------------------------------------------- */
function redirigir(string $url): void
{
    if (!headers_sent()) {          // aún podemos usar header()
        ob_clean();                 // limpia buffer por si acaso
        header("Location: $url");
    } else {                        // ya salió output -> plan B
        echo "<script>window.location='$url';</script>";
    }
    exit;
}