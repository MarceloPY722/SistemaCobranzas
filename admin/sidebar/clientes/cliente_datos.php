<?php include 'inc/sidebar.php'; ?>

<?php
require_once 'inc/cnx.php';

// Verificar si se proporcionó un ID de cliente
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ver_clientes.php?error=id_invalido');
    exit();
}

$cliente_id = $_GET['id'];

// Consulta para obtener los datos del cliente
$query = "SELECT c.*, u.email as usuario_email, u.activo 
          FROM clientes c 
          JOIN usuarios u ON c.usuario_id = u.id 
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
                                <?php if(!empty($cliente['imagen']) && $cliente['imagen'] != 'default.png'): ?>
                                    <img src="/sistemacobranzas/uploads/profiles/<?php echo $cliente['imagen']; ?>" 
                                         alt="Perfil" 
                                         class="img-fluid rounded-circle profile-image-large mb-3"
                                         style="width: 200px; height: 200px; object-fit: cover; border: 4px solid #007bff;">
                                <?php else: ?>
                                    <img src="/sistemacobranzas/uploads/profiles/default.png" 
                                         alt="Perfil" 
                                         class="img-fluid rounded-circle profile-image-large mb-3"
                                         style="width: 200px; height: 200px; object-fit: cover; border: 4px solid #007bff;">
                                <?php endif; ?>
                                <h4><?php echo htmlspecialchars($cliente['nombre']); ?></h4>
                                <p class="text-muted">
                                    <span class="badge <?php echo $cliente['activo'] ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $cliente['activo'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 class="border-bottom pb-2">Información Personal</h5>
                                        <table class="table table-borderless">
                                            <tr>
                                                <th>ID:</th>
                                                <td><?php echo $cliente['id']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Identificación:</th>
                                                <td><?php echo htmlspecialchars($cliente['identificacion']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Email:</th>
                                                <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Teléfono:</th>
                                                <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Dirección:</th>
                                                <td><?php echo htmlspecialchars($cliente['direccion']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Fecha de registro:</th>
                                                <td><?php echo date('d/m/Y', strtotime($cliente['created_at'])); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="border-bottom pb-2">Resumen de Cuenta</h5>
                                        <?php
                                        $total_deudas = 0;
                                        $total_pendiente = 0;
                                        
                                        if ($result_deudas->num_rows > 0) {
                                            while ($deuda = $result_deudas->fetch_assoc()) {
                                                $total_deudas += $deuda['monto'];
                                                $total_pendiente += $deuda['saldo_pendiente'];
                                            }
                                            // Reiniciar el puntero del resultado para usarlo después
                                            $result_deudas->data_seek(0);
                                        }
                                        ?>
                                        <div class="card bg-light mb-3">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-6 text-center">
                                                        <h6>Total Deudas</h6>
                                                        <h4 class="text-primary"><?php echo number_format($total_deudas, 0, ',', '.'); ?> Gs.</h4>
                                                    </div>
                                                    <div class="col-6 text-center">
                                                        <h6>Saldo Pendiente</h6>
                                                        <h4 class="text-danger"><?php echo number_format($total_pendiente, 0, ',', '.'); ?> Gs.</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <h6 class="mt-3">Reclamos Activos: <?php echo $result_reclamos->num_rows; ?></h6>
                                        <div class="d-grid gap-2 mt-3">
                                            <a href="modificar_cliente.php?id=<?php echo $cliente_id; ?>" class="btn btn-primary">
                                                <i class="bi bi-pencil"></i> Editar Cliente
                                            </a>
                                            <button class="btn btn-success" onclick="window.location.href='agregar_deuda.php?cliente_id=<?php echo $cliente_id; ?>'">
                                                <i class="bi bi-plus-circle"></i> Agregar Deuda
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sección de Deudas -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-custom text-white">
                        <h5 class="mb-0">Deudas del Cliente</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($result_deudas->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Descripción</th>
                                            <th>Monto</th>
                                            <th>Saldo Pendiente</th>
                                            <th>Vencimiento</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($deuda = $result_deudas->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $deuda['id']; ?></td>
                                                <td><?php echo htmlspecialchars($deuda['descripcion']); ?></td>
                                                <td><?php echo number_format($deuda['monto'], 0, ',', '.'); ?> Gs.</td>
                                                <td><?php echo number_format($deuda['saldo_pendiente'], 0, ',', '.'); ?> Gs.</td>
                                                <td><?php echo date('d/m/Y', strtotime($deuda['fecha_vencimiento'])); ?></td>
                                                <td>
                                                    <?php
                                                    $estado_class = '';
                                                    switch ($deuda['estado']) {
                                                        case 'pendiente':
                                                            $estado_class = 'bg-warning';
                                                            break;
                                                        case 'pagado':
                                                            $estado_class = 'bg-success';
                                                            break;
                                                        case 'vencido':
                                                            $estado_class = 'bg-danger';
                                                            break;
                                                        case 'cancelado':
                                                            $estado_class = 'bg-secondary';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $estado_class; ?>">
                                                        <?php echo ucfirst($deuda['estado']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="detalles_deuda.php?id=<?php echo $deuda['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="registrar_pago.php?deuda_id=<?php echo $deuda['id']; ?>" class="btn btn-sm btn-success">
                                                        <i class="bi bi-cash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                Este cliente no tiene deudas registradas.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sección de Reclamos -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-custom text-white">
                        <h5 class="mb-0">Reclamos del Cliente</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($result_reclamos->num_rows > 0): ?>
                            <div class="accordion" id="accordionReclamos">
                                <?php $counter = 1; while ($reclamo = $result_reclamos->fetch_assoc()): ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading<?php echo $counter; ?>">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $counter; ?>" aria-expanded="false" aria-controls="collapse<?php echo $counter; ?>">
                                                <div class="d-flex justify-content-between w-100 me-3">
                                                    <span>Reclamo #<?php echo $reclamo['id']; ?> - <?php echo date('d/m/Y', strtotime($reclamo['created_at'])); ?></span>
                                                    <span class="badge <?php echo ($reclamo['estado'] == 'abierto' || $reclamo['estado'] == 'en_proceso') ? 'bg-warning' : 'bg-success'; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $reclamo['estado'])); ?>
                                                    </span>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse<?php echo $counter; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $counter; ?>" data-bs-parent="#accordionReclamos">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Asunto:</strong> <?php echo htmlspecialchars($reclamo['asunto']); ?></p>
                                                        <p><strong>Descripción:</strong> <?php echo htmlspecialchars($reclamo['descripcion']); ?></p>
                                                        <p><strong>Fecha de creación:</strong> <?php echo date('d/m/Y H:i', strtotime($reclamo['created_at'])); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Estado:</strong> 
                                                            <span class="badge <?php echo ($reclamo['estado'] == 'abierto' || $reclamo['estado'] == 'en_proceso') ? 'bg-warning' : 'bg-success'; ?>">
                                                                <?php echo ucfirst(str_replace('_', ' ', $reclamo['estado'])); ?>
                                                            </span>
                                                        </p>
                                                        <?php if (!empty($reclamo['respuesta'])): ?>
                                                            <p><strong>Respuesta:</strong> <?php echo htmlspecialchars($reclamo['respuesta']); ?></p>
                                                            <p><strong>Fecha de respuesta:</strong> <?php echo date('d/m/Y H:i', strtotime($reclamo['updated_at'])); ?></p>
                                                        <?php endif; ?>
                                                        <?php if ($reclamo['estado'] == 'abierto' || $reclamo['estado'] == 'en_proceso'): ?>
                                                            <a href="responder_reclamo.php?id=<?php echo $reclamo['id']; ?>" class="btn btn-sm btn-primary mt-2">
                                                                <i class="bi bi-reply"></i> Responder
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php $counter++; endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                Este cliente no tiene reclamos registrados.
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <button class="btn btn-primary" onclick="window.location.href='registrar_reclamo.php?cliente_id=<?php echo $cliente_id; ?>'">
                                <i class="bi bi-plus-circle"></i> Registrar Nuevo Reclamo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts adicionales específicos para esta página -->
<script>
    // Script para manejar la visualización de detalles de deudas o reclamos si es necesario
    document.addEventListener('DOMContentLoaded', function() {
        // Código para inicializar componentes específicos si es necesario
        
        // Si hay un parámetro en la URL para abrir un reclamo específico
        const urlParams = new URLSearchParams(window.location.search);
        const reclamoId = urlParams.get('reclamo_id');
        
        if (reclamoId) {
            // Buscar el reclamo y abrirlo
            const reclamoElements = document.querySelectorAll('.accordion-item');
            reclamoElements.forEach(item => {
                const headerText = item.querySelector('.accordion-button').textContent;
                if (headerText.includes(`Reclamo #${reclamoId}`)) {
                    item.querySelector('.accordion-button').click();
                }
            });
        }
    });
</script>

</body>
</html>