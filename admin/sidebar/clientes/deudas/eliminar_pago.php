<?php
// Iniciar buffer de salida para evitar "headers already sent"
ob_start();

// Incluir el sidebar correctamente
include '../../../../admin/include/sidebar.php';
require_once '../../cnx.php';

// Verificar si se proporcionó un ID de pago
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../ver_clientes.php?error=id_invalido');
    exit();
}

$pago_id = $_GET['id'];
$deuda_id = isset($_GET['deuda_id']) ? $_GET['deuda_id'] : 0;

// Consulta para obtener los datos del pago
$query = "SELECT p.*, d.id as deuda_id, d.saldo_pendiente, d.estado as deuda_estado, c.id as cuota_id, c.monto_cuota, c.estado as cuota_estado, c.numero_cuota
          FROM pagos p 
          JOIN deudas d ON p.deuda_id = d.id 
          LEFT JOIN cuotas_deuda c ON p.cuota_id = c.id
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

// Procesar la eliminación si se confirma
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmar_eliminacion'])) {
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
        
        // 2. Si el pago estaba asociado a una cuota, actualizar su estado
        if (!empty($pago['cuota_id'])) {
            // Verificar si hay otros pagos para esta cuota
            $query_otros_pagos = "SELECT SUM(monto_pagado) as total_pagado 
                                 FROM pagos 
                                 WHERE cuota_id = ? AND id != ? AND estado != 'anulado'";
            $stmt_otros_pagos = $conn->prepare($query_otros_pagos);
            $stmt_otros_pagos->bind_param("ii", $pago['cuota_id'], $pago_id);
            $stmt_otros_pagos->execute();
            $result_otros_pagos = $stmt_otros_pagos->get_result();
            $otros_pagos = $result_otros_pagos->fetch_assoc();
            
            $total_pagado = $otros_pagos['total_pagado'] ?? 0;
            
            // Determinar el nuevo estado de la cuota
            $nuevo_estado_cuota = 'pendiente';
            if ($total_pagado >= $pago['monto_cuota']) {
                $nuevo_estado_cuota = 'pagado';
            } elseif (strtotime($pago['fecha_vencimiento'] ?? 'now') < time()) {
                $nuevo_estado_cuota = 'vencido';
            }
            
            // Actualizar la cuota
            $query_update_cuota = "UPDATE cuotas_deuda SET estado = ? WHERE id = ?";
            $stmt_update_cuota = $conn->prepare($query_update_cuota);
            $stmt_update_cuota->bind_param("si", $nuevo_estado_cuota, $pago['cuota_id']);
            $stmt_update_cuota->execute();
        }
        
        // 3. Eliminar el pago
        $query_eliminar_pago = "DELETE FROM pagos WHERE id = ?";
        $stmt_eliminar_pago = $conn->prepare($query_eliminar_pago);
        $stmt_eliminar_pago->bind_param("i", $pago_id);
        $stmt_eliminar_pago->execute();
        
        // 4. Registrar la acción en el historial (si existe la tabla)
        if ($conn->query("SHOW TABLES LIKE 'historial_sistema'")->num_rows > 0) {
            $accion = "Eliminación de pago #$pago_id";
            $usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 0;
            
            $query_historial = "INSERT INTO historial_sistema (usuario_id, accion, detalles) VALUES (?, ?, ?)";
            $stmt_historial = $conn->prepare($query_historial);
            $detalles = "Se eliminó el pago #$pago_id por un monto de " . number_format($pago['monto_pagado'], 0, ',', '.') . " Gs.";
            $stmt_historial->bind_param("iss", $usuario_id, $accion, $detalles);
            $stmt_historial->execute();
        }
        
        // Confirmar transacción
        $conn->commit();
        
        // Redirigir a la página de detalles de la deuda con mensaje de éxito
        header('Location: ver_deuda.php?id=' . $deuda_id . '&success=pago_eliminado');
        exit();
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        $error = "Error al eliminar el pago: " . $e->getMessage();
    }
}

// Obtener información adicional para mostrar en la página
$query_deuda = "SELECT d.*, c.nombre as cliente_nombre 
                FROM deudas d 
                JOIN clientes c ON d.cliente_id = c.id 
                WHERE d.id = ?";
$stmt_deuda = $conn->prepare($query_deuda);
$stmt_deuda->bind_param("i", $deuda_id);
$stmt_deuda->execute();
$result_deuda = $stmt_deuda->get_result();
$deuda = $result_deuda->fetch_assoc();

// Función para formatear montos
function formatMoney($amount) {
    return number_format($amount, 0, ',', '.');
}
?>

<!-- Contenido principal - Asegurarse de que esté dentro del content-wrapper -->
<div class="content-wrapper">
    <div class="container-fluid px-4">
        <h1 class="mt-4">Eliminar Pago</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="/sistemacobranzas/admin/index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/sistemacobranzas/admin/sidebar/clientes/ver_clientes.php">Clientes</a></li>
            <li class="breadcrumb-item"><a href="ver_deuda.php?id=<?php echo $deuda_id; ?>">Deuda #<?php echo $deuda_id; ?></a></li>
            <li class="breadcrumb-item active">Eliminar Pago</li>
        </ol>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-trash me-1"></i>
                Confirmar Eliminación de Pago
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Advertencia:</strong> Esta acción eliminará permanentemente el pago y actualizará el saldo pendiente de la deuda. Esta acción no se puede deshacer.
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Información del Pago</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th>ID del Pago:</th>
                                <td><?php echo $pago_id; ?></td>
                            </tr>
                            <tr>
                                <th>Fecha de Pago:</th>
                                <td><?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></td>
                            </tr>
                            <tr>
                                <th>Monto Pagado:</th>
                                <td>₲ <?php echo formatMoney($pago['monto_pagado']); ?></td>
                            </tr>
                            <tr>
                                <th>Método de Pago:</th>
                                <td><?php echo $pago['metodo_pago']; ?></td>
                            </tr>
                            <?php if (!empty($pago['cuota_id'])): ?>
                            <tr>
                                <th>Cuota:</th>
                                <td>Cuota #<?php echo $pago['numero_cuota'] ?? 'N/A'; ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Información de la Deuda</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th>Cliente:</th>
                                <td><?php echo $deuda['cliente_nombre']; ?></td>
                            </tr>
                            <tr>
                                <th>Descripción:</th>
                                <td><?php echo $deuda['descripcion']; ?></td>
                            </tr>
                            <tr>
                                <th>Saldo Actual:</th>
                                <td>₲ <?php echo formatMoney($deuda['saldo_pendiente']); ?></td>
                            </tr>
                            <tr>
                                <th>Nuevo Saldo (después de eliminar):</th>
                                <td>₲ <?php echo formatMoney($deuda['saldo_pendiente'] + $pago['monto_pagado']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <form method="post" action="">
                    <div class="d-flex justify-content-center">
                        <a href="ver_deuda.php?id=<?php echo $deuda_id; ?>" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-left me-1"></i> Cancelar
                        </a>
                        <button type="submit" name="confirmar_eliminacion" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Confirmar Eliminación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir el footer si es necesario
if (file_exists('../../../../admin/include/footer.php')) {
    include '../../../../admin/include/footer.php';
}
?>
