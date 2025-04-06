<?php include '../../../admin/include/sidebar.php'; ?>

<!-- Remove these map-related includes -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<?php
require_once '../cnx.php';

// Verificar si se proporcionó un ID de cliente
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ver_clientes.php?error=id_invalido');
    exit();
}

$cliente_id = $_GET['id'];

// Consulta modificada para obtener los datos del cliente
// Eliminamos la referencia a usuario_id que no existe
$query = "SELECT c.* 
          FROM clientes c 
          WHERE c.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ver_clientes.php?error=cliente_no_encontrado');
    exit();
}

$cliente = $result->fetch_assoc();

// Consulta para obtener las deudas del cliente
$query_deudas = "SELECT d.*, p.nombre as politica_nombre, p.tasa 
                FROM deudas d 
                JOIN politicas_interes p ON d.politica_interes_id = p.id 
                WHERE d.cliente_id = ? 
                ORDER BY d.fecha_vencimiento DESC";
$stmt_deudas = $conn->prepare($query_deudas);
$stmt_deudas->bind_param("i", $cliente_id);
$stmt_deudas->execute();
$result_deudas = $stmt_deudas->get_result();

// Calcular la deuda total
$deuda_total = 0;
$deudas_array = [];
while ($row = $result_deudas->fetch_assoc()) {
    $deudas_array[] = $row;
    if ($row['estado'] != 'pagado') {
        $deuda_total += $row['saldo_pendiente'];
    }
}
// Reiniciar el puntero del resultado
$result_deudas->data_seek(0);

// Consulta para obtener los reclamos del cliente
$query_reclamos = "SELECT * FROM reclamos WHERE cliente_id = ? ORDER BY created_at DESC";
$stmt_reclamos = $conn->prepare($query_reclamos);
$stmt_reclamos->bind_param("i", $cliente_id);
$stmt_reclamos->execute();
$result_reclamos = $stmt_reclamos->get_result();
?>

