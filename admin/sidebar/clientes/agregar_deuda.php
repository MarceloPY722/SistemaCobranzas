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
$query_politicas = "SELECT id, nombre, tipo, periodo, tasa, tasa_escalonada_json, penalizacion_fija, dias_penalizacion FROM politicas_interes WHERE activa = 1 ORDER BY nombre ASC";
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
    
    // Asegurarse de que la fecha de emisión tenga el formato correcto para MySQL
    $fecha_emision = date('Y-m-d'); // Formato YYYY-MM-DD para MySQL
    
    $dia_vencimiento = filter_input(INPUT_POST, 'dia_vencimiento', FILTER_VALIDATE_INT);
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_SPECIAL_CHARS);
    $notas = filter_input(INPUT_POST, 'notas', FILTER_SANITIZE_SPECIAL_CHARS);
    $cuotas = filter_input(INPUT_POST, 'cuotas', FILTER_VALIDATE_INT);
    
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
    
    // Eliminar la validación de fecha_emision ya que ahora se establece automáticamente
    
    if (!$dia_vencimiento) {
        $errores[] = "El día de vencimiento mensual es requerido";
    }
    
    if (!$cuotas || $cuotas <= 0) {
        $errores[] = "El número de cuotas debe ser un valor positivo";
    }
    
    // Si no hay errores, proceder a guardar
    if (empty($errores)) {
        try {
            // El saldo pendiente inicialmente es igual al monto total
            $saldo_pendiente = $monto;
            
            // Calcular fecha de vencimiento general (última cuota)
            $fecha_emision_obj = new DateTime($fecha_emision);
            $fecha_vencimiento = clone $fecha_emision_obj;
            $fecha_vencimiento->modify('+' . $cuotas . ' months');
            $fecha_vencimiento_str = $fecha_vencimiento->format('Y-m-d');
            
            // Estado inicial de la deuda
            $estado = 'pendiente';
            
            // Inicializar interés acumulado en 0
            $interes_acumulado = 0;
            
            // Insertar la deuda con campos adicionales para el cálculo de interés
            $stmt = $conn->prepare("INSERT INTO deudas (cliente_id, politica_interes_id, monto, cuotas, fecha_emision, 
                                   saldo_pendiente, interes_acumulado, descripcion, fecha_vencimiento, estado, notas) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            // Corregir el tipo de parámetros para que coincida con el número de valores (11 parámetros)
            $stmt->bind_param("iiidsddssss", $final_cliente_id, $politica_interes_id, $monto, $cuotas, $fecha_emision, 
                             $saldo_pendiente, $interes_acumulado, $descripcion, $fecha_vencimiento_str, $estado, $notas);
            
            $stmt->execute();
            $deuda_id = $conn->insert_id;
            
            // Crear las cuotas
            if ($cuotas > 0) {
                $monto_cuota = $monto / $cuotas;
                $fecha_base = new DateTime($fecha_emision);
                
                for ($i = 1; $i <= $cuotas; $i++) {
                    // Calcular fecha de vencimiento para cada cuota usando el día seleccionado
                    $fecha_venc_cuota = clone $fecha_base;
                    $fecha_venc_cuota->modify('+' . $i . ' months');
                    $fecha_venc_cuota->setDate(
                        $fecha_venc_cuota->format('Y'), 
                        $fecha_venc_cuota->format('m'), 
                        $dia_vencimiento
                    );
                    
                    // Ajustar si el día no existe en el mes (ej. 30 de febrero)
                    if ($fecha_venc_cuota->format('d') != $dia_vencimiento) {
                        $fecha_venc_cuota->modify('last day of this month');
                    }
                    
                    $fecha_venc_str = $fecha_venc_cuota->format('Y-m-d');
                    $estado_cuota = 'pendiente';
                    $interes_cuota = 0;
                    
                    // Insertar la cuota con campo para interés acumulado
                    $stmt_cuota = $conn->prepare("INSERT INTO cuotas_deuda (deuda_id, numero_cuota, monto_cuota, 
                                               fecha_vencimiento, estado, interes_acumulado) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_cuota->bind_param("iidssd", $deuda_id, $i, $monto_cuota, $fecha_venc_str, $estado_cuota, $interes_cuota);
                    $stmt_cuota->execute();
                }
            }
            
            // Registrar en el historial
            $usuario_id = $_SESSION['user_id'];
            $accion = 'creación';
            $detalle = "Creación de nueva deuda por monto " . number_format($monto, 2, ',', '.') . " Gs.";
            
            $stmt_hist = $conn->prepare("INSERT INTO historial_deudas (deuda_id, usuario_id, accion, detalle) 
                          VALUES (?, ?, ?, ?)");
            
            $stmt_hist->bind_param("iiss", $deuda_id, $usuario_id, $accion, $detalle);
            $stmt_hist->execute();
            
            // Redireccionar a la página del cliente
            header("Location: cliente_datos.php?id=" . $final_cliente_id . "&success=deuda_registrada");
            exit;
            
        } catch (Exception $e) {
            $error = "Error al registrar la deuda: " . $e->getMessage();
        }
    }
}

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
                    <div class="card-body form-container">
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
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label for="cliente_id" class="form-label required">Cliente</label>
                                    <select name="cliente_id" id="cliente_id" class="form-select form-select-lg" required>
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
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="politica_interes_id" class="form-label required">Política de Interés</label>
                                    <select name="politica_interes_id" id="politica_interes_id" class="form-select form-select-lg" required>
                                        <option value="">Seleccione una política</option>
                                        <?php foreach ($politicas as $politica): ?>
                                            <?php 
                                                $descripcion_tasa = '';
                                                switch ($politica['tipo']) {
                                                    case 'simple':
                                                        $descripcion_tasa = $politica['tasa'] . '% ' . $politica['periodo'];
                                                        break;
                                                    case 'compuesto':
                                                        $descripcion_tasa = $politica['tasa'] . '% ' . $politica['periodo'] . ' compuesto';
                                                        break;
                                                    case 'escalonado':
                                                        $descripcion_tasa = 'Escalonado ' . $politica['periodo'];
                                                        break;
                                                }
                                            ?>
                                            <option value="<?php echo $politica['id']; ?>" <?php echo (isset($_POST['politica_interes_id']) && $_POST['politica_interes_id'] == $politica['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($politica['nombre'] . ' - ' . $descripcion_tasa); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Por favor seleccione una política de interés</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="monto" class="form-label required">Monto Total (Gs.)</label>
                                    <input type="number" name="monto" id="monto" class="form-control form-control-lg" step="1" min="1" required value="<?php echo isset($_POST['monto']) ? htmlspecialchars($_POST['monto']) : ''; ?>">
                                    <div class="invalid-feedback">Por favor ingrese un monto válido</div>
                                </div>
                            </div>
                            
                            <div id="politica_info" class="row mb-4" style="display: none;">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <h5 class="politica-nombre"></h5>
                                        <div class="politica-descripcion"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="cuotas" class="form-label required">Número de Cuotas</label>
                                    <input type="number" name="cuotas" id="cuotas" class="form-control form-control-lg" min="1" value="<?php echo isset($_POST['cuotas']) ? htmlspecialchars($_POST['cuotas']) : '1'; ?>" required>
                                    <div class="invalid-feedback">Por favor ingrese un número válido de cuotas</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="monto_cuota" class="form-label">Monto por Cuota (Gs.)</label>
                                    <input type="text" id="monto_cuota" class="form-control form-control-lg" readonly>
                                </div>
                            </div>
                        
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label for="descripcion" class="form-label required">Descripción de la Deuda</label>
                                    <input type="text" name="descripcion" id="descripcion" class="form-control form-control-lg" required value="<?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?>">
                                    <div class="invalid-feedback">Por favor ingrese una descripción</div>
                                </div>
                            </div>
                        
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Emisión</label>
                                    <input type="text" class="form-control form-control-lg" value="<?php echo date('d/m/Y'); ?>" readonly>
                                    <small class="text-muted">La fecha de emisión se establece automáticamente al día de hoy</small>
                                    <!-- Campo oculto con la fecha en formato Y-m-d para el procesamiento interno -->
                                </div>
                                <div class="col-md-6">
                                    <label for="dia_vencimiento" class="form-label required">Día de Vencimiento Mensual</label>
                                    <select name="dia_vencimiento" id="dia_vencimiento" class="form-select form-select-lg" required>
                                        <option value="">Seleccione el día de vencimiento</option>
                                        <option value="5" <?php echo (isset($_POST['dia_vencimiento']) && $_POST['dia_vencimiento'] == 5) ? 'selected' : ''; ?>>5 de cada mes</option>
                                        <option value="15" <?php echo (isset($_POST['dia_vencimiento']) && $_POST['dia_vencimiento'] == 15) ? 'selected' : ''; ?>>15 de cada mes</option>
                                        <option value="30" <?php echo (isset($_POST['dia_vencimiento']) && $_POST['dia_vencimiento'] == 30) ? 'selected' : ''; ?>>30 de cada mes</option>
                                    </select>
                                    <div class="invalid-feedback">Por favor seleccione el día de vencimiento mensual</div>
                                </div>
                            </div>
                        
                            <div class="mb-4">
                                <label for="notas" class="form-label">Notas Adicionales</label>
                                <textarea name="notas" id="notas" class="form-control form-control-lg" rows="4"><?php echo isset($_POST['notas']) ? htmlspecialchars($_POST['notas']) : ''; ?></textarea>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-5">
                                <a href="<?php echo $cliente_id ? 'cliente_datos.php?id=' . $cliente_id : 'ver_clientes.php'; ?>" class="btn btn-secondary btn-lg me-md-2">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
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
    .content-wrapper {
        margin-left: 250px;
        padding: 20px;
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
    .form-container {
        padding: 2rem;
    }
    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
        font-size: 1.05rem;
    }
    .form-control, .form-select {
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid #ced4da;
    }
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
    }
    .alert-info {
        background-color: #e8f4f8;
        border-color: #b8e7f3;
        color: #0c5460;
        border-radius: 0.5rem;
        padding: 1.25rem;
    }
    .row {
        margin-bottom: 1.5rem;
    }
</style>

<script>
// Validación del formulario usando Bootstrap
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
})()

