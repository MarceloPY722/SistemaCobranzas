<?php
session_start();
require_once '../cnx.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../index.php');
    exit;
}

$cliente_id = null;
if (isset($_GET['cliente_id']) && is_numeric($_GET['cliente_id'])) {
    $cliente_id = $_GET['cliente_id'];
    
    // Verificar que el cliente exista
    $check_cliente = "SELECT id, nombre FROM clientes WHERE id = ?";
    $stmt_check = $conn->prepare($check_cliente);
    $stmt_check->bind_param("i", $cliente_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows === 0) {
        header('Location: ver_clientes.php?error=cliente_no_encontrado');
        exit();
    }
    
    $cliente_data = $result_check->fetch_assoc();
}

// Obtener la lista de clientes para el selector (solo si no hay cliente_id)
if (!$cliente_id) {
    $query_clientes = "SELECT id, nombre, identificacion FROM clientes ORDER BY nombre ASC";
    $result_clientes = $conn->query($query_clientes);
    $clientes = [];
    while ($row = $result_clientes->fetch_assoc()) {
        $clientes[] = $row;
    }
}

// Obtener la lista de políticas de interés activas
$query_politicas = "SELECT id, nombre, tipo, tasa FROM politicas_interes WHERE activa = 1 ORDER BY nombre ASC";
$result_politicas = $conn->query($query_politicas);
$politicas = [];
while ($row = $result_politicas->fetch_assoc()) {
    $politicas[] = $row;
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar entradas
    $post_cliente_id = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
    $politica_interes_id = filter_input(INPUT_POST, 'politica_interes_id', FILTER_VALIDATE_INT);
    $monto = filter_input(INPUT_POST, 'monto', FILTER_VALIDATE_FLOAT);
    $fecha_emision = filter_input(INPUT_POST, 'fecha_emision', FILTER_SANITIZE_SPECIAL_CHARS);
    $fecha_vencimiento = filter_input(INPUT_POST, 'fecha_vencimiento', FILTER_SANITIZE_SPECIAL_CHARS);
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS);
    $notas = filter_input(INPUT_POST, 'notas', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Si tenemos un cliente_id por GET, usamos ese, sino el del POST
    if ($cliente_id) {
        $final_cliente_id = $cliente_id;
    } else {
        $final_cliente_id = $post_cliente_id;
    }
    
    // Validaciones básicas
    $errores = [];
    
    if (!$final_cliente_id) {
        $errores[] = "Debe seleccionar un cliente válido";
    }
    
    if (!$politica_interes_id) {
        $errores[] = "Debe seleccionar una política de interés válida";
    }
    
    if (!$monto || $monto <= 0) {
        $errores[] = "El monto debe ser un valor positivo";
    }
    
    if (!$fecha_emision) {
        $errores[] = "La fecha de emisión es requerida";
    }
    
    if (!$fecha_vencimiento) {
        $errores[] = "La fecha de vencimiento es requerida";
    }
    
    // Si no hay errores, proceder a guardar
    if (empty($errores)) {
        try {
            // El saldo pendiente inicialmente es igual al monto total
            $saldo_pendiente = $monto;
            
            // Insertar la deuda
            $stmt = $conn->prepare("INSERT INTO deudas (cliente_id, politica_interes_id, monto, fecha_emision, saldo_pendiente, descripcion, fecha_vencimiento, notas) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            // Corregir los tipos de datos para las fechas
            $stmt->bind_param("iidsdsss", $final_cliente_id, $politica_interes_id, $monto, $fecha_emision, $saldo_pendiente, $descripcion, $fecha_vencimiento, $notas);
            
            $stmt->execute();
            $deuda_id = $conn->insert_id;
            
            // Registrar en el historial
            $usuario_id = $_SESSION['user_id'];
            $accion = 'creación';
            $detalle = "Creación de nueva deuda por monto $" . number_format($monto, 2);
            
            $stmt_hist = $conn->prepare("INSERT INTO historial_deudas (deuda_id, usuario_id, accion, detalle) 
                          VALUES (?, ?, ?, ?)");
            
            $stmt_hist->bind_param("iiss", $deuda_id, $usuario_id, $accion, $detalle);
            
            $stmt_hist->execute();
            
            // Mensaje de éxito
            $mensaje = "Deuda registrada correctamente con ID: $deuda_id";
            
            // Redireccionar a la página del cliente
            echo "<script>window.location.href = 'cliente_datos.php?id=" . $final_cliente_id . "&success=" . urlencode($mensaje) . "';</script>";
            exit;
            
        } catch (Exception $e) {
            $error = "Error al registrar la deuda: " . $e->getMessage();
        }
    }
}

// Incluir el sidebar después de procesar la lógica para evitar problemas de headers
include '../../../admin/include/sidebar.php';
?>

<!-- Contenido principal -->
<div class="content-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><?php echo $cliente_id ? 'Registrar Nueva Deuda para ' . htmlspecialchars($cliente_data['nombre']) : 'Registrar Nueva Deuda'; ?></h4>
                        <a href="<?php echo $cliente_id ? 'cliente_datos.php?id=' . $cliente_id : 'ver_clientes.php'; ?>" class="btn btn-light">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errores)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    <?php foreach ($errores as $err): ?>
                                        <li><?php echo $err; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <?php if (!$cliente_id): ?>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="cliente_id" class="form-label required">Cliente</label>
                                    <select name="cliente_id" id="cliente_id" class="form-select" required>
                                        <option value="">Seleccione un cliente</option>
                                        <?php foreach ($clientes as $cliente): ?>
                                            <option value="<?php echo $cliente['id']; ?>" <?php echo (isset($_POST['cliente_id']) && $_POST['cliente_id'] == $cliente['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cliente['nombre'] . ' (' . $cliente['identificacion'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Por favor seleccione un cliente</div>
                                </div>
                            </div>
                            <?php else: ?>
                                <input type="hidden" name="cliente_id" value="<?php echo $cliente_id; ?>">
                            <?php endif; ?>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="politica_interes_id" class="form-label required">Política de Interés</label>
                                    <select name="politica_interes_id" id="politica_interes_id" class="form-select" required>
                                        <option value="">Seleccione una política</option>
                                        <?php foreach ($politicas as $politica): ?>
                                            <option value="<?php echo $politica['id']; ?>" <?php echo (isset($_POST['politica_interes_id']) && $_POST['politica_interes_id'] == $politica['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($politica['nombre'] . ' - ' . $politica['tipo'] . ' (' . $politica['tasa'] . '%)'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Por favor seleccione una política de interés</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="monto" class="form-label required">Monto Total (G$)</label>
                                    <input type="number" name="monto" id="monto" class="form-control" step="0.01" min="0.01" required value="<?php echo isset($_POST['monto']) ? htmlspecialchars($_POST['monto']) : ''; ?>">
                                    <div class="invalid-feedback">Por favor ingrese un monto válido</div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="descripcion" class="form-label required">Descripción de la Deuda</label>
                                    <input type="text" name="descripcion" id="descripcion" class="form-control" required value="<?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?>">
                                    <div class="invalid-feedback">Por favor ingrese una descripción</div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="fecha_emision" class="form-label required">Fecha de Emisión</label>
                                    <input type="date" name="fecha_emision" id="fecha_emision" class="form-control date-picker" required value="<?php echo isset($_POST['fecha_emision']) ? htmlspecialchars($_POST['fecha_emision']) : date('Y-m-d'); ?>">
                                    <div class="invalid-feedback">Por favor seleccione una fecha de emisión</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="fecha_vencimiento" class="form-label required">Fecha de Vencimiento</label>
                                    <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control date-picker" required value="<?php echo isset($_POST['fecha_vencimiento']) ? htmlspecialchars($_POST['fecha_vencimiento']) : date('Y-m-d', strtotime('+30 days')); ?>">
                                    <div class="invalid-feedback">Por favor seleccione una fecha de vencimiento</div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notas" class="form-label">Notas Adicionales</label>
                                <textarea name="notas" id="notas" class="form-control" rows="3"><?php echo isset($_POST['notas']) ? htmlspecialchars($_POST['notas']) : ''; ?></textarea>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="<?php echo $cliente_id ? 'cliente_datos.php?id=' . $cliente_id : 'ver_clientes.php'; ?>" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Registrar Deuda
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
    /* Estilos generales */
    .content-wrapper {
        margin-left: 250px;
        padding: 20px;
    }
    body {
        background-color: #f8f9fa;
        color: #212529;
    }
    .bg-custom {
        background-color: #121a35;
    }
    .required::after {
        content: ' *';
        color: #f00;
    }
    .card {
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
    }
    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
    .shadow {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Estilos para modo oscuro */
    body.dark-mode {
        background-color: #212529;
        color: #fff;
    }
    body.dark-mode .list-group-item {
        background-color: #2a3356;
        color: #fff;
        border-color: #3a4366;
    }
    
    body.dark-mode .card {
        background-color: #2a3356;
        border-color: #3a4366;
    }
    
    body.dark-mode .card-text {
        color: #fff;
    }
    
    body.dark-mode .text-muted {
        color: #adb5bd !important;
    }
    
    body.dark-mode .form-control, 
    body.dark-mode .form-select {
        background-color: #2a3356;
        color: #fff;
        border-color: #3a4366;
    }
    
    body.dark-mode .form-control:focus, 
    body.dark-mode .form-select:focus {
        background-color: #3a4366;
        color: #fff;
        border-color: #4a5376;
        box-shadow: 0 0 0 0.25rem rgba(118, 74, 241, 0.25);
    }
    
    /* Estilos para formularios en modo claro */
    .form-control, .form-select {
        background-color: #fff;
        color: #212529;
        border: 1px solid #ced4da;
    }
    .form-control:focus, .form-select:focus {
        background-color: #fff;
        color: #212529;
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    .btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }
    
    /* Estilos para campos requeridos */
    .form-label.required::after {
        content: ' *';
        color: #dc3545;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar selectores de fecha
        flatpickr('.date-picker', {
            dateFormat: 'Y-m-d',
            allowInput: true
        });
        
        // Validación de fechas
        document.getElementById('fecha_vencimiento').addEventListener('change', function() {
            const fechaEmision = new Date(document.getElementById('fecha_emision').value);
            const fechaVencimiento = new Date(this.value);
            
            if (fechaVencimiento < fechaEmision) {
                alert('La fecha de vencimiento no puede ser anterior a la fecha de emisión');
                this.value = document.getElementById('fecha_emision').value;
            }
        });
        
        document.getElementById('fecha_emision').addEventListener('change', function() {
            const fechaEmision = new Date(this.value);
            const fechaVencimientoEl = document.getElementById('fecha_vencimiento');
            const fechaVencimiento = new Date(fechaVencimientoEl.value);
            
            if (fechaVencimiento < fechaEmision) {
                fechaVencimientoEl.value = this.value;
            }
        });
        
        // Validación del formulario con Bootstrap
        const forms = document.querySelectorAll('.needs-validation');
        
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    });
</script>