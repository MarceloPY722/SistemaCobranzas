<?php
session_start();
require_once '../include/cnx.php';

// Verificar si el usuario está logueado como cliente
if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../../index.php');
    exit;
}

$cliente_id = $_SESSION['cliente_id'];

$estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : null;

$query_base = "SELECT d.*, p.nombre as politica_nombre, p.tasa 
               FROM deudas d 
               JOIN politicas_interes p ON d.politica_interes_id = p.id 
               WHERE d.cliente_id = ?";

if ($estado_filtro && in_array($estado_filtro, ['pendiente', 'vencido', 'pagado'])) {
    $query_base .= " AND d.estado = ?";
    $stmt = $pdo->prepare($query_base . " ORDER BY d.fecha_emision DESC");
    $stmt->execute([$cliente_id, $estado_filtro]);
} else {
    $stmt = $pdo->prepare($query_base . " ORDER BY d.fecha_emision DESC");
    $stmt->execute([$cliente_id]);
}

$prestamos = $stmt->fetchAll();

function formatMoney($amount) {
    return '₲ ' . number_format($amount, 0, ',', '.');
}

include '../include/sidebar.php';
?>

<div class="content-wrapper">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <?php 
                            if ($estado_filtro) {
                                echo 'Préstamos ' . ucfirst($estado_filtro) . 's';
                            } else {
                                echo 'Todos los Préstamos';
                            }
                            ?>
                        </h4>
                        <div>
                            <a href="../index.php" class="btn btn-light me-2">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <div class="btn-group">
                                <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    Filtrar por Estado
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item <?php echo !$estado_filtro ? 'active' : ''; ?>" href="ver_prestamos.php">Todos</a></li>
                                    <li><a class="dropdown-item <?php echo $estado_filtro == 'pendiente' ? 'active' : ''; ?>" href="ver_prestamos.php?estado=pendiente">Pendientes</a></li>
                                    <li><a class="dropdown-item <?php echo $estado_filtro == 'vencido' ? 'active' : ''; ?>" href="ver_prestamos.php?estado=vencido">Vencidos</a></li>
                                    <li><a class="dropdown-item <?php echo $estado_filtro == 'pagado' ? 'active' : ''; ?>" href="ver_prestamos.php?estado=pagado">Pagados</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (count($prestamos) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-white">ID</th>
                                            <th class="text-white">Descripción</th>
                                            <th class="text-white">Monto</th>
                                            <th class="text-white">Saldo Pendiente</th>
                                            <th class="text-white">Fecha Emisión</th>
                                            <th class="text-white">Fecha Vencimiento</th>
                                            <th class="text-white">Estado</th>
                                            <th class="text-white">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($prestamos as $prestamo): ?>
                                            <tr>
                                                <td><?php echo $prestamo['id']; ?></td>
                                                <td><?php echo htmlspecialchars($prestamo['descripcion']); ?></td>
                                                <td><?php echo formatMoney($prestamo['monto']); ?></td>
                                                <td><?php echo formatMoney($prestamo['saldo_pendiente']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($prestamo['fecha_emision'])); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($prestamo['fecha_vencimiento'])); ?></td>
                                                <td>
                                                    <?php 
                                                    $estado_class = '';
                                                    switch($prestamo['estado']) {
                                                        case 'pendiente':
                                                            $estado_class = 'badge bg-warning';
                                                            break;
                                                        case 'pagado':
                                                            $estado_class = 'badge bg-success';
                                                            break;
                                                        case 'vencido':
                                                            $estado_class = 'badge bg-danger';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="<?php echo $estado_class; ?>">
                                                        <?php echo ucfirst($prestamo['estado']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="detalle_prestamo.php?id=<?php echo $prestamo['id']; ?>" class="btn btn-sm btn-info text-white">
                                                        <i class="bi bi-eye"></i> Ver Detalles
                                                    </a>
                                                    <?php if ($prestamo['estado'] != 'pagado'): ?>
                                                    <a href="../pagos/realizar_pago.php?deuda_id=<?php echo $prestamo['id']; ?>" class="btn btn-sm btn-primary mt-1">
                                                        <i class="bi bi-cash"></i> Pagar
                                                    </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <?php 
                                if ($estado_filtro) {
                                    echo "No tienes préstamos " . strtolower($estado_filtro) . "s en este momento.";
                                } else {
                                    echo "No tienes préstamos registrados en este momento.";
                                }
                                ?>
                            </div>
                        <?php endif; ?>
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
        
        .dropdown-menu {
            background-color: #343a40;
        }
        
        .dropdown-item {
            color: white;
        }
        
        .dropdown-item:hover, .dropdown-item:focus {
            background-color: #495057;
            color: white;
        }
        
        .dropdown-item.active {
            background-color: #007bff;
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
    }
</style>