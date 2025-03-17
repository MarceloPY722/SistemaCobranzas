<?php include '../../../admin/include/sidebar.php'; ?>

<?php
require_once '../cnx.php';

// Verificar si se proporcionó un ID de cliente
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ver_clientes.php?error=id_invalido');
    exit();
}

$cliente_id = $_GET['id'];

// Consulta para obtener los datos del cliente
$query = "SELECT * FROM clientes WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ver_clientes.php?error=cliente_no_encontrado');
    exit();
}

$cliente = $result->fetch_assoc();

// Consulta para obtener las deudas del cliente
$query_deudas = "SELECT d.*, p.nombre as politica_nombre, p.tasa 
                FROM deudas d 
                JOIN politicas_interes p ON d.politica_interes_id = p.id 
                WHERE d.cliente_id = ? AND d.estado != 'pagado'
                ORDER BY d.fecha_vencimiento ASC";
$stmt_deudas = $conn->prepare($query_deudas);
$stmt_deudas->bind_param("i", $cliente_id);
$stmt_deudas->execute();
$result_deudas = $stmt_deudas->get_result();

// Procesar el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_cliente'])) {
    $nombre = $_POST['nombre'];
    $identificacion = $_POST['identificacion'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $password = $_POST['password'];
    
    // Validar campos requeridos
    if (empty($nombre) || empty($identificacion)) {
        $error_msg = "Los campos Nombre e Identificación son obligatorios.";
    } else {
        // Verificar si la identificación ya existe (excluyendo el cliente actual)
        $stmt = $conn->prepare("SELECT id FROM clientes WHERE identificacion = ? AND id != ?");
        $stmt->bind_param("si", $identificacion, $cliente_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_msg = "La identificación ya está registrada para otro cliente.";
        } else {
            // Verificar si el email ya existe (excluyendo el cliente actual)
            if (!empty($email)) {
                $stmt = $conn->prepare("SELECT id FROM clientes WHERE email = ? AND id != ?");
                $stmt->bind_param("si", $email, $cliente_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error_msg = "El email ya está registrado para otro cliente.";
                }
            }
            
            // Si no hay errores, procesar la imagen si se subió una nueva
            if (!isset($error_msg)) {
                $imagen = $cliente['imagen']; // Mantener la imagen actual por defecto
                
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['imagen']['tmp_name'];
                    $file_name = $_FILES['imagen']['name'];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    // Verificar la extensión del archivo
                    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (!in_array($file_ext, $allowed_exts)) {
                        $error_msg = "Formato de imagen no válido. Se permiten: jpg, jpeg, png, gif.";
                    } else {
                        // Generar un nombre único para la imagen
                        $new_file_name = uniqid() . '.' . $file_ext;
                        $upload_path = '../../../uploads/profiles/' . $new_file_name;
                        
                        // Crear el directorio si no existe
                        if (!file_exists('../../../uploads/profiles/')) {
                            mkdir('../../../uploads/profiles/', 0777, true);
                        }
                        
                        // Mover el archivo subido al directorio de destino
                        if (move_uploaded_file($file_tmp, $upload_path)) {
                            // Si se sube correctamente, actualizar el nombre de la imagen
                            $imagen = $new_file_name;
                            
                            // Eliminar la imagen anterior si no es la predeterminada
                            if ($cliente['imagen'] != 'default.png' && file_exists('../../../uploads/profiles/' . $cliente['imagen'])) {
                                unlink('../../../uploads/profiles/' . $cliente['imagen']);
                            }
                        } else {
                            $error_msg = "Error al subir la imagen. Por favor, inténtelo de nuevo.";
                        }
                    }
                }
                
                // Actualizar la contraseña si se proporcionó una nueva
                $password_update = "";
                $password_param = "";
                
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $password_update = ", password = ?";
                    $password_param = $hashed_password;
                }
                
                // Actualizar los datos del cliente en la base de datos
                if (!isset($error_msg)) {
                    // Obtener el valor de ubicacion_link
                    $ubicacion_link = filter_input(INPUT_POST, 'ubicacion_link', FILTER_SANITIZE_URL);
                    
                    if (!empty($password)) {
                        // Keep only this correct statement with the password parameter
                        $stmt = $conn->prepare("UPDATE clientes SET nombre = ?, identificacion = ?, email = ?, telefono = ?, direccion = ?, imagen = ?, ubicacion_link = ?, password = ? WHERE id = ?");
                        $stmt->bind_param("ssssssssi", $nombre, $identificacion, $email, $telefono, $direccion, $imagen, $ubicacion_link, $password_param, $cliente_id);
                    } else {
                        $stmt = $conn->prepare("UPDATE clientes SET nombre = ?, identificacion = ?, email = ?, telefono = ?, direccion = ?, imagen = ?, ubicacion_link = ? WHERE id = ?");
                        $stmt->bind_param("sssssssi", $nombre, $identificacion, $email, $telefono, $direccion, $imagen, $ubicacion_link, $cliente_id);
                    }
                    
                    if ($stmt->execute()) {
                        // Use JavaScript redirect instead of header() to avoid "headers already sent" error
                        echo "<script>window.location.href = 'cliente_datos.php?id=$cliente_id&success=actualizado';</script>";
                        exit();
                    } else {
                        $error_msg = "Error al actualizar los datos: " . $conn->error;
                    }
                }
            }
        }
    }
}

