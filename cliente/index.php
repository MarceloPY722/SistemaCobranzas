<?php
session_start();
require_once 'include/cnx.php';

if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../index.php');
    exit;
}

$cliente_id = $_SESSION['cliente_id'];

$query = "SELECT * FROM clientes WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$cliente_id]);
$cliente = $stmt->fetch();

include 'include/sidebar.php';
?>

<div class="content-wrapper">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-custom text-white">
                        <h4 class="mb-0">Bienvenido, <?php echo htmlspecialchars($cliente['nombre']); ?></h4>
                    </div>
                    <div class="card-body">
                        <p>Bienvenido al panel de cliente. Aquí podrás gestionar tus préstamos y pagos.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Préstamos Pendientes</h5>
                        <?php
                        $query_pendientes = "SELECT COUNT(*) as total FROM deudas WHERE cliente_id = ? AND estado = 'pendiente'";
                        $stmt_pendientes = $pdo->prepare($query_pendientes);
                        $stmt_pendientes->execute([$cliente_id]);
                        $pendientes = $stmt_pendientes->fetch();
                        ?>
                        <p class="card-text display-4"><?php echo $pendientes['total']; ?></p>
                        <a href="prestamos/ver_prestamos.php?estado=pendiente" class="btn btn-light">Ver detalles</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5 class="card-title">Préstamos Vencidos</h5>
                        <?php
                        $query_vencidos = "SELECT COUNT(*) as total FROM deudas WHERE cliente_id = ? AND estado = 'vencido'";
                        $stmt_vencidos = $pdo->prepare($query_vencidos);
                        $stmt_vencidos->execute([$cliente_id]);
                        $vencidos = $stmt_vencidos->fetch();
                        ?>
                        <p class="card-text display-4"><?php echo $vencidos['total']; ?></p>
                        <a href="prestamos/ver_prestamos.php?estado=vencido" class="btn btn-light">Ver detalles</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Préstamos Pagados</h5>
                        <?php
                        $query_pagados = "SELECT COUNT(*) as total FROM deudas WHERE cliente_id = ? AND estado = 'pagado'";
                        $stmt_pagados = $pdo->prepare($query_pagados);
                        $stmt_pagados->execute([$cliente_id]);
                        $pagados = $stmt_pagados->fetch();
                        ?>
                        <p class="card-text display-4"><?php echo $pagados['total']; ?></p>
                        <a href="prestamos/ver_prestamos.php?estado=pagado" class="btn btn-light">Ver detalles</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-custom text-white">
                        <h5 class="mb-0">Pagos Recientes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Monto</th>
                                        <th>Método de Pago</th>
                                        <th>Préstamo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query_pagos = "SELECT p.*, d.descripcion 
                                                   FROM pagos p 
                                                   JOIN deudas d ON p.deuda_id = d.id 
                                                   WHERE d.cliente_id = ? 
                                                   ORDER BY p.fecha_pago DESC LIMIT 5";
                                    $stmt_pagos = $pdo->prepare($query_pagos);
                                    $stmt_pagos->execute([$cliente_id]);
                                    
                                    if ($stmt_pagos->rowCount() > 0) {
                                        while ($pago = $stmt_pagos->fetch()) {
                                            echo "<tr>";
                                            echo "<td>" . date('d/m/Y', strtotime($pago['fecha_pago'])) . "</td>";
                                            echo "<td>₲ " . number_format($pago['monto_pagado'], 0, ',', '.') . "</td>";
                                            echo "<td>" . htmlspecialchars($pago['metodo_pago']) . "</td>";
                                            echo "<td>" . htmlspecialchars($pago['descripcion']) . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center'>No hay pagos recientes</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
    @media (prefers-color-scheme: dark) {
        .card {
            background-color: #2c3e50;
            color: white;
        }
        
        .table {
            color: white;
        }
        
        .table-striped > tbody > tr {
            color: white;
        }
        
        .text-dark {
            color: white !important;
        }
        
        .card-body {
            color: white;
        }
    }
</style>