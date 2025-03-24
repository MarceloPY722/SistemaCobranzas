<?php include '../../../../admin/include/sidebar.php'; ?>

<?php
require_once '../../cnx.php';

// Verificar si se proporcionó un ID de deuda
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../ver_clientes.php?error=id_invalido');
    exit();
}

$deuda_id = $_GET['id'];

// Consulta para obtener los datos de la deuda con información del cliente y política de interés
$query = "SELECT d.*, c.nombre as cliente_nombre, c.id as cliente_id, c.identificacion as cliente_identificacion,
          p.nombre as politica_nombre, p.tasa, p.tipo as politica_tipo, p.periodo as politica_periodo
          FROM deudas d 
          JOIN clientes c ON d.cliente_id = c.id
          JOIN politicas_interes p ON d.politica_interes_id = p.id
          WHERE d.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $deuda_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ../../ver_clientes.php?error=deuda_no_encontrada');
    exit();
}

$deuda = $result->fetch_assoc();

// Consulta para obtener las cuotas de la deuda
$query_cuotas = "SELECT * FROM cuotas_deuda WHERE deuda_id = ? ORDER BY numero_cuota ASC";
$stmt_cuotas = $conn->prepare($query_cuotas);
$stmt_cuotas->bind_param("i", $deuda_id);
$stmt_cuotas->execute();
$result_cuotas = $stmt_cuotas->get_result();

// Consulta para obtener los pagos relacionados con esta deuda
$query_pagos = "SELECT * FROM pagos WHERE deuda_id = ? ORDER BY fecha_pago DESC";
$stmt_pagos = $conn->prepare($query_pagos);
$stmt_pagos->bind_param("i", $deuda_id);
$stmt_pagos->execute();
$result_pagos = $stmt_pagos->get_result();

// Consulta para obtener el historial de la deuda
$query_historial = "SELECT h.*, u.nombre as usuario_nombre 
                   FROM historial_deudas h
                   LEFT JOIN usuarios u ON h.usuario_id = u.id
                   WHERE h.deuda_id = ? 
                   ORDER BY h.created_at DESC";
$stmt_historial = $conn->prepare($query_historial);
$stmt_historial->bind_param("i", $deuda_id);
$stmt_historial->execute();
$result_historial = $stmt_historial->get_result();

// Calcular días de atraso si está vencida
$dias_atraso = 0;
if ($deuda['estado'] == 'vencido') {
    $fecha_vencimiento = new DateTime($deuda['fecha_vencimiento']);
    $hoy = new DateTime();
    $diff = $hoy->diff($fecha_vencimiento);
    $dias_atraso = $diff->days;
}

// Calcular interés acumulado
$interes_acumulado = 0;
if ($deuda['estado'] == 'vencido') {
    // Cálculo básico de interés (puede ser más complejo según tus reglas de negocio)
    $interes_diario = ($deuda['tasa'] / 100) / 30; // Tasa mensual dividida por 30 días
    $interes_acumulado = $deuda['saldo_pendiente'] * $interes_diario * $dias_atraso;
}

// Función para formatear dinero
function formatMoney($amount) {
    // Check if amount is null or not numeric and provide a default value
    if ($amount === null || !is_numeric($amount)) {
        $amount = 0;
    }
    return number_format($amount, 0, ',', '.') . ' Gs.';
}

// Función para obtener el estado de la cuota con badge
function getEstadoBadge($estado) {
    switch ($estado) {
        case 'pendiente':
            return '<span class="badge bg-warning text-dark">Pendiente</span>';
        case 'pagado':
            return '<span class="badge bg-success">Pagado</span>';
        case 'vencido':
            return '<span class="badge bg-danger">Vencido</span>';
        case 'parcial':
            return '<span class="badge bg-info">Pago Parcial</span>';
        default:
            return '<span class="badge bg-secondary">Desconocido</span>';
    }
}
?>

