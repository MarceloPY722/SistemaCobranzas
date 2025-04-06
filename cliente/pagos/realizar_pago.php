<?php
session_start();
require_once '../include/cnx.php';

// Verificar si el usuario está logueado como cliente
if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../../index.php');
    exit;
}

$cliente_id = $_SESSION['cliente_id'];

// Verificar si se proporcionó un ID de deuda
if (!isset($_GET['deuda_id']) || !is_numeric($_GET['deuda_id'])) {
    header('Location: ../prestamos/ver_prestamos.php?error=id_invalido');
    exit;
}

$deuda_id = $_GET['deuda_id'];
$cuota_id = isset($_GET['cuota_id']) ? $_GET['cuota_id'] : 0;

// Obtener los datos de la deuda y verificar que pertenezca al cliente
$query = "SELECT d.*, p.nombre as politica_nombre, p.tasa 
          FROM deudas d 
          JOIN politicas_interes p ON d.politica_interes_id = p.id 
          WHERE d.id = ? AND d.cliente_id = ? AND d.estado != 'pagado'";
$stmt = $pdo->prepare($query);
$stmt->execute([$deuda_id, $cliente_id]);

if ($stmt->rowCount() === 0) {
    header('Location: ../prestamos/ver_prestamos.php?error=deuda_no_encontrada');
    exit;
}

$deuda = $stmt->fetch();

// Si se especificó una cuota, obtener sus datos
$cuota_seleccionada = null;
if ($cuota_id > 0) {
    $query_cuota = "SELECT * FROM cuotas_deuda WHERE id = ? AND deuda_id = ? AND estado != 'pagado'";
    $stmt_cuota = $pdo->prepare($query_cuota);
    $stmt_cuota->execute([$cuota_id, $deuda_id]);
    
    if ($stmt_cuota->rowCount() > 0) {
        $cuota_seleccionada = $stmt_cuota->fetch();
    }
}

// Obtener las cuotas de la deuda
$query_cuotas = "SELECT * FROM cuotas_deuda WHERE deuda_id = ? ORDER BY numero_cuota ASC";
$stmt_cuotas = $pdo->prepare($query_cuotas);
$stmt_cuotas->execute([$deuda_id]);
$cuotas = $stmt_cuotas->fetchAll();

// Verificar si hay cuotas pagadas y actualizar su estado
foreach ($cuotas as $key => $cuota) {
    // Verificar si la cuota ya tiene pagos registrados
    $query_pagos = "SELECT SUM(monto_pagado) as total_pagado FROM pagos WHERE cuota_id = ? AND estado != 'rechazado'";
    $stmt_pagos = $pdo->prepare($query_pagos);
    $stmt_pagos->execute([$cuota['id']]);
    $pago_info = $stmt_pagos->fetch();
    
    if ($pago_info && $pago_info['total_pagado'] >= $cuota['monto_cuota']) {
        // Si el total pagado es mayor o igual al monto de la cuota, marcarla como pagada
        $cuotas[$key]['estado'] = 'pagado';
        
        // Actualizar en la base de datos si no está ya marcada como pagada
        if ($cuota['estado'] != 'pagado') {
            $update_estado = "UPDATE cuotas_deuda SET estado = 'pagado' WHERE id = ?";
            $stmt_update = $pdo->prepare($update_estado);
            $stmt_update->execute([$cuota['id']]);
        }
    }
}

// Inicializar variables para el formulario
$monto_pagado = $cuota_seleccionada ? $cuota_seleccionada['monto_cuota'] : '';
$metodo_pago = '';
$comprobante = '';
$error_msg = '';
$success_msg = '';

