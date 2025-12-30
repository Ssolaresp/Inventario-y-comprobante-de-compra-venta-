<?php
// CRÍTICO: No debe haber NADA antes de esta línea (ni espacios, ni BOM)
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Desactivar salida de errores directa
ini_set('display_errors', 0);
error_reporting(0);

try {
    // Iniciar sesión si es necesario
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Incluir el controlador (ajusta la ruta según tu estructura)
    require_once __DIR__ . '/../../../Controlador/ClientesControlador.php';
    
    // Validar que llegó el parámetro
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('No se recibió el ID del departamento');
    }
    
    $idDepartamento = intval($_GET['id']);
    
    if ($idDepartamento <= 0) {
        throw new Exception('ID de departamento inválido');
    }
    
    // Obtener municipios
    $controlador = new ClientesControlador();
    $municipios = $controlador->obtenerMunicipios($idDepartamento);
    
    // Verificar que se obtuvieron datos
    if (!is_array($municipios)) {
        $municipios = [];
    }
    
    // Devolver JSON
    echo json_encode($municipios, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // En caso de error, devolver JSON con el error
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'mensaje' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
exit;