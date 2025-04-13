<?php include '../../../gestor/inc/sidebar.php'; ?>

<?php
require_once '../../../gestor/sidebar/cnx.php';

// Verificar si se proporcionó un ID de reclamo
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ver_reclamos.php?error=id_invalido');
    exit();
}

$reclamo_id = $_GET['id'];

// Consulta para obtener los detalles del reclamo
$query = "SELECT r.*, c.nombre AS cliente_nombre, c.telefono, c.email, c.identificacion,
          u.nombre AS respondido_por_nombre
          FROM reclamos r
          JOIN clientes c ON r.cliente_id = c.id
          LEFT JOIN usuarios u ON r.respondido_por = u.id
          WHERE r.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $reclamo_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ver_reclamos.php?error=reclamo_no_encontrado');
    exit();
}

$reclamo = $result->fetch_assoc();
?>

<!-- Contenido principal -->
<div class="content-wrapper">
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Detalles del Reclamo #<?php echo $reclamo_id; ?></h4>
                <a href="ver_reclamos.php" class="btn btn-light">
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
                                        if($reclamo['estado'] == 'abierto') echo 'bg-danger';
                                        elseif($reclamo['estado'] == 'en_proceso') echo 'bg-warning';
                                        elseif($reclamo['estado'] == 'cerrado') echo 'bg-success';
                                        else echo 'bg-secondary';
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $reclamo['estado'])); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Fecha de Creación:</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($reclamo['created_at'])); ?></td>
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
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Información del Cliente</h5>
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">Nombre:</th>
                                <td><?php echo htmlspecialchars($reclamo['cliente_nombre'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Identificación:</th>
                                <td><?php echo htmlspecialchars($reclamo['identificacion'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Teléfono:</th>
                                <td><?php echo htmlspecialchars($reclamo['telefono'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?php echo htmlspecialchars($reclamo['email'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Acciones:</th>
                                <td>
                                    <a href="../clientes/cliente_datos.php?id=<?php echo $reclamo['cliente_id']; ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-person"></i> Ver Cliente
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Respuesta (si existe) -->
                <?php if(!empty($reclamo['respuesta'])): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">Respuesta</h5>
                        <div class="p-3 bg-light rounded">
                            <div class="mb-2">
                                <strong>Respondido por:</strong> 
                                <?php echo htmlspecialchars($reclamo['respondido_por_nombre'] ?? ''); ?>
                                <?php if(!empty($reclamo['fecha_respuesta'])): ?>
                                <span class="ms-3">
                                    <strong>Fecha:</strong> 
                                    <?php echo date('d/m/Y H:i', strtotime($reclamo['fecha_respuesta'])); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="p-3 border rounded bg-white">
                                <?php echo nl2br(htmlspecialchars($reclamo['respuesta'] ?? '')); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if($reclamo['estado'] != 'cerrado'): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <a href="../../inc/responder_reclamo.php?id=<?php echo $reclamo_id; ?>" class="btn btn-warning">
                                    <i class="bi bi-reply"></i> Responder Reclamo
                                </a>
                                
                              
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
    // Set the confirm button's href
    document.getElementById('btnConfirmarCierre').href = '../../inc/cerrar_reclamo.php?id=' + reclamoId;
    
    // Show the modal
    var myModal = new bootstrap.Modal(document.getElementById('cerrarReclamoModal'));
    myModal.show();
}

// Check for success or error messages in URL parameters
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('success')) {
        const successType = urlParams.get('success');
        if (successType === 'reclamo_finalizado') {
            // Show success message
            alert('El reclamo ha sido finalizado exitosamente.');
            // Reload the page without the success parameter
            window.location.href = 'ver_detalle_reclamo.php?id=<?php echo $reclamo_id; ?>';
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
        
        // Show error message
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
    
    /* Estilos para modo oscuro */
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
</style>