// Procesar el formulario de pago
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_pago'])) {
    $deuda_id = $_POST['deuda_id'];
    $monto_pagado = $_POST['monto_pagado'];
    $metodo_pago = $_POST['metodo_pago'];
    $fecha_pago = $_POST['fecha_pago'];
    
    // Validar campos requeridos
    if (empty($deuda_id) || empty($monto_pagado) || empty($metodo_pago) || empty($fecha_pago)) {
        $error_pago = "Todos los campos son obligatorios para registrar un pago.";
    } else {
        // Obtener información de la deuda
        $stmt = $conn->prepare("SELECT monto, saldo_pendiente FROM deudas WHERE id = ?");
        $stmt->bind_param("i", $deuda_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $deuda = $result->fetch_assoc();
        
        if ($monto_pagado <= 0) {
            $error_pago = "El monto del pago debe ser mayor que cero.";
        } elseif ($monto_pagado > $deuda['saldo_pendiente']) {
            $error_pago = "El monto del pago no puede ser mayor que el saldo pendiente.";
        } else {
            // Procesar el comprobante si se subió uno
            $comprobante = null;
            
            if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['comprobante']['tmp_name'];
                $file_name = $_FILES['comprobante']['name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // Verificar la extensión del archivo
                $allowed_exts = ['jpg', 'jpeg', 'png', 'pdf'];
                
                if (!in_array($file_ext, $allowed_exts)) {
                    $error_pago = "Formato de comprobante no válido. Se permiten: jpg, jpeg, png, pdf.";
                } else {
                    // Generar un nombre único para el comprobante
                    $new_file_name = 'comprobante_' . uniqid() . '.' . $file_ext;
                    $upload_path = '../../../uploads/comprobantes/' . $new_file_name;
                    
                    // Crear el directorio si no existe
                    if (!file_exists('../../../uploads/comprobantes/')) {
                        mkdir('../../../uploads/comprobantes/', 0777, true);
                    }
                    
                    // Mover el archivo subido al directorio de destino
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $comprobante = $new_file_name;
                    } else {
                        $error_pago = "Error al subir el comprobante. Por favor, inténtelo de nuevo.";
                    }
                }
            }
            
            // Si no hay errores, registrar el pago
            if (!isset($error_pago)) {
                // Iniciar transacción
                $conn->begin_transaction();
                
                try {
                    // Insertar el pago
                    $stmt = $conn->prepare("INSERT INTO pagos (deuda_id, monto_pagado, metodo_pago, fecha_pago, comprobante, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("idsss", $deuda_id, $monto_pagado, $metodo_pago, $fecha_pago, $comprobante);
                    $stmt->execute();
                    
                    // Actualizar el saldo pendiente de la deuda
                    $nuevo_saldo = $deuda['saldo_pendiente'] - $monto_pagado;
                    $nuevo_estado = ($nuevo_saldo <= 0) ? 'pagado' : 'pendiente';
                    
                    $stmt = $conn->prepare("UPDATE deudas SET saldo_pendiente = ?, estado = ? WHERE id = ?");
                    $stmt->bind_param("dsi", $nuevo_saldo, $nuevo_estado, $deuda_id);
                    $stmt->execute();
                    
                    // Registrar en el historial de deudas
                    $usuario_id = $_SESSION['usuario_id']; // Asumiendo que tienes el ID del usuario en la sesión
                    $accion = "Pago registrado";
                    $detalle = "Pago de " . number_format($monto_pagado, 2, '.', ',') . " Gs. mediante " . $metodo_pago;
                    
                    $stmt = $conn->prepare("INSERT INTO historial_deudas (deuda_id, usuario_id, accion, detalle, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->bind_param("iiss", $deuda_id, $usuario_id, $accion, $detalle);
                    $stmt->execute();
                    
                    // Confirmar la transacción
                    $conn->commit();
                    
                    // Use JavaScript redirect instead of header()
                    echo "<script>window.location.href = 'cliente_datos.php?id=$cliente_id&success=pago_registrado';</script>";
                    exit();
                } catch (Exception $e) {
                    // Revertir la transacción en caso de error
                    $conn->rollback();
                    $error_pago = "Error al registrar el pago: " . $e->getMessage();
                }
            }
        }
    }
}
?>

<!-- Contenido principal -->
<div class="content-wrapper">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Editar Cliente</h4>
                        <a href="cliente_datos.php?id=<?php echo $cliente_id; ?>" class="btn btn-light">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error_msg)): ?>
                            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                        <?php endif; ?>
                        
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-4 text-center mb-4">
                                    <div class="profile-img-container mb-3">
                                        <?php if(!empty($cliente['imagen']) && $cliente['imagen'] != 'default.png'): ?>
                                            <img src="../../../uploads/profiles/<?php echo $cliente['imagen']; ?>" 
                                                 alt="Perfil" 
                                                 class="img-fluid rounded-circle profile-image"
                                                 style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #121a35;">
                                        <?php else: ?>
                                            <img src="../../../uploads/profiles/default.png" 
                                                 alt="Perfil" 
                                                 class="img-fluid rounded-circle profile-image"
                                                 style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #121a35;">
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label for="imagen" class="form-label">Cambiar Imagen</label>
                                        <input type="file" class="form-control" id="imagen" name="imagen">
                                        <small class="form-text text-muted">Formatos permitidos: JPG, JPEG, PNG, GIF</small>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nombre" class="form-label">Nombre Completo</label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="identificacion" class="form-label">Identificación</label>
                                            <input type="text" class="form-control" id="identificacion" name="identificacion" value="<?php echo htmlspecialchars($cliente['identificacion']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($cliente['email']); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="telefono" class="form-label">Teléfono</label>
                                            <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono']); ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="direccion" class="form-label">Dirección</label>
                                        <textarea class="form-control" id="direccion" name="direccion" rows="2"><?php echo htmlspecialchars($cliente['direccion']); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="ubicacion_link" class="form-label d-flex justify-content-between align-items-center">
                                            Ubicación en Google Maps
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="editUbicacion">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </label>
                                        <?php if(empty($cliente['ubicacion_link'])): ?>
                                            <div id="ubicacion_placeholder" class="alert alert-warning mb-2">
                                                <i class="bi bi-geo-alt"></i> El Usuario aún no cargó su ubicación
                                            </div>
                                            <div id="ubicacion_input_container" style="display: none;">
                                                <input type="url" 
                                                    class="form-control" 
                                                    id="ubicacion_link" 
                                                    name="ubicacion_link" 
                                                    placeholder="https://maps.app.goo.gl/...">
                                                <div class="form-text">Pega aquí el enlace compartido de Google Maps de tu ubicación</div>
                                            </div>
                                        <?php else: ?>
                                            <div id="ubicacion_placeholder" class="d-flex align-items-center mb-2">
                                                <a href="<?php echo htmlspecialchars($cliente['ubicacion_link']); ?>" target="_blank" class="btn btn-sm btn-info me-2">
                                                    <i class="bi bi-geo-alt"></i> Ver ubicación actual
                                                </a>
                                                <span class="text-muted small">Enlace guardado</span>
                                            </div>
                                            <div id="ubicacion_input_container" style="display: none;">
                                                <input type="url" 
                                                    class="form-control" 
                                                    id="ubicacion_link" 
                                                    name="ubicacion_link" 
                                                    value="<?php echo htmlspecialchars($cliente['ubicacion_link']); ?>"
                                                    placeholder="https://maps.app.goo.gl/...">
                                                <div class="form-text">Pega aquí el enlace compartido de Google Maps de tu ubicación</div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Nueva Contraseña (dejar en blanco para mantener la actual)</label>
                                        <input type="password" class="form-control" id="password" name="password">
                                        <small class="form-text text-muted">Complete este campo solo si desea cambiar la contraseña.</small>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="submit" name="actualizar_cliente" class="btn btn-primary">
                                            <i class="bi bi-save"></i> Guardar Cambios
                                        </button>
                                    </div>
                                </div>
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
        // Manejar el botón de editar ubicación
        const editUbicacionBtn = document.getElementById('editUbicacion');
        const ubicacionPlaceholder = document.getElementById('ubicacion_placeholder');
        const ubicacionInputContainer = document.getElementById('ubicacion_input_container');
        
        if (editUbicacionBtn) {
            editUbicacionBtn.addEventListener('click', function() {
                // Ocultar el placeholder y mostrar el campo de entrada
                ubicacionPlaceholder.style.display = 'none';
                ubicacionInputContainer.style.display = 'block';
                
                // Enfocar el campo de entrada
                document.getElementById('ubicacion_link').focus();
            });
        }
    });
</script>
                                        