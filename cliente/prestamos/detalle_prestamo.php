<?php
session_start();
require_once '../include/cnx.php';

// Verificar si el usuario está logueado como cliente
if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../../index.php');
    exit;
}

$cliente_id = $_SESSION['cliente_id'];

// Verificar si se proporcionó un ID de préstamo
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ver_prestamos.php?error=id_invalido');
    exit;
}

$prestamo_id = $_GET['id'];

// Obtener los datos del préstamo y verificar que pertenezca al cliente
$query = "SELECT d.*, p.nombre as politica_nombre, p.tasa 
          FROM deudas d 
          JOIN politicas_interes p ON d.politica_interes_id = p.id 
          WHERE d.id = ? AND d.cliente_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$prestamo_id, $cliente_id]);

if ($stmt->rowCount() === 0) {
    header('Location: ver_prestamos.php?error=prestamo_no_encontrado');
    exit;
}

$prestamo = $stmt->fetch();

// Obtener el historial de pagos del préstamo
$query_pagos = "SELECT p.*, c.numero_cuota 
                FROM pagos p 
                LEFT JOIN cuotas_deuda c ON p.cuota_id = c.id 
                WHERE p.deuda_id = ? 
                ORDER BY p.fecha_pago DESC";
$stmt_pagos = $pdo->prepare($query_pagos);
$stmt_pagos->execute([$prestamo_id]);
$pagos = $stmt_pagos->fetchAll();

// Calcular el total pagado
$total_pagado = 0;
foreach ($pagos as $pago) {
    $total_pagado += $pago['monto_pagado'];
}

// Obtener las cuotas del préstamo
$query_cuotas = "SELECT * FROM cuotas_deuda 
                WHERE deuda_id = ? 
                ORDER BY numero_cuota ASC";
$stmt_cuotas = $pdo->prepare($query_cuotas);
$stmt_cuotas->execute([$prestamo_id]);
$cuotas_todas = $stmt_cuotas->fetchAll();

// Filtrar cuotas para mostrar solo una por número de cuota
$cuotas = [];
$numeros_cuota = [];
foreach ($cuotas_todas as $cuota) {
    if (!in_array($cuota['numero_cuota'], $numeros_cuota)) {
        $numeros_cuota[] = $cuota['numero_cuota'];
        $cuotas[] = $cuota;
    }
}

// Función para formatear montos
function formatMoney($amount) {
    return '₲ ' . number_format($amount, 0, ',', '.');
}

// Calcular días de atraso
$fecha_vencimiento = new DateTime($prestamo['fecha_vencimiento']);
$fecha_actual = new DateTime();
$dias_atraso = 0;

if ($fecha_actual > $fecha_vencimiento && $prestamo['estado'] != 'pagado') {
    $dias_atraso = $fecha_actual->diff($fecha_vencimiento)->days;
}

include '../include/sidebar.php';
?>

