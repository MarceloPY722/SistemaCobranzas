<?php
session_start();
require_once '../include/cnx.php';

// Verificar si el usuario está logueado como cliente
if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../../index.php');
    exit;
}

$cliente_id = $_SESSION['cliente_id'];

// Obtener los datos del cliente
$query = "SELECT * FROM clientes WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$cliente_id]);
$cliente = $stmt->fetch();

// Obtener estadísticas del cliente
// Total de deuda pendiente
$query_deuda = "SELECT SUM(saldo_pendiente) as deuda_total FROM deudas WHERE cliente_id = ? AND estado != 'pagado'";
$stmt_deuda = $pdo->prepare($query_deuda);
$stmt_deuda->execute([$cliente_id]);
$deuda_info = $stmt_deuda->fetch();
$deuda_total = $deuda_info['deuda_total'] ?? 0;

// Total de préstamos
$query_prestamos = "SELECT COUNT(*) as total_prestamos FROM deudas WHERE cliente_id = ?";
$stmt_prestamos = $pdo->prepare($query_prestamos);
$stmt_prestamos->execute([$cliente_id]);
$prestamos_info = $stmt_prestamos->fetch();
$total_prestamos = $prestamos_info['total_prestamos'];

// Total pagado
$query_pagado = "SELECT SUM(p.monto_pagado) as total_pagado 
                FROM pagos p 
                JOIN deudas d ON p.deuda_id = d.id 
                WHERE d.cliente_id = ? AND p.estado = 'aprobado'";
$stmt_pagado = $pdo->prepare($query_pagado);
$stmt_pagado->execute([$cliente_id]);
$pagado_info = $stmt_pagado->fetch();
$total_pagado = $pagado_info['total_pagado'] ?? 0;

// Función para formatear montos
function formatMoney($amount) {
    return '₲ ' . number_format($amount, 0, ',', '.');
}

include '../include/sidebar.php';
?>

<div class="content-wrapper">
    <div class="container mt-4">
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>¡Éxito!</strong> Los datos han sido actualizados correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Mi Perfil</h4>
                        <a href="editar_perfil.php" class="btn btn-light">
                            <i class="bi bi-pencil"></i> Editar Perfil
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="profile-img-container mb-3">
                                    <?php if(!empty($cliente['imagen']) && $cliente['imagen'] != 'default.png'): ?>
                                        <img src="../../uploads/profiles/<?php echo $cliente['imagen']; ?>" 
                                             alt="Perfil" 
                                             class="img-fluid rounded-circle profile-image"
                                             style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #121a35;">
                                    <?php else: ?>
                                        <img src="../../uploads/profiles/default.png" 
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
                                                        <h4 class="text-danger"><?php echo formatMoney($deuda_total); ?></h4>
                                                    </div>
                                                    <div class="col-6">
                                                        <h6 class="text-muted">Total Pagado</h6>
                                                        <h4 class="text-success"><?php echo formatMoney($total_pagado); ?></h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row text-center">
                                                    <div class="col-12">
                                                        <h6 class="text-muted">Préstamos Totales</h6>
                                                        <h4><?php echo $total_prestamos; ?></h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="info-section-title">Información de Contacto</h5>
                                        <div class="card">
                                            <div class="card-body">
                                                <p><i class="bi bi-envelope"></i> <strong>Email:</strong> <?php echo htmlspecialchars($cliente['email']); ?></p>
                                                <p><i class="bi bi-telephone"></i> <strong>Teléfono:</strong> <?php echo htmlspecialchars($cliente['telefono']); ?></p>
                                                <p><i class="bi bi-geo-alt"></i> <strong>Dirección:</strong> <?php echo htmlspecialchars($cliente['direccion']); ?></p>
                                            </div>
                                        </div>
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
    </div>
</div>

<style>
    .profile-image {
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transition: transform 0.3s;
    }
    
    .profile-image:hover {
        transform: scale(1.05);
    }
    
    .cliente-nombre {
        margin-top: 15px;
        font-weight: 600;
    }
    
    .info-section-title {
        border-bottom: 2px solid #121a35;
        padding-bottom: 8px;
        margin-bottom: 15px;
        color: #121a35;
        font-weight: 600;
    }
    
    .cliente-info-list .list-group-item {
        border-left: none;
        border-right: none;
    }
    
    .cliente-info-list .list-group-item:first-child {
        border-top: none;
    }
    
    @media (prefers-color-scheme: dark) {
        .card {
            background-color: #2c3e50;
            color: white;
        }
        
        .list-group-item {
            background-color: #2c3e50;
            color: white;
            border-color: #4a5568;
        }
        
        .info-section-title {
            color: white;
            border-bottom-color: #4a5568;
        }
        
        .text-muted {
            color: #a0aec0 !important;
        }
        
        .text-dark {
            color: white !important;
        }
    }
    
    /* Support for Bootstrap 5 dark mode */
    [data-bs-theme="dark"] .card,
    [data-bs-theme="dark"] .list-group-item,
    .dark-mode .card,
    .dark-mode .list-group-item {
        background-color: #2c3e50;
        color: white;
    }
    
    [data-bs-theme="dark"] .info-section-title,
    .dark-mode .info-section-title {
        color: white;
        border-bottom-color: #4a5568;
    }
    
    [data-bs-theme="dark"] .text-muted,
    .dark-mode .text-muted {
        color: #a0aec0 !important;
    }
</style>