<!-- Contenido principal -->
<div class="content-wrapper">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Datos del Cliente</h4>
                        <a href="ver_clientes.php" class="btn btn-light">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="profile-img-container mb-3">
                                    <?php if(!empty($cliente['imagen']) && $cliente['imagen'] != 'default.png'): ?>
                                        <img src="../../../uploads/profiles/<?php echo $cliente['imagen']; ?>" 
                                             alt="Perfil" 
                                             class="img-fluid rounded-circle profile-image"
                                             style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #121a35;">
                                    <?php else: ?>
                                        <img src="../../../uploads/profiles/default.png" 
                                             alt="Perfil" 
                                             class="img-fluid rounded-circle profile-image"
                                             style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #121a35;">
                                    <?php endif; ?>
                                </div>
                                <h3 class="cliente-nombre"><?php echo htmlspecialchars($cliente['nombre']); ?></h3>
                                <span class="badge bg-primary">Cliente</span>
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 class="info-section-title">Resumen de Cuenta</h5>
                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <div class="row text-center">
                                                    <div class="col-6">
                                                        <h6 class="text-muted">Deuda Total</h6>
                                                        <h3 class="text-primary"><?php echo number_format($deuda_total, 0, ',', '.'); ?> Gs.</h3>
                                                    </div>
                                                    <div class="col-6">
                                                        <h6 class="text-muted">Pagos Realizados</h6>
                                                        <h3 class="text-success">0 Gs.</h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-grid gap-2 mt-3">
                                            <a href="editar_cliente.php?id=<?php echo $cliente['id']; ?>" class="btn btn-primary">
                                                <i class="bi bi-pencil-square"></i> Editar Cliente
                                            </a>
                                            <a href="agregar_deuda.php?cliente_id=<?php echo $cliente['id']; ?>" class="btn btn-success">
                                                <i class="bi bi-plus-circle"></i> Agregar Deuda
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="info-section-title">Ubicación</h5>
                                        <?php if(!empty($cliente['ubicacion_link'])): ?>
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <p><strong>Dirección:</strong> <?php echo htmlspecialchars($cliente['direccion']); ?></p>
                                                    
                                                    <!-- Google Maps Button Only -->
                                                    <a href="<?php echo htmlspecialchars($cliente['ubicacion_link']); ?>" 
                                                       target="_blank" 
                                                       class="btn btn-info w-100">
                                                        <i class="bi bi-geo-alt"></i> Ver en Google Maps
                                                    </a>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning">
                                                <i class="bi bi-exclamation-triangle"></i>
                                                El Usuario aún no cargó su ubicación.
                                                <a href="editar_cliente.php?id=<?php echo $cliente['id']; ?>&section=ubicacion" 
                                                   class="alert-link">
                                                    Agregar ubicación
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <h5 class="info-section-title">Información Personal</h5>
                                        <ul class="list-group cliente-info-list">
                                            <li class="list-group-item d-flex justify-content-between">
                                                <strong>ID:</strong>
                                                <span><?php echo $cliente['id']; ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <strong>Identificación:</strong>
                                                <span><?php echo htmlspecialchars($cliente['identificacion']); ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <strong>Email:</strong>
                                                <span><?php echo !empty($cliente['email']) ? htmlspecialchars($cliente['email']) : ''; ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <strong>Teléfono:</strong>
                                                <span><?php echo htmlspecialchars($cliente['telefono']); ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <strong>Dirección:</strong>
                                                <span><?php echo htmlspecialchars($cliente['direccion']); ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <strong>Fecha de Registro:</strong>
                                                <span><?php echo date('d/m/Y', strtotime($cliente['created_at'])); ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Deudas -->
        <div class="card mb-4">
            <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Deudas del Cliente</h5>
                <a href="agregar_deuda.php?cliente_id=<?php echo $cliente_id; ?>" class="btn btn-light btn-sm">
                    <i class="bi bi-plus-circle"></i> Nueva Deuda
                </a>
            </div>
            <div class="card-body">
                <?php if($result_deudas->num_rows > 0): ?>
                    <div class="deudas-container">
                        <?php 
                        $contador_deudas = 1; // Inicializar contador de deudas
                        while($deuda = $result_deudas->fetch_assoc()): 
                        ?>
                            <div class="deuda-item mb-3">
                                <div class="deuda-header" onclick="toggleDeuda('deuda-<?php echo $deuda['id']; ?>')">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>#<?php echo $contador_deudas; ?></strong> - 
                                            <?php echo htmlspecialchars($deuda['descripcion']); ?>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="badge <?php 
                                                if($deuda['estado'] == 'pendiente') echo 'bg-warning text-dark';
                                                elseif($deuda['estado'] == 'pagado') echo 'bg-success';
                                                elseif($deuda['estado'] == 'vencido') echo 'bg-danger';
                                            ?> me-2">
                                                <?php echo ucfirst($deuda['estado']); ?>
                                            </span>
                                            <i class="bi bi-chevron-down toggle-icon"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="deuda-content" id="deuda-<?php echo $deuda['id']; ?>">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Monto:</strong> <?php echo number_format($deuda['monto'], 0, ',', '.'); ?> Gs.</p>
                                            <p><strong>Fecha Emisión:</strong> <?php echo date('d/m/Y', strtotime($deuda['fecha_emision'])); ?></p>
                                            <p><strong>Fecha Vencimiento:</strong> <?php echo date('d/m/Y', strtotime($deuda['fecha_vencimiento'])); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Política de Interés:</strong> <?php echo htmlspecialchars($deuda['politica_nombre']); ?> (<?php echo $deuda['tasa']; ?>%)</p>
                                            <p><strong>Saldo Pendiente:</strong> <?php echo number_format($deuda['saldo_pendiente'], 0, ',', '.'); ?> Gs.</p>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <div class="btn-group" role="group">
                                            <a href="deudas/ver_deuda.php?id=<?php echo $deuda['id']; ?>" class="btn btn-sm btn-info" title="Ver deuda">
                                                <i class="bi bi-eye"></i> Ver detalles
                                            </a>
                                            <a href="deudas/editar_deuda.php?id=<?php echo $deuda['id']; ?>" class="btn btn-sm btn-primary" title="Editar deuda">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                            <button class="btn btn-sm btn-danger" onclick="eliminarDeuda(<?php echo $deuda['id']; ?>)" title="Eliminar deuda">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php 
                        $contador_deudas++; // Incrementar contador después de cada deuda
                        endwhile; 
                        ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        Este cliente no tiene deudas registradas.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sección de Reclamos -->
        <div class="card mb-4">
            <div class="card-header bg-custom text-white">
                <h5 class="mb-0">Reclamos del Cliente</h5>
            </div>
            <div class="card-body">
                <?php if($result_reclamos->num_rows > 0): ?>
                    <div class="row">
                        <?php 
                        $counter = 0;
                        while($reclamo = $result_reclamos->fetch_assoc()): 
                        ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Reclamo #<?php echo $reclamo['id']; ?></h6>
                                        <span class="badge <?php echo ($reclamo['estado'] == 'abierto') ? 'bg-danger' : 'bg-success'; ?>">
                                            <?php echo ucfirst($reclamo['estado']); ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text"><?php echo htmlspecialchars($reclamo['descripcion']); ?></p>
                                        <p class="card-text"><small class="text-muted">Fecha: <?php echo date('d/m/Y H:i', strtotime($reclamo['created_at'])); ?></small></p>
                                    </div>
                                    <div class="card-footer">
                                        <a href="ver_reclamo.php?id=<?php echo $reclamo['id']; ?>" class="btn btn-sm btn-primary">Ver Detalles</a>
                                    </div>
                                </div>
                            </div>
                            <?php if($counter % 2 == 1): ?>
                                <div class="w-100"></div>
                            <?php endif; ?>
                        <?php $counter++; endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        Este cliente no tiene reclamos registrados.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos generales */
    .content-wrapper {
        margin-left: 250px;
        padding: 20px;
    }
    .bg-custom {
        background-color: #121a35;
    }
    .cliente-nombre {
        margin-top: 10px;
        font-weight: 600;
    }
    .info-section-title {
        margin-bottom: 15px;
        font-weight: 600;
        border-bottom: 2px solid #121a35;
        padding-bottom: 5px;
    }
    .cliente-info-list .list-group-item {
        padding: 10px 15px;
    }
    
    /* Estilos para las deudas colapsables */
    .deuda-item {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .deuda-header {
        padding: 15px;
        background-color: #f8f9fa;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    
    .deuda-header:hover {
        background-color: #e9ecef;
    }
    
    .deuda-content {
        padding: 15px;
        border-top: 1px solid #dee2e6;
        display: none;
        background-color: #fff;
    }
    
    .toggle-icon {
        transition: transform 0.3s ease;
    }
    
    .toggle-icon.rotate {
        transform: rotate(180deg);
    }
    
    /* Mini map styles */
    #mini-map {
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    
    body.dark-mode #mini-map {
        box-shadow: 0 0 10px rgba(255,255,255,0.1);
    }
    
    /* Fix for Leaflet controls in dark mode */
    body.dark-mode .leaflet-control-zoom a {
        color: #333;
    }
    
    /* Estilos para modo oscuro */
    body.dark-mode .list-group-item {
        background-color: #2a3356;
        color: #fff;
        border-color: #3a4366;
    }
    
    body.dark-mode .card-text {
        color: #fff;
    }
    
    body.dark-mode .text-muted {
        color: #adb5bd !important;
    }
    
    body.dark-mode .info-section-title {
        border-bottom-color: #764AF1;
        color: #fff;
    }
    
    body.dark-mode .profile-image {
        border-color: #764AF1 !important;
    }
    
    body.dark-mode .badge.bg-warning.text-dark {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }
    
    body.dark-mode .badge.bg-success {
        background-color: #198754 !important;
    }
    
    body.dark-mode .badge.bg-danger {
        background-color: #dc3545 !important;
    }
    
    body.dark-mode .badge.bg-primary {
        background-color: #0d6efd !important;
    }
    
    body.dark-mode .table {
        color: #fff;
    }
    
    body.dark-mode .table thead th {
        border-color: #3a4366;
    }
    
    body.dark-mode .table td, 
    body.dark-mode .table th {
        border-color: #3a4366;
    }
    
    body.dark-mode .table-hover tbody tr:hover {
        background-color: #2a3356;
        color: #fff !important;
    }
    
    /* Add this new style to ensure text stays white on hover */
    body.dark-mode .table-hover tbody tr:hover td {
        color: #fff !important;
    }
    
    body.dark-mode .alert-info {
        background-color: #0d6efd20;
        color: #0dcaf0;
        border-color: #0d6efd40;
    }
    
    body.dark-mode .card-header.d-flex {
        background-color: #2a3356;
        border-color: #3a4366;
    }
    
    body.dark-mode .card-footer {
        background-color: #2a3356;
        border-color: #3a4366;
    }
    
    /* Estilos para deudas en modo oscuro */
    body.dark-mode .deuda-item {
        border-color: #3a4366;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    
    body.dark-mode .deuda-header {
        background-color: #2a3356;
        color: #fff;
    }
    
    body.dark-mode .deuda-header:hover {
        background-color: #3a4366;
    }
    
    body.dark-mode .deuda-content {
        background-color: #1e2746;
        color: #fff;
        border-top-color: #3a4366;
    }
    
    /* Estilos para impresión */
    @media print {
        .sidebar, .btn-light, .btn-primary, .btn-success, .btn-info, .btn-danger {
            display: none;
        }
        .content-wrapper {
            margin-left: 0;
            padding: 0;
        }
        body {
            background-color: white !important;
            color: black !important;
        }
        .card {
            border: none !important;
        }
        .card-header {
            background-color: white !important;
            color: black !important;
            border-bottom: 1px solid #ddd;
        }
        .table {
            color: black !important;
        }
        .table td, .table th {
            color: black !important;
        }
    }
</style>

<script>
    function eliminarDeuda(id) {
        if (confirm('¿Está seguro de que desea eliminar esta deuda?')) {
            window.location.href = 'deudas/eliminar_deuda.php?id=' + id + '&cliente_id=<?php echo $cliente_id; ?>';
        }
    }
    
    function toggleDeuda(deudaId) {
        const content = document.getElementById(deudaId);
        const header = content.previousElementSibling;
        const icon = header.querySelector('.toggle-icon');
        
        if (content.style.display === 'block') {
            content.style.display = 'none';
            icon.classList.remove('rotate');
        } else {
            content.style.display = 'block';
            icon.classList.add('rotate');
        }
    }
</script>