// Procesar el formulario de pago
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['realizar_pago'])) {
    $monto_pagado = isset($_POST['monto_pagado']) ? floatval(str_replace('.', '', $_POST['monto_pagado'])) : 0;
    $metodo_pago = isset($_POST['metodo_pago']) ? trim($_POST['metodo_pago']) : '';
    $cuota_id = isset($_POST['cuota_id']) ? intval($_POST['cuota_id']) : 0;
    
    // Validar el monto
    if ($monto_pagado <= 0) {
        $error_msg = 'El monto debe ser mayor a cero.';
    }
    
    // Validar el método de pago
    if (empty($metodo_pago)) {
        $error_msg = 'Debe seleccionar un método de pago.';
    }
    
    // Procesar el comprobante si se subió
    if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $filename = $_FILES['comprobante']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $error_msg = 'El archivo debe ser una imagen (JPG, PNG) o un PDF.';
        } else {
            // Generar un nombre único para el archivo
            $new_filename = uniqid() . '.' . $ext;
            $upload_dir = '../../uploads/comprobantes/';
            
            // Crear el directorio si no existe
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Mover el archivo
            if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $upload_dir . $new_filename)) {
                $comprobante = $new_filename;
            } else {
                $error_msg = 'Error al subir el archivo.';
            }
        }
    }
    
    if (empty($error_msg)) {
        try {
            // Iniciar transacción
            $pdo->beginTransaction();
            
            // Registrar el pago
            $query_pago = "INSERT INTO pagos (deuda_id, monto_pagado, fecha_pago, metodo_pago, comprobante, estado, created_at, cuota_id) 
                          VALUES (?, ?, NOW(), ?, ?, 'aprobado', NOW(), ?)";
            $stmt_pago = $pdo->prepare($query_pago);
            $stmt_pago->execute([$deuda_id, $monto_pagado, $metodo_pago, $comprobante, $cuota_id]);
            
            // Si hay cuotas definidas y se seleccionó una cuota específica
            if ($cuota_id > 0) {
                // Obtener la información de la cuota seleccionada
                $query_cuota_info = "SELECT * FROM cuotas_deuda WHERE id = ?";
                $stmt_cuota_info = $pdo->prepare($query_cuota_info);
                $stmt_cuota_info->execute([$cuota_id]);
                $cuota_info = $stmt_cuota_info->fetch();
                
                // Verificar si la cuota tiene saldo_pendiente
                $has_saldo_column = false;
                try {
                    $check_column = $pdo->query("SHOW COLUMNS FROM cuotas_deuda LIKE 'saldo_pendiente'");
                    $has_saldo_column = ($check_column->rowCount() > 0);
                } catch (Exception $e) {
                    // La columna no existe
                }
                
                if ($has_saldo_column) {
                    // Si existe la columna saldo_pendiente, actualizar
                    $nuevo_saldo_cuota = max(0, $cuota_info['saldo_pendiente'] - $monto_pagado);
                    $estado_cuota = ($nuevo_saldo_cuota <= 0) ? 'pagado' : $cuota_info['estado'];
                    
                    $query_update_cuota = "UPDATE cuotas_deuda SET saldo_pendiente = ?, estado = ? WHERE id = ?";
                    $stmt_update_cuota = $pdo->prepare($query_update_cuota);
                    $stmt_update_cuota->execute([$nuevo_saldo_cuota, $estado_cuota, $cuota_id]);
                } else {
                    // Si no existe la columna, solo actualizar el estado
                    $estado_cuota = ($monto_pagado >= $cuota_info['monto_cuota']) ? 'pagado' : $cuota_info['estado'];
                    
                    $query_update_cuota = "UPDATE cuotas_deuda SET estado = ? WHERE id = ?";
                    $stmt_update_cuota = $pdo->prepare($query_update_cuota);
                    $stmt_update_cuota->execute([$estado_cuota, $cuota_id]);
                    
                    // Intentar agregar la columna saldo_pendiente
                    try {
                        $pdo->exec("ALTER TABLE cuotas_deuda ADD COLUMN saldo_pendiente DECIMAL(12,2) DEFAULT NULL AFTER monto_cuota");
                        $pdo->exec("UPDATE cuotas_deuda SET saldo_pendiente = monto_cuota WHERE saldo_pendiente IS NULL");
                        
                        // Actualizar el saldo de la cuota actual
                        $nuevo_saldo_cuota = max(0, $cuota_info['monto_cuota'] - $monto_pagado);
                        $query_update_saldo = "UPDATE cuotas_deuda SET saldo_pendiente = ? WHERE id = ?";
                        $stmt_update_saldo = $pdo->prepare($query_update_saldo);
                        $stmt_update_saldo->execute([$nuevo_saldo_cuota, $cuota_id]);
                    } catch (Exception $e) {
                        // Ignorar errores al intentar agregar la columna
                    }
                }
            }
            
            // Actualizar el saldo pendiente de la deuda
            $nuevo_saldo = max(0, $deuda['saldo_pendiente'] - $monto_pagado);
            $nuevo_estado = ($nuevo_saldo <= 0) ? 'pagado' : $deuda['estado'];
            
            $query_update = "UPDATE deudas SET saldo_pendiente = ?, estado = ? WHERE id = ?";
            $stmt_update = $pdo->prepare($query_update);
            $stmt_update->execute([$nuevo_saldo, $nuevo_estado, $deuda_id]);
            
            // Confirmar transacción
            $pdo->commit();
            
            // Establecer mensaje de éxito
            $success_msg = 'El pago ha sido registrado correctamente.';
            
            // Redirigir a la página de detalle del préstamo con mensaje de éxito
            header('Location: ../prestamos/detalle_prestamo.php?id=' . $deuda_id . '&success=pago');
            exit;
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $pdo->rollBack();
            $error_msg = 'Error al procesar el pago: ' . $e->getMessage();
        }
    }
}

