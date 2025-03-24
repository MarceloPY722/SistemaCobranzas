<?php
// Start with PHP code - no output before headers
require_once '../../cnx.php';

// Verificar si se proporcionó un ID de deuda
if (!isset($_GET['deuda_id']) || !is_numeric($_GET['deuda_id'])) {
    header('Location: ../../ver_clientes.php?error=id_invalido');
    exit();
}

$deuda_id = $_GET['deuda_id'];

// Consulta para obtener los datos de la deuda con información del cliente
$query = "SELECT d.*, c.nombre as cliente_nombre, c.id as cliente_id, c.identificacion as cliente_identificacion,
          p.nombre as politica_nombre, p.tasa, p.tipo as politica_tipo, p.periodo as politica_periodo
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

// Verificar si la deuda ya está pagada
if ($deuda['estado'] == 'pagado') {
    header('Location: ver_deuda.php?id=' . $deuda_id . '&error=deuda_ya_pagada');
    exit();
}

// Consulta para obtener las cuotas pendientes de la deuda
$query_cuotas = "SELECT * FROM cuotas_deuda WHERE deuda_id = ? AND (estado = 'pendiente' OR estado = 'vencido') ORDER BY numero_cuota ASC";
$stmt_cuotas = $conn->prepare($query_cuotas);
$stmt_cuotas->bind_param("i", $deuda_id);
$stmt_cuotas->execute();
$result_cuotas = $stmt_cuotas->get_result();

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar y sanitizar los datos del formulario
    $monto = filter_input(INPUT_POST, 'monto', FILTER_VALIDATE_FLOAT);
    // Replace deprecated FILTER_SANITIZE_STRING with htmlspecialchars
    $fecha_pago = htmlspecialchars(trim($_POST['fecha_pago'] ?? ''), ENT_QUOTES, 'UTF-8');
    $metodo_pago = htmlspecialchars(trim($_POST['metodo_pago'] ?? ''), ENT_QUOTES, 'UTF-8');
    $referencia = htmlspecialchars(trim($_POST['referencia'] ?? ''), ENT_QUOTES, 'UTF-8');
    $notas = htmlspecialchars(trim($_POST['notas'] ?? ''), ENT_QUOTES, 'UTF-8');
    $cuotas_pagadas = isset($_POST['cuotas']) ? $_POST['cuotas'] : [];
    
    // Validar que el monto sea mayor que cero
    if (!$monto || $monto <= 0) {
        $error = "El monto debe ser mayor que cero.";
    } else {
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // Registrar el pago - ajustado a la estructura real de la tabla
            $query_pago = "INSERT INTO pagos (deuda_id, monto_pagado, metodo_pago, fecha_pago, created_at) 
                          VALUES (?, ?, ?, ?, NOW())";
            $stmt_pago = $conn->prepare($query_pago);
            $stmt_pago->bind_param("idss", $deuda_id, $monto, $metodo_pago, $fecha_pago);
            $stmt_pago->execute();
            $pago_id = $conn->insert_id;
            
            // Make sure usuario_id is defined and exists in the usuarios table
            // Use a valid user ID that definitely exists in the database
            $usuario_id = 17; // Using the Administrador user ID
            
            // Actualizar el saldo pendiente de la deuda
            $nuevo_saldo = $deuda['saldo_pendiente'] - $monto;
            if ($nuevo_saldo <= 0) {
                // Si el saldo es cero o negativo, marcar la deuda como pagada
                $query_update_deuda = "UPDATE deudas SET saldo_pendiente = 0, estado = 'pagado' WHERE id = ?";
                $stmt_update_deuda = $conn->prepare($query_update_deuda);
                $stmt_update_deuda->bind_param("i", $deuda_id);
            } else {
                // Si aún queda saldo, actualizar solo el monto
                $query_update_deuda = "UPDATE deudas SET saldo_pendiente = ? WHERE id = ?";
                $stmt_update_deuda = $conn->prepare($query_update_deuda);
                $stmt_update_deuda->bind_param("di", $nuevo_saldo, $deuda_id);
            }
            $stmt_update_deuda->execute();
            
            // Actualizar las cuotas seleccionadas
            if (!empty($cuotas_pagadas)) {
                // Remove updated_at from the query if it doesn't exist in the table
                $query_update_cuota = "UPDATE cuotas_deuda SET estado = 'pagado' WHERE id = ?";
                $stmt_update_cuota = $conn->prepare($query_update_cuota);
                
                foreach ($cuotas_pagadas as $cuota_id) {
                    $stmt_update_cuota->bind_param("i", $cuota_id);
                    $stmt_update_cuota->execute();
                }
            }
            
            // Registrar en el historial
            $detalle = "Pago de " . number_format($monto, 0, ',', '.') . " Gs. mediante " . $metodo_pago;
            if (!empty($referencia)) {
                $detalle .= " (Ref: " . $referencia . ")";
            }
            
            $query_historial = "INSERT INTO historial_deudas (deuda_id, accion, detalle, usuario_id, created_at) 
                              VALUES (?, 'pago', ?, ?, NOW())";
            $stmt_historial = $conn->prepare($query_historial);
            $stmt_historial->bind_param("isi", $deuda_id, $detalle, $usuario_id);
            $stmt_historial->execute();
            
            // Confirmar la transacción
            $conn->commit();
            
            // Redireccionar a la página de detalles de la deuda
            header('Location: ver_deuda.php?id=' . $deuda_id . '&success=pago_registrado');
            exit();
            
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            $conn->rollback();
            $error = "Error al registrar el pago: " . $e->getMessage();
        }
    }
}

