<?php
session_start();
require_once '../include/cnx.php';

// Verificar si el usuario está logueado como cliente
if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../../index.php');
    exit;
}

$cliente_id = $_SESSION['cliente_id'];

// Obtener fecha actual
$fecha_actual = date('Y-m-d');
// Fecha límite (30 días después)
$fecha_limite = date('Y-m-d', strtotime('+30 days'));

// Obtener próximos vencimientos (cuotas que vencen en los próximos 30 días)
$query_vencimientos = "SELECT c.*, d.descripcion as prestamo_descripcion, d.id as deuda_id 
                      FROM cuotas_deuda c 
                      JOIN deudas d ON c.deuda_id = d.id 
                      WHERE d.cliente_id = ? 
                      AND c.estado != 'pagado' 
                      AND c.fecha_vencimiento BETWEEN ? AND ? 
                      ORDER BY c.fecha_vencimiento ASC";
$stmt_vencimientos = $pdo->prepare($query_vencimientos);
$stmt_vencimientos->execute([$cliente_id, $fecha_actual, $fecha_limite]);
$vencimientos = $stmt_vencimientos->fetchAll();

// Obtener vencimientos atrasados (cuotas vencidas)
$query_atrasados = "SELECT c.*, d.descripcion as prestamo_descripcion, d.id as deuda_id 
                   FROM cuotas_deuda c 
                   JOIN deudas d ON c.deuda_id = d.id 
                   WHERE d.cliente_id = ? 
                   AND c.estado != 'pagado' 
                   AND c.fecha_vencimiento < ? 
                   ORDER BY c.fecha_vencimiento ASC";
$stmt_atrasados = $pdo->prepare($query_atrasados);
$stmt_atrasados->execute([$cliente_id, $fecha_actual]);
$atrasados = $stmt_atrasados->fetchAll();

// Función para formatear montos
function formatMoney($amount) {
    return '₲ ' . number_format($amount, 0, ',', '.');
}

// Función para calcular días de diferencia
function calcularDiasDiferencia($fecha) {
    $fecha_actual = new DateTime(date('Y-m-d'));
    $fecha_vencimiento = new DateTime($fecha);
    $diferencia = $fecha_actual->diff($fecha_vencimiento);
    
    return $diferencia->days * ($fecha_vencimiento > $fecha_actual ? 1 : -1);
}

include '../include/sidebar.php';
?>

<div class="content-wrapper">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Próximos Vencimientos</h4>
                        <a href="../index.php" class="btn btn-light">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Resumen de Vencimientos -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-danger text-white">
                                    <div class="card-body text-center">
                                        <h5>Pagos Vencidos</h5>
                                        <h3><?php echo count($atrasados); ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body text-center">
                                        <h5>Próximos a Vencer</h5>
                                        <h3><?php echo count($vencimientos); ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h5>Total a Pagar</h5>
                                        <?php
                                        $total_pagar = 0;
                                        foreach ($atrasados as $cuota) {
                                            $total_pagar += $cuota['monto_cuota'];
                                        }
                                        foreach ($vencimientos as $cuota) {
                                            $total_pagar += $cuota['monto_cuota'];
                                        }
                                        ?>
                                        <h3><?php echo formatMoney($total_pagar); ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pagos Vencidos -->
                        <div class="card mb-4">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0">Pagos Vencidos</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($atrasados) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Préstamo</th>
                                                <th>Cuota</th>
                                                <th>Monto</th>
                                                <th>Vencimiento</th>
                                                <th>Días Vencido</th>
                                                <th>Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($atrasados as $cuota): ?>
                                                <tr>
                                                    <td>
                                                        <a href="../prestamos/detalle_prestamo.php?id=<?php echo $cuota['deuda_id']; ?>">
                                                            <?php echo htmlspecialchars($cuota['prestamo_descripcion']); ?>
                                                        </a>
                                                    </td>
                                                    <td>Cuota <?php echo $cuota['numero_cuota']; ?></td>
                                                    <td><?php echo formatMoney($cuota['monto_cuota']); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($cuota['fecha_vencimiento'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-danger">
                                                            <?php echo abs(calcularDiasDiferencia($cuota['fecha_vencimiento'])); ?> días
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="realizar_pago.php?id=<?php echo $cuota['deuda_id']; ?>&cuota=<?php echo $cuota['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="bi bi-cash"></i> Pagar
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle"></i> No tiene pagos vencidos. ¡Felicitaciones!
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Próximos Vencimientos -->
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0">Próximos Vencimientos (30 días)</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($vencimientos) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Préstamo</th>
                                                <th>Cuota</th>
                                                <th>Monto</th>
                                                <th>Vencimiento</th>
                                                <th>Días Restantes</th>
                                                <th>Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($vencimientos as $cuota): ?>
                                                <tr>
                                                    <td>
                                                        <a href="../prestamos/detalle_prestamo.php?id=<?php echo $cuota['deuda_id']; ?>">
                                                            <?php echo htmlspecialchars($cuota['prestamo_descripcion']); ?>
                                                        </a>
                                                    </td>
                                                    <td>Cuota <?php echo $cuota['numero_cuota']; ?></td>
                                                    <td><?php echo formatMoney($cuota['monto_cuota']); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($cuota['fecha_vencimiento'])); ?></td>
                                                    <td>
                                                        <?php $dias_restantes = calcularDiasDiferencia($cuota['fecha_vencimiento']); ?>
                                                        <span class="badge <?php echo $dias_restantes <= 7 ? 'bg-danger' : 'bg-warning text-dark'; ?>">
                                                            <?php echo $dias_restantes; ?> días
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="realizar_pago.php?id=<?php echo $cuota['deuda_id']; ?>&cuota=<?php echo $cuota['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="bi bi-cash"></i> Pagar
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> No tiene pagos próximos a vencer en los siguientes 30 días.
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS for dark mode compatibility -->
<style>
    @media (prefers-color-scheme: dark) {
        .card {
            background-color: #2c3e50;
            color: white;
        }
        
        .card-header.bg-light {
            background-color: #34495e !important;
            color: white;
        }
        
        .form-control, .form-select {
            background-color: #34495e;
            color: white;
            border-color: #4a5568;
        }
        
        .form-control:focus, .form-select:focus {
            background-color: #34495e;
            color: white;
        }
        
        .table {
            color: white;
        }
        
        .table-striped>tbody>tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .alert-info, .alert-success {
            background-color: #34495e;
            color: white;
            border-color: #4a5568;
        }
    }
</style>