<!-- Contenido principal -->
<div class="content-wrapper">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Detalles de la Deuda #<?php echo $deuda_id; ?></h4>
                        <div>
                            <a href="../cliente_datos.php?id=<?php echo $deuda['cliente_id']; ?>" class="btn btn-light me-2">
                                <i class="bi bi-arrow-left"></i> Volver al Cliente
                            </a>
                            <a href="editar_deuda.php?id=<?php echo $deuda_id; ?>" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> Editar Deuda
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="info-section-title">Información de la Deuda</h5>
                                <ul class="list-group mb-4">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Cliente:</strong>
                                        <a href="../cliente_datos.php?id=<?php echo $deuda['cliente_id']; ?>">
                                            <?php echo htmlspecialchars($deuda['cliente_nombre']); ?>
                                        </a>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Identificación:</strong>
                                        <span><?php echo htmlspecialchars($deuda['cliente_identificacion']); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Descripción:</strong>
                                        <span><?php echo htmlspecialchars($deuda['descripcion']); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Monto Original:</strong>
                                        <span><?php echo formatMoney($deuda['monto']); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Cuotas:</strong>
                                        <span><?php echo $deuda['cuotas']; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Saldo Pendiente:</strong>
                                        <span class="<?php echo ($deuda['estado'] == 'pagado') ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo formatMoney($deuda['saldo_pendiente']); ?>
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Fecha de Emisión:</strong>
                                        <span><?php echo date('d/m/Y', strtotime($deuda['fecha_emision'])); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Fecha de Vencimiento:</strong>
                                        <span><?php echo date('d/m/Y', strtotime($deuda['fecha_vencimiento'])); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Estado:</strong>
                                        <span>
                                            <?php echo getEstadoBadge($deuda['estado']); ?>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5 class="info-section-title">Política de Interés</h5>
                                <ul class="list-group mb-4">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Política:</strong>
                                        <span><?php echo htmlspecialchars($deuda['politica_nombre']); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Tasa de Interés:</strong>
                                        <span><?php echo $deuda['tasa']; ?>% <?php echo $deuda['politica_periodo']; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Tipo:</strong>
                                        <span><?php echo ucfirst(htmlspecialchars($deuda['politica_tipo'])); ?></span>
                                    </li>
                                </ul>

                                <?php if($deuda['estado'] == 'vencido'): ?>
                                <div class="card bg-light mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title text-danger">Información de Mora</h5>
                                        <ul class="list-group">
                                            <li class="list-group-item d-flex justify-content-between">
                                                <strong>Días de Atraso:</strong>
                                                <span class="text-danger"><?php echo $dias_atraso; ?> días</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <strong>Interés Acumulado:</strong>
                                                <span class="text-danger"><?php echo formatMoney($interes_acumulado); ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <strong>Total a Pagar:</strong>
                                                <span class="text-danger"><?php echo formatMoney($deuda['saldo_pendiente'] + $interes_acumulado); ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if(!empty($deuda['notas'])): ?>
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Notas</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><?php echo nl2br(htmlspecialchars($deuda['notas'])); ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="d-flex gap-2">
                                    <?php if($deuda['estado'] != 'pagado'): ?>
                                    <a href="registrar_pago.php?deuda_id=<?php echo $deuda_id; ?>" class="btn btn-success">
                                        <i class="bi bi-cash"></i> Registrar Pago
                                    </a>
                                    <?php endif; ?>
                                    <a href="eliminar_deuda.php?id=<?php echo $deuda_id; ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar esta deuda? Esta acción no se puede deshacer.')">
                                        <i class="bi bi-trash"></i> Eliminar Deuda
                                    </a>
                                    <button type="button" class="btn btn-info" onclick="imprimirComprobante()">
                                        <i class="bi bi-printer"></i> Imprimir Comprobante
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Cuotas -->
        <div class="card mb-4">
            <div class="card-header bg-custom text-white">
                <h5 class="mb-0">Plan de Cuotas</h5>
            </div>
            <div class="card-body">
                <?php if($result_cuotas->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Cuota</th>
                                    <th>Monto</th>
                                    <th>Vencimiento</th>
                                    <th>Estado</th>
                                    <th>Interés</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($cuota = $result_cuotas->fetch_assoc()): 
                                    $total_cuota = $cuota['monto_cuota'] + $cuota['interes_acumulado'];
                                ?>
                                <tr class="<?php echo ($cuota['estado'] == 'vencido') ? 'table-danger' : (($cuota['estado'] == 'pagado') ? 'table-success' : ''); ?>">
                                    <td><?php echo $cuota['numero_cuota']; ?></td>
                                    <td><?php echo formatMoney($cuota['monto_cuota']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($cuota['fecha_vencimiento'])); ?></td>
                                    <td><?php echo getEstadoBadge($cuota['estado']); ?></td>
                                    <td><?php echo formatMoney($cuota['interes_acumulado']); ?></td>
                                    <td><strong><?php echo formatMoney($total_cuota); ?></strong></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        No hay cuotas registradas para esta deuda.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sección de Pagos -->
        <div class="card mb-4">
            <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Historial de Pagos</h5>
                <?php if($deuda['estado'] != 'pagado'): ?>
                <a href="registrar_pago.php?deuda_id=<?php echo $deuda_id; ?>" class="btn btn-light btn-sm">
                    <i class="bi bi-plus-circle"></i> Nuevo Pago
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if($result_pagos->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Método</th>
                                    <th>Referencia</th>
                                    <th>Registrado por</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($pago = $result_pagos->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></td>
                                    <td><?php echo formatMoney($pago['monto_pagado']); ?></td>
                                    <td><?php echo ucfirst(htmlspecialchars($pago['metodo_pago'])); ?></td>
                                    <td><?php echo htmlspecialchars($pago['referencia'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($pago['usuario_nombre'] ?? 'Sistema'); ?></td>
                                    <td>
                                        <a href="ver_pago.php?id=<?php echo $pago['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="./anular_pago.php?id=<?php echo $pago['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que deseas anular este pago? Esta acción no se puede deshacer.')">
                                            <i class="bi bi-x-circle"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        No hay pagos registrados para esta deuda.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sección de Historial -->
        <div class="card mb-4">
            <div class="card-header bg-custom text-white">
                <h5 class="mb-0">Historial de Actividad</h5>
            </div>
            <div class="card-body">
                <?php if($result_historial->num_rows > 0): ?>
                    <div class="timeline">
                        <?php while($historial = $result_historial->fetch_assoc()): ?>
                            <div class="timeline-item">
                                <div class="timeline-date">
                                    <?php echo date('d/m/Y H:i', strtotime($historial['created_at'])); ?>
                                </div>
                                <div class="timeline-content">
                                    <h6><?php echo ucfirst($historial['accion']); ?></h6>
                                    <p><?php echo htmlspecialchars($historial['detalle']); ?></p>
                                    <small>Por: <?php echo htmlspecialchars($historial['usuario_nombre'] ?? 'Sistema'); ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        No hay registros de actividad para esta deuda.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="imprimirModal" tabindex="-1" aria-labelledby="imprimirModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imprimirModalLabel">Vista previa del comprobante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="comprobante-contenido">
                <!-- Contenido del comprobante -->
                <div class="comprobante-header text-center">
                    <h3>Comprobante de Deuda</h3>
                    <p>Sistema de Cobranzas</p>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <p><strong>Cliente:</strong> <?php echo htmlspecialchars($deuda['cliente_nombre']); ?></p>
                        <p><strong>Identificación:</strong> <?php echo htmlspecialchars($deuda['cliente_identificacion']); ?></p>
                    </div>
                    <div class="col-6 text-end">
                        <p><strong>Fecha:</strong> <?php echo date('d/m/Y'); ?></p>
                        <p><strong>Deuda #:</strong> <?php echo $deuda_id; ?></p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h5>Detalles de la Deuda</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th>Descripción</th>
                                <td><?php echo htmlspecialchars($deuda['descripcion']); ?></td>
                            </tr>
                            <tr>
                                <th>Monto Original</th>
                                <td><?php echo formatMoney($deuda['monto']); ?></td>
                            </tr>
                            <tr>
                                <th>Saldo Pendiente</th>
                                <td><?php echo formatMoney($deuda['saldo_pendiente']); ?></td>
                            </tr>
                            <tr>
                                <th>Fecha de Emisión</th>
                                <td><?php echo date('d/m/Y', strtotime($deuda['fecha_emision'])); ?></td>
                            </tr>
                            <tr>
                                <th>Fecha de Vencimiento</th>
                                <td><?php echo date('d/m/Y', strtotime($deuda['fecha_vencimiento'])); ?></td>
                            </tr>
                            <tr>
                                <th>Estado</th>
                                <td>
                                    <?php if($deuda['estado'] == 'pendiente'): ?>
                                        Pendiente
                                    <?php elseif($deuda['estado'] == 'pagado'): ?>
                                        Pagado
                                    <?php elseif($deuda['estado'] == 'vencido'): ?>
                                        Vencido
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <h5>Plan de Cuotas</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Cuota</th>
                                    <th>Monto</th>
                                    <th>Vencimiento</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Reiniciar el puntero del resultado
                                $result_cuotas->data_seek(0);
                                while($cuota = $result_cuotas->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><?php echo $cuota['numero_cuota']; ?></td>
                                    <td><?php echo formatMoney($cuota['monto_cuota']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($cuota['fecha_vencimiento'])); ?></td>
                                    <td>
                                        <?php if($cuota['estado'] == 'pendiente'): ?>
                                            Pendiente
                                        <?php elseif($cuota['estado'] == 'pagado'): ?>
                                            Pagado
                                        <?php elseif($cuota['estado'] == 'vencido'): ?>
                                            Vencido
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <p>Este documento no tiene validez fiscal. Es solo un comprobante informativo.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="printComprobante()">Imprimir</button>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding: 20px 0;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
        padding-left: 30px;
        border-left: 2px solid var(--border-color);
    }
    
    .timeline-date {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 5px;
    }
    
    .timeline-content {
        background-color: var(--card-bg);
        padding: 15px;
        border-radius: 5px;
    }
    
    .info-section-title {
        border-bottom: 2px solid #121a35;
        padding-bottom: 8px;
        margin-bottom: 15px;
    }
    
    .bg-custom {
        background-color: #121a35;
    }
    
    /* Dark mode adaptations */
    body.dark-mode {
        background-color: #121a35;
        color: #e9ecef;
    }
    
    body.dark-mode .info-section-title {
        border-bottom-color: #2a3c70;
    }
    
    body.dark-mode .card,
    body.dark-mode .dark-mode-element {
        background-color: #1e2337;
        border-color: #2a3c70;
        color: #e9ecef;
    }
    
    body.dark-mode .list-group-item {
        background-color: #1e2337;
        border-color: #2a3c70;
        color: #e9ecef;
    }
    
    body.dark-mode .modal-content {
        background-color: #1e2337;
        color: #e9ecef;
    }
    
    body.dark-mode .table {
        color: #e9ecef;
    }
    
    body.dark-mode .table-bordered,
    body.dark-mode .table-bordered th,
    body.dark-mode .table-bordered td {
        border-color: #2a3c70;
    }
    
    body.dark-mode .alert-info {
        background-color: #0d2e45;
        color: #9eeaf9;
        border-color: #0f5885;
    }
    
    body.dark-mode .bg-light {
        background-color: #1e2337 !important;
    }
    
    body.dark-mode .text-dark {
        color: #e9ecef !important;
    }
    
    /* CSS variables for theme consistency */
    :root {
        --border-color: #dee2e6;
        --text-muted: #6c757d;
        --card-bg: #f8f9fa;
    }
    
    body.dark-mode {
        --border-color: #2a3c70;
        --text-muted: #adb5bd;
        --card-bg: #2a3c70;
    }
    
    @media print {
        body * {
            visibility: hidden;
        }
        #comprobante-contenido, #comprobante-contenido * {
            visibility: visible;
        }
        #comprobante-contenido {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            background-color: white !important;
            color: black !important;
        }
        
        #comprobante-contenido .table {
            color: black !important;
        }
        
        #comprobante-contenido .table-bordered,
        #comprobante-contenido .table-bordered th,
        #comprobante-contenido .table-bordered td {
            border-color: #dee2e6 !important;
        }
    }
</style>

<script>
    // Apply theme-specific classes to elements when theme changes
    document.addEventListener('DOMContentLoaded', function() {
        // Check if there's a theme preference stored
        const isDarkMode = localStorage.getItem('darkMode') === 'true';
        
        // Apply dark mode if it's enabled
        if (isDarkMode) {
            document.body.classList.add('dark-mode');
            applyDarkModeStyles();
        }
        
        // Listen for theme changes
        document.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'darkModeButton') {
                setTimeout(function() {
                    const isDarkModeNow = document.body.classList.contains('dark-mode');
                    if (isDarkModeNow) {
                        applyDarkModeStyles();
                    } else {
                        removeDarkModeStyles();
                    }
                }, 100);
            }
        });
    });
    
    function applyDarkModeStyles() {
        // Apply dark mode styles to specific elements
        document.querySelectorAll('.card, .list-group-item, .modal-content, .table').forEach(el => {
            el.classList.add('dark-mode-element');
        });
        
        // Update table styles
        document.querySelectorAll('.table-bordered, .table-bordered th, .table-bordered td').forEach(el => {
            el.style.borderColor = '#2a3c70';
        });
        
        // Update alert styles
        document.querySelectorAll('.alert-info').forEach(el => {
            el.style.backgroundColor = '#0d2e45';
            el.style.color = '#9eeaf9';
            el.style.borderColor = '#0f5885';
        });
    }
    
    function removeDarkModeStyles() {
        // Remove dark mode styles
        document.querySelectorAll('.card, .list-group-item, .modal-content, .table').forEach(el => {
            el.classList.remove('dark-mode-element');
        });
        
        // Reset table styles
        document.querySelectorAll('.table-bordered, .table-bordered th, .table-bordered td').forEach(el => {
            el.style.borderColor = '';
        });
        
        // Reset alert styles
        document.querySelectorAll('.alert-info').forEach(el => {
            el.style.backgroundColor = '';
            el.style.color = '';
            el.style.borderColor = '';
        });
    }
    
    function imprimirComprobante() {
        var modal = new bootstrap.Modal(document.getElementById('imprimirModal'));
        modal.show();
    }
    
    function printComprobante() {
        window.print();
    }

