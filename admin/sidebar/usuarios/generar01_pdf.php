<?php
require_once '../cnx.php';
require('../../../fpdf/fpdf.php');

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'Lista de Usuarios', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Create new PDF instance
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 11);

// Table Header
$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(70, 10, 'Nombre', 1, 0, 'C', true);
$pdf->Cell(70, 10, 'Email', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Rol', 1, 1, 'C', true);

// Set font for data
$pdf->SetFont('Arial', '', 10);

// Query to get user data
$query = "SELECT u.nombre, u.email, r.nombre as rol_nombre 
          FROM usuarios u 
          JOIN roles r ON u.rol_id = r.id
          WHERE r.id != 3
          ORDER BY u.nombre ASC";

$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(70, 10, utf8_decode($row['nombre']), 1);
        $pdf->Cell(70, 10, utf8_decode($row['email']), 1);
        $pdf->Cell(50, 10, utf8_decode($row['rol_nombre']), 1, 1);
    }
} else {
    $pdf->Cell(190, 10, 'Error al obtener los datos.', 1, 1, 'C');
}

// Output the PDF
$pdf->Output('I', 'Lista_Usuarios.pdf');