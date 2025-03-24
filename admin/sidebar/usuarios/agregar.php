<?php include '../../../admin/include/sidebar.php'; ?>

<div class="content-wrapper">
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-custom text-white">
                <h4 class="mb-0">Registro de Nuevo Usuario</h4>
            </div>
            <div class="card-body">
                <form action="procesar_usuario.php" method="POST" enctype="multipart/form-data">
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
                            </div>
                            <div class="mb-3">
                                <label for="rol_id" class="form-label">Rol</label>
                                <select class="form-select" id="rol_id" name="rol_id" required>
                                    <option value="">Seleccione un rol</option>
                                    <?php
                                    // Conexión a la base de datos
                                    include 'inc/cnx.php';
                                    
                                    // Consulta para obtener los roles
                                    $query = "SELECT id, nombre FROM roles ORDER BY nombre";
                                    $result = $conn->query($query);
                                    
                                    // Mostrar opciones
                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo "<option value='" . $row['id'] . "'>" . $row['nombre'] . "</option>";
                                        }
                                    }
                                    $conn->close();
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="activo" class="form-label">Estado</label>
                                <select class="form-select" id="activo" name="activo">
                                    <option value="1" selected>Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="imagen" class="form-label">Imagen de Perfil</label>
                                <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*" onchange="previewImage(this)">
                                <div class="mt-2">
                                    <img id="preview" src="#" alt="Vista previa" style="display: none; max-width: 200px; max-height: 200px; border-radius: 50%;">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Permisos</label>
                                <div class="form-text mb-2">Los permisos se asignarán según el rol seleccionado.</div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary custom-primary">Registrar Usuario</button>
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

.form-control:focus, .form-select:focus {
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
