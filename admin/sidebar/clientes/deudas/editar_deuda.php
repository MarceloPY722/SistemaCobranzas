<?php
// Start output buffering before any output
ob_start();
session_start(); // Start the session
include '../../../../admin/include/sidebar.php';
require_once '../../cnx.php';

// Verificar si se proporcionó un ID de deuda
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../ver_clientes.php?error=id_invalido');
    exit();
}

$deuda_id = $_GET['id'];

// Obtener información de la deuda
$query = "SELECT d.*, c.nombre as cliente_nombre, c.id as cliente_id, 
          p.nombre as politica_nombre, p.id as politica_id
          FROM deudas d 
          JOIN clientes c ON d.cliente_id = c.id
          JOIN politicas_interes p ON d.politica_interes_id = p.id
          WHERE d.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $deuda_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ../../ver_clientes.php?error=deuda_no_encontrada');
    exit();
}

$deuda = $result->fetch_assoc();

// Obtener todas las políticas de interés para el select
$query_politicas = "SELECT * FROM politicas_interes ORDER BY nombre";
$result_politicas = $conn->query($query_politicas);

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar datos
    $descripcion = trim($_POST['descripcion']);
    $monto = floatval($_POST['monto']);
    $saldo_pendiente = floatval($_POST['saldo_pendiente']);
    $fecha_emision = $_POST['fecha_emision'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];
    $politica_id = intval($_POST['politica_id']);
    $estado = $_POST['estado'];
    $notas = trim($_POST['notas']);
    
    // Validaciones básicas
    if (empty($descripcion) || $monto <= 0 || $saldo_pendiente < 0) {
        $error = "Por favor complete todos los campos obligatorios correctamente.";
    } else {
        // Actualizar la deuda en la base de datos
        $query_update = "UPDATE deudas SET 
                        descripcion = ?, 
                        monto = ?, 
                        saldo_pendiente = ?, 
                        fecha_emision = ?, 
                        fecha_vencimiento = ?, 
                        politica_interes_id = ?, 
                        estado = ?, 
                        notas = ?
                        WHERE id = ?";
        
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("sddsssssi", 
            $descripcion, 
            $monto, 
            $saldo_pendiente, 
            $fecha_emision, 
            $fecha_vencimiento, 
            $politica_id, 
            $estado, 
            $notas,
            $deuda_id
        );
        
        if ($stmt_update->execute()) {
            // Registrar en historial
            $query_historial = "INSERT INTO historial_deudas (deuda_id, accion, detalle, usuario_id) 
                               VALUES (?, 'actualización', 'Se actualizó la información de la deuda', ?)";
            $stmt_historial = $conn->prepare($query_historial);
            
            // Use a default user ID (1) if session user_id is not set
            $usuario_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
            
            $stmt_historial->bind_param("ii", $deuda_id, $usuario_id);
            $stmt_historial->execute();
            
            // Redireccionar a la página de detalles de la deuda
            header("Location: ver_deuda.php?id=$deuda_id&success=deuda_actualizada");
            exit();
        } else {
            $error = "Error al actualizar la deuda: " . $conn->error;
        }
    }
}

// Función para formatear dinero
function formatMoney($amount) {
    return number_format($amount, 0, ',', '.') . ' Gs.';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Deuda - Sistema de Cobranzas</title>
    <style>
        .info-section-title {
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .bg-custom {
            background-color: #343a40;
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-custom text-white">
                            <h5 class="mb-0">Editar Deuda</h5>
                        </div>
                        <div class="card-body">
                            <?php if(isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h5 class="info-section-title">Información del Cliente</h5>
                                        <div class="mb-3">
                                            <label class="form-label">Cliente</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($deuda['cliente_nombre']); ?>" readonly>
                                            <input type="hidden" name="cliente_id" value="<?php echo $deuda['cliente_id']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="info-section-title">Detalles de la Deuda</h5>
                                        <div class="mb-3">
                                            <label for="descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="descripcion" name="descripcion" value="<?php echo htmlspecialchars($deuda['descripcion']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="monto" class="form-label">Monto Original <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="monto" name="monto" value="<?php echo $deuda['monto']; ?>" required>
                                                <span class="input-group-text">Gs.</span>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="saldo_pendiente" class="form-label">Saldo Pendiente <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="saldo_pendiente" name="saldo_pendiente" value="<?php echo $deuda['saldo_pendiente']; ?>" required>
                                                <span class="input-group-text">Gs.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h5 class="info-section-title">Fechas</h5>
                                        <div class="mb-3">
                                            <label for="fecha_emision" class="form-label">Fecha de Emisión <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="fecha_emision" name="fecha_emision" value="<?php echo $deuda['fecha_emision']; ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" value="<?php echo $deuda['fecha_vencimiento']; ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h5 class="info-section-title">Política de Interés y Estado</h5>
                                        <div class="mb-3">
                                            <label for="politica_id" class="form-label">Política de Interés <span class="text-danger">*</span></label>
                                            <select class="form-select" id="politica_id" name="politica_id" required>
                                                <?php while($politica = $result_politicas->fetch_assoc()): ?>
                                                <option value="<?php echo $politica['id']; ?>" <?php echo ($politica['id'] == $deuda['politica_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($politica['nombre']); ?> (<?php echo $politica['tasa']; ?>%)
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                            <select class="form-select" id="estado" name="estado" required>
                                                <option value="pendiente" <?php echo ($deuda['estado'] == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                                <option value="vencido" <?php echo ($deuda['estado'] == 'vencido') ? 'selected' : ''; ?>>Vencido</option>
                                                <option value="pagado" <?php echo ($deuda['estado'] == 'pagado') ? 'selected' : ''; ?>>Pagado</option>
                                                <option value="cancelado" <?php echo ($deuda['estado'] == 'cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <h5 class="info-section-title">Notas Adicionales</h5>
                                    <div class="mb-3">
                                        <label for="notas" class="form-label">Notas</label>
                                        <textarea class="form-control" id="notas" name="notas" rows="4"><?php echo htmlspecialchars($deuda['notas']); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Guardar Cambios
                                    </button>
                                    <a href="ver_deuda.php?id=<?php echo $deuda_id; ?>" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Validación del formulario
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                const monto = parseFloat(document.getElementById('monto').value);
                const saldoPendiente = parseFloat(document.getElementById('saldo_pendiente').value);
                const fechaEmision = new Date(document.getElementById('fecha_emision').value);
                const fechaVencimiento = new Date(document.getElementById('fecha_vencimiento').value);
                
                let hasError = false;
                
                if (monto <= 0) {
                    alert('El monto debe ser mayor que cero.');
                    hasError = true;
                }
                
                if (saldoPendiente < 0) {
                    alert('El saldo pendiente no puede ser negativo.');
                    hasError = true;
                }
                
                if (fechaVencimiento < fechaEmision) {
                    alert('La fecha de vencimiento no puede ser anterior a la fecha de emisión.');
                    hasError = true;
                }
                
                if (hasError) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>