<?php

require_once 'inc/auth.php';

require_once 'inc/cnx.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Cobranzas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        .badge {
            font-size: 0.9em;
            padding: 0.6em 0.8em;
        }
        main {
            margin-left: 250px;
            padding: 20px;
        }
        @media (max-width: 768px) {
            main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <?php include 'inc/sidebar.php'; ?>

    <main>
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Panel de Control</h1>
        </div>

        <!-- Estadísticas -->
        <div class="row">
            <?php
            try {
                // Total de Clientes
                $stmt = $pdo->query("SELECT COUNT(id) AS total FROM clientes");
                $total_clientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Deudores Activos
                $stmt = $pdo->query("SELECT COUNT(DISTINCT cliente_id) AS total 
                                   FROM deudas 
                                   WHERE estado IN ('pendiente', 'vencido')");
                $total_deudores = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Reclamos Pendientes
                $stmt = $pdo->query("SELECT COUNT(id) AS total 
                                   FROM reclamos 
                                   WHERE estado IN ('abierto', 'en_proceso')");
                $total_reclamos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
            } catch(PDOException $e) {
                error_log("Error en consulta: " . $e->getMessage());
                $total_clientes = 0;
                $total_deudores = 0;
                $total_reclamos = 0;
            }
            ?>
            
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(54, 162, 235, 0.2); color: #36A2EB;">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <div>
                        <h5>Clientes Registrados</h5>
                        <h3><?= number_format($total_clientes) ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(255, 99, 132, 0.2); color: #FF6384;">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <div>
                        <h5>Deudores Activos</h5>
                        <h3><?= number_format($total_deudores) ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(255, 159, 64, 0.2); color: #FF9F40;">
                        <i class="fas fa-bell fa-2x"></i>
                    </div>
                    <div>
                        <h5>Reclamos Pendientes</h5>
                        <h3><?= number_format($total_reclamos) ?></h3>
                        
                    </div>
                </div>
            </div>
        </div>

        <!-- Reclamos Recientes -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Reclamos Recientes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Cliente</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = $pdo->query("SELECT r.*, c.nombre AS cliente_nombre 
                                                    FROM reclamos r
                                                    JOIN clientes c ON r.cliente_id = c.id
                                                    WHERE r.estado != 'cerrado'
                                                    ORDER BY r.created_at DESC 
                                                    LIMIT 10");
                                
                                while ($reclamo = $stmt->fetch(PDO::FETCH_ASSOC)):
                                    $estado = strtolower($reclamo['estado']);
                                    $badge_color = match($estado) {
                                        'abierto' => 'bg-warning',
                                        'en_proceso' => 'bg-info',
                                        'resuelto' => 'bg-success',
                                        'cerrado' => 'bg-secondary',
                                        default => 'bg-secondary'
                                    };
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($reclamo['cliente_nombre']) ?></td>
                                <td><?= htmlspecialchars(substr($reclamo['descripcion'], 0, 50)) . (strlen($reclamo['descripcion']) > 50 ? '...' : '') ?></td>
                                <td>
                                    <span class="badge <?= $badge_color ?>">
                                        <?= ucfirst($estado) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($reclamo['created_at'])) ?></td>
                                <td>
                                    <a href="inc/responder_reclamo.php?id=<?= $reclamo['id'] ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-reply"></i> Responder
                                    </a>
                                </td>
                            </tr>
                            <?php
                                endwhile;
                            } catch(PDOException $e) {
                                echo '<tr><td colspan="5" class="text-center text-danger">Error al cargar los datos</td></tr>';
                                error_log("Error en consulta: " . $e->getMessage());
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>