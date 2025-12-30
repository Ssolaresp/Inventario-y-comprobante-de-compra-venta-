<?php
// NO BORRES - Debe ser lo primero
ob_start();
session_start();

if (!isset($_SESSION['usuario_id'])) {
    ob_end_clean();
    header('Location: ../../login.php');
    exit;
}

require_once '../../Controlador/SalidasAlmacenControlador.php';

// Verificar TCPDF
if (!class_exists('TCPDF')) {
    if (file_exists(__DIR__ . '/../../../vendor/autoload.php')) {
        require_once __DIR__ . '/../../../vendor/autoload.php';
    } else {
        ob_end_clean();
        die('Error: TCPDF no instalado. Ejecuta: composer require tecnickcom/tcpdf');
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
    $controlador = new SalidasAlmacenControlador();
    $salida = $controlador->obtenerParaPDF($id);
    
    if (!$salida) {
        throw new Exception('Salida no encontrada');
    }
    
    $detalle = $controlador->obtenerDetalle($id);
    
} catch (Exception $e) {
    ob_end_clean();
    die('Error: ' . htmlspecialchars($e->getMessage()));
}

// Limpiar buffer
ob_end_clean();

// ==================== GENERAR PDF ====================
$pdf = new \TCPDF('P', 'mm', 'LETTER', true, 'UTF-8');

$pdf->SetCreator('Sistema Zenith');
$pdf->SetAuthor('Sistema de Inventario');
$pdf->SetTitle('Salida ' . $salida['numero_salida']);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

// Colores
$rojo = [231, 76, 60];
$gris = [189, 195, 199];

// ========== TÍTULO ==========
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor($rojo[0], $rojo[1], $rojo[2]);
$pdf->Cell(0, 10, 'SALIDA DE ALMACÉN', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 5, 'Sistema Zenith - Gestión de Inventario', 0, 1, 'C');
$pdf->Ln(8);

// ========== INFORMACIÓN PRINCIPAL ==========
$pdf->SetFillColor($rojo[0], $rojo[1], $rojo[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, ' DATOS DE LA SALIDA', 0, 1, 'L', true);
$pdf->Ln(2);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 9);

// Tabla información
$datos = [
    ['Número:', $salida['numero_salida'], 'Estado:', $salida['estado']],
    ['Almacén:', $salida['almacen'], 'Tipo:', $salida['tipo_salida']],
    ['Fecha Salida:', date('d/m/Y H:i', strtotime($salida['fecha_salida'])), 'Doc. Ref.:', $salida['documento_referencia'] ?: 'N/A'],
    ['Usuario Registra:', $salida['usuario_registra'] ?? 'N/A', 'Usuario Autoriza:', $salida['usuario_autoriza'] ?? 'N/A']
];

if ($salida['fecha_autorizacion']) {
    $datos[] = ['Fecha Autorización:', date('d/m/Y H:i', strtotime($salida['fecha_autorizacion'])), '', ''];
}

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

// Motivo
if (!empty($salida['motivo'])) {
    $pdf->Ln(2);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(40, 6, 'Motivo:', 1);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(140, 6, substr($salida['motivo'], 0, 80), 1, 1);
}

$pdf->Ln(5);

// ========== PRODUCTOS ==========
$pdf->SetFillColor($rojo[0], $rojo[1], $rojo[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, ' DETALLE DE PRODUCTOS', 0, 1, 'L', true);
$pdf->Ln(2);

// Encabezados
$pdf->SetFillColor($gris[0], $gris[1], $gris[2]);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(12, 7, '#', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Código', 1, 0, 'C', true);
$pdf->Cell(70, 7, 'Producto', 1, 0, 'C', true);
$pdf->Cell(20, 7, 'Cant.', 1, 0, 'C', true);
$pdf->Cell(24, 7, 'P. Unit.', 1, 0, 'C', true);
$pdf->Cell(24, 7, 'Subtotal', 1, 1, 'C', true);

// Datos
$pdf->SetFont('helvetica', '', 8);
$num = 1;
$totalCantidad = 0;
$totalGeneral = 0;

foreach ($detalle as $item) {
    $subtotal = $item['cantidad'] * $item['precio_unitario'];
    $totalCantidad += $item['cantidad'];
    $totalGeneral += $subtotal;
    
    $pdf->Cell(12, 6, $num++, 1, 0, 'C');
    $pdf->Cell(30, 6, $item['codigo_producto'], 1);
    $pdf->Cell(70, 6, substr($item['producto'], 0, 40), 1);
    $pdf->Cell(20, 6, $item['cantidad'], 1, 0, 'C');
    $pdf->Cell(24, 6, 'Q ' . number_format($item['precio_unitario'], 2), 1, 0, 'R');
    $pdf->Cell(24, 6, 'Q ' . number_format($subtotal, 2), 1, 1, 'R');
    
    // Observaciones del item
    if (!empty($item['observaciones'])) {
        $pdf->SetFont('helvetica', 'I', 7);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(12, 4, '', 0);
        $pdf->Cell(168, 4, 'Obs: ' . substr($item['observaciones'], 0, 80), 0, 1);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);
    }
}

// Totales
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(112, 6, 'TOTALES', 1, 0, 'R', true);
$pdf->Cell(20, 6, $totalCantidad, 1, 0, 'C', true);
$pdf->Cell(24, 6, '', 1, 0, 'C', true);
$pdf->Cell(24, 6, 'Q ' . number_format($totalGeneral, 2), 1, 1, 'R', true);

// ========== FIRMAS ==========
$pdf->Ln(15);
$pdf->SetFont('helvetica', '', 8);

$w = 60;
$gap = 30;

$pdf->Cell($w, 5, '', 0, 0);
$pdf->Cell($gap, 5, '', 0, 0);
$pdf->Cell($w, 5, '', 0, 1);

$pdf->Cell($w, 0.5, '', 'T', 0);
$pdf->Cell($gap, 0.5, '', 0, 0);
$pdf->Cell($w, 0.5, '', 'T', 1);

$pdf->SetFont('helvetica', 'B', 8);
$pdf->Cell($w, 5, 'Registra', 0, 0, 'C');
$pdf->Cell($gap, 5, '', 0, 0);
$pdf->Cell($w, 5, 'Autoriza / Recibe', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 7);
$pdf->Cell($w, 4, $salida['usuario_registra'] ?? '', 0, 0, 'C');
$pdf->Cell($gap, 4, '', 0, 0);
$pdf->Cell($w, 4, $salida['usuario_autoriza'] ?? '', 0, 1, 'C');

// Pie de página
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 7);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 4, 'Generado: ' . date('d/m/Y H:i:s'), 0, 1, 'C');

// Salida
$pdf->Output('Salida_' . $salida['numero_salida'] . '.pdf', 'I');
exit;
?>