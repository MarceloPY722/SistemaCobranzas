<?php
// Start output buffering to prevent any output before PDF
ob_start();

require_once '../../cnx.php';
require('../../../../fpdf/fpdf.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirect instead of outputting error messages
    ob_end_clean(); // Clear buffer
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
    // Redirect instead of outputting error messages
    ob_end_clean(); // Clear buffer
    header('Location: ../../ver_clientes.php?error=deuda_no_encontrada');
    exit();
}

$deuda = $result->fetch_assoc();

// Consulta para obtener los pagos de la deuda
$query_pagos = "SELECT * FROM pagos WHERE deuda_id = ? ORDER BY fecha_pago ASC";
$stmt_pagos = $conn->prepare($query_pagos);
$stmt_pagos->bind_param("i", $deuda_id);
$stmt_pagos->execute();
$result_pagos = $stmt_pagos->get_result();

// Función para formatear dinero
function formatMoney($amount) {
    // Check if amount is null or not numeric
    if ($amount === null || !is_numeric($amount)) {
        return '0 Gs.';
    }
    return number_format($amount, 0, ',', '.') . ' Gs.';
}

// Función para manejar caracteres especiales
function utf8_to_win1252($text) {
    if (!is_string($text) || $text === null) {
        return '';
    }
    
    // Reemplazos manuales para caracteres problemáticos
    $text = str_replace('á', 'a', $text);
    $text = str_replace('é', 'e', $text);
    $text = str_replace('í', 'i', $text);
    $text = str_replace('ó', 'o', $text);
    $text = str_replace('ú', 'u', $text);
    $text = str_replace('ñ', 'n', $text);
    $text = str_replace('Á', 'A', $text);
    $text = str_replace('É', 'E', $text);
    $text = str_replace('Í', 'I', $text);
    $text = str_replace('Ó', 'O', $text);
    $text = str_replace('Ú', 'U', $text);
    $text = str_replace('Ñ', 'N', $text);
    
    return $text;
}

// Crear PDF
class PDF extends FPDF {
    function Header() {
        // Logo - Fix the path to the logo image
        // Using an absolute path to ensure the file is found
        $logo_path = 'C:/laragon/www/sistemacobranzas/assets/img/logo.png';
        
        // Check if the file exists before trying to use it
        if (file_exists($logo_path)) {
            $this->Image($logo_path, 10, 10, 30);
        }
        
        // Título
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'DETALLE DE DEUDA', 0, 1, 'C');
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Información del cliente
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Informacion del Cliente', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 7, 'Cliente:', 0);
$pdf->Cell(0, 7, $deuda['cliente_nombre'] ?? 'No disponible', 0, 1);
$pdf->Cell(40, 7, 'Identificacion:', 0);
$pdf->Cell(0, 7, $deuda['identificacion'] ?? 'No disponible', 0, 1);
$pdf->Cell(40, 7, 'Telefono:', 0);
$pdf->Cell(0, 7, $deuda['telefono'] ?? 'No disponible', 0, 1);
$pdf->Cell(40, 7, 'Email:', 0);
$pdf->Cell(0, 7, $deuda['email'] ?? 'No disponible', 0, 1);
$pdf->Ln(5);

// Información de la deuda
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Informacion de la Deuda', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(60, 7, 'Descripcion:', 0);
$pdf->Cell(0, 7, utf8_to_win1252($deuda['descripcion'] ?? 'No disponible'), 0, 1);
$pdf->Cell(60, 7, 'Monto Original:', 0);
$pdf->Cell(0, 7, formatMoney($deuda['monto']), 0, 1);
$pdf->Cell(60, 7, 'Saldo Pendiente:', 0);
$pdf->Cell(0, 7, formatMoney($deuda['saldo_pendiente']), 0, 1);
$pdf->Cell(60, 7, 'Fecha de Emision:', 0);
$pdf->Cell(0, 7, $deuda['fecha_emision'] ? date('d/m/Y', strtotime($deuda['fecha_emision'])) : 'No disponible', 0, 1);
$pdf->Cell(60, 7, 'Fecha de Vencimiento:', 0);
$pdf->Cell(0, 7, $deuda['fecha_vencimiento'] ? date('d/m/Y', strtotime($deuda['fecha_vencimiento'])) : 'No disponible', 0, 1);
$pdf->Cell(60, 7, 'Estado:', 0);
$pdf->Cell(0, 7, ucfirst($deuda['estado'] ?? 'No disponible'), 0, 1);
$pdf->Cell(60, 7, 'Politica de Interes:', 0);
$pdf->Cell(0, 7, utf8_to_win1252($deuda['politica_nombre'] ?? 'No disponible') . ' (' . ($deuda['tasa'] ?? '0') . '%)', 0, 1);
$pdf->Ln(5);

// Historial de pagos
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Historial de Pagos', 0, 1);

// Add debug info about number of payments found
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 5, "ID de deuda consultado: {$deuda_id} - Pagos encontrados: {$result_pagos->num_rows}", 0, 1);
$pdf->SetFont('Arial', '', 10);

