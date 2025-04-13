<?php
session_start();
require_once '../include/cnx.php';

// Verificar si el usuario está logueado como cliente
if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../../index.php');
    exit;
}

$cliente_id = $_SESSION['cliente_id'];

// Filtros
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$filtro_fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// Construir la consulta base
$query = "SELECT r.*, d.descripcion as prestamo_descripcion 
          FROM reclamos r 
          LEFT JOIN deudas d ON r.deuda_id = d.id 
          WHERE r.cliente_id = ?";
$params = [$cliente_id];

// Aplicar filtros
if (!empty($filtro_estado)) {
    $query .= " AND r.estado = ?";
    $params[] = $filtro_estado;
}

if (!empty($filtro_fecha_inicio)) {
    $query .= " AND DATE(r.created_at) >= ?";
    $params[] = $filtro_fecha_inicio;
}

if (!empty($filtro_fecha_fin)) {
    $query .= " AND DATE(r.created_at) <= ?";
    $params[] = $filtro_fecha_fin;
}

// Ordenar por fecha de creación (más reciente primero)
$query .= " ORDER BY r.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reclamos = $stmt->fetchAll();

// Obtener estadísticas
$query_stats = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'abierto' THEN 1 ELSE 0 END) as abiertos,
                SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
                SUM(CASE WHEN estado = 'resuelto' THEN 1 ELSE 0 END) as resueltos,
                SUM(CASE WHEN estado = 'cerrado' THEN 1 ELSE 0 END) as cerrados
                FROM reclamos 
                WHERE cliente_id = ?";
$stmt_stats = $pdo->prepare($query_stats);
$stmt_stats->execute([$cliente_id]);
$stats = $stmt_stats->fetch();

include '../include/sidebar.php';
?>

<div class="content-wrapper">
    <div class="container mt-4">
        <?php if(isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>¡Éxito!</strong> Su reclamo ha sido enviado correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Mis Reclamos</h4>
                        <a href="nuevo_reclamo.php" class="btn btn-light">
                            <i class="bi bi-plus-circle"></i> Nuevo Reclamo
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Estadísticas -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h5>Total Reclamos</h5>
                                        <h3><?php echo $stats['total']; ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h5>Abiertos</h5>
                                        <h3><?php echo $stats['abiertos']; ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h5>En Proceso</h5>
                                        <h3><?php echo $stats['en_proceso']; ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5>Resueltos</h5>
                                        <h3><?php echo $stats['resueltos'] + $stats['cerrados']; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Filtros -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Filtros</h5>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="" class="row g-3">
                                    <div class="col-md-4">
                                        <label for="estado" class="form-label">Estado</label>
                                        <select class="form-select" id="estado" name="estado">
                                            <option value="">Todos</option>
                                            <option value="abierto" <?php if($filtro_estado == 'abierto') echo 'selected'; ?>>Abierto</option>
                                            <option value="en_proceso" <?php if($filtro_estado == 'en_proceso') echo 'selected'; ?>>En Proceso</option>
                                            <option value="resuelto" <?php if($filtro_estado == 'resuelto') echo 'selected'; ?>>Resuelto</option>
                                            <option value="cerrado" <?php if($filtro_estado == 'cerrado') echo 'selected'; ?>>Cerrado</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo $filtro_fecha_inicio; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo $filtro_fecha_fin; ?>">
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-filter"></i> Filtrar
                                        </button>
                                        <a href="mis_reclamos.php" class="btn btn-secondary">
                                            <i class="bi bi-x-circle"></i> Limpiar Filtros
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Tabla de Reclamos -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <!-- Removed ID column -->
                                        <th class="text-white">Asunto</th>
                                        <th class="text-white">Préstamo</th>
                                        <th class="text-white">Fecha</th>
                                        <th class="text-white">Estado</th>
                                        <th class="text-white">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($reclamos) > 0): ?>
                                        <?php foreach ($reclamos as $reclamo): ?>
                                            <tr>
                                                <!-- Removed ID column data -->
                                                <td><?php echo htmlspecialchars($reclamo['asunto']); ?></td>
                                                <td>
                                                    <?php if ($reclamo['deuda_id']): ?>
                                                        <a href="../prestamos/detalle_prestamo.php?id=<?php echo $reclamo['deuda_id']; ?>">
                                                            <?php echo htmlspecialchars($reclamo['prestamo_descripcion']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">No asociado</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($reclamo['created_at'])); ?></td>
                                                <td>
                                                    <span class="badge <?php 
                                                        if($reclamo['estado'] == 'abierto') echo 'bg-warning';
                                                        elseif($reclamo['estado'] == 'en_proceso') echo 'bg-info';
                                                        elseif($reclamo['estado'] == 'resuelto') echo 'bg-success';
                                                        elseif($reclamo['estado'] == 'cerrado') echo 'bg-secondary';
                                                    ?>">
                                                        <?php 
                                                        $estado = $reclamo['estado'];
                                                        if ($estado == 'abierto') echo 'Abierto';
                                                        elseif ($estado == 'en_proceso') echo 'En Proceso';
                                                        elseif ($estado == 'resuelto') echo 'Resuelto';
                                                        elseif ($estado == 'cerrado') echo 'Cerrado';
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="ver_reclamo.php?id=<?php echo $reclamo['id']; ?>" class="btn btn-sm btn-info text-white">
                                                        <i class="bi bi-eye"></i> Ver Detalles
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No hay reclamos registrados</td>
                                        </tr>
                                    <?php endif; ?>
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
        
        .form-control, .form-select {
            background-color: #34495e;
            color: white;
            border-color: #4a5568;
        }
        
        .form-control:focus, .form-select:focus {
            background-color: #34495e;
            color: white;
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
    
    /* Support for Bootstrap 5 dark mode */
    [data-bs-theme="dark"] .card,
    .dark-mode .card {
        background-color: #2c3e50;
        color: white;
    }
    
    [data-bs-theme="dark"] .form-control,
    [data-bs-theme="dark"] .form-select,
    .dark-mode .form-control,
    .dark-mode .form-select {
        background-color: #34495e;
        color: white;
        border-color: #4a5568;
    }
    
    [data-bs-theme="dark"] .card-header.bg-light,
    .dark-mode .card-header.bg-light {
        background-color: #34495e !important;
        color: white;
    }
</style>