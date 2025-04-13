<?php
require_once 'inc/auth.php';
require_once 'inc/cnx.php'; // This should define $conn, but it seems it's not working correctly

// Make sure $conn is defined before using it
if (!isset($conn) || $conn === null) {
    // Try to establish the connection again
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "sistema_cobranzas"; // Updated database name
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

require_once 'inc/header.php';
require_once 'inc/sidebar.php';

// Consulta para obtener deudas vencidas o que vencen esta semana
$query = "SELECT d.id as deuda_id, d.descripcion, d.monto, d.saldo_pendiente, d.fecha_vencimiento, 
                 c.id as cliente_id, c.nombre as cliente_nombre, c.telefono 
          FROM deudas d 
          JOIN clientes c ON d.cliente_id = c.id 
          WHERE (d.estado = 'vencido' OR 
                (d.estado = 'pendiente' AND d.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY))) 
          AND d.saldo_pendiente > 0
          ORDER BY d.fecha_vencimiento ASC";

$result = $conn->query($query);
?>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mt-4 mb-4">Panel de Control</h2>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-custom text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Clientes con pagos pendientes o vencidos
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="tabla-vencimientos">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Descripción</th>
                                            <th>Monto Pendiente</th>
                                            <th>Fecha Vencimiento</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['cliente_nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                                                <td><?php echo number_format($row['saldo_pendiente'], 0, ',', '.'); ?> Gs.</td>
                                                <td>
                                                    <?php 
                                                    $fecha_vencimiento = new DateTime($row['fecha_vencimiento']);
                                                    $hoy = new DateTime();
                                                    echo $fecha_vencimiento->format('d/m/Y');
                                                    
                                                    // Calcular días de diferencia
                                                    $diff = $hoy->diff($fecha_vencimiento);
                                                    if ($fecha_vencimiento < $hoy) {
                                                        echo ' <span class="badge bg-danger">Vencido hace ' . $diff->days . ' días</span>';
                                                    } else {
                                                        echo ' <span class="badge bg-warning text-dark">Vence en ' . $diff->days . ' días</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if ($fecha_vencimiento < $hoy): ?>
                                                        <span class="badge bg-danger">Vencido</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="ver_detalles.php?id=<?php echo $row['cliente_id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i> Ver Detalles
                                                    </a>
                                                    <a href="sidebar/clientes/deudas/ver_deuda.php?id=<?php echo $row['deuda_id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-file-earmark-text"></i> Deuda
                                                    </a>
                                                    <a href="sidebar/clientes/cliente_datos.php?id=<?php echo $row['cliente_id']; ?>" class="btn btn-sm btn-secondary">
                                                        <i class="bi bi-person"></i> Cliente
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i> No hay clientes con pagos pendientes o vencidos para esta semana.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar detalles del cliente -->
<div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-custom text-white">
                <h5 class="modal-title" id="detalleModalLabel">Detalles del Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title cliente-nombre"></h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Teléfono:</strong>
                                <span class="cliente-telefono"></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Monto Pendiente:</strong>
                                <span class="cliente-monto text-danger fw-bold"></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Descripción:</strong>
                                <span class="cliente-descripcion"></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Script para manejar el modal de detalles
    document.addEventListener('DOMContentLoaded', function() {
        const detalleModal = document.getElementById('detalleModal');
        if (detalleModal) {
            detalleModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const cliente = button.getAttribute('data-cliente');
                const telefono = button.getAttribute('data-telefono');
                const monto = button.getAttribute('data-monto');
                const descripcion = button.getAttribute('data-descripcion');
                
                const modalTitle = detalleModal.querySelector('.modal-title');
                const clienteNombre = detalleModal.querySelector('.cliente-nombre');
                const clienteTelefono = detalleModal.querySelector('.cliente-telefono');
                const clienteMonto = detalleModal.querySelector('.cliente-monto');
                const clienteDescripcion = detalleModal.querySelector('.cliente-descripcion');
                
                modalTitle.textContent = 'Detalles de Contacto';
                clienteNombre.textContent = cliente;
                clienteTelefono.textContent = telefono;
                clienteMonto.textContent = monto;
                clienteDescripcion.textContent = descripcion;
            });
        }
        
        // Inicializar DataTables si está disponible
        if ($.fn.DataTable) {
            $('#tabla-vencimientos').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "order": [[3, "asc"]]
            });
        }
    });
</script>

<style>
    .bg-custom {
        background-color: #121a35 !important;
    }
    
    /* Estilos para modo oscuro */
    body.dark-mode .card {
        background-color: #2d3748 !important;
        color: #fff !important;
    }
    body.dark-mode .table {
        color: #fff !important;
    }
    body.dark-mode .table td, 
    body.dark-mode .table th {
        color: #fff !important;
    }
    body.dark-mode .modal-content {
        background-color: #2d3748 !important;
        color: #fff !important;
    }
    body.dark-mode .list-group-item {
        background-color: #2d3748 !important;
        color: #fff !important;
        border-color: #4a5568 !important;
    }
    body.dark-mode .alert-info {
        background-color: #2a4365 !important;
        color: #fff !important;
        border-color: #4299e1 !important;
    }
</style>

 