<div class="content-wrapper">
    <div class="container mt-4">
        <?php if(isset($_GET['success']) && $_GET['success'] == 'pago'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>¡Éxito!</strong> El pago ha sido registrado correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Detalle del Préstamo</h4>
                        <div>
                            <a href="ver_prestamos.php" class="btn btn-light me-2">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <?php if ($prestamo['estado'] != 'pagado'): ?>
                            
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Información del Préstamo</h5>
                                <div class="mb-3">
                                    <p><strong>ID:</strong> <?php echo $prestamo['id']; ?></p>
                                    <p><strong>Descripción:</strong> <?php echo htmlspecialchars($prestamo['descripcion']); ?></p>
                                    <p><strong>Monto Original:</strong> <?php echo formatMoney($prestamo['monto']); ?></p>
                                    <p><strong>Saldo Pendiente:</strong> <?php echo formatMoney($prestamo['saldo_pendiente']); ?></p>
                                    <p><strong>Total Pagado:</strong> <?php echo formatMoney($total_pagado); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Fechas y Estado</h5>
                                <div class="mb-3">
                                    <p><strong>Fecha de Emisión:</strong> <?php echo date('d/m/Y', strtotime($prestamo['fecha_emision'])); ?></p>
                                    <p><strong>Fecha de Vencimiento:</strong> <?php echo date('d/m/Y', strtotime($prestamo['fecha_vencimiento'])); ?></p>
                                    <p>
                                        <strong>Estado:</strong> 
                                        <span class="badge <?php 
                                            if($prestamo['estado'] == 'pendiente') echo 'bg-warning';
                                            elseif($prestamo['estado'] == 'pagado') echo 'bg-success';
                                            elseif($prestamo['estado'] == 'vencido') echo 'bg-danger';
                                        ?>">
                                            <?php echo ucfirst($prestamo['estado']); ?>
                                        </span>
                                    </p>
                                    <?php if ($dias_atraso > 0 && $prestamo['estado'] != 'pagado'): ?>
                                    <p class="text-danger"><strong>Días de atraso:</strong> <?php echo $dias_atraso; ?> días</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2 mb-3">Política de Interés</h5>
                                <div class="mb-3">
                                    <p><strong>Política:</strong> <?php echo htmlspecialchars($prestamo['politica_nombre']); ?></p>
                                    <p><strong>Tasa de Interés:</strong> <?php echo $prestamo['tasa']; ?>%</p>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2 mb-3">Historial de Pagos</h5>
                                <?php if (count($pagos) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th class="text-white">Fecha</th>
                                                <th class="text-white">Monto</th>
                                                <th class="text-white">Cuota</th>
                                                <th class="text-white">Método</th>
                                                <th class="text-white">Estado</th>
                                                <th class="text-white">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pagos as $pago): ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($pago['fecha_pago'])); ?></td>
                                                    <td><?php echo formatMoney($pago['monto_pagado']); ?></td>
                                                    <td>
                                                        <?php 
                                                        if (!empty($pago['numero_cuota'])) {
                                                            echo 'Cuota ' . $pago['numero_cuota'];
                                                        } else {
                                                            echo 'Pago general';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($pago['metodo_pago']); ?></td>
                                                    <td>
                                                        <span class="badge <?php 
                                                            if($pago['estado'] == 'pendiente') echo 'bg-warning';
                                                            elseif($pago['estado'] == 'aprobado') echo 'bg-success';
                                                            elseif($pago['estado'] == 'rechazado') echo 'bg-danger';
                                                        ?>">
                                                            <?php echo ucfirst($pago['estado']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="../pagos/comprobante_pago.php?id=<?php echo $pago['id']; ?>" class="btn btn-sm btn-info" target="_blank">
                                                            <i class="bi bi-download"></i> Descargar
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-info">
                                    No hay pagos registrados para este préstamo.
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Sección de Cuotas -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2 mb-3">Cuotas del Préstamo</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th class="text-white">Cuota</th>
                                                <th class="text-white">Monto</th>
                                                <th class="text-white">Vencimiento</th>
                                                <th class="text-white">Estado</th>
                                                <th class="text-white">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $cuotas_mostradas = []; // Array para controlar cuotas ya mostradas
                                            foreach ($cuotas as $cuota): 
                                                // Evitar mostrar cuotas duplicadas con el mismo número
                                                if (in_array($cuota['numero_cuota'], $cuotas_mostradas)) {
                                                    continue;
                                                }
                                                $cuotas_mostradas[] = $cuota['numero_cuota'];
                                            ?>
                                                <tr>
                                                    <td>Cuota <?php echo $cuota['numero_cuota']; ?></td>
                                                    <td><?php echo formatMoney($cuota['monto_cuota']); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($cuota['fecha_vencimiento'])); ?></td>
                                                    <td>
                                                        <span class="badge <?php 
                                                            if($cuota['estado'] == 'pendiente') echo 'bg-warning';
                                                            elseif($cuota['estado'] == 'pagado') echo 'bg-success';
                                                            elseif($cuota['estado'] == 'vencido') echo 'bg-danger';
                                                        ?>">
                                                            <?php echo ucfirst($cuota['estado']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($cuota['estado'] != 'pagado' && $prestamo['estado'] != 'pagado'): ?>
                                                            <a href="../pagos/realizar_pago.php?deuda_id=<?php echo $prestamo_id; ?>&cuota_id=<?php echo $cuota['id']; ?>" class="btn btn-sm btn-primary">
                                                                Pagar
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="badge bg-success">Pagada</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- REMOVE THIS DUPLICATE SECTION -->
                        <!-- The duplicate Historial de Pagos section has been removed -->

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom CSS for dark mode compatibility -->
<style>
    @media (prefers-color-scheme: dark) {
        .card {
            background-color: #2c3e50;
            color: white;
        }
        
        .table {
            color: white !important;
        }
        
        .table-striped > tbody > tr {
            color: white !important;
        }
        
        .table-striped > tbody > tr > td {
            color: white !important;
        }
        
        .text-dark {
            color: white !important;
        }
        
        .card-body {
            color: white;
        }
        
        .alert-info {
            background-color: #17a2b8;
            color: white;
            border-color: #148a9c;
        }
        
        /* Additional styles to ensure text visibility */
        tbody tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }
        
        tbody tr:nth-of-type(even) {
            background-color: rgba(0, 0, 0, 0.2) !important;
        }
        
        .table-striped tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
        }
        
        .border-bottom {
            border-color: #4a5568 !important;
        }
    }
</style>