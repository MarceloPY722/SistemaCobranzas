<?php
session_start();
require_once '../include/cnx.php';

// Verificar si el usuario está logueado como cliente
if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../../index.php');
    exit;
}

$cliente_id = $_SESSION['cliente_id'];

// Obtener todos los pagos del cliente
$query_pagos = "SELECT p.*, d.descripcion as prestamo_descripcion, c.numero_cuota 
                FROM pagos p 
                JOIN deudas d ON p.deuda_id = d.id 
                LEFT JOIN cuotas_deuda c ON p.cuota_id = c.id 
                WHERE d.cliente_id = ? 
                ORDER BY p.created_at DESC";
$stmt_pagos = $pdo->prepare($query_pagos);
$stmt_pagos->execute([$cliente_id]);
$pagos = $stmt_pagos->fetchAll();

// Función para formatear montos
function formatMoney($amount) {
    return '₲ ' . number_format($amount, 0, ',', '.');
}

// Filtros
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_metodo = isset($_GET['metodo']) ? $_GET['metodo'] : '';
$filtro_fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$filtro_fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// Aplicar filtros si están establecidos
$pagos_filtrados = [];
foreach ($pagos as $pago) {
    $incluir = true;
    
    if (!empty($filtro_estado) && $pago['estado'] != $filtro_estado) {
        $incluir = false;
    }
    
    if (!empty($filtro_metodo) && $pago['metodo_pago'] != $filtro_metodo) {
        $incluir = false;
    }
    
    if (!empty($filtro_fecha_inicio)) {
        $fecha_pago = date('Y-m-d', strtotime($pago['created_at']));
        if ($fecha_pago < $filtro_fecha_inicio) {
            $incluir = false;
        }
    }
    
    if (!empty($filtro_fecha_fin)) {
        $fecha_pago = date('Y-m-d', strtotime($pago['created_at']));
        if ($fecha_pago > $filtro_fecha_fin) {
            $incluir = false;
        }
    }
    
    if ($incluir) {
        $pagos_filtrados[] = $pago;
    }
}

// Si hay filtros activos, usar los pagos filtrados
if (!empty($filtro_estado) || !empty($filtro_metodo) || !empty($filtro_fecha_inicio) || !empty($filtro_fecha_fin)) {
    $pagos = $pagos_filtrados;
}

// Calcular estadísticas
$total_pagado = 0;
$pagos_aprobados = 0;
$pagos_pendientes = 0;
$pagos_rechazados = 0;

foreach ($pagos as $pago) {
    if ($pago['estado'] == 'aprobado') {
        $total_pagado += $pago['monto_pagado'];
        $pagos_aprobados++;
    } elseif ($pago['estado'] == 'pendiente') {
        $pagos_pendientes++;
    } elseif ($pago['estado'] == 'rechazado') {
        $pagos_rechazados++;
    }
}

include '../include/sidebar.php';
?>

<div class="content-wrapper">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Historial de Pagos</h4>
                        <a href="../index.php" class="btn btn-light">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Resumen de Pagos -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5>Total Pagado</h5>
                                        <h3><?php echo formatMoney($total_pagado); ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5>Pagos Aprobados</h5>
                                        <h3><?php echo $pagos_aprobados; ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h5>Pagos Pendientes</h5>
                                        <h3><?php echo $pagos_pendientes; ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger text-white">
                                    <div class="card-body text-center">
                                        <h5>Pagos Rechazados</h5>
                                        <h3><?php echo $pagos_rechazados; ?></h3>
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
                                    <div class="col-md-3">
                                        <label for="estado" class="form-label">Estado</label>
                                        <select class="form-select" id="estado" name="estado">
                                            <option value="">Todos</option>
                                            <option value="aprobado" <?php if($filtro_estado == 'aprobado') echo 'selected'; ?>>Aprobado</option>
                                            <option value="pendiente" <?php if($filtro_estado == 'pendiente') echo 'selected'; ?>>Pendiente</option>
                                            <option value="rechazado" <?php if($filtro_estado == 'rechazado') echo 'selected'; ?>>Rechazado</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="metodo" class="form-label">Método de Pago</label>
                                        <select class="form-select" id="metodo" name="metodo">
                                            <option value="">Todos</option>
                                            <option value="Efectivo" <?php if($filtro_metodo == 'Efectivo') echo 'selected'; ?>>Efectivo</option>
                                            <option value="Transferencia" <?php if($filtro_metodo == 'Transferencia') echo 'selected'; ?>>Transferencia</option>
                                            <option value="Tarjeta" <?php if($filtro_metodo == 'Tarjeta') echo 'selected'; ?>>Tarjeta</option>
                                            <option value="Depósito" <?php if($filtro_metodo == 'Depósito') echo 'selected'; ?>>Depósito</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo $filtro_fecha_inicio; ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo $filtro_fecha_fin; ?>">
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-filter"></i> Filtrar
                                        </button>
                                        <a href="historial_pagos.php" class="btn btn-secondary">
                                            <i class="bi bi-x-circle"></i> Limpiar Filtros
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Tabla de Pagos -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-white">Fecha y Hora</th>
                                        <th class="text-white">Préstamo</th>
                                        <th class="text-white">Monto</th>
                                        <th class="text-white">Cuota</th>
                                        <th class="text-white">Método</th>
                                        <th class="text-white">Estado</th>
                                        <th class="text-white">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($pagos) > 0): ?>
                                        <?php foreach ($pagos as $pago): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y H:i:s', strtotime($pago['created_at'])); ?></td>
                                                <td>
                                                    <a href="../prestamos/detalle_prestamo.php?id=<?php echo $pago['deuda_id']; ?>">
                                                        <?php echo htmlspecialchars($pago['prestamo_descripcion']); ?>
                                                    </a>
                                                </td>
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
                                                    <a href="comprobante_pago.php?id=<?php echo $pago['id']; ?>" class="btn btn-sm btn-info text-white">
                                                        <i class="bi bi-download"></i> Descargar
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No hay pagos registrados</td>
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