// Función para formatear montos
function formatMoney($amount) {
    return '₲ ' . number_format($amount, 0, ',', '.');
}

include '../include/sidebar.php';
?>

<div class="content-wrapper">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-custom text-white">
                        <h4 class="mb-0">Realizar Pago</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error_msg)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error_msg; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success_msg)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $success_msg; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Información del Préstamo</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Descripción:</th>
                                        <td><?php echo htmlspecialchars($deuda['descripcion']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Monto Total:</th>
                                        <td><?php echo formatMoney($deuda['monto']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Saldo Pendiente:</th>
                                        <td><?php echo formatMoney($deuda['saldo_pendiente']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Fecha de Vencimiento:</th>
                                        <td><?php echo date('d/m/Y', strtotime($deuda['fecha_vencimiento'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Estado:</th>
                                        <td>
                                            <span class="badge <?php 
                                                if($deuda['estado'] == 'pendiente') echo 'bg-warning';
                                                elseif($deuda['estado'] == 'pagado') echo 'bg-success';
                                                elseif($deuda['estado'] == 'vencido') echo 'bg-danger';
                                            ?>">
                                                <?php echo ucfirst($deuda['estado']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Formulario de Pago</h5>
                                <form method="post" action="" enctype="multipart/form-data" id="payment-form">
                                    <input type="hidden" name="cuota_id" id="cuota_id" value="<?php echo $cuota_id; ?>">
                                    
                                    <div class="mb-3">
                                        <label for="monto_pagado" class="form-label">Monto a Pagar (₲):</label>
                                        <input type="text" class="form-control" id="monto_pagado" name="monto_pagado" value="<?php echo number_format($monto_pagado, 0, ',', '.'); ?>" required>
                                        <small class="text-muted">Ingrese el monto sin puntos ni símbolos.</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="metodo_pago" class="form-label">Método de Pago:</label>
                                        <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                            <option value="">Seleccione un método</option>
                                            <option value="Efectivo" <?php if($metodo_pago == 'Efectivo') echo 'selected'; ?>>Efectivo</option>
                                            <option value="Transferencia" <?php if($metodo_pago == 'Transferencia') echo 'selected'; ?>>Transferencia Bancaria</option>
                                            <option value="Tarjeta" <?php if($metodo_pago == 'Tarjeta') echo 'selected'; ?>>Tarjeta de Crédito/Débito</option>
                                            <option value="Giro" <?php if($metodo_pago == 'Giro') echo 'selected'; ?>>Giro</option>
                                            <option value="Otro" <?php if($metodo_pago == 'Otro') echo 'selected'; ?>>Otro</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="comprobante" class="form-label">Comprobante de Pago (opcional):</label>
                                        <input type="file" class="form-control" id="comprobante" name="comprobante">
                                        <small class="text-muted">Formatos permitidos: JPG, PNG, PDF. Máximo 2MB.</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Cuota Seleccionada:</label>
                                        <div id="cuota-seleccionada">
                                            <?php if ($cuota_seleccionada): ?>
                                                <div class="alert alert-info">
                                                    Cuota #<?php echo $cuota_seleccionada['numero_cuota']; ?> - 
                                                    Monto: <?php echo formatMoney($cuota_seleccionada['monto_cuota']); ?> - 
                                                    Vencimiento: <?php echo date('d/m/Y', strtotime($cuota_seleccionada['fecha_vencimiento'])); ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-warning">
                                                    No hay cuota seleccionada. El pago se aplicará al saldo general.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" name="realizar_pago" class="btn btn-primary">
                                            <i class="bi bi-cash"></i> Realizar Pago
                                        </button>
                                        <a href="../prestamos/detalle_prestamo.php?id=<?php echo $deuda_id; ?>" class="btn btn-secondary">
                                            <i class="bi bi-arrow-left"></i> Volver
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Tabla de cuotas -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2 mb-3">Cuotas del Préstamo</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th class="text-white">Cuota</th>
                                                <th class="text-white">Monto</th>
                                                <th class="text-white">Vencimiento</th>
                                                <th class="text-white">Estado</th>
                                                <th class="text-white">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            // Crear un array para rastrear cuotas ya mostradas por número
                                            $cuotas_mostradas = [];
                                            
                                            foreach ($cuotas as $cuota): 
                                                // Evitar mostrar cuotas duplicadas con el mismo número
                                                if (in_array($cuota['numero_cuota'], $cuotas_mostradas)) {
                                                    continue;
                                                }
                                                $cuotas_mostradas[] = $cuota['numero_cuota'];
                                                
                                                $estado_cuota = $cuota['estado'] ?? $deuda['estado'];
                                                $fecha_vencimiento = isset($cuota['fecha_vencimiento']) ? $cuota['fecha_vencimiento'] : $deuda['fecha_vencimiento'];
                                                $cuota_pagada = ($estado_cuota == 'pagado');
                                            ?>
                                                <tr>
                                                    <td>Cuota <?php echo $cuota['numero_cuota']; ?></td>
                                                    <td><?php echo formatMoney($cuota['monto_cuota']); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($fecha_vencimiento)); ?></td>
                                                    <td>
                                                        <span class="badge <?php 
                                                            if($estado_cuota == 'pendiente') echo 'bg-warning';
                                                            elseif($estado_cuota == 'pagado') echo 'bg-success';
                                                            elseif($estado_cuota == 'vencido') echo 'bg-danger';
                                                        ?>">
                                                            <?php echo ucfirst($estado_cuota); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if (!$cuota_pagada): ?>
                                                            <button type="button" class="btn btn-sm btn-primary seleccionar-cuota" 
                                                                    data-cuota-id="<?php echo $cuota['id']; ?>"
                                                                    data-cuota-monto="<?php echo (int)$cuota['monto_cuota']; ?>"
                                                                    data-cuota-numero="<?php echo $cuota['numero_cuota']; ?>">
                                                                Pagar esta cuota
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="badge bg-success">Pagada</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Formatear el campo de monto al escribir
    const montoInput = document.getElementById('monto_pagado');
    if (montoInput) {
        montoInput.addEventListener('input', function(e) {
            try {
                // Guardar la posición del cursor
                const start = this.selectionStart;
                const end = this.selectionEnd;
                
                // Eliminar todo excepto números
                let value = this.value.replace(/\D/g, '');
                
                // Formatear con puntos solo si hay un valor
                if (value && value.length > 0) {
                    value = Number(value).toLocaleString('es-PY');
                }
                
                // Actualizar el valor
                this.value = value;
                
                // Restaurar la posición del cursor
                this.setSelectionRange(start, end);
            } catch (error) {
                // Si hay error, simplemente limpiar caracteres no numéricos
                this.value = this.value.replace(/[^\d.]/g, '');
                console.log('Error al formatear:', error);
            }
        });
    }
    
    // Manejar la selección de cuotas
    const botonesSeleccionarCuota = document.querySelectorAll('.seleccionar-cuota');
    const cuotaIdInput = document.getElementById('cuota_id');
    const cuotaSeleccionadaDiv = document.getElementById('cuota-seleccionada');
    
    botonesSeleccionarCuota.forEach(function(boton) {
        boton.addEventListener('click', function() {
            const cuotaId = this.getAttribute('data-cuota-id');
            const cuotaMonto = this.getAttribute('data-cuota-monto');
            const cuotaNumero = this.getAttribute('data-cuota-numero');
            
            // Actualizar el campo oculto con el ID de la cuota
            if (cuotaIdInput) cuotaIdInput.value = cuotaId;
            
            // Actualizar el monto a pagar con el monto de la cuota
            if (montoInput) {
                try {
                    montoInput.value = Number(cuotaMonto).toLocaleString('es-PY');
                } catch (error) {
                    montoInput.value = cuotaMonto;
                    console.log('Error al formatear monto de cuota:', error);
                }
            }
            if (cuotaSeleccionadaDiv) {
                try {
                    cuotaSeleccionadaDiv.innerHTML = `
                        <div class="alert alert-info">
                            Cuota #${cuotaNumero} seleccionada - Monto: ₲ ${Number(cuotaMonto).toLocaleString('es-PY')}
                        </div>
                    `;
                } catch (error) {
                    cuotaSeleccionadaDiv.innerHTML = `
                        <div class="alert alert-info">
                            Cuota #${cuotaNumero} seleccionada - Monto: ₲ ${cuotaMonto}
                        </div>
                    `;
                }
            }
            
            // Hacer scroll al formulario
            const form = document.getElementById('payment-form');
            if (form) form.scrollIntoView({ behavior: 'smooth' });
        });
    });
});
</script>