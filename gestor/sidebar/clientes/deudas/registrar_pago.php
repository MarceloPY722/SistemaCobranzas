<?php
// Remove the duplicate session_start() call
// session_start(); - This line should be removed
require_once '../../../inc/auth.php';
require_once '../../cnx.php';

// Verificar si se proporcionó un ID de deuda
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../../ver_clientes.php?error=id_invalido');
    exit();
}

$deuda_id = intval($_GET['id']);
$cuota_id = isset($_GET['cuota']) ? intval($_GET['cuota']) : null;

// Obtener información de la deuda
$query_deuda = "SELECT d.*, c.nombre as cliente_nombre 
                FROM deudas d 
                JOIN clientes c ON d.cliente_id = c.id 
                WHERE d.id = ?";
$stmt_deuda = $conn->prepare($query_deuda);
$stmt_deuda->bind_param("i", $deuda_id);
$stmt_deuda->execute();
$result_deuda = $stmt_deuda->get_result();

if ($result_deuda->num_rows === 0) {
    header('Location: ../../../ver_clientes.php?error=deuda_no_encontrada');
    exit();
}

$deuda = $result_deuda->fetch_assoc();
$cliente_id = $deuda['cliente_id'];

// Si se especificó una cuota, obtener su información
$cuota = null;
if ($cuota_id) {
    $query_cuota = "SELECT * FROM cuotas_deuda WHERE id = ? AND deuda_id = ?";
    $stmt_cuota = $conn->prepare($query_cuota);
    $stmt_cuota->bind_param("ii", $cuota_id, $deuda_id);
    $stmt_cuota->execute();
    $result_cuota = $stmt_cuota->get_result();
    
    if ($result_cuota->num_rows > 0) {
        $cuota = $result_cuota->fetch_assoc();
    }
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $monto_pagado = filter_input(INPUT_POST, 'monto_pagado', FILTER_VALIDATE_FLOAT);
    $fecha_pago = filter_input(INPUT_POST, 'fecha_pago', FILTER_SANITIZE_SPECIAL_CHARS);
    $metodo_pago = filter_input(INPUT_POST, 'metodo_pago', FILTER_SANITIZE_SPECIAL_CHARS);
    $notas = filter_input(INPUT_POST, 'notas', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Validar datos
    $errores = [];
    if (!$monto_pagado || $monto_pagado <= 0) {
        $errores[] = "El monto debe ser un valor positivo";
    }
    
    if (empty($fecha_pago)) {
        $errores[] = "La fecha de pago es requerida";
    }
    
    if (empty($metodo_pago)) {
        $errores[] = "El método de pago es requerido";
    }
    
    // Procesar comprobante si se subió uno
    $comprobante = null;
    if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['comprobante']['tmp_name'];
        $file_name = $_FILES['comprobante']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Verificar la extensión del archivo
        $allowed_exts = ['jpg', 'jpeg', 'png', 'pdf'];
        
        if (!in_array($file_ext, $allowed_exts)) {
            $errores[] = "El formato del comprobante no es válido. Use JPG, JPEG, PNG o PDF.";
        } else {
            // Generar un nombre único para el archivo
            $new_file_name = uniqid() . '.' . $file_ext;
            $upload_path = '../../../../uploads/comprobantes/' . $new_file_name;
            
            // Crear el directorio si no existe
            if (!file_exists('../../../../uploads/comprobantes/')) {
                mkdir('../../../../uploads/comprobantes/', 0777, true);
            }
            
            // Mover el archivo subido al directorio de destino
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $comprobante = $new_file_name;
            } else {
                $errores[] = "Error al subir el comprobante.";
            }
        }
    }
    
    // Si no hay errores, registrar el pago
    if (empty($errores)) {
        $conn->begin_transaction();
        
        try {
            // Insertar el pago
            $query_pago = "INSERT INTO pagos (deuda_id, cuota_id, monto_pagado, fecha_pago, metodo_pago, comprobante, notas, created_by) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_pago = $conn->prepare($query_pago);
            $stmt_pago->bind_param("iidssssi", $deuda_id, $cuota_id, $monto_pagado, $fecha_pago, $metodo_pago, $comprobante, $notas, $_SESSION['user_id']);
            $stmt_pago->execute();
            $pago_id = $conn->insert_id;
            
            // Actualizar el saldo de la deuda
            $nuevo_saldo = $deuda['saldo_pendiente'] - $monto_pagado;
            if ($nuevo_saldo < 0) $nuevo_saldo = 0;
            
            $query_update_deuda = "UPDATE deudas SET 
                                  saldo_pendiente = ?,
                                  fecha_ultimo_pago = ?,
                                  updated_at = NOW() 
                                  WHERE id = ?";
            $stmt_update_deuda = $conn->prepare($query_update_deuda);
            $stmt_update_deuda->bind_param("dsi", $nuevo_saldo, $fecha_pago, $deuda_id);
            $stmt_update_deuda->execute();
            
            // Si se pagó una cuota específica, actualizar su estado
            if ($cuota_id) {
                $estado_cuota = 'pagado';
                $query_update_cuota = "UPDATE cuotas_deuda SET 
                                      estado = ?,
                                      fecha_pago = ?,
                                      updated_at = NOW() 
                                      WHERE id = ?";
                $stmt_update_cuota = $conn->prepare($query_update_cuota);
                $stmt_update_cuota->bind_param("ssi", $estado_cuota, $fecha_pago, $cuota_id);
                $stmt_update_cuota->execute();
            }
            
            // Registrar en el historial
            $accion = "Pago registrado";
            $detalles = "Se registró un pago de " . number_format($monto_pagado, 0, ',', '.') . " Gs. para la deuda #" . $deuda_id;
            if ($cuota_id) {
                $detalles .= ", cuota #" . $cuota_id;
            }
            
            $query_historial = "INSERT INTO historial_deudas (deuda_id, usuario_id, accion, detalles) 
                               VALUES (?, ?, ?, ?)";
            $stmt_historial = $conn->prepare($query_historial);
            $stmt_historial->bind_param("iiss", $deuda_id, $_SESSION['user_id'], $accion, $detalles);
            $stmt_historial->execute();
            
            $conn->commit();
            
            // Redireccionar con mensaje de éxito
            header("Location: ../cliente_datos.php?id=$cliente_id&success=pago_registrado");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error_msg = "Error al registrar el pago: " . $e->getMessage();
        }
    }
}

