<?php
session_start();
require_once '../include/cnx.php';

// Verificar si el usuario está logueado como cliente
if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../../index.php');
    exit;
}

$cliente_id = $_SESSION['cliente_id'];

// Obtener los préstamos del cliente para el selector
$query_prestamos = "SELECT id, descripcion, monto, fecha_emision, estado 
                    FROM deudas 
                    WHERE cliente_id = ? 
                    ORDER BY fecha_emision DESC";
$stmt_prestamos = $pdo->prepare($query_prestamos);
$stmt_prestamos->execute([$cliente_id]);
$prestamos = $stmt_prestamos->fetchAll();

// Procesar el formulario cuando se envía
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deuda_id = isset($_POST['deuda_id']) ? $_POST['deuda_id'] : null;
    $asunto = trim($_POST['asunto']);
    $descripcion = trim($_POST['descripcion']);
    
    // Validar campos
    if (empty($asunto) || empty($descripcion)) {
        $mensaje = 'Por favor complete todos los campos obligatorios.';
        $tipo_mensaje = 'danger';
    } else {
        try {
            // Convertir deuda_id vacío a NULL para la base de datos
            $deuda_id = !empty($deuda_id) ? $deuda_id : null;
            
            // Insertar el reclamo en la base de datos
            $query = "INSERT INTO reclamos (cliente_id, deuda_id, asunto, descripcion, estado) 
                      VALUES (?, ?, ?, ?, 'abierto')";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$cliente_id, $deuda_id, $asunto, $descripcion]);
            
            $reclamo_id = $pdo->lastInsertId();
            
            // Manejar archivos adjuntos si existen
            if (isset($_FILES['adjuntos']) && $_FILES['adjuntos']['error'][0] != UPLOAD_ERR_NO_FILE) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                $upload_dir = '../../uploads/documentos/';
                
                // Crear directorio si no existe
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $files = $_FILES['adjuntos'];
                $file_count = count($files['name']);
                
                for ($i = 0; $i < $file_count; $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $tmp_name = $files['tmp_name'][$i];
                        $file_type = $files['type'][$i];
                        $original_name = $files['name'][$i];
                        
                        // Verificar tipo de archivo
                        if (!in_array($file_type, $allowed_types)) {
                            continue; // Saltar archivos no permitidos
                        }
                        
                        // Generar nombre único para el archivo
                        $file_name = uniqid() . '_' . $original_name;
                        $destination = $upload_dir . $file_name;
                        
                        // Mover archivo al directorio de destino
                        if (move_uploaded_file($tmp_name, $destination)) {
                            // Guardar referencia en la base de datos
                            $query_doc = "INSERT INTO documentos (cliente_id, nombre_original, ruta_archivo, tipo_documento, created_at) 
                                          VALUES (?, ?, ?, 'reclamo', NOW())";
                            $stmt_doc = $pdo->prepare($query_doc);
                            $stmt_doc->execute([$cliente_id, $original_name, $file_name]);
                        }
                    }
                }
            }
            
            $mensaje = 'Su reclamo ha sido enviado correctamente. Número de reclamo: ' . $reclamo_id;
            $tipo_mensaje = 'success';
            
            // Redireccionar a la página de lista de reclamos con mensaje de éxito
            header('Location: mis_reclamos.php?success=1&reclamo_id=' . $reclamo_id);
            exit;
            
        } catch (PDOException $e) {
            $mensaje = 'Error al enviar el reclamo: ' . $e->getMessage();
            $tipo_mensaje = 'danger';
        }
    }
}

include '../include/sidebar.php';
?>

<div class="content-wrapper">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Nuevo Reclamo</h4>
                        <a href="mis_reclamos.php" class="btn btn-light">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($mensaje)): ?>
                            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                                <?php echo $mensaje; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="deuda_id" class="form-label">Préstamo relacionado (opcional)</label>
                                <select class="form-select" id="deuda_id" name="deuda_id">
                                    <option value="">Seleccione un préstamo</option>
                                    <?php foreach ($prestamos as $prestamo): ?>
                                        <option value="<?php echo $prestamo['id']; ?>">
                                            <?php echo htmlspecialchars($prestamo['descripcion']); ?> - 
                                            ₲ <?php echo number_format($prestamo['monto'], 0, ',', '.'); ?> - 
                                            <?php echo date('d/m/Y', strtotime($prestamo['fecha_emision'])); ?> - 
                                            <?php echo ucfirst($prestamo['estado']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Si su reclamo está relacionado con un préstamo específico, selecciónelo aquí.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="asunto" class="form-label">Asunto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="asunto" name="asunto" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción detallada <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="5" required></textarea>
                                <div class="form-text">Por favor, proporcione todos los detalles relevantes para que podamos atender su reclamo de manera efectiva.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="adjuntos" class="form-label">Archivos adjuntos (opcional)</label>
                                <input class="form-control" type="file" id="adjuntos" name="adjuntos[]" multiple>
                                <div class="form-text">Puede adjuntar imágenes, PDFs u otros documentos relevantes (máx. 5MB por archivo).</div>
                                <div class="form-text">Formatos permitidos: JPG, PNG, GIF, PDF, DOC, DOCX</div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="mis_reclamos.php" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary" id="btnEnviarReclamo">
                                    <i class="bi bi-send"></i> Enviar Reclamo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación de tamaño de archivos
    document.getElementById('adjuntos').addEventListener('change', function() {
        const maxSize = 5 * 1024 * 1024; // 5MB
        const files = this.files;
        
        for (let i = 0; i < files.length; i++) {
            if (files[i].size > maxSize) {
                alert('El archivo ' + files[i].name + ' excede el tamaño máximo permitido de 5MB.');
                this.value = ''; // Limpiar el input
                break;
            }
        }
    });
    
    // Prevenir envíos múltiples del formulario
    const form = document.querySelector('form');
    const btnEnviar = document.getElementById('btnEnviarReclamo');
    
    form.addEventListener('submit', function() {
        // Deshabilitar el botón para prevenir múltiples envíos
        btnEnviar.disabled = true;
        btnEnviar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...';
    });
});
</script>

<!-- Custom CSS for dark mode compatibility -->
<style>
    @media (prefers-color-scheme: dark) {
        .card {
            background-color: #2c3e50;
            color: white;
        }
        
        .form-control, .form-select {
            background-color: #34495e;
            color: white;
            border-color: #4a5568;
        }
        
        .form-control:focus, .form-select:focus {
            background-color: #34495e;
            color: white;
        }
        
        .form-text {
            color: #cbd5e0 !important;
        }
    }
    
    /* Support for Bootstrap 5 dark mode */
    [data-bs-theme="dark"] .card,
    .dark-mode .card {
        background-color: #2c3e50;
        color: white;
    }
    
    [data-bs-theme="dark"] .form-control,
    [data-bs-theme="dark"] .form-select,
    .dark-mode .form-control,
    .dark-mode .form-select {
        background-color: #34495e;
        color: white;
        border-color: #4a5568;
    }
    
    [data-bs-theme="dark"] .form-text,
    .dark-mode .form-text {
        color: #cbd5e0 !important;
    }
</style>