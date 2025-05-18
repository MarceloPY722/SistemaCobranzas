<?php include 'include/header_index.php' ?>
<?php include 'include/sidebar.php'?>

<?php
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

$mensaje_vencimiento = '';
try {
    $query_vencidas = "SELECT 1 FROM cuotas_deuda cd
                       JOIN deudas d ON cd.deuda_id = d.id
                       WHERE d.cliente_id = ?
                       AND cd.estado != 'pagado'
                       AND cd.fecha_vencimiento < CURDATE()
                       LIMIT 1";
    $stmt_vencidas = $pdo->prepare($query_vencidas);
    $stmt_vencidas->execute([$cliente_id]);
    $tiene_vencidas = $stmt_vencidas->fetchColumn();

    if ($tiene_vencidas) {
        $mensaje_vencimiento = '<div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-octagon-fill"></i> ¡Atención! Tienes cuotas vencidas pendientes de pago.</div>';
    } else {
        $query_proximo_vencimiento = "SELECT MIN(cd.fecha_vencimiento) as proxima_fecha
                                      FROM cuotas_deuda cd
                                      JOIN deudas d ON cd.deuda_id = d.id
                                      WHERE d.cliente_id = ?
                                      AND cd.estado = 'pendiente'
                                      AND cd.fecha_vencimiento >= CURDATE()"; // Get only future or today's due dates
        $stmt_proximo_vencimiento = $pdo->prepare($query_proximo_vencimiento);
        $stmt_proximo_vencimiento->execute([$cliente_id]);
        $proxima_cuota = $stmt_proximo_vencimiento->fetch();

        if ($proxima_cuota && $proxima_cuota['proxima_fecha']) {
            $fecha_vencimiento = new DateTime($proxima_cuota['proxima_fecha']);
            $hoy = new DateTime();
            $hoy->setTime(0, 0, 0);
            $fecha_vencimiento->setTime(0, 0, 0);

            $diferencia = $hoy->diff($fecha_vencimiento);
            $dias_restantes = $diferencia->days;

            if ($dias_restantes == 0) {
                $mensaje_vencimiento = '<div class="alert alert-warning" role="alert"><i class="bi bi-exclamation-triangle-fill"></i> ¡Tu próxima cuota vence hoy!</div>';
            } elseif ($dias_restantes == 1) {
                $mensaje_vencimiento = '<div class="alert alert-info" role="alert"><i class="bi bi-info-circle-fill"></i> Tu próxima cuota vence mañana.</div>';
            } else {
                $mensaje_vencimiento = '<div class="alert alert-info" role="alert"><i class="bi bi-info-circle-fill"></i> Próximo vencimiento en ' . $dias_restantes . ' días.</div>';
            }
        } else {
             $mensaje_vencimiento = '<div class="alert alert-success" role="alert"><i class="bi bi-check-circle-fill"></i> ¡Estás al día con tus pagos! No tienes vencimientos próximos.</div>';
        }
    }

} catch (PDOException $e) {
    error_log("Database error fetching due date info: " . $e->getMessage()); // Log the specific error
    $mensaje_vencimiento = '<div class="alert alert-danger" role="alert">Error al obtener información de vencimientos.</div>';
}

?>
<div class="content-wrapper">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-custom text-white">
                        <?php echo $mensaje_vencimiento; ?>
                        <h4 class="mb-0">Bienvenido, <?php echo htmlspecialchars($cliente['nombre']); ?></h4>
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
        .table th, 
        .table td {
            color: white !important;
        }
    }
    
    [data-bs-theme="dark"] .table th,
    [data-bs-theme="dark"] .table td,
    .dark-mode .table th,
    .dark-mode .table td {
        color: white !important;
    }
</style>