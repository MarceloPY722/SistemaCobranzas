<?php include '../../../admin/include/sidebar.php'; ?>

<?php
require_once '../../../admin/include/cnx.php';
$conn = $pdo;

// Verificar si se proporcionó un ID de usuario
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ver_usuarios.php?error=id_invalido');
    exit();
}

$usuario_id = $_GET['id'];

// Consulta para obtener los datos del usuario
$query = "SELECT u.*, r.nombre as rol_nombre 
          FROM usuarios u 
          JOIN roles r ON u.rol_id = r.id 
          WHERE u.id = ?";
$stmt = $conn->prepare($query);
$stmt->bindParam(1, $usuario_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    header('Location: ver_usuarios.php?error=usuario_no_encontrado');
    exit();
}

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Consulta para obtener todos los roles disponibles
$query_roles = "SELECT * FROM roles ORDER BY nombre";
$stmt_roles = $conn->prepare($query_roles);
$stmt_roles->execute();
$roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Contenido principal -->
<div class="content-wrapper">
    <div class="container mt-4">
        <?php if(isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>¡Éxito!</strong> Los datos del usuario han sido actualizados correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>¡Error!</strong> 
                <?php 
                    switch($_GET['error']) {
                        case 'datos_incompletos':
                            echo "Por favor complete todos los campos requeridos.";
                            break;
                        case 'email_duplicado':
                            echo "El correo electrónico ya está registrado por otro usuario.";
                            break;
                        case 'imagen_invalida':
                            echo "El formato de la imagen no es válido. Use JPG, PNG o GIF.";
                            break;
                        case 'imagen_tamano':
                            echo "La imagen excede el tamaño máximo permitido (2MB).";
                            break;
                        default:
                            echo "Ocurrió un error al actualizar los datos.";
                    }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Editar Usuario</h4>
                <a href="ver_usuarios.php" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
            <div class="card-body">
                <form action="procesar_edicion.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                    
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <div class="mb-3">
                                <label class="form-label">Imagen de Perfil Actual</label>
                                <div class="d-flex justify-content-center">
                                    <?php if(!empty($usuario['imagen']) && $usuario['imagen'] != 'default.png'): ?>
                                        <img src="/sistemacobranzas/uploads/usuarios/<?php echo htmlspecialchars($usuario['imagen']); ?>" 
                                             alt="Perfil" 
                                             class="img-thumbnail profile-image-large"
                                             style="width: 150px; height: 150px; object-fit: cover;">
                                    <?php else: ?>
                                        <img src="/sistemacobranzas/uploads/profiles/default.png" 
                                             alt="Perfil" 
                                             class="img-thumbnail profile-image-large"
                                             style="width: 150px; height: 150px; object-fit: cover;">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="imagen" class="form-label">Cambiar Imagen</label>
                                <input type="file" class="form-control" id="imagen" name="imagen">
                                <div class="form-text">Formatos permitidos: JPG, PNG, GIF. Máximo 2MB.</div>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre Completo</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="rol_id" class="form-label">Rol</label>
                                    <select class="form-select" id="rol_id" name="rol_id" required>
                                        <?php foreach($roles as $rol): ?>
                                            <option value="<?php echo $rol['id']; ?>" <?php echo ($usuario['rol_id'] == $rol['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($rol['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="activo" class="form-label">Estado</label>
                                    <select class="form-select" id="activo" name="activo" required>
                                        <option value="1" <?php echo ($usuario['activo'] == 1) ? 'selected' : ''; ?>>Activo</option>
                                        <option value="0" <?php echo ($usuario['activo'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Nueva Contraseña (dejar en blanco para mantener la actual)</label>
                                <input type="password" class="form-control" id="password" name="password">
                                <div class="form-text">Mínimo 8 caracteres. Incluya letras y números para mayor seguridad.</div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <button type="button" class="btn btn-secondary me-md-2" onclick="window.location.href='ver_usuarios.php'">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </div>
                    </div>
                </form>
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
    .bg-custom {
        background-color: #121a35;
    }
    .profile-image-large {
        object-fit: cover;
        border: 3px solid #121a35;
    }
    
    /* Estilos específicos para modo oscuro */
    body.dark-mode .card {
        background-color: #2c3e50;
        color: #fff;
    }
    body.dark-mode .form-control,
    body.dark-mode .form-select {
        background-color: #34495e;
        color: #fff;
        border-color: #4a6285;
    }
    body.dark-mode .form-text {
        color: #bdc3c7;
    }
</style>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>