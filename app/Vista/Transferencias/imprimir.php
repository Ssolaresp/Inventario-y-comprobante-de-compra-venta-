<?php
// NO BORRES ESTA LÍNEA - Debe ser lo primero del archivo
ob_start();
session_start();

// Validar sesión
if (!isset($_SESSION['usuario_id'])) {
    ob_end_clean();
    header('Location: ../../login.php');
    exit;
}

// Incluir dependencias
require_once '../../Controlador/TransferenciasControlador.php';

// Verificar si TCPDF está instalado
if (!class_exists('TCPDF')) {
    if (file_exists(__DIR__ . '/../../../vendor/autoload.php')) {
        require_once __DIR__ . '/../../../vendor/autoload.php';
    } else {
        ob_end_clean();
        die('Error: TCPDF no está instalado. Ejecuta: composer require tecnickcom/tcpdf');
    }
}

// Obtener ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    ob_end_clean();
    die('ID inválido');
}

// Obtener datos
try {
    $controlador = new TransferenciasControlador();
    $transferencia = $controlador->obtener($id);
    
    if (!$transferencia) {
        throw new Exception('Transferencia no encontrada');
    }
    
    $detalle = $controlador->obtenerDetalle($id);
    
} catch (Exception $e) {
    ob_end_clean();
    die('Error: ' . htmlspecialchars($e->getMessage()));
}

// IMPORTANTE: Limpiar buffer antes de generar PDF
ob_end_clean();

// ==================== GENERAR PDF ====================
$pdf = new \TCPDF('P', 'mm', 'LETTER', true, 'UTF-8');

// Configuración
$pdf->SetCreator('Sistema Zenith');
$pdf->SetAuthor('Sistema de Inventario');
$pdf->SetTitle('Transferencia ' . $transferencia['numero_transferencia']);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

// Colores
$azul = [41, 128, 185];
$gris = [189, 195, 199];

// ========== TÍTULO ==========
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor($azul[0], $azul[1], $azul[2]);
$pdf->Cell(0, 10, 'ORDEN DE TRANSFERENCIA', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 5, 'Sistema Zenith - Gestión de Inventario', 0, 1, 'C');
$pdf->Ln(8);

// ========== INFORMACIÓN PRINCIPAL ==========
$pdf->SetFillColor($azul[0], $azul[1], $azul[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, ' DATOS GENERALES', 0, 1, 'L', true);
$pdf->Ln(2);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 9);

// Tabla 2x2
$datos = [
    ['Número:', $transferencia['numero_transferencia'], 'Estado:', $transferencia['estado']],
    ['Fecha:', date('d/m/Y H:i', strtotime($transferencia['fecha_solicitud'])), 'Solicitante:', $transferencia['usuario_solicita'] ?? 'N/A']
];

foreach ($datos as $fila) {
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(40, 6, $fila[0], 1);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(50, 6, $fila[1], 1);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(40, 6, $fila[2], 1);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(50, 6, $fila[3], 1, 1);
}

$pdf->Ln(5);

// ========== ALMACENES ==========
$pdf->SetFillColor($azul[0], $azul[1], $azul[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, ' ALMACENES', 0, 1, 'L', true);
$pdf->Ln(2);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(30, 6, 'Origen:', 1);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(150, 6, $transferencia['almacen_origen'], 1, 1);

$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(30, 6, 'Destino:', 1);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(150, 6, $transferencia['almacen_destino'], 1, 1);

$pdf->Ln(5);

// ========== PRODUCTOS ==========
$pdf->SetFillColor($azul[0], $azul[1], $azul[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, ' PRODUCTOS', 0, 1, 'L', true);
$pdf->Ln(2);

// Encabezados
$pdf->SetFillColor($gris[0], $gris[1], $gris[2]);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(12, 7, '#', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Código', 1, 0, 'C', true);
$pdf->Cell(90, 7, 'Producto', 1, 0, 'C', true);
$pdf->Cell(23, 7, 'Enviado', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Recibido', 1, 1, 'C', true);

// Datos
$pdf->SetFont('helvetica', '', 8);
$num = 1;
$totalEnv = 0;
$totalRec = 0;

foreach ($detalle as $item) {
    $cantEnv = $item['cantidad'];
    $cantRec = $item['cantidad_recibida'] ?? 0;
    
    $totalEnv += $cantEnv;
    $totalRec += $cantRec;
    
    $pdf->Cell(12, 6, $num++, 1, 0, 'C');
    $pdf->Cell(30, 6, $item['codigo_producto'], 1);
    $pdf->Cell(90, 6, substr($item['producto'], 0, 50), 1);
    $pdf->Cell(23, 6, $cantEnv, 1, 0, 'C');
    $pdf->Cell(25, 6, $cantRec > 0 ? $cantRec : '-', 1, 1, 'C');
}

// Totales
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(132, 6, 'TOTALES', 1, 0, 'R', true);
$pdf->Cell(23, 6, $totalEnv, 1, 0, 'C', true);
$pdf->Cell(25, 6, $totalRec > 0 ? $totalRec : '-', 1, 1, 'C', true);

// Observaciones
if (!empty($transferencia['observaciones'])) {
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(0, 5, 'Observaciones:', 0, 1);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(0, 4, $transferencia['observaciones'], 1);
}

// ========== FIRMAS ==========
$pdf->Ln(15);
$pdf->SetFont('helvetica', '', 8);

$w = 55;
$gap = 7;

$pdf->Cell($w, 5, '', 0, 0);
$pdf->Cell($gap, 5, '', 0, 0);
$pdf->Cell($w, 5, '', 0, 0);
$pdf->Cell($gap, 5, '', 0, 0);
$pdf->Cell($w, 5, '', 0, 1);

$pdf->Cell($w, 0.5, '', 'T', 0);
$pdf->Cell($gap, 0.5, '', 0, 0);
$pdf->Cell($w, 0.5, '', 'T', 0);
$pdf->Cell($gap, 0.5, '', 0, 0);
$pdf->Cell($w, 0.5, '', 'T', 1);

$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell($w, 5, 'Solicita', 0, 0, 'C');
$pdf->Cell($gap, 5, '', 0, 0);
$pdf->Cell($w, 5, 'Envía', 0, 0, 'C');
$pdf->Cell($gap, 5, '', 0, 0);
$pdf->Cell($w, 5, 'Recibe', 0, 1, 'C');

// Pie de página
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 7);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 4, 'Generado: ' . date('d/m/Y H:i:s'), 0, 1, 'C');

// Salida
$pdf->Output('Transferencia_' . $transferencia['numero_transferencia'] . '.pdf', 'I');
exit;
?>