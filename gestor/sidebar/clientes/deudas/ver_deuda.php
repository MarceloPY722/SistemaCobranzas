<?php include '../../../inc/sidebar.php'; ?>

<?php
require_once '../../cnx.php';

// Verificar si se proporcionó un ID de deuda
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../ver_clientes.php?error=id_invalido');
    exit();
}

$deuda_id = $_GET['id'];

// Consulta para obtener los datos de la deuda con información del cliente y política de interés
$query = "SELECT d.*, c.nombre as cliente_nombre, c.id as cliente_id, 
          p.nombre as politica_nombre, p.tasa, p.tipo as politica_tipo
          FROM deudas d 
          JOIN clientes c ON d.cliente_id = c.id
          JOIN politicas_interes p ON d.politica_interes_id = p.id
          WHERE d.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $deuda_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ver_clientes.php?error=deuda_no_encontrada');
    exit();
}

$deuda = $result->fetch_assoc();

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
    return number_format($amount, 0, ',', '.') . ' Gs.';
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
                                        <strong>Descripción:</strong>
                                        <span><?php echo htmlspecialchars($deuda['descripcion']); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Monto Original:</strong>
                                        <span><?php echo formatMoney($deuda['monto']); ?></span>
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
                                            <?php if($deuda['estado'] == 'pendiente'): ?>
                                                <span class="badge bg-warning text-dark">Pendiente</span>
                                            <?php elseif($deuda['estado'] == 'pagado'): ?>
                                                <span class="badge bg-success">Pagado</span>
                                            <?php elseif($deuda['estado'] == 'vencido'): ?>
                                                <span class="badge bg-danger">Vencido</span>
                                            <?php endif; ?>
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
                                        <span><?php echo $deuda['tasa']; ?>% mensual</span>
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

        <!-- Sección de Pagos -->
        <div class="card mb-4">
            <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pagos Realizados</h5>
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
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Método</th>
                                    <th>Comprobante</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($pago = $result_pagos->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $pago['id']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></td>
                                    <td><?php echo formatMoney($pago['monto_pagado']); ?></td>
                                    <td><?php echo htmlspecialchars($pago['metodo_pago']); ?></td>
                                    <td>
                                        <?php if(!empty($pago['comprobante'])): ?>
                                            <a href="#" class="btn btn-sm btn-outline-info" onclick="verComprobante('<?php echo htmlspecialchars($pago['comprobante']); ?>')">
                                                Ver Comprobante
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Sin comprobante</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-danger" onclick="eliminarPago(<?php echo $pago['id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
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

        <!-- Historial de la Deuda -->
        <div class="card mb-4">
            <div class="card-header bg-custom text-white">
                <h5 class="mb-0">Historial de la Deuda</h5>
            </div>
            <div class="card-body">
                <?php if($result_historial->num_rows > 0): ?>
                    <div class="timeline">
                        <?php while($historial = $result_historial->fetch_assoc()): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">
                                        <?php echo ucfirst($historial['accion']); ?>
                                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($historial['created_at'])); ?></small>
                                    </h6>
                                    <p class="timeline-text">
                                        <?php echo isset($historial['descripcion']) ? htmlspecialchars($historial['descripcion']) : ''; ?>
                                        <?php if(!empty($historial['usuario_nombre'])): ?>
                                            <br><small>Por: <?php echo htmlspecialchars($historial['usuario_nombre']); ?></small>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        No hay registros en el historial de esta deuda.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para eliminar deuda -->
<div class="modal fade" id="eliminarDeudaModal" tabindex="-1" aria-labelledby="eliminarDeudaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eliminarDeudaModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar esta deuda? Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="eliminar_deuda.php?id=<?php echo $deuda_id; ?>" class="btn btn-danger">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver comprobante -->
<div class="modal fade" id="comprobanteModal" tabindex="-1" aria-labelledby="comprobanteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="comprobanteModalLabel">Comprobante de Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="comprobanteImg" src="" class="img-fluid" alt="Comprobante de pago">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a id="descargarComprobante" href="" download class="btn btn-primary">Descargar</a>
            </div>
        </div>
    </div>
</div>

<!-- Scripts específicos para esta página -->
<script>
    // Función para ver comprobante
    function verComprobante(rutaComprobante) {
        const comprobanteImg = document.getElementById('comprobanteImg');
        const descargarBtn = document.getElementById('descargarComprobante');
        
        // Establecer la ruta de la imagen
        comprobanteImg.src = '../../../../uploads/comprobantes/' + rutaComprobante;
        descargarBtn.href = '../../../../uploads/comprobantes/' + rutaComprobante;
        
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById('comprobanteModal'));
        modal.show();
    }
    
    // Función para eliminar pago
    function eliminarPago(pagoId) {
        if (confirm('¿Estás seguro de que deseas eliminar este pago? Esta acción no se puede deshacer.')) {
            window.location.href = 'eliminar_pago.php?id=' + pagoId + '&deuda_id=<?php echo $deuda_id; ?>';
        }
    }
    
    // Función para imprimir comprobante
    function imprimirComprobante() {
        // Crear una ventana de impresión
        const printWindow = window.open('', '_blank');
        
        // Contenido HTML para imprimir
        const contenido = `
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Comprobante de Deuda</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                        line-height: 1.6;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 20px;
                        border-bottom: 1px solid #ddd;
                        padding-bottom: 10px;
                    }
                    .info-section {
                        margin-bottom: 20px;
                    }
                    .info-row {
                        display: flex;
                        margin-bottom: 5px;
                    }
                    .info-label {
                        font-weight: bold;
                        width: 200px;
                    }
                    .info-value {
                        flex: 1;
                    }
                    .footer {
                        margin-top: 50px;
                        text-align: center;
                        font-size: 12px;
                        color: #666;
                    }
                    .total {
                        font-size: 18px;
                        font-weight: bold;
                        margin-top: 20px;
                        text-align: right;
                    }
                    @media print {
                        body {
                            margin: 0;
                            padding: 15px;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Comprobante de Deuda</h1>
                    <p>Sistema de Cobranzas</p>
                </div>
                
                <div class="info-section">
                    <h2>Información de la Deuda</h2>
                    <div class="info-row">
                        <div class="info-label">Número de Deuda:</div>
                        <div class="info-value">#<?php echo $deuda_id; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Cliente:</div>
                        <div class="info-value"><?php echo htmlspecialchars($deuda['cliente_nombre']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Descripción:</div>
                        <div class="info-value"><?php echo htmlspecialchars($deuda['descripcion']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Fecha de Emisión:</div>
                        <div class="info-value"><?php echo date('d/m/Y', strtotime($deuda['fecha_emision'])); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Fecha de Vencimiento:</div>
                        <div class="info-value"><?php echo date('d/m/Y', strtotime($deuda['fecha_vencimiento'])); ?></div>
                    </div>
                </div>
                
                <div class="info-section">
                    <h2>Detalles del Monto</h2>
                    <div class="info-row">
                        <div class="info-label">Monto Original:</div>
                        <div class="info-value"><?php echo formatMoney($deuda['monto']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Saldo Pendiente:</div>
                        <div class="info-value"><?php echo formatMoney($deuda['saldo_pendiente']); ?></div>
                    </div>
                    <?php if($deuda['estado'] == 'vencido'): ?>
                    <div class="info-row">
                        <div class="info-label">Días de Atraso:</div>
                        <div class="info-value"><?php echo $dias_atraso; ?> días</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Interés Acumulado:</div>
                        <div class="info-value"><?php echo formatMoney($interes_acumulado); ?></div>
                    </div>
                    <div class="total">
                        Total a Pagar: <?php echo formatMoney($deuda['saldo_pendiente'] + $interes_acumulado); ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="footer">
                    <p>Este documento es un comprobante informativo y no tiene validez fiscal.</p>
                    <p>Fecha de impresión: <?php echo date('d/m/Y H:i:s'); ?></p>
                </div>
            </body>
            </html>
        `;
        
        // Escribir el contenido en la ventana de impresión
        printWindow.document.write(contenido);
        printWindow.document.close();
        
        // Esperar a que se cargue el contenido y luego imprimir
        printWindow.onload = function() {
            printWindow.print();
            // printWindow.close(); // Opcional: cerrar después de imprimir
        };
    }
</script>

<style>
    /* Estilos generales */
    .content-wrapper {
        padding: 20px;
    }
    
    .card {
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }
    
    .card-header {
        border-radius: 8px 8px 0 0;
        font-weight: 600;
    }
    
    .bg-custom {
        background-color: #121a35;
    }
    
    .info-section-title {
        color: #121a35;
        border-bottom: 2px solid #121a35;
        padding-bottom: 8px;
        margin-bottom: 15px;
        font-weight: 600;
    }
    
    /* Estilos para la tabla */
    .table {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    /* Estilos para la línea de tiempo */
    .timeline {
        position: relative;
        padding: 20px 0;
    }
    
    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        left: 15px;
        height: 100%;
        width: 2px;
        background: #dee2e6;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 25px;
        padding-left: 40px;
    }
    
    .timeline-marker {
        position: absolute;
        top: 5px;
        left: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #121a35;
        border: 4px solid #fff;
    }
    
    .timeline-title {
        margin-bottom: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .timeline-text {
        margin-bottom: 0;
    }
    
    /* Estilos para modo oscuro */
    body.dark-mode .card {
        background-color: #1e2746;
        border-color: #2a3356;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }
    
    body.dark-mode .card-header:not(.bg-custom) {
        background-color: #2a3356;
        color: #fff;
        border-color: #3a4366;
    }
    
    body.dark-mode .card-body {
        color: #fff;
    }
    
    body.dark-mode .info-section-title {
        color: #fff;
        border-bottom-color: #764AF1;
    }
    
    body.dark-mode .list-group-item {
        background-color: #2a3356;
        color: #fff;
        border-color: #3a4366;
    }
    
    body.dark-mode .list-group-item a {
        color: #8be9fd;
    }
    
    body.dark-mode .text-muted {
        color: #adb5bd !important;
    }
    
    body.dark-mode .table {
        color: #fff;
    }
    
    body.dark-mode .table th {
        background-color: #2a3356;
        color: #fff;
        border-color: #3a4366;
    }
    
    body.dark-mode .table td {
        border-color: #3a4366;
    }
    
    body.dark-mode .table-hover tbody tr:hover {
        background-color: rgba(118, 74, 241, 0.1);
    }
    
    body.dark-mode .alert-info {
        background-color: rgba(13, 202, 240, 0.1);
        color: #0dcaf0;
        border-color: rgba(13, 202, 240, 0.2);
    }
    
    body.dark-mode .timeline:before {
        background: #3a4366;
    }
    
    body.dark-mode .timeline-marker {
        background: #764AF1;
        border-color: #1e2746;
    }
    
    body.dark-mode .btn-light {
        background-color: #2a3356;
        border-color: #3a4366;
        color: #fff;
    }
    
    body.dark-mode .btn-light:hover {
        background-color: #3a4366;
        color: #fff;
    }
    
    body.dark-mode .btn-outline-info {
        color: #0dcaf0;
        border-color: #0dcaf0;
    }
    
    body.dark-mode .btn-outline-info:hover {
        background-color: #0dcaf0;
        color: #000;
    }
    
    body.dark-mode .modal-content {
        background-color: #1e2746;
        color: #fff;
    }
    
    body.dark-mode .modal-header, 
    body.dark-mode .modal-footer {
        border-color: #3a4366;
    }
    
    body.dark-mode .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    
    /* Estilos para la tarjeta de información de mora en modo oscuro */
    body.dark-mode .card.bg-light {
        background-color: #2a3356 !important;
    }
    
    body.dark-mode .card.bg-light .card-title {
        color: #ff6b6b;
    }
    
    body.dark-mode .card.bg-light .list-group-item {
        background-color: #1e2746;
    }
    
    /* Mejoras para modo claro */
    .card-header.bg-custom h4,
    .card-header.bg-custom h5 {
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        font-weight: 600;
    }
    
    .list-group-item {
        border-left: none;
        border-right: none;
    }
    
    .list-group-item:first-child {
        border-top: none;
    }
    
    .list-group-item:last-child {
        border-bottom: none;
    }
    
    .badge {
        font-weight: 500;
        padding: 0.5em 0.8em;
    }
    
    /* Estilos para impresión */
    @media print {
        .content-wrapper {
            margin-left: 0;
            padding: 0;
        }
        .card {
            box-shadow: none;
            border: 1px solid #ddd;
        }
        .card-header.bg-custom {
            background-color: #f8f9fa !important;
            color: #000 !important;
        }
        .btn, .sidebar {
            display: none;
        }
    }
</style>