// Only include sidebar after all potential redirects
include '../../../../admin/include/sidebar.php';

// Función para formatear dinero
function formatMoney($amount) {
    return number_format($amount, 0, ',', '.') . ' Gs.';
}
?>

<!-- HTML content starts here -->
<div class="content-wrapper">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Registrar Pago</h4>
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
                        
                        <!-- Add form opening tag with method and action -->
                        <form method="POST" action="">
                            <!-- Add payment details section -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h5 class="info-section-title">Información de la Deuda</h5>
                                    <ul class="list-group">
                                        <!-- Existing debt information -->
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Cliente:</strong>
                                        <span><?php echo htmlspecialchars($deuda['cliente_nombre']); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Descripción:</strong>
                                        <span><?php echo htmlspecialchars($deuda['descripcion']); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Monto Original:</strong>
                                        <span><?php echo formatMoney($deuda['monto']); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <strong>Saldo Pendiente:</strong>
                                        <span class="text-danger"><?php echo formatMoney($deuda['saldo_pendiente']); ?></span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <!-- Add payment fields -->
                                <h5 class="info-section-title">Detalles del Pago</h5>
                                <div class="mb-3">
                                    <label for="monto" class="form-label">Monto a Pagar</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="monto" name="monto" required 
                                            value="<?php echo isset($_POST['monto']) ? htmlspecialchars($_POST['monto']) : ''; ?>">
                                        <span class="input-group-text">Gs.</span>
                                    </div>
                                </div>
                                
                                <!-- Add auto-select button -->
                                <div class="mb-3">
                                    <button type="button" class="btn btn-outline-primary" id="autoSelectCuotas">
                                        <i class="bi bi-check-all"></i> Seleccionar cuotas automáticamente
                                    </button>
                                </div>
                                <div class="mb-3">
                                    <label for="fecha_pago" class="form-label">Fecha de Pago</label>
                                    <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" required 
                                        value="<?php echo isset($_POST['fecha_pago']) ? htmlspecialchars($_POST['fecha_pago']) : date('Y-m-d'); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="metodo_pago" class="form-label">Método de Pago</label>
                                    <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                        <option value="">Seleccione un método</option>
                                        <option value="Efectivo" <?php echo (isset($_POST['metodo_pago']) && $_POST['metodo_pago'] == 'Efectivo') ? 'selected' : ''; ?>>Efectivo</option>
                                        <option value="Transferencia" <?php echo (isset($_POST['metodo_pago']) && $_POST['metodo_pago'] == 'Transferencia') ? 'selected' : ''; ?>>Transferencia</option>
                                        <option value="Tarjeta" <?php echo (isset($_POST['metodo_pago']) && $_POST['metodo_pago'] == 'Tarjeta') ? 'selected' : ''; ?>>Tarjeta</option>
                                        <option value="Cheque" <?php echo (isset($_POST['metodo_pago']) && $_POST['metodo_pago'] == 'Cheque') ? 'selected' : ''; ?>>Cheque</option>
                                        <option value="Otro" <?php echo (isset($_POST['metodo_pago']) && $_POST['metodo_pago'] == 'Otro') ? 'selected' : ''; ?>>Otro</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="referencia" class="form-label">Referencia (opcional)</label>
                                    <input type="text" class="form-control" id="referencia" name="referencia" 
                                        value="<?php echo isset($_POST['referencia']) ? htmlspecialchars($_POST['referencia']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="info-section-title">Cuotas Pendientes</h5>
                                <?php if($result_cuotas->num_rows > 0): ?>
                                <div class="mb-3">
                                    <label class="form-label">Cuotas a Pagar</label>
                                    <div class="alert alert-info">
                                        Seleccione las cuotas que desea pagar con este monto. Si no selecciona ninguna, 
                                        el pago se aplicará al saldo general de la deuda.
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Seleccionar</th>
                                                    <th>Cuota</th>
                                                    <th>Monto</th>
                                                    <th>Vencimiento</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                // Reiniciar el puntero del resultado
                                                $result_cuotas->data_seek(0);
                                                while($cuota = $result_cuotas->fetch_assoc()): 
                                                    $total_cuota = $cuota['monto_cuota'] + $cuota['interes_acumulado'];
                                                ?>
                                                <tr class="<?php echo ($cuota['estado'] == 'vencido') ? 'table-danger' : ''; ?>">
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="cuotas[]" value="<?php echo $cuota['id']; ?>" id="cuota_<?php echo $cuota['id']; ?>" <?php echo (isset($_POST['cuotas']) && in_array($cuota['id'], $_POST['cuotas'])) ? 'checked' : ''; ?>>
                                                        </div>
                                                    </td>
                                                    <td><?php echo $cuota['numero_cuota']; ?></td>
                                                    <td><?php echo formatMoney($total_cuota); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($cuota['fecha_vencimiento'])); ?></td>
                                                    <td>
                                                        <?php if($cuota['estado'] == 'pendiente'): ?>
                                                            <span class="badge bg-warning text-dark">Pendiente</span>
                                                        <?php elseif($cuota['estado'] == 'vencido'): ?>
                                                            <span class="badge bg-danger">Vencido</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="notas" class="form-label">Notas/Observaciones</label>
                                <textarea class="form-control" id="notas" name="notas" rows="3"><?php echo isset($_POST['notas']) ? htmlspecialchars($_POST['notas']) : ''; ?></textarea>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="ver_deuda.php?id=<?php echo $deuda_id; ?>" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Registrar Pago
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding: 20px 0;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
        padding-left: 30px;
        border-left: 2px solid var(--border-color);
    }
    
    .timeline-date {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 5px;
    }
    
    .timeline-content {
        background-color: var(--card-bg);
        padding: 15px;
        border-radius: 5px;
    }
    
    .info-section-title {
        border-bottom: 2px solid #121a35;
        padding-bottom: 8px;
        margin-bottom: 15px;
    }
    
    .bg-custom {
        background-color: #121a35;
    }
    
    /* Dark mode adaptations */
    body.dark-mode {
        background-color: #121a35;
        color: #e9ecef;
    }
    
    body.dark-mode .info-section-title {
        border-bottom-color: #2a3c70;
    }
    
    body.dark-mode .card,
    body.dark-mode .dark-mode-element {
        background-color: #1e2337;
        border-color: #2a3c70;
        color: #e9ecef;
    }
    
    body.dark-mode .list-group-item {
        background-color: #1e2337;
        border-color: #2a3c70;
        color: #e9ecef;
    }
    
    body.dark-mode .modal-content {
        background-color: #1e2337;
        color: #e9ecef;
    }
    
    body.dark-mode .table {
        color: #e9ecef;
    }
    
    body.dark-mode .table-bordered,
    body.dark-mode .table-bordered th,
    body.dark-mode .table-bordered td {
        border-color: #2a3c70;
    }
    
    body.dark-mode .alert-info {
        background-color: #0d2e45;
        color: #9eeaf9;
        border-color: #0f5885;
    }
    
    body.dark-mode .bg-light {
        background-color: #1e2337 !important;
    }
    
    body.dark-mode .text-dark {
        color: #e9ecef !important;
    }
    
    /* Remove hover effect from tables */
    .card-body .table tr:hover {
        background-color: inherit !important;
    }
    
    body.dark-mode .card-body .table tr:hover {
        background-color: inherit !important;
    }
    
    /* CSS variables for theme consistency */
    :root {
        --border-color: #dee2e6;
        --text-muted: #6c757d;
        --card-bg: #f8f9fa;
    }
    
    body.dark-mode {
        --border-color: #2a3c70;
        --text-muted: #adb5bd;
        --card-bg: #2a3c70;
    }