</script>

<style>
    .timeline {
        position: relative;
        padding: 20px 0;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
        padding-left: 30px;
        border-left: 2px solid var(--border-color);
    }
    
    .timeline-date {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 5px;
    }
    
    .timeline-content {
        background-color: var(--card-bg);
        padding: 15px;
        border-radius: 5px;
    }
    
    .info-section-title {
        border-bottom: 2px solid #121a35;
        padding-bottom: 8px;
        margin-bottom: 15px;
    }
    
    .bg-custom {
        background-color: #121a35;
    }
    
    /* Dark mode adaptations */
    body.dark-mode {
        background-color: #121a35;
        color: #e9ecef;
    }
    
    body.dark-mode .info-section-title {
        border-bottom-color: #2a3c70;
    }
    
    body.dark-mode .card,
    body.dark-mode .dark-mode-element {
        background-color: #1e2337;
        border-color: #2a3c70;
        color: #e9ecef;
    }
    
    body.dark-mode .list-group-item {
        background-color: #1e2337;
        border-color: #2a3c70;
        color: #e9ecef;
    }
    
    body.dark-mode .modal-content {
        background-color: #1e2337;
        color: #e9ecef;
    }
    
    body.dark-mode .table {
        color: #e9ecef;
    }
    
    body.dark-mode .table-bordered,
    body.dark-mode .table-bordered th,
    body.dark-mode .table-bordered td {
        border-color: #2a3c70;
    }
    
    body.dark-mode .alert-info {
        background-color: #0d2e45;
        color: #9eeaf9;
        border-color: #0f5885;
    }
    
    body.dark-mode .bg-light {
        background-color: #1e2337 !important;
    }
    
    body.dark-mode .text-dark {
        color: #e9ecef !important;
    }
    
    /* Remove hover effect from cuotas table */
    .card-body .table tr:hover {
        background-color: inherit !important;
    }
    
    body.dark-mode .card-body .table tr:hover {
        background-color: inherit !important;
    }
    
    /* CSS variables for theme consistency */
    :root {
        --border-color: #dee2e6;
        --text-muted: #6c757d;
        --card-bg: #f8f9fa;
    }
    
    body.dark-mode {
        --border-color: #2a3c70;
        --text-muted: #adb5bd;
        --card-bg: #2a3c70;
    }
    
    @media print {
        body * {
            visibility: hidden;
        }
        #comprobante-contenido, #comprobante-contenido * {
            visibility: visible;
        }
        #comprobante-contenido {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            background-color: white !important;
            color: black !important;
        }
        
        #comprobante-contenido .table {
            color: black !important;
        }
        
        #comprobante-contenido .table-bordered,
        #comprobante-contenido .table-bordered th,
        #comprobante-contenido .table-bordered td {
            border-color: #dee2e6 !important;
        }
    }
</style>