// Formatear montos en guaraníes
function formatMoney($amount) {
    return number_format($amount, 0, ',', '.') . ' Gs.';
}

include '../../../inc/header.php';
include '../../../inc/sidebar.php';
?>

<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row mb-4">
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../../../index.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="../ver_clientes.php">Clientes</a></li>
                        <li class="breadcrumb-item"><a href="../cliente_datos.php?id=<?php echo $cliente_id; ?>">Detalles del Cliente</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Registrar Pago</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-custom text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-cash-coin me-2"></i>
                            Registrar Pago
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($errores) && !empty($errores)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errores as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_msg)): ?>
                            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                        <?php endif; ?>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold">Información de la Deuda</h6>
                                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($deuda['cliente_nombre']); ?></p>
                                <p><strong>Descripción:</strong> <?php echo htmlspecialchars($deuda['descripcion']); ?></p>
                                <p><strong>Monto Total:</strong> <?php echo formatMoney($deuda['monto']); ?></p>
                                <p><strong>Saldo Pendiente:</strong> <?php echo formatMoney($deuda['saldo_pendiente']); ?></p>
                            </div>
                            <?php if ($cuota): ?>
                            <div class="col-md-6">
                                <h6 class="fw-bold">Información de la Cuota</h6>
                                <p><strong>Número de Cuota:</strong> <?php echo $cuota['numero_cuota']; ?></p>
                                <p><strong>Monto de Cuota:</strong> <?php echo formatMoney($cuota['monto_cuota']); ?></p>
                                <p><strong>Fecha Vencimiento:</strong> <?php echo date('d/m/Y', strtotime($cuota['fecha_vencimiento'])); ?></p>
                                <p><strong>Estado:</strong> 
                                    <span class="badge <?php echo ($cuota['estado'] == 'vencido') ? 'bg-danger' : (($cuota['estado'] == 'pendiente') ? 'bg-warning' : 'bg-success'); ?>">
                                        <?php echo ucfirst($cuota['estado']); ?>
                                    </span>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="monto_pagado" class="form-label">Monto a Pagar</label>
                                        <input type="number" class="form-control" id="monto_pagado" name="monto_pagado" 
                                               value="<?php echo $cuota ? $cuota['monto_cuota'] : $deuda['saldo_pendiente']; ?>" required>
                                        <small class="form-text text-muted">Monto máximo: <?php echo formatMoney($deuda['saldo_pendiente']); ?></small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="fecha_pago" class="form-label">Fecha de Pago</label>
                                        <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" 
                                               value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="metodo_pago" class="form-label">Método de Pago</label>
                                        <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                            <option value="">Seleccione un método</option>
                                            <option value="Efectivo">Efectivo</option>
                                            <option value="Transferencia">Transferencia Bancaria</option>
                                            <option value="Tarjeta">Tarjeta de Crédito/Débito</option>
                                            <option value="Cheque">Cheque</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="comprobante" class="form-label">Comprobante de Pago (opcional)</label>
                                        <input type="file" class="form-control" id="comprobante" name="comprobante">
                                        <small class="form-text text-muted">Formatos permitidos: JPG, JPEG, PNG, PDF</small>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="notas" class="form-label">Notas (opcional)</label>
                                        <textarea class="form-control" id="notas" name="notas" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Registrar Pago
                                </button>
                                <a href="../cliente_datos.php?id=<?php echo $cliente_id; ?>" class="btn btn-secondary ms-2">
                                    <i class="bi bi-x-circle me-2"></i>Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Cerrar conexiones
