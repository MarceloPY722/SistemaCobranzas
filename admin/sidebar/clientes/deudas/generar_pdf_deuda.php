<?php
require_once '../../cnx.php';
require('../../../../fpdf/fpdf.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../ver_clientes.php?error=id_invalido');
    exit();
}

$deuda_id = $_GET['id'];

$query = "SELECT d.*, c.nombre as cliente_nombre, c.id as cliente_id, 
          c.email, c.telefono, c.identificacion,
          p.nombre as politica_nombre, p.tasa, p.tipo as politica_tipo
          FROM deudas d 
          JOIN clientes c ON d.cliente_id = c.id
          JOIN politicas_interes p ON d.politica_interes_id = p.id
          WHERE d.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $deuda_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ../../ver_clientes.php?error=deuda_no_encontrada');
    exit();
}

$deuda = $result->fetch_assoc();

// Consulta para obtener los pagos relacionados con esta deuda
$query_pagos = "SELECT * FROM pagos WHERE deuda_id = ? ORDER BY fecha_pago DESC";
$stmt_pagos = $conn->prepare($query_pagos);
$stmt_pagos->bind_param("i", $deuda_id);
$stmt_pagos->execute();
$result_pagos = $stmt_pagos->get_result();

// Calcular días de atraso si está vencida
$dias_atraso = 0;
if ($deuda['estado'] == 'vencido') {
    $fecha_vencimiento = new DateTime($deuda['fecha_vencimiento']);
    $hoy = new DateTime();
    $diff = $hoy->diff($fecha_vencimiento);
    $dias_atraso = $diff->days;
}

// Calcular interés acumulado
$interes_acumulado = 0;
if ($deuda['estado'] == 'vencido') {
    $interes_diario = ($deuda['tasa'] / 100) / 30; // Tasa mensual dividida por 30 días
    $interes_acumulado = $deuda['saldo_pendiente'] * $interes_diario * $dias_atraso;
}

// Función para formatear dinero
function formatMoney($amount) {
    return number_format($amount, 0, ',', '.') . ' Gs.';
}

