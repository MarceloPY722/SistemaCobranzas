<?php
session_start();
require_once '../include/cnx.php';

// Verificar si el usuario está logueado como cliente
if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../../index.php');
    exit;
}

$cliente_id = $_SESSION['cliente_id'];
$error_msg = '';
$success_msg = '';

// Obtener los datos actuales del cliente
$query = "SELECT * FROM clientes WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$cliente_id]);
$cliente = $stmt->fetch();

// Procesar el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $password = trim($_POST['password']);
    $password_confirm = trim($_POST['password_confirm']);
    
    // Validar los datos
    if (empty($nombre)) {
        $error_msg = 'El nombre es obligatorio.';
    } elseif (empty($email)) {
        $error_msg = 'El correo electrónico es obligatorio.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'El correo electrónico no es válido.';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error_msg = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif (!empty($password) && $password !== $password_confirm) {
        $error_msg = 'Las contraseñas no coinciden.';
    } else {
        // Verificar si el email ya está en uso por otro cliente
        $query_check = "SELECT id FROM clientes WHERE email = ? AND id != ?";
        $stmt_check = $pdo->prepare($query_check);
        $stmt_check->execute([$email, $cliente_id]);
        
        if ($stmt_check->rowCount() > 0) {
            $error_msg = 'El correo electrónico ya está en uso por otro cliente.';
        } else {
            // Procesar la imagen si se subió una nueva
            $imagen = $cliente['imagen']; // Mantener la imagen actual por defecto
            
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png'];
                $filename = $_FILES['imagen']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (!in_array($ext, $allowed)) {
                    $error_msg = 'La imagen debe ser JPG o PNG.';
                } else {
                    // Generar un nombre único para la imagen
                    $new_filename = uniqid() . '.' . $ext;
                    $upload_dir = '../../uploads/profiles/';
                    
                    // Crear el directorio si no existe
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    // Mover la imagen
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $upload_dir . $new_filename)) {
                        // Si hay una imagen anterior y no es la default, eliminarla
                        if (!empty($cliente['imagen']) && $cliente['imagen'] != 'default.png' && file_exists($upload_dir . $cliente['imagen'])) {
                            unlink($upload_dir . $cliente['imagen']);
                        }
                        
                        $imagen = $new_filename;
                    } else {
                        $error_msg = 'Error al subir la imagen.';
                    }
                }
            }
            
            if (empty($error_msg)) {
                try {
                    // Preparar la consulta SQL
                    if (!empty($password)) {
                        // Si se proporcionó una nueva contraseña, actualizarla también
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        $query_update = "UPDATE clientes SET 
                                        nombre = ?, 
                                        email = ?, 
                                        telefono = ?, 
                                        direccion = ?, 
                                        password = ?,
                                        imagen = ? 
                                        WHERE id = ?";
                        $stmt_update = $pdo->prepare($query_update);
                        $stmt_update->execute([$nombre, $email, $telefono, $direccion, $password_hash, $imagen, $cliente_id]);
                    } else {
                        // Si no se proporcionó una nueva contraseña, mantener la actual
                        $query_update = "UPDATE clientes SET 
                                        nombre = ?, 
                                        email = ?, 
                                        telefono = ?, 
                                        direccion = ?, 
                                        imagen = ? 
                                        WHERE id = ?";
                        $stmt_update = $pdo->prepare($query_update);
                        $stmt_update->execute([$nombre, $email, $telefono, $direccion, $imagen, $cliente_id]);
                    }
                    
                    // Redirigir a la página de perfil con mensaje de éxito
                    header('Location: mi_perfil.php?success=1');
                    exit;
                } catch (PDOException $e) {
                    $error_msg = 'Error al actualizar los datos: ' . $e->getMessage();
                }
            }
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
                        <h4 class="mb-0">Editar Mi Perfil</h4>
                        <a href="mi_perfil.php" class="btn btn-light">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error_msg)): ?>
                            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                        <?php endif; ?>
                        
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-4 text-center mb-4">
                                    <div class="profile-img-container mb-3">
                                        <?php if(!empty($cliente['imagen']) && $cliente['imagen'] != 'default.png'): ?>
                                            <img src="../../uploads/profiles/<?php echo $cliente['imagen']; ?>" 
                                                 alt="Perfil" 
                                                 class="img-fluid rounded-circle profile-image"
                                                 id="preview"
                                                 style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #121a35;">
                                        <?php else: ?>
                                            <img src="../../uploads/profiles/default.png" 
                                                 alt="Perfil" 
                                                 class="img-fluid rounded-circle profile-image"
                                                 id="preview"
                                                 style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #121a35;">
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label for="imagen" class="form-label">Cambiar Imagen de Perfil</label>
                                        <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*" onchange="previewImage(this)">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nombre" class="form-label">Nombre Completo</label>
                                                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Correo Electrónico</label>
                                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($cliente['email']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="identificacion" class="form-label">Identificación (No editable)</label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($cliente['identificacion']); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="telefono" class="form-label">Teléfono</label>
                                                <input type="tel" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono']); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label for="direccion" class="form-label">Dirección</label>
                                                <textarea class="form-control" id="direccion" name="direccion" rows="3"><?php echo htmlspecialchars($cliente['direccion']); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="password" class="form-label">Nueva Contraseña (dejar en blanco para mantener la actual)</label>
                                                <input type="password" class="form-control" id="password" name="password">
                                                <small class="form-text text-muted">Mínimo 6 caracteres</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="password_confirm" class="form-label">Confirmar Nueva Contraseña</label>
                                                <input type="password" class="form-control" id="password_confirm" name="password_confirm">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                <a href="mi_perfil.php" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-image {
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transition: transform 0.3s;
    }
    
    .profile-image:hover {
        transform: scale(1.05);
    }
    
    @media (prefers-color-scheme: dark) {
        .card {
            background-color: #2c3e50;
            color: white;
        }
        
        .form-control {
            background-color: #34495e;
            color: white;
            border-color: #4a5568;
        }
        
        .form-control:focus {
            background-color: #34495e;
            color: white;
        }
        
        .text-muted {
            color: #a0aec0 !important;
        }
    }
    
    /* Support for Bootstrap 5 dark mode */
    [data-bs-theme="dark"] .card,
    .dark-mode .card {
        background-color: #2c3e50;
        color: white;
    }
    
    [data-bs-theme="dark"] .form-control,
    .dark-mode .form-control {
        background-color: #34495e;
        color: white;
        border-color: #4a5568;
    }
</style>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>