if (isset($stmt_deuda)) $stmt_deuda->close();
if (isset($stmt_cuota)) $stmt_cuota->close();
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validar que el monto no exceda el saldo pendiente
    const montoInput = document.getElementById('monto_pagado');
    const saldoPendiente = <?php echo $deuda['saldo_pendiente']; ?>;
    
    montoInput.addEventListener('change', function() {
        if (parseFloat(this.value) > saldoPendiente) {
            alert('El monto no puede ser mayor al saldo pendiente.');
            this.value = saldoPendiente;
        }
    });
});
</script>

<style>
/* Dark mode styles to ensure text visibility */
body.dark-mode {
    background-color: #121212;
    color: #ffffff;
}

body.dark-mode .content-wrapper {
    background-color: #121212;
}

body.dark-mode .card {
    background-color: #1e1e1e;
    border-color: #333;
}

body.dark-mode .card-header {
    background-color: #2c2c2c !important;
    color: #fff !important;
}

body.dark-mode .form-control,
body.dark-mode .form-select,
body.dark-mode .input-group-text {
    background-color: #2c2c2c;
    border-color: #444;
    color: #ffffff;
}

body.dark-mode .form-control:focus,
body.dark-mode .form-select:focus {
    background-color: #333;
    color: #fff;
    border-color: #0d6efd;
}

body.dark-mode .form-text,
body.dark-mode small.text-muted {
    color: #adb5bd !important;
}

body.dark-mode label,
body.dark-mode p,
body.dark-mode h6,
body.dark-mode strong {
    color: #ffffff;
}

body.dark-mode .breadcrumb-item a {
    color: #8bb9fe;
}

body.dark-mode .breadcrumb-item.active {
    color: #ffffff;
}

body.dark-mode .btn-secondary {
    background-color: #4d5154;
    border-color: #6c757d;
}

body.dark-mode .badge.bg-warning {
    color: #000000;
}

body.dark-mode select option {
    background-color: #2c2c2c;
    color: #ffffff;
}

body.dark-mode ::placeholder {
    color: #adb5bd !important;
    opacity: 1;
}
</style>