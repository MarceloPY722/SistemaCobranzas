<?php
require_once '../../inc/auth.php';
require_once '../cnx.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../index.php');
    exit;
}

$mensaje = '';
$tipo_mensaje = '';
$deuda_id = isset($_GET['deuda_id']) ? intval($_GET['deuda_id']) : null;
$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deuda_id = isset($_POST['deuda_id']) ? intval($_POST['deuda_id']) : 0;
    $monto_pagado = isset($_POST['monto_pagado']) ? str_replace(['.', ','], ['', '.'], $_POST['monto_pagado']) : 0;
    $fecha_pago = isset($_POST['fecha_pago']) ? $_POST['fecha_pago'] : date('Y-m-d');
    $metodo_pago = isset($_POST['metodo_pago']) ? $_POST['metodo_pago'] : '';
    $notas = isset($_POST['notas']) ? $_POST['notas'] : '';
    
    if ($deuda_id <= 0 || $monto_pagado <= 0) {
        $mensaje = 'Por favor, complete todos los campos requeridos.';
        $tipo_mensaje = 'danger';
    } else {
        $comprobante = null;
        if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            $filename = $_FILES['comprobante']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $upload_dir = '../../../uploads/comprobantes/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $new_filename = uniqid() . '.' . $ext;
                $destination = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $destination)) {
                    $comprobante = $new_filename;
                } else {
                    $mensaje = 'Error al subir el comprobante.';
                    $tipo_mensaje = 'danger';
                }
            } else {
                $mensaje = 'Tipo de archivo no permitido. Solo se permiten JPG, JPEG, PNG y PDF.';
                $tipo_mensaje = 'danger';
            }
        }
        
        if (empty($mensaje)) {
            $query = "INSERT INTO pagos (deuda_id, monto_pagado, fecha_pago, metodo_pago, comprobante) 
                      VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("idsss", $deuda_id, $monto_pagado, $fecha_pago, $metodo_pago, $comprobante);
            
            if ($stmt->execute()) {
                $query_update = "UPDATE deudas SET 
                                saldo_pendiente = GREATEST(0, saldo_pendiente - ?),
                                estado = CASE WHEN saldo_pendiente - ? <= 0 THEN 'Pagada' ELSE estado END
                                WHERE id = ?";
                
                $stmt_update = $conn->prepare($query_update);
                $stmt_update->bind_param("ddi", $monto_pagado, $monto_pagado, $deuda_id);
                
                if ($stmt_update->execute()) {
                    $mensaje = 'Pago registrado correctamente.';
                    $tipo_mensaje = 'success';
                
                    header("refresh:2;url=historial_pagos.php");
                } else {
                    $mensaje = 'Error al actualizar el saldo de la deuda: ' . $stmt_update->error;
                    $tipo_mensaje = 'danger';
                }
            } else {
                $mensaje = 'Error al registrar el pago: ' . $stmt->error;
                $tipo_mensaje = 'danger';
            }
        }
    }
}

$query_deudas = "SELECT d.id, d.descripcion, d.monto as monto_total, d.saldo_pendiente as saldo, c.id as cliente_id, c.nombre as cliente_nombre 
                FROM deudas d 
                JOIN clientes c ON d.cliente_id = c.id 
                WHERE d.estado != 'Pagada'";

if ($cliente_id) {
    $query_deudas .= " AND c.id = " . intval($cliente_id);
}

$query_deudas .= " ORDER BY c.nombre ASC, d.fecha_vencimiento ASC";
$result_deudas = $conn->query($query_deudas);

$deuda_info = null;
if ($deuda_id) {
    $query_deuda = "SELECT d.*, d.monto as monto_total, c.nombre as cliente_nombre 
                   FROM deudas d 
                   JOIN clientes c ON d.cliente_id = c.id 
                   WHERE d.id = ?";
    
    $stmt_deuda = $conn->prepare($query_deuda);
    $stmt_deuda->bind_param("i", $deuda_id);
    $stmt_deuda->execute();
    $result_deuda = $stmt_deuda->get_result();
    
    if ($result_deuda->num_rows > 0) {
        $deuda_info = $result_deuda->fetch_assoc();
    }
}