// Crear PDF
class PDF extends FPDF {
    function Header() {
        // No incluimos encabezado predeterminado ya que lo haremos manualmente
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        // Fix the encoding issue by using ASCII characters instead of UTF-8
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
    
    // Función para dibujar un rectángulo con borde
    function RectangleWithBorder($x, $y, $w, $h) {
        $this->Rect($x, $y, $w, $h);
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Centered main title with better spacing
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetXY(0, 15);
$pdf->Cell(210, 10, 'COMPROBANTE DE DEUDA', 0, 1, 'C');

// Encabezado con información del cliente - better positioned
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetXY(0, 30);
$pdf->Cell(210, 7, 'Informacion Personal Del Cliente', 0, 1, 'C');

// Client info in a bordered box

$pdf->SetFont('Arial', '', 12);
$pdf->SetXY(25, 45);
$pdf->Cell(40, 7, 'Cliente', 0, 0);
$pdf->Cell(100, 7, utf8_decode($deuda['cliente_nombre']), 0, 1);
$pdf->SetX(25);
$pdf->Cell(40, 7, 'Email', 0, 0);
$pdf->Cell(100, 7, $deuda['email'], 0, 1);
$pdf->SetX(25);
$pdf->Cell(40, 7, 'Telefono', 0, 0);
$pdf->Cell(100, 7, $deuda['telefono'], 0, 1);
$pdf->SetX(25);
$pdf->Cell(40, 7, 'CI', 0, 0);
$pdf->Cell(100, 7, $deuda['identificacion'], 0, 1);


// Etiquetas para los rectángulos - better positioned
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetXY(20, 80);
$pdf->Cell(80, 10, 'INFORMACION', 0, 0, 'C');
$pdf->SetXY(110, 80);
$pdf->Cell(80, 10, 'POLITICA DE INTERES', 0, 0, 'C');

// Información en el rectángulo izquierdo - better spacing
$pdf->SetFont('Arial', '', 12);
$pdf->SetXY(25, 90);
$pdf->MultiCell(70, 7, 'Descripcion: ' . utf8_decode($deuda['descripcion']) . "\n\n" .
                       'Monto Original: ' . formatMoney($deuda['monto']) . "\n\n" .
                       'Saldo Pendiente: ' . formatMoney($deuda['saldo_pendiente']) . "\n\n" .
                       'Fecha Emision: ' . date('d/m/Y', strtotime($deuda['fecha_emision'])) . "\n\n" .
                       'Fecha Vencimiento: ' . date('d/m/Y', strtotime($deuda['fecha_vencimiento'])) . "\n\n" .
                       'Estado: ' . ucfirst($deuda['estado']), 0, 'L');

// Información en el rectángulo derecho - better spacing
$pdf->SetXY(115, 90);
$pdf->MultiCell(70, 7, 'Politica: ' . utf8_decode($deuda['politica_nombre']) . "\n\n" .
                       'Tasa de Interes: ' . $deuda['tasa'] . '% mensual' . "\n\n" .
                       'Tipo: ' . ucfirst(utf8_decode($deuda['politica_tipo'])), 0, 'L');

// Si está vencida, agregar información de mora
if($deuda['estado'] == 'vencido') {
    $pdf->SetXY(115, 130);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(70, 7, 'Información de Mora:', 0, 1);
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetX(115);
    $pdf->MultiCell(70, 7, 'Días de Atraso: ' . $dias_atraso . ' días' . "\n" .
                           'Interés Acumulado: ' . formatMoney($interes_acumulado) . "\n" .
                           'Total a Pagar: ' . formatMoney($deuda['saldo_pendiente'] + $interes_acumulado), 0, 'L');
}

// Dibujar el rectángulo para pagos - better positioned
$pdf->RectangleWithBorder(20, 195, 170, 80);

// Título de pagos centrado
$pdf->SetXY(20, 195);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(170, 10, 'Pagos Realizados', 0, 1, 'C');

// Información de pagos en el rectángulo inferior
$pdf->SetFont('Arial', '', 11);

if($result_pagos->num_rows > 0) {
    $pdf->SetX(25);
    $pdf->Cell(20, 7, 'ID', 1, 0, 'C');
    $pdf->Cell(45, 7, 'Fecha', 1, 0, 'C');
    $pdf->Cell(50, 7, 'Monto', 1, 0, 'C');
    $pdf->Cell(45, 7, 'Método', 1, 0, 'C');
    $pdf->Ln();
    
    $pdf->SetFont('Arial', '', 10);
    $count = 0;
    while($pago = $result_pagos->fetch_assoc() && $count < 5) { // Increased to 5 payments
        $pdf->SetX(25);
        $pdf->Cell(20, 6, $pago['id'], 1, 0, 'C');
        $pdf->Cell(45, 6, date('d/m/Y', strtotime($pago['fecha_pago'])), 1, 0, 'C');
        $pdf->Cell(50, 6, formatMoney($pago['monto_pagado']), 1, 0, 'R');
        $pdf->Cell(45, 6, utf8_decode($pago['metodo_pago']), 1, 0, 'C');
        $pdf->Ln();
        $count++;
    }
    
    if($result_pagos->num_rows > 5) {
        $pdf->SetX(25);
        $pdf->Cell(160, 6, '... y ' . ($result_pagos->num_rows - 5) . ' pagos más', 0, 1, 'C');
    }
    
    // Add total amount paid
    $query_total = "SELECT SUM(monto_pagado) as total FROM pagos WHERE deuda_id = ?";
    $stmt_total = $conn->prepare($query_total);
    $stmt_total->bind_param("i", $deuda_id);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $total_pagado = $result_total->fetch_assoc()['total'];
    
    $pdf->Ln(5);
    $pdf->SetX(25);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(115, 7, 'Total Pagado:', 0, 0, 'R');
    $pdf->Cell(45, 7, formatMoney($total_pagado), 0, 1, 'R');
} else {
    $pdf->SetX(25);
    $pdf->Cell(160, 20, 'No hay pagos registrados para esta deuda.', 0, 1, 'C');
}


$pdf->Output('Deuda_' . $deuda_id . '.pdf', 'I');
?>