if ($result_pagos->num_rows > 0) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 7, 'Fecha', 1);
    $pdf->Cell(40, 7, 'Monto', 1);
    $pdf->Cell(40, 7, 'Método', 1);
    $pdf->Cell(0, 7, 'Referencia', 1);
    $pdf->Ln();
    
    $pdf->SetFont('Arial', '', 10);
    $total_pagado = 0;
    
    while ($pago = $result_pagos->fetch_assoc()) {
        // Add debug info for each payment
        $fecha = isset($pago['fecha_pago']) ? date('d/m/Y', strtotime($pago['fecha_pago'])) : 'N/A';
        $monto = isset($pago['monto']) ? formatMoney($pago['monto']) : '0 Gs.';
        $metodo = isset($pago['metodo_pago']) ? $pago['metodo_pago'] : 'N/A';
        $referencia = isset($pago['referencia']) ? $pago['referencia'] : '';
        
        $pdf->Cell(40, 7, $fecha, 1);
        $pdf->Cell(40, 7, $monto, 1);
        $pdf->Cell(40, 7, $metodo, 1);
        $pdf->Cell(0, 7, $referencia, 1);
        $pdf->Ln();
        
        $total_pagado += isset($pago['monto']) ? $pago['monto'] : 0;
    }
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 7, 'Total Pagado:', 1);
    $pdf->Cell(40, 7, formatMoney($total_pagado), 1);
    $pdf->Cell(0, 7, '', 1);
    $pdf->Ln();
} else {
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 7, 'No hay pagos registrados para esta deuda.', 1, 1);
}

// Información de mora (si aplica)
if (isset($deuda['estado']) && $deuda['estado'] == 'vencido') {
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Información de Mora', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    
    // Calcular días de mora
    $fecha_vencimiento = isset($deuda['fecha_vencimiento']) ? new DateTime($deuda['fecha_vencimiento']) : null;
    $hoy = new DateTime();
    $dias_mora = $fecha_vencimiento ? $hoy->diff($fecha_vencimiento)->days : 0;
    
    // Calcular interés acumulado
    $interes_acumulado = 0;
    if (isset($deuda['saldo_pendiente']) && isset($deuda['tasa']) && $fecha_vencimiento) {
        if ($deuda['politica_tipo'] == 'diario') {
            $interes_acumulado = ($deuda['saldo_pendiente'] * ($deuda['tasa'] / 100) * $dias_mora);
        } else {
            // Mensual (aproximado)
            $interes_acumulado = ($deuda['saldo_pendiente'] * ($deuda['tasa'] / 100) * ($dias_mora / 30));
        }
    }
    
    $pdf->Cell(60, 7, 'Días de Mora:', 0);
    $pdf->Cell(0, 7, $dias_mora . ' días', 0, 1);
    $pdf->Cell(60, 7, 'Interés Acumulado:', 0);
    $pdf->Cell(0, 7, formatMoney($interes_acumulado), 0, 1);
    $pdf->Cell(60, 7, 'Total a Pagar:', 0);
    $pdf->Cell(0, 7, formatMoney(($deuda['saldo_pendiente'] ?? 0) + $interes_acumulado), 0, 1);
}

// Clean any output and send the PDF
ob_end_clean();
$pdf->Output('I', 'Deuda_' . $deuda_id . '.pdf');
?>