function formatMoney($amount) {
    return number_format($amount, 0, ',', '.') . ' Gs.';
}

include '../../inc/header.php';
include '../../inc/sidebar.php';
?>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../../index.php"> <i class="bi bi-house"> Inicio</i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Registrar Pago</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-custom text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-cash-coin me-2"></i>
                            Registrar Nuevo Pago
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data" id="formPago">
                          
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label for="deuda_id" class="form-label">Seleccione la deuda a pagar <span class="text-danger">*</span></label>
                                    <select class="form-select" id="deuda_id" name="deuda_id" required>
                                        <option value="">-- Seleccione una deuda --</option>
                                        <?php while ($deuda = $result_deudas->fetch_assoc()): ?>
                                            <option value="<?php echo $deuda['id']; ?>" 
                                                    data-saldo="<?php echo $deuda['saldo']; ?>"
                                                    data-cliente="<?php echo htmlspecialchars($deuda['cliente_nombre']); ?>"
                                                    <?php echo ($deuda_id == $deuda['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($deuda['cliente_nombre'] . ' - ' . $deuda['descripcion'] . ' - Saldo: ' . formatMoney($deuda['saldo'])); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-4" id="deudaInfo" style="display: <?php echo $deuda_info ? 'block' : 'none'; ?>;">
                                <div class="col-md-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Cliente:</strong> <span id="clienteNombre"><?php echo $deuda_info ? htmlspecialchars($deuda_info['cliente_nombre']) : ''; ?></span></p>
                                                    <p class="mb-1"><strong>Descripción:</strong> <span id="deudaDescripcion"><?php echo $deuda_info ? htmlspecialchars($deuda_info['descripcion']) : ''; ?></span></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Monto Total:</strong> <span id="montoTotal"><?php echo $deuda_info ? formatMoney($deuda_info['monto_total']) : ''; ?></span></p>
                                                    <p class="mb-1"><strong>Saldo Pendiente:</strong> <span id="saldoPendiente" class="text-danger"><?php echo $deuda_info ? formatMoney($deuda_info['saldo']) : ''; ?></span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="monto_pagado" class="form-label">Monto a Pagar <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Gs.</span>
                                        <input type="text" class="form-control" id="monto_pagado" name="monto_pagado" required 
                                               placeholder="Ingrese el monto" value="<?php echo $deuda_info ? number_format($deuda_info['saldo'], 0, ',', '.') : ''; ?>">
                                    </div>
                                    <div class="form-text" id="montoHelp">El monto no puede ser mayor al saldo pendiente.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="fecha_pago" class="form-label">Fecha de Pago</label>
                                    <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="metodo_pago" class="form-label">Método de Pago</label>
                                    <select class="form-select" id="metodo_pago" name="metodo_pago">
                                        <option value="Efectivo">Efectivo</option>
                                        <option value="Transferencia">Transferencia Bancaria</option>
                                        <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                                        <option value="Tarjeta de Débito">Tarjeta de Débito</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="comprobante" class="form-label">Comprobante de Pago</label>
                                    <input type="file" class="form-control" id="comprobante" name="comprobante">
                                    <div class="form-text">Formatos permitidos: JPG, JPEG, PNG, PDF. Máximo 2MB.</div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notas" class="form-label">Notas</label>
                                <textarea class="form-control" id="notas" name="notas" rows="3" placeholder="Información adicional sobre el pago"></textarea>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="historial_pagos.php" class="btn btn-outline-secondary me-md-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary" id="btnRegistrarPago">
                                    <i class="bi bi-check-circle me-1"></i> Registrar Pago
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const montoInput = document.getElementById('monto_pagado');
    
    montoInput.addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, '');
        
        if (value) {
            value = parseInt(value, 10).toLocaleString('es-PY');
        }
        
        this.value = value;
    });
    
    const deudaSelect = document.getElementById('deuda_id');
    const deudaInfo = document.getElementById('deudaInfo');
    const clienteNombre = document.getElementById('clienteNombre');
    const deudaDescripcion = document.getElementById('deudaDescripcion');
    const montoTotal = document.getElementById('montoTotal');
    const saldoPendiente = document.getElementById('saldoPendiente');
    
    deudaSelect.addEventListener('change', function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            const saldo = selectedOption.dataset.saldo;
            const cliente = selectedOption.dataset.cliente;
            const descripcion = selectedOption.text.split(' - ')[1];
            
            clienteNombre.textContent = cliente;
            deudaDescripcion.textContent = descripcion;
            montoTotal.textContent = 'Calculando...'; 
            saldoPendiente.textContent = new Intl.NumberFormat('es-PY', {
                style: 'currency',
                currency: 'PYG',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(saldo).replace('PYG', 'Gs.');
            
            montoInput.value = new Intl.NumberFormat('es-PY').format(saldo);
            
            deudaInfo.style.display = 'block';
        } else {
           
            deudaInfo.style.display = 'none';
            montoInput.value = '';
        }
    });
    
    const formPago = document.getElementById('formPago');
    
    formPago.addEventListener('submit', function(e) {
        const deudaId = deudaSelect.value;
        
        if (!deudaId) {
            e.preventDefault();
            alert('Por favor, seleccione una deuda.');
            return;
        }
        
        const selectedOption = deudaSelect.options[deudaSelect.selectedIndex];
        const saldo = parseFloat(selectedOption.dataset.saldo);
        
        const montoIngresado = parseFloat(montoInput.value.replace(/\./g, '').replace(',', '.'));
        
        if (isNaN(montoIngresado) || montoIngresado <= 0) {
            e.preventDefault();
            alert('Por favor, ingrese un monto válido mayor a cero.');
            return;
        }
        
        if (montoIngresado > saldo) {
            e.preventDefault();
            alert('El monto a pagar no puede ser mayor al saldo pendiente.');
            return;
        }
    });
    
   
    if (deudaSelect.value) {
        const event = new Event('change');
        deudaSelect.dispatchEvent(event);
    }
});
</script>

