<?php
// Include sidebar after all potential redirects
require_once '../../cnx.php';

// Verificar si se proporcionó un ID de pago
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../ver_clientes.php?error=id_invalido');
    exit();
}

$pago_id = $_GET['id'];

// Consulta para obtener los datos del pago
$query = "SELECT p.*, d.id as deuda_id, d.saldo_pendiente, d.estado as deuda_estado 
          FROM pagos p 
          JOIN deudas d ON p.deuda_id = d.id 
          WHERE p.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $pago_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ../../ver_clientes.php?error=pago_no_encontrado');
    exit();
}

$pago = $result->fetch_assoc();
$deuda_id = $pago['deuda_id'];

// Procesar la anulación si se confirma
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmar_anulacion'])) {
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // 1. Actualizar el saldo pendiente de la deuda
        $nuevo_saldo = $pago['saldo_pendiente'] + $pago['monto_pagado'];
        
        // Si la deuda estaba pagada, cambiar su estado a pendiente
        if ($pago['deuda_estado'] == 'pagado') {
            $query_update_deuda = "UPDATE deudas SET saldo_pendiente = ?, estado = 'pendiente' WHERE id = ?";
        } else {
            $query_update_deuda = "UPDATE deudas SET saldo_pendiente = ? WHERE id = ?";
        }
        
        $stmt_update_deuda = $conn->prepare($query_update_deuda);
        $stmt_update_deuda->bind_param("di", $nuevo_saldo, $deuda_id);
        $stmt_update_deuda->execute();
        
        // 2. Marcar el pago como anulado
        // 2. Instead of updating a non-existent 'estado' column, we'll add a new column to track if the payment is canceled
        // You can either:
        // 1. Add a 'is_anulado' column to your pagos table, or
        // 2. Simply delete the payment record (not recommended for audit purposes)
        // 3. Add a note to the payment record
        
        // Option 1: If you want to add an 'is_anulado' column to your database:
        $query_anular_pago = "UPDATE pagos SET is_anulado = 1 WHERE id = ?";
        
        // Option 2: If you prefer to just delete the payment (not recommended):
        // $query_anular_pago = "DELETE FROM pagos WHERE id = ?";
        
        // Option 3: If you have a 'notas' or similar column in your pagos table:
        // $query_anular_pago = "UPDATE pagos SET notas = CONCAT(IFNULL(notas, ''), ' [ANULADO] ') WHERE id = ?";
        
        $stmt_anular_pago = $conn->prepare($query_anular_pago);
        $stmt_anular_pago->bind_param("i", $pago_id);
        $stmt_anular_pago->execute();
        
        // 3. Revertir las cuotas pagadas con este pago (opcional, si tienes esta relación)
        // Si tienes una tabla que relaciona pagos con cuotas, puedes actualizar las cuotas aquí
        
        // 4. Registrar en el historial
        $usuario_id = 17; // Usar un ID de usuario válido o implementar sistema de sesiones
        $detalle = "Anulación de pago de " . number_format($pago['monto_pagado'], 0, ',', '.') . " Gs. realizado el " . 
                  date('d/m/Y', strtotime($pago['fecha_pago']));
        
        $query_historial = "INSERT INTO historial_deudas (deuda_id, accion, detalle, usuario_id, created_at) 
                          VALUES (?, 'anulacion_pago', ?, ?, NOW())";
        $stmt_historial = $conn->prepare($query_historial);
        $stmt_historial->bind_param("isi", $deuda_id, $detalle, $usuario_id);
        $stmt_historial->execute();
        
        // Confirmar la transacción
        $conn->commit();
        
        // Redireccionar a la página de detalles de la deuda
        header('Location: ver_deuda.php?id=' . $deuda_id . '&success=pago_anulado');
        exit();
        
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollback();
        $error = "Error al anular el pago: " . $e->getMessage();
    }
}

// Incluir el sidebar después de todas las redirecciones potenciales
include '../../../../admin/include/sidebar.php';
?>

<!-- Contenido principal -->
<div class="content-wrapper">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Anular Pago</h4>
                        <a href="ver_deuda.php?id=<?php echo $deuda_id; ?>" class="btn btn-light">
                            <i class="bi bi-arrow-left"></i> Volver a Detalles
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-warning">
                            <h5><i class="bi bi-exclamation-triangle"></i> Advertencia</h5>
                            <p>Está a punto de anular un pago. Esta acción:</p>
                            <ul>
                                <li>Aumentará el saldo pendiente de la deuda</li>
                                <li>Podría cambiar el estado de la deuda de "pagado" a "pendiente"</li>
                                <li>No puede deshacerse</li>
                            </ul>
                        </div>
                        
                        <h5 class="info-section-title">Detalles del Pago</h5>
                        <ul class="list-group mb-4">
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Fecha del Pago:</strong>
                                <span><?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Monto:</strong>
                                <span class="text-danger"><?php echo number_format($pago['monto_pagado'], 0, ',', '.') . ' Gs.'; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Método de Pago:</strong>
                                <span><?php echo ucfirst(htmlspecialchars($pago['metodo_pago'])); ?></span>
                            </li>
                            <?php if (!empty($pago['referencia'])): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Referencia:</strong>
                                <span><?php echo htmlspecialchars($pago['referencia']); ?></span>
                            </li>
                            <?php endif; ?>
                        </ul>
                        
                        <form method="POST" action="">
                            <div class="d-grid gap-2">
                                <button type="submit" name="confirmar_anulacion" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea anular este pago?')">
                                    <i class="bi bi-x-circle"></i> Confirmar Anulación
                                </button>
                                <a href="ver_deuda.php?id=<?php echo $deuda_id; ?>" class="btn btn-secondary">
                                    Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .info-section-title {
        border-bottom: 2px solid #121a35;
        padding-bottom: 8px;
        margin-bottom: 15px;
    }
    
    .bg-custom {
        background-color: #121a35;
    }
    
    /* Dark mode adaptations */
    body.dark-mode .info-section-title {
        border-bottom-color: #2a3c70;
    }
    
    body.dark-mode .card {
        background-color: #1e2337;
        border-color: #2a3c70;
        color: #e9ecef;
    }
    
    body.dark-mode .list-group-item {
        background-color: #1e2337;
        border-color: #2a3c70;
        color: #e9ecef;
    }
    
    body.dark-mode .alert-warning {
        background-color: #332701;
        color: #ffd761;
        border-color: #664d03;
    }
</style>