</style>

<script>
    // Apply theme-specific classes to elements when theme changes
    document.addEventListener('DOMContentLoaded', function() {
        // Check if there's a theme preference stored
        const isDarkMode = localStorage.getItem('darkMode') === 'true';
        
        // Apply dark mode if it's enabled
        if (isDarkMode) {
            document.body.classList.add('dark-mode');
            applyDarkModeStyles();
        }
        
        // Listen for theme changes
        document.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'darkModeButton') {
                setTimeout(function() {
                    const isDarkModeNow = document.body.classList.contains('dark-mode');
                    if (isDarkModeNow) {
                        applyDarkModeStyles();
                    } else {
                        removeDarkModeStyles();
                    }
                }, 100);
            }
        });
    });
    
    function applyDarkModeStyles() {
        // Apply dark mode styles to specific elements
        document.querySelectorAll('.card, .list-group-item, .modal-content, .table').forEach(el => {
            el.classList.add('dark-mode-element');
        });
        
        // Update table styles
        document.querySelectorAll('.table-bordered, .table-bordered th, .table-bordered td').forEach(el => {
            el.style.borderColor = '#2a3c70';
        });
        
        // Update alert styles
        document.querySelectorAll('.alert-info').forEach(el => {
            el.style.backgroundColor = '#0d2e45';
            el.style.color = '#9eeaf9';
            el.style.borderColor = '#0f5885';
        });
    }
    
    function removeDarkModeStyles() {
        // Remove dark mode styles
        document.querySelectorAll('.card, .list-group-item, .modal-content, .table').forEach(el => {
            el.classList.remove('dark-mode-element');
        });
        
        // Reset table styles
        document.querySelectorAll('.table-bordered, .table-bordered th, .table-bordered td').forEach(el => {
            el.style.borderColor = '';
        });
        
        // Reset alert styles
        document.querySelectorAll('.alert-info').forEach(el => {
            el.style.backgroundColor = '';
            el.style.color = '';
            el.style.borderColor = '';
        });
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the auto-select button and add click event
        const autoSelectBtn = document.getElementById('autoSelectCuotas');
        if (autoSelectBtn) {
            autoSelectBtn.addEventListener('click', autoSelectCuotasBasedOnAmount);
        }
        
        // Function to automatically select cuotas based on payment amount
        function autoSelectCuotasBasedOnAmount() {
            // Get the payment amount
            const montoInput = document.getElementById('monto');
            if (!montoInput || !montoInput.value) {
                alert('Por favor, ingrese un monto de pago primero.');
                return;
            }
            
            const montoTotal = parseFloat(montoInput.value);
            if (isNaN(montoTotal) || montoTotal <= 0) {
                alert('Por favor, ingrese un monto válido mayor a cero.');
                return;
            }
            
            // Get all cuota checkboxes
            const cuotaCheckboxes = document.querySelectorAll('input[name="cuotas[]"]');
            if (cuotaCheckboxes.length === 0) {
                return;
            }
            
            // Uncheck all checkboxes first
            cuotaCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Calculate which cuotas can be covered with the amount
            let montoRestante = montoTotal;
            let cuotasSeleccionadas = 0;
            
            for (let i = 0; i < cuotaCheckboxes.length; i++) {
                const checkbox = cuotaCheckboxes[i];
                const row = checkbox.closest('tr');
                
                // Get the cuota amount from the table row (3rd column)
                const montoText = row.cells[2].textContent.trim();
                // Extract numeric value from format like "100.000 Gs."
                const montoCuota = parseFloat(montoText.replace(/\./g, '').replace(/\s+Gs\./g, '').replace(/,/g, '.'));
                
                if (montoRestante >= montoCuota) {
                    checkbox.checked = true;
                    montoRestante -= montoCuota;
                    cuotasSeleccionadas++;
                } else {
                    break;
                }
            }
            
            if (cuotasSeleccionadas > 0) {
                alert(`Se han seleccionado ${cuotasSeleccionadas} cuota(s) automáticamente.`);
            } else {
                alert('El monto ingresado no es suficiente para cubrir ninguna cuota completa.');
            }
        }
    });
</script>