<style>
@media print {
    .sidebar, .navbar, .breadcrumb, .btn-group, .collapse, button, .actions-column, .modal {
        display: none !important;
    }
    
    .content-wrapper {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
}

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

body.dark-mode .card.bg-light {
    background-color: #2c2c2c !important;
}

body.dark-mode .card.bg-light .card-body {
    color: #ffffff; 
}

body.dark-mode .text-muted,
body.dark-mode .form-text {
    color: #ffffff !important; 
}

body.dark-mode strong {
    color: #ffffff;
}

body.dark-mode .btn-outline-secondary {
    color: #ffffff; 
    border-color: #adb5bd;
}

body.dark-mode .alert-success {
    background-color: #0f5132;
    color: #ffffff; 
    border-color: #0f5132;
}

body.dark-mode .alert-danger {
    background-color: #842029;
    color: #ffffff; 
    border-color: #842029;
}

body.dark-mode a {
    color: #8bb9fe; 
}

body.dark-mode .breadcrumb {
    background-color: #1e1e1e;
}

body.dark-mode .breadcrumb-item a {
    color: #8bb9fe; 
}

body.dark-mode .breadcrumb-item.active {
    color: #ffffff;
}

body.dark-mode label {
    color: #ffffff;
}

body.dark-mode option {
    background-color: #2c2c2c;
    color: #ffffff;
}

body.dark-mode #clienteNombre,
body.dark-mode #deudaDescripcion,
body.dark-mode #montoTotal,
body.dark-mode #saldoPendiente {
    color: #ffffff !important; 
}

body.dark-mode .text-danger {
    color: #ff6b6b !important; 
}

body.dark-mode .btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

body.dark-mode .btn-outline-secondary:hover {
    background-color: #4d5154;
    color: #ffffff;
}

body.dark-mode select option {
    background-color: #2c2c2c;
}

body.dark-mode input[type="file"]::file-selector-button {
    background-color: #4d5154;
    color: #ffffff;
    border-color: #6c757d;
}

body.dark-mode ::placeholder {
    color: #adb5bd !important;
    opacity: 1;
}
</style>
</div>