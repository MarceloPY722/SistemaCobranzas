<?php
session_start();
require_once '../include/cnx.php';

// Verificar si el usuario está logueado como cliente
if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../../index.php');
    exit;
}

$cliente_id = $_SESSION['cliente_id'];

// Verificar si se proporcionó un ID de reclamo
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: mis_reclamos.php');
    exit;
}

$reclamo_id = $_GET['id'];

// Obtener detalles del reclamo
$query = "SELECT r.*, d.descripcion as prestamo_descripcion, d.monto as prestamo_monto, 
          d.fecha_emision as prestamo_fecha 
          FROM reclamos r 
          LEFT JOIN deudas d ON r.deuda_id = d.id 
          WHERE r.id = ? AND r.cliente_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$reclamo_id, $cliente_id]);
$reclamo = $stmt->fetch();

// Verificar si el reclamo existe y pertenece al cliente
if (!$reclamo) {
    header('Location: mis_reclamos.php');
    exit;
}

// Obtener documentos adjuntos
$query_docs = "SELECT * FROM documentos 
              WHERE cliente_id = ? AND tipo_documento = 'reclamo' 
              AND created_at >= ? AND created_at <= DATE_ADD(?, INTERVAL 1 DAY)";
$stmt_docs = $pdo->prepare($query_docs);
$stmt_docs->execute([$cliente_id, $reclamo['created_at'], $reclamo['created_at']]);
$documentos = $stmt_docs->fetchAll();

// Obtener información del gestor que respondió (si existe)
$respondido_por_nombre = null;
if (!empty($reclamo['respondido_por'])) {
    $query_gestor = "SELECT nombre FROM usuarios WHERE id = ?";
    $stmt_gestor = $pdo->prepare($query_gestor);
    $stmt_gestor->execute([$reclamo['respondido_por']]);
    $gestor = $stmt_gestor->fetch();
    if ($gestor) {
        $respondido_por_nombre = $gestor['nombre'];
    }
}

// Procesar nueva respuesta del cliente
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nueva_respuesta'])) {
    $respuesta = trim($_POST['respuesta']);
    
    if (empty($respuesta)) {
        $mensaje = 'Por favor ingrese un mensaje.';
        $tipo_mensaje = 'danger';
    } else {
        try {
            // Actualizar la respuesta del cliente
            $query = "UPDATE reclamos SET respuesta_cliente = ?, fecha_respuesta_cliente = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$respuesta, $reclamo_id]);
            
            $mensaje = 'Su respuesta ha sido enviada exitosamente.';
            $tipo_mensaje = 'success';
            
            // Recargar los datos del reclamo
            $stmt = $pdo->prepare($query);
            $stmt->execute([$reclamo_id, $cliente_id]);
            $reclamo = $stmt->fetch();
            
        } catch (PDOException $e) {
            $mensaje = 'Error al enviar la respuesta: ' . $e->getMessage();
            $tipo_mensaje = 'danger';
        }
    }
}

// Fix the include path - change from sidebar/sidebar.php to include/sidebar.php
include '../include/sidebar.php';
?>

<div class="content-wrapper">
    <div class="container mt-4">
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detalles del Reclamo #<?php echo $reclamo_id; ?></h5>
                <a href="mis_reclamos.php" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">Información del Reclamo</h6>
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">Estado:</th>
                                <td>
                                    <span class="badge <?php 
                                        if($reclamo['estado'] == 'abierto') echo 'bg-danger';
                                        elseif($reclamo['estado'] == 'en_proceso') echo 'bg-warning';
                                        elseif($reclamo['estado'] == 'cerrado') echo 'bg-success';
                                        else echo 'bg-secondary';
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $reclamo['estado'] ?? '')); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Fecha:</th>
                                <td><?php echo !empty($reclamo['created_at']) ? date('d/m/Y H:i', strtotime($reclamo['created_at'])) : 'N/A'; ?></td>
                            </tr>
           
                         <tr>
                                <th>Asunto:</th>
                                <td><?php echo htmlspecialchars($reclamo['asunto'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Descripción:</th>
                                <td><?php echo nl2br(htmlspecialchars($reclamo['descripcion'] ?? '')); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <?php if (!empty($reclamo['deuda_id'])): ?>
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">Información del Préstamo</h6>
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">Descripción:</th>
                                <td><?php echo htmlspecialchars($reclamo['prestamo_descripcion'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Monto:</th>
                                <td>$<?php echo number_format($reclamo['prestamo_monto'] ?? 0, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Fecha:</th>
                                <td><?php echo !empty($reclamo['prestamo_fecha']) ? date('d/m/Y', strtotime($reclamo['prestamo_fecha'])) : 'N/A'; ?></td>
                            </tr>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Documentos Adjuntos -->
                <?php if (count($documentos) > 0): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Documentos Adjuntos</h5>
                        <div class="row">
                            <?php foreach ($documentos as $doc): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo htmlspecialchars($doc['nombre_original'] ?? 'Documento'); ?></h6>
                                            <p class="card-text small text-muted">
                                                Subido: <?php echo date('d/m/Y H:i', strtotime($doc['created_at'])); ?>
                                            </p>
                                            <a href="../../descargar_documento.php?id=<?php echo $doc['id']; ?>" 
                                               class="btn btn-sm btn-primary" target="_blank">
                                                <i class="bi bi-download"></i> Descargar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            
                <?php if (!empty($reclamo['respuesta'])): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Respuesta del Gestor</h6>
                        <div class="p-3 bg-light rounded">
                            <?php if ($respondido_por_nombre): ?>
                            <div class="mb-2">
                                <strong>Respondido por:</strong> <?php echo htmlspecialchars($respondido_por_nombre); ?>
                                <?php if (!empty($reclamo['fecha_respuesta'])): ?>
                                <span class="ms-3">
                                    <strong>Fecha:</strong> 
                                    <?php echo date('d/m/Y H:i', strtotime($reclamo['fecha_respuesta'])); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <div class="p-3 border rounded bg-white">
                                <?php echo nl2br(htmlspecialchars($reclamo['respuesta'] ?? '')); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Respuesta del cliente (si existe) -->
                <?php if (!empty($reclamo['respuesta_cliente'])): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Su Respuesta</h6>
                        <div class="p-3 bg-light rounded">
                            <?php if (!empty($reclamo['fecha_respuesta_cliente'])): ?>
                            <div class="mb-2">
                                <strong>Fecha:</strong> 
                                <?php echo date('d/m/Y H:i', strtotime($reclamo['fecha_respuesta_cliente'])); ?>
                            </div>
                            <?php endif; ?>
                            <div class="p-3 border rounded bg-white">
                                <?php echo nl2br(htmlspecialchars($reclamo['respuesta_cliente'] ?? '')); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Formulario para responder (si el reclamo no está cerrado) -->
                <?php if (isset($reclamo['estado']) && $reclamo['estado'] != 'cerrado' && !empty($reclamo['respuesta']) && empty($reclamo['respuesta_cliente'])): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Responder al Gestor</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="respuesta" class="form-label">Su respuesta</label>
                                        <textarea class="form-control" id="respuesta" name="respuesta" rows="4" required></textarea>
                                    </div>
                                    <button type="submit" name="nueva_respuesta" class="btn btn-primary">
                                        <i class="bi bi-send"></i> Enviar Respuesta
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .content-wrapper {
        margin-left: 250px;
        padding: 20px;
    }
    @media (max-width: 768px) {
        .content-wrapper {
            margin-left: 0;
        }
    }
    
    /* Estilos para modo claro */
    body {
        background-color: #f8f9fa;
        color: #212529;
    }
    
    .card {
        background-color: #ffffff;
        border: 0.5px solid #000000;
        border-color: #000000;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    
    }
    
    .card-header.bg-primary {
        background-color:rgb(17, 48, 94) !important;
    }
    
    .table {
        color: #212529;
    }
    
    .bg-light {
        background-color:rgb(37, 73, 109) !important;
    }
    
    .border {
        border-color: #dee2e6 !important;
    }
    
    .bg-white {
        background-color: #ffffff !important;
    }
    
    .text-muted {
        color: #6c757d !important;
    }
    
    /* Dark mode styles */
    body.dark-mode .card {
        background-color: #2d3748 !important;
        color: #fff !important;
    }
    body.dark-mode .bg-light {
        background-color: #1a202c !important;
    }
    body.dark-mode .border {
        border-color: #4a5568 !important;
    }
    body.dark-mode .bg-white {
        background-color: #2d3748 !important;
        color: #fff !important;
    }
    body.dark-mode .text-muted {
        color: #a0aec0 !important;
    }
    body.dark-mode .table {
        color: #fff !important;
    }
    body.dark-mode .table td, 
    body.dark-mode .table th {
        color: #fff !important;
    }
    body.dark-mode .form-label {
        color: #fff !important;
    }
    body.dark-mode .form-control {
        background-color: #1a202c !important;
        color: #fff !important;
        border-color: #4a5568 !important;
    }
    body.dark-mode .form-control:focus {
        background-color: #2d3748 !important;
        color: #fff !important;
    }
    body.dark-mode .card-header.bg-light {
        background-color: #2d3748 !important;
        color: #fff !important;
    }
    body.dark-mode .modal-content {
        background-color: #2d3748 !important;
        color: #fff !important;
    }
    body.dark-mode .alert {
        color: #fff !important;
    }
</style>