// Formatear monto con separadores de miles
document.getElementById('monto').addEventListener('input', function(e) {
    let value = this.value.replace(/\D/g, "");
    if (value.length > 0) {
        value = parseInt(value).toLocaleString('es-PY');
        this.value = value.replace(/\./g, "");
    }
    calcularMontoCuota();
});

// Calcular y mostrar el monto por cuota
document.getElementById('cuotas').addEventListener('input', calcularMontoCuota);

function calcularMontoCuota() {
    const monto = parseInt(document.getElementById('monto').value.replace(/\D/g, "")) || 0;
    const cuotas = parseInt(document.getElementById('cuotas').value) || 1;
    
    if (monto > 0 && cuotas > 0) {
        const montoCuota = Math.round(monto / cuotas);
        document.getElementById('monto_cuota').value = montoCuota.toLocaleString('es-PY') + ' Gs.';
    } else {
        document.getElementById('monto_cuota').value = '';
    }
}

// Inicializar el cálculo de cuotas
window.addEventListener('DOMContentLoaded', calcularMontoCuota);

// Mostrar información detallada de la política de interés seleccionada
document.getElementById('politica_interes_id').addEventListener('change', function() {
    const politicaId = this.value;
    if (!politicaId) {
        document.getElementById('politica_info').style.display = 'none';
        return;
    }
    
    const politicas = <?php echo json_encode($politicas); ?>;
    const politica = politicas.find(p => p.id == politicaId);
    
    if (politica) {
        let descripcion = '';
        
        switch (politica.tipo) {
            case 'simple':
                descripcion = `<p>Interés simple al ${politica.tasa}% ${politica.periodo}.</p>`;
                descripcion += `<p>El interés se calcula sobre el monto original sin capitalización.</p>`;
                break;
                
            case 'compuesto':
                descripcion = `<p>Interés compuesto al ${politica.tasa}% ${politica.periodo}.</p>`;
                descripcion += `<p>El interés se calcula sobre el monto más los intereses acumulados (capitalización).</p>`;
                break;
                
            case 'escalonado':
                descripcion = `<p>Interés escalonado ${politica.periodo}:</p><ul>`;
                
                if (politica.tasa_escalonada_json) {
                    const escalas = JSON.parse(politica.tasa_escalonada_json);
                    escalas.forEach(escala => {
                        const hasta = escala.dias_hasta ? `${escala.dias_hasta}` : 'en adelante';
                        descripcion += `<li>Día ${escala.dias_desde} a día ${hasta}: ${escala.tasa}% diario</li>`;
                    });
                }
                
                descripcion += `</ul>`;
                
                if (politica.penalizacion_fija) {
                    descripcion += `<p>Penalización fija de ${parseInt(politica.penalizacion_fija).toLocaleString('es-PY')} Gs. después de ${politica.dias_penalizacion} días de atraso.</p>`;
                }
                break;
        }
        
        document.querySelector('.politica-nombre').textContent = politica.nombre;
        document.querySelector('.politica-descripcion').innerHTML = descripcion;
        document.getElementById('politica_info').style.display = 'block';
    }
});
</script>
</body>
</html>