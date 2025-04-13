<?php
require_once 'inc/auth.php';
require_once 'inc/cnx.php';

// Make sure $conn is defined before using it
if (!isset($conn) || $conn === null) {
    // Try to establish the connection again
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "sistema_cobranzas";
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

require_once 'inc/header.php';
require_once 'inc/sidebar.php';
$cliente_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($cliente_id <= 0) {
    echo "<script>alert('ID de cliente no válido'); window.location.href='index.php';</script>";
    exit;
}

$query_cliente = "SELECT * FROM clientes WHERE id = ?";
$stmt_cliente = $conn->prepare($query_cliente);
$stmt_cliente->bind_param("i", $cliente_id);
$stmt_cliente->execute();
$result_cliente = $stmt_cliente->get_result();

if ($result_cliente->num_rows === 0) {
    echo "<script>alert('Cliente no encontrado'); window.location.href='index.php';</script>";
    exit;
}

$cliente = $result_cliente->fetch_assoc();

$query_deudas = "SELECT d.*, 
                       (SELECT COUNT(*) FROM cuotas_deuda WHERE deuda_id = d.id AND estado = 'vencido') as cuotas_vencidas,
                       (SELECT COUNT(*) FROM cuotas_deuda WHERE deuda_id = d.id AND estado = 'pendiente') as cuotas_pendientes,
                       (SELECT COUNT(*) FROM cuotas_deuda WHERE deuda_id = d.id AND estado = 'pagado') as cuotas_pagadas
                FROM deudas d 
                WHERE d.cliente_id = ? AND d.saldo_pendiente > 0
                ORDER BY d.fecha_vencimiento ASC";

$stmt_deudas = $conn->prepare($query_deudas);
$stmt_deudas->bind_param("i", $cliente_id);
$stmt_deudas->execute();
$result_deudas = $stmt_deudas->get_result();

$query_cuotas = "SELECT cd.*, d.descripcion as deuda_descripcion 
                FROM cuotas_deuda cd
                JOIN deudas d ON cd.deuda_id = d.id
                WHERE d.cliente_id = ? AND 
                      (cd.estado = 'vencido' OR 
                      (cd.estado = 'pendiente' AND cd.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)))
                ORDER BY cd.fecha_vencimiento ASC";

$stmt_cuotas = $conn->prepare($query_cuotas);
$stmt_cuotas->bind_param("i", $cliente_id);
$stmt_cuotas->execute();
$result_cuotas = $stmt_cuotas->get_result();
?>

<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row mb-4">
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detalles del Cliente</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <!-- Información del Cliente -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-custom text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person-circle me-2"></i>
                            Información del Cliente
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center mb-3">
                                <img src="<?php echo isset($cliente['imagen']) && $cliente['imagen'] != 'default.png' ? 'uploads/clientes/' . $cliente['imagen'] : 'assets/img/default-user.png'; ?>" 
                                     class="img-fluid rounded-circle mb-3" 
                                     style="max-width: 150px; height: auto;">
                                <h4><?php echo htmlspecialchars($cliente['nombre']); ?></h4>
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <h6><i class="bi bi-credit-card me-2"></i> Identificación:</h6>
                                        <p><?php echo htmlspecialchars($cliente['identificacion'] ?? 'No disponible'); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h6><i class="bi bi-telephone me-2"></i> Teléfono:</h6>
                                        <p>
                                            <?php if (!empty($cliente['telefono'])): ?>
                                                <a href="tel:<?php echo htmlspecialchars($cliente['telefono']); ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($cliente['telefono']); ?>
                                                </a>
                                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $cliente['telefono']); ?>" 
                                                   class="btn btn-success btn-sm ms-2" target="_blank">
                                                    <i class="bi bi-whatsapp"></i> WhatsApp
                                                </a>
                                            <?php else: ?>
                                                No disponible
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h6><i class="bi bi-envelope me-2"></i> Email:</h6>
                                        <p>
                                            <?php if (!empty($cliente['email'])): ?>
                                                <a href="mailto:<?php echo htmlspecialchars($cliente['email']); ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($cliente['email']); ?>
                                                </a>
                                            <?php else: ?>
                                                No disponible
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h6><i class="bi bi-geo-alt me-2"></i> Dirección:</h6>
                                        <p>
                                            <?php echo htmlspecialchars($cliente['direccion'] ?? 'No disponible'); ?>
                                            <?php if (!empty($cliente['ubicacion_link'])): ?>
                                                <a href="<?php echo htmlspecialchars($cliente['ubicacion_link']); ?>" 
                                                   class="btn btn-info btn-sm ms-2" target="_blank">
                                                    <i class="bi bi-map"></i> Ver Mapa
                                                </a>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <h6><i class="bi bi-calendar-check me-2"></i> Cliente desde:</h6>
                                        <p>
                                            <?php 
                                            $fecha = new DateTime($cliente['created_at']);
                                            echo $fecha->format('d/m/Y'); 
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="sidebar/clientes/cliente_datos.php?id=<?php echo $cliente_id; ?>" class="btn btn-primary">
                            <i class="bi bi-pencil-square"></i> Editar Cliente
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resumen de Deudas -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-custom text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-cash-stack me-2"></i>
                            Resumen de Deudas
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($result_deudas->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="tabla-deudas">
                                    <thead>
                                        <tr>
                                            <th>Descripción</th>
                                            <th>Monto Total</th>
                                            <th>Saldo Pendiente</th>
                                            <th>Progreso</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $total_pendiente = 0;
                                        while ($deuda = $result_deudas->fetch_assoc()): 
                                            $total_pendiente += $deuda['saldo_pendiente'];
                                            $porcentaje_pagado = 100 - (($deuda['saldo_pendiente'] / $deuda['monto']) * 100);
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($deuda['descripcion']); ?></td>
                                                <td><?php echo number_format($deuda['monto'], 0, ',', '.'); ?> Gs.</td>
                                                <td class="text-danger fw-bold"><?php echo number_format($deuda['saldo_pendiente'], 0, ',', '.'); ?> Gs.</td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-success" role="progressbar" 
                                                             style="width: <?php echo $porcentaje_pagado; ?>%;" 
                                                             aria-valuenow="<?php echo $porcentaje_pagado; ?>" 
                                                             aria-valuemin="0" aria-valuemax="100">
                                                            <?php echo round($porcentaje_pagado); ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($deuda['estado'] == 'vencido'): ?>
                                                        <span class="badge bg-danger">Vencido</span>
                                                    <?php elseif ($deuda['estado'] == 'pendiente'): ?>
                                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Pagado</span>
                                                    <?php endif; ?>
                                                    
                                                    <span class="badge bg-info">
                                                        <?php echo $deuda['cuotas_pagadas']; ?> pagadas / 
                                                        <?php echo $deuda['cuotas_pendientes']; ?> pendientes / 
                                                        <?php echo $deuda['cuotas_vencidas']; ?> vencidas
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="sidebar/clientes/deudas/ver_deuda.php?id=<?php echo $deuda['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-eye"></i> Ver Deuda
                                                    </a>
                                                    <a href="sidebar/clientes/deudas/registrar_pago.php?id=<?php echo $deuda['id']; ?>" class="btn btn-sm btn-success">
                                                        <i class="bi bi-cash"></i> Registrar Pago
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total Pendiente:</th>
                                            <th class="text-danger"><?php echo number_format($total_pendiente, 0, ',', '.'); ?> Gs.</th>
                                            <th colspan="3"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i> Este cliente no tiene deudas pendientes.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cuotas Próximas y Vencidas -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-custom text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-event me-2"></i>
                            Cuotas Próximas y Vencidas
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($result_cuotas->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="tabla-cuotas">
                                    <thead>
                                        <tr>
                                            <th>Deuda</th>
                                            <th>Cuota</th>
                                            <th>Monto</th>
                                            <th>Fecha Vencimiento</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($cuota = $result_cuotas->fetch_assoc()): 
                                            $fecha_vencimiento = new DateTime($cuota['fecha_vencimiento']);
                                            $hoy = new DateTime();
                                            $diff = $hoy->diff($fecha_vencimiento);
                                        ?>
                                            <tr class="<?php echo $cuota['estado'] == 'vencido' ? 'table-danger' : ''; ?>">
                                                <td><?php echo htmlspecialchars($cuota['deuda_descripcion']); ?></td>
                                                <td><?php echo $cuota['numero_cuota']; ?></td>
                                                <td><?php echo number_format($cuota['monto_cuota'], 0, ',', '.'); ?> Gs.</td>
                                                <td>
                                                    <?php echo $fecha_vencimiento->format('d/m/Y'); ?>
                                                    <?php if ($fecha_vencimiento < $hoy): ?>
                                                        <span class="badge bg-danger">Vencido hace <?php echo $diff->days; ?> días</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Vence en <?php echo $diff->days; ?> días</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($cuota['estado'] == 'vencido'): ?>
                                                        <span class="badge bg-danger">Vencido</span>
                                                    <?php elseif ($cuota['estado'] == 'pendiente'): ?>
                                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Pagado</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="sidebar/clientes/deudas/ver_deuda.php?id=<?php echo $cuota['deuda_id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-eye"></i> Ver Deuda
                                                    </a>
                                                    <?php if ($cuota['estado'] != 'pagado'): ?>
                                                    <a href="sidebar/clientes/deudas/registrar_pago.php?id=<?php echo $cuota['deuda_id']; ?>&cuota=<?php echo $cuota['id']; ?>" class="btn btn-sm btn-success">
                                                        <i class="bi bi-cash"></i> Pagar
                                                    </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i> Este cliente no tiene cuotas próximas a vencer o vencidas.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar DataTables si está disponible
        if ($.fn.DataTable) {
            $('#tabla-deudas').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "order": [[3, "desc"]]
            });
            
            $('#tabla-cuotas').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "order": [[3, "asc"]]
            });
        }
    });
</script>

<?php
// Cerrar las conexiones
$stmt_cliente->close();
$stmt_deudas->close();
$stmt_cuotas->close();
?>

<style>
    /* Dark mode styles */
    .content-wrapper {
        background-color: #121212;
        color: #ffffff; /* Change text color to white */
    }
    
    .card {
        background-color: #1e1e1e;
        border-color: #333;
    }
    
    .card-header {
        background-color: #2c3e50;
        color: #ffffff; /* Change text color to white */
    }
    
    .card-body {
        background-color: #1e1e1e;
        color: #ffffff; /* Change text color to white */
    }
    
    .card-footer {
        background-color: #252525;
        border-top-color: #333;
        color: #ffffff; /* Change text color to white */
    }
    
    .breadcrumb {
        background-color: #252525;
        color: #ffffff; /* Change text color to white */
    }
    
    .breadcrumb-item a {
        color: #7eb4ea;
    }
    
    .breadcrumb-item.active {
        color: #ffffff; /* Change text color to white */
    }
    
    .table {
        color: #ffffff; /* Change text color to white */
    }
    
    .table-hover tbody tr:hover {
        background-color: #2c2c2c;
    }
    
    .table thead th {
        border-bottom-color: #444;
        background-color: #252525;
        color: #ffffff; /* Change text color to white */
    }
    
    .table td, .table th {
        border-top-color: #444;
        color: #ffffff; /* Change text color to white */
    }
    
    .alert-info {
        background-color: #1a3a4a;
        color: #ffffff; /* Change text color to white */
        border-color: #164a5b;
    }
    
    .alert-success {
        background-color: #1a472e;
        color: #ffffff; /* Change text color to white */
        border-color: #165735;
    }
    
    .progress {
        background-color: #333;
    }
    
    .text-danger {
        color: #ff6b6b !important;
    }
    
    .bg-custom {
        background-color: #2c3e50 !important;
    }
    
    .table-danger, .table-danger > td, .table-danger > th {
        background-color: #422;
        color: #ffffff; /* Change text color to white */
    }
    
    /* DataTables specific styling */
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_filter, 
    .dataTables_wrapper .dataTables_info, 
    .dataTables_wrapper .dataTables_processing, 
    .dataTables_wrapper .dataTables_paginate {
        color: #ffffff; /* Change text color to white */
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        color: #ffffff !important; /* Change text color to white */
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        color: #333 !important;
        background: #ffffff;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        color: white !important;
        background: #444;
    }
</style>
