<?php 
session_start();
require_once '../cnx.php';

// Existing auth check
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../index.php');
    exit;
}

// Add activity update here
$stmt = $conn->prepare("UPDATE usuarios SET last_activity = NOW() WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);

include '../../inc/sidebar.php';

?>
<div class="content-wrapper">
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-custom text-white">
                <h4 class="mb-0">Registro de Nuevo Cliente</h4>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                        switch ($_GET['error']) {
                            case 'campos_vacios':
                                echo 'Por favor complete todos los campos requeridos.';
                                break;
                            case 'email_duplicado':
                                echo 'El correo electrónico ya está registrado.';
                                break;
                            case 'identificacion_duplicada':
                                echo 'La identificación ya está registrada.';
                                break;
                            case 'telefono_duplicado':
                                echo 'El número de teléfono ya está registrado.';
                                break;
                            case 'extension_invalida':
                                echo 'El formato de imagen no es válido. Use JPG, JPEG, PNG o GIF.';
                                break;
                            case 'subida_foto':
                                echo 'Error al subir la imagen. ' . (isset($_GET['code']) ? 'Código: ' . $_GET['code'] : '');
                                break;
                            case 'db_error':
                                echo 'Error en la base de datos: ' . (isset($_GET['message']) ? $_GET['message'] : 'Error desconocido');
                                break;
                            default:
                                echo 'Ha ocurrido un error. Por favor intente nuevamente.';
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <form action="procesar_cliente.php" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small class="form-text text-muted">La contraseña debe tener al menos 8 caracteres.</small>
                            </div>
                            <div class="mb-3">
                                <label for="identificacion" class="form-label">Cedula</label>
                                <input type="text" class="form-control" id="identificacion" name="identificacion" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <textarea class="form-control" id="direccion" name="direccion" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono">
                            </div>
                            <div class="mb-3">
                                <label for="imagen" class="form-label">Imagen de Perfil</label>
                                <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*" onchange="previewImage(this)">
                                <div class="mt-2">
                                    <img id="preview" src="#" alt="Vista previa" style="display: none; max-width: 200px; max-height: 200px; border-radius: 50%;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary custom-primary">Registrar Cliente</button>
                        <button type="reset" class="btn btn-secondary">Limpiar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.content-wrapper {
    margin-left: 250px;
    padding: 20px;
}

.card {
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    border-radius: 10px;
}

.card-header {
    border-radius: 10px 10px 0 0;
}

.bg-custom {
    background-color: #121a35 !important;
}

.form-label {
    font-weight: 500;
}

.form-control:focus {
    border-color: #121a35;
    box-shadow: 0 0 0 0.2rem rgba(18, 26, 53, 0.25);
}

.btn-primary.custom-primary {
    background-color: #121a35;
    border-color: #121a35;
}

.btn-primary.custom-primary:hover {
    background-color: #1a2547;
    border-color: #1a2547;
}

.btn {
    padding: 8px 20px;
    margin: 0 5px;
}
</style>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Add this before closing body tag -->
<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>