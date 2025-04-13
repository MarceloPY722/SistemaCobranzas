<?php
session_start();
require_once 'cnx.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php'); 
    exit;
}

$claim_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($claim_id == 0) {
    header('Location: ../sidebar/reclamos/ver_reclamos.php?error=id_invalido');
    exit;
}

$stmt = $pdo->prepare("SELECT r.*, c.nombre AS cliente_nombre, c.email, c.telefono, c.identificacion 
                       FROM reclamos r
                       JOIN clientes c ON r.cliente_id = c.id
                       WHERE r.id = ?");
$stmt->execute([$claim_id]);
$claim = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$claim) {
    header('Location: ../sidebar/reclamos/ver_reclamos.php?error=reclamo_no_encontrado');
    exit;
}

// Obtener documentos adjuntos del reclamo
$query_docs = "SELECT * FROM documentos 
              WHERE cliente_id = ? AND tipo_documento = 'reclamo' 
              AND created_at >= ? AND created_at <= DATE_ADD(?, INTERVAL 1 DAY)";
$stmt_docs = $pdo->prepare($query_docs);
$stmt_docs->execute([$claim['cliente_id'], $claim['created_at'], $claim['created_at']]);
$documentos = $stmt_docs->fetchAll(PDO::FETCH_ASSOC);

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $respuesta = trim($_POST['respuesta']); 
    $user_id = $_SESSION['user_id']; 
    
    if (!empty($respuesta)) {
        try {
            // Actualizar el estado del reclamo a "en_proceso" si estaba "abierto"
            if ($claim['estado'] == 'abierto') {
                $update_estado = $pdo->prepare("UPDATE reclamos SET estado = 'en_proceso', respuesta = ?, fecha_respuesta = NOW() WHERE id = ?");
                $update_estado->execute([$respuesta, $claim_id]);
            } else {
                // Si ya está en proceso, solo actualizamos la respuesta
                $update_reclamo = $pdo->prepare("UPDATE reclamos SET respuesta = ?, fecha_respuesta = NOW() WHERE id = ?");
                $update_reclamo->execute([$respuesta, $claim_id]);
            }
            
            $success_message = "Respuesta enviada con éxito.";
            
            // Recargar los datos del reclamo
            $stmt->execute([$claim_id]);
            $claim = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $error_message = "Error al enviar la respuesta: " . $e->getMessage();
        }
    } else {
        $error_message = "Por favor, ingrese una respuesta.";
    }
}

include '../inc/sidebar.php';
?>

<div class="content-wrapper">
    <div class="container mt-4">
        <?php if(!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>¡Éxito!</strong> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if(!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>¡Error!</strong> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Responder al Reclamo #<?php echo $claim_id; ?></h4>
                <a href="../sidebar/reclamos/ver_reclamos.php" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Información del Reclamo</h5>
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">Estado:</th>
                                <td>
                                    <span class="badge <?php 
                                        if($claim['estado'] == 'abierto') echo 'bg-danger';
                                        elseif($claim['estado'] == 'en_proceso') echo 'bg-warning';
                                        elseif($claim['estado'] == 'cerrado') echo 'bg-success';
                                        else echo 'bg-secondary';
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $claim['estado'])); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Fecha de Creación:</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($claim['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <th>Asunto:</th>
                                <td><?php echo htmlspecialchars($claim['asunto']); ?></td>
                            </tr>
                            <tr>
                                <th>Descripción:</th>
                                <td><?php echo nl2br(htmlspecialchars($claim['descripcion'])); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Información del Cliente</h5>
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">Nombre:</th>
                                <td><?php echo htmlspecialchars($claim['cliente_nombre']); ?></td>
                            </tr>
                            <tr>
                                <th>Identificación:</th>
                                <td><?php echo htmlspecialchars($claim['identificacion']); ?></td>
                            </tr>
                            <tr>
                                <th>Teléfono:</th>
                                <td><?php echo htmlspecialchars($claim['telefono']); ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?php echo htmlspecialchars($claim['email']); ?></td>
                            </tr>
                        </table>
                    </div>
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
                                            <a href="../../uploads/documentos/<?php echo htmlspecialchars($doc['nombre_archivo'] ?? ''); ?>" 
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
                
                <!-- Respuesta Anterior (si existe) -->
                <?php if(!empty($claim['respuesta'])): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Respuesta Anterior</h5>
                        <div class="p-3 bg-light rounded">
                            <div class="mb-2">
                                <strong>Fecha:</strong> 
                                <?php echo !empty($claim['fecha_respuesta']) ? date('d/m/Y H:i', strtotime($claim['fecha_respuesta'])) : 'No disponible'; ?>
                            </div>
                            <div class="p-3 border rounded bg-white">
                                <?php echo nl2br(htmlspecialchars($claim['respuesta'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Respuesta del cliente (si existe) -->
                <?php if (!empty($claim['respuesta_cliente'])): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Respuesta del Cliente</h5>
                        <div class="p-3 bg-light rounded">
                            <?php if (!empty($claim['fecha_respuesta_cliente'])): ?>
                            <div class="mb-2">
                                <strong>Fecha:</strong> 
                                <?php echo date('d/m/Y H:i', strtotime($claim['fecha_respuesta_cliente'])); ?>
                            </div>
                            <?php endif; ?>
                            <div class="p-3 border rounded bg-white">
                                <?php echo nl2br(htmlspecialchars($claim['respuesta_cliente'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Formulario de Respuesta -->
                <?php if($claim['estado'] != 'cerrado'): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Responder</h5>
                                <?php if($claim['estado'] != 'cerrado'): ?>
                                    <button type="button" class="btn btn-success" onclick="confirmarCierre(<?php echo $claim_id; ?>)">
                                        <i class="bi bi-check-circle"></i> Cerrar Reclamo
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="respuesta" class="form-label">Su respuesta</label>
                                        <textarea class="form-control" id="respuesta" name="respuesta" rows="4" required></textarea>
                                    </div>
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-send"></i> Enviar Respuesta
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle"></i> Este reclamo ha sido finalizado y no puede recibir más respuestas.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para cerrar reclamo -->
<div class="modal fade" id="cerrarReclamoModal" tabindex="-1" aria-labelledby="cerrarReclamoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cerrarReclamoModalLabel">Confirmar cierre de reclamo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro que desea cerrar este reclamo? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="btnConfirmarCierre" class="btn btn-success">Confirmar</a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarCierre(reclamoId) {
    // Open confirmation page in new tab
    window.open('confirmar_cierre.php?id=' + reclamoId, '_blank', 'width=600,height=400');
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('success')) {
        const successType = urlParams.get('success');
        if (successType === 'reclamo_finalizado') {
            alert('El reclamo ha sido finalizado exitosamente.');
            window.location.href = 'responder_reclamo.php?id=<?php echo $claim_id; ?>';
        }
    }
    if (urlParams.has('error')) {
        const errorType = urlParams.get('error');
        let errorMessage = 'Ha ocurrido un error.';   
        if (errorType === 'no_actualizado') {
            errorMessage = 'No se pudo actualizar el estado del reclamo.';
        } else if (errorType === 'db_error') {
            errorMessage = 'Error en la base de datos: ' + urlParams.get('mensaje');
        }
        alert(errorMessage);
    }
});

</script>
<style>
    .content-wrapper {
        margin-left: 250px;
        padding: 20px;
    }
    .bg-custom {
        background-color: #121a35;
    }
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
</style>