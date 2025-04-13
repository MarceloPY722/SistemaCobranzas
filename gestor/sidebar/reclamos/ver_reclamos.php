<?php include '../../../gestor/inc/sidebar.php'; ?>

<?php
require_once '../../../gestor/sidebar/cnx.php';

$query = "SELECT r.*, c.nombre AS cliente_nombre 
          FROM reclamos r
          JOIN clientes c ON r.cliente_id = c.id
          ORDER BY r.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Contenido principal -->
<div class="content-wrapper">
    <div class="container mt-4">
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>¡Éxito!</strong> 
                <?php 
                    switch($_GET['success']) {
                        case 'reclamo_cerrado':
                            echo "El reclamo ha sido cerrado exitosamente.";
                            break;
                        case 'reclamo_respondido':
                            echo "El reclamo ha sido respondido exitosamente.";
                            break;
                        default:
                            echo "La operación se completó exitosamente.";
                    }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>¡Error!</strong> 
                <?php 
                    switch($_GET['error']) {
                        case 'id_invalido':
                            echo "ID de reclamo inválido.";
                            break;
                        case 'reclamo_no_encontrado':
                            echo "El reclamo no existe.";
                            break;
                        default:
                            echo "Ocurrió un error inesperado.";
                    }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Lista de Reclamos</h4>
                <button onclick="window.location.href='generar_pdf_reclamos.php'" class="btn btn-light">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tabla-reclamos">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Asunto</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result->num_rows > 0): ?>
                                <?php while($reclamo = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $reclamo['id']; ?></td>
                                    <td><?php echo htmlspecialchars($reclamo['cliente_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($reclamo['descripcion'], 0, 50)) . (strlen($reclamo['descripcion']) > 50 ? '...' : ''); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($reclamo['created_at'])); ?></td>
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
                                    <td>
                                        <div class="btn-group">
                                            <a href="ver_detalle_reclamo.php?id=<?php echo $reclamo['id']; ?>" class="btn btn-sm btn-info" title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if($reclamo['estado'] != 'cerrado'): ?>
                                            <a href="../../inc/responder_reclamo.php?id=<?php echo $reclamo['id']; ?>" class="btn btn-sm btn-primary" title="Responder">
                                                <i class="bi bi-reply"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="confirmarCierre(<?php echo $reclamo['id']; ?>)" 
                                                    title="Cerrar reclamo">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay reclamos registrados</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para cerrar reclamo -->
<div class="modal fade" id="cerrarReclamoModal" tabindex="-1" aria-labelledby="cerrarReclamoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="cerrarReclamoModalLabel">Confirmar cierre de reclamo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro que desea cerrar este reclamo? Esta acción marcará el reclamo como resuelto.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="../../../gestor/inc/cerrar_reclamo.php" method="POST">
                    <input type="hidden" name="reclamo_id" id="reclamo_id_cierre" value="">
                    <button type="submit" class="btn btn-success">Confirmar cierre</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .content-wrapper {
        margin-left: 250px;
        padding: 20px;
    }
    .bg-custom {
        background-color: #121a35;
    }
    .btn-group .btn {
        margin-right: 2px;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    
    /* Estilos para modo oscuro */
    body.dark-mode .table {
        color: #fff !important;
    }
    body.dark-mode .table td, 
    body.dark-mode .table th {
        color: #fff !important;
    }
    body.dark-mode .card {
        background-color: #2d3748 !important;
        color: #fff !important;
    }
</style>

<script>
    function confirmarCierre(reclamoId) {
        document.getElementById('reclamo_id_cierre').value = reclamoId;
        var modal = new bootstrap.Modal(document.getElementById('cerrarReclamoModal'));
        modal.show();
    }
    
    // Inicializar DataTables si está disponible
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#tabla-reclamos').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "order": [[0, "desc"]]
            });
        }
    });
</script>