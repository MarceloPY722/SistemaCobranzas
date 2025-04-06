<?php
session_start();
require_once '../include/cnx.php';

// Verificar si el usuario está logueado como cliente
if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../../index.php');
    exit;
}

$cliente_id = $_SESSION['cliente_id'];

// Verificar si se proporcionó un ID de pago
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../prestamos/ver_prestamos.php?error=id_invalido');
    exit;
}

$pago_id = $_GET['id'];

// Obtener los datos del pago y verificar que pertenezca al cliente
$query = "SELECT p.*, d.cliente_id, d.descripcion as deuda_descripcion, c.numero_cuota, c.monto_cuota, cl.nombre as cliente_nombre
          FROM pagos p 
          JOIN deudas d ON p.deuda_id = d.id 
          LEFT JOIN cuotas_deuda c ON p.cuota_id = c.id
          JOIN clientes cl ON d.cliente_id = cl.id
          WHERE p.id = ? AND d.cliente_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$pago_id, $cliente_id]);

if ($stmt->rowCount() === 0) {
    header('Location: ../prestamos/ver_prestamos.php?error=pago_no_encontrado');
    exit;
}

$pago = $stmt->fetch();

// Función para formatear montos
function formatMoney($amount) {
    return '₲ ' . number_format($amount, 0, ',', '.');
}

// Generar el comprobante en HTML
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pago #<?php echo $pago_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .comprobante {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 0.9em;
            color: #6c757d;
        }
        .print-btn {
            margin-top: 20px;
            text-align: center;
        }
        @media print {
            .print-btn {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="comprobante">
        <div class="header">
            <h2>COMPROBANTE DE PAGO</h2>
            <h4>Sistema de Cobranzas</h4>
        </div>
        
        <div class="info-row">
            <div><strong>Comprobante N°:</strong> <?php echo $pago_id; ?></div>
            <div><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($pago['fecha_pago'])); ?></div>
        </div>
        
        <div class="info-row">
            <div><strong>Cliente:</strong> <?php echo htmlspecialchars($pago['cliente_nombre']); ?></div>
            <div><strong>Estado:</strong> <?php echo ucfirst($pago['estado']); ?></div>
        </div>
        
        <hr>
        
        <h5>Detalles del Pago</h5>
        
        <div class="info-row">
            <div><strong>Préstamo:</strong> <?php echo htmlspecialchars($pago['deuda_descripcion']); ?></div>
            <div><strong>Método de Pago:</strong> <?php echo htmlspecialchars($pago['metodo_pago']); ?></div>
        </div>
        
        <?php if (!empty($pago['numero_cuota'])): ?>
        <div class="info-row">
            <div><strong>Cuota:</strong> <?php echo $pago['numero_cuota']; ?> de <?php echo $pago['monto_cuota'] ? formatMoney($pago['monto_cuota']) : 'N/A'; ?></div>
        </div>
        <?php endif; ?>
        
        <div class="info-row">
            <div><strong>Monto Pagado:</strong> <?php echo formatMoney($pago['monto_pagado']); ?></div>
        </div>
        
        <?php if (!empty($pago['comprobante'])): ?>
        <div class="info-row">
            <div><strong>Comprobante adjunto:</strong> Sí</div>
        </div>
        <?php endif; ?>
        
        <hr>
        
        <div class="footer">
            <p>Este comprobante es válido como constancia de pago.</p>
            <p>Fecha de emisión del comprobante: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>
    
    <div class="print-btn">
        <button class="btn btn-primary" onclick="window.print()">Imprimir Comprobante</button>
        <a href="../prestamos/detalle_prestamo.php?id=<?php echo $pago['deuda_id']; ?>" class="btn btn-secondary">Volver</a>
    </div>
</body>
</html>