<?php include 'include/auth.php'; ?>
<?php include 'include/header1.php'; ?>

    <div class="register-container">
        <h2>Registro de Cliente</h2>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="profile-pic-container">
                <img id="profilePicPreview" src="uploads/profiles/default.png" alt="Foto de perfil">
                <label for="profilePicInput">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                    </svg>
                </label>
                <input type="file" id="profilePicInput" name="imagen" accept="image/*">
            </div>

            <input type="text" name="nombre" placeholder="Nombre completo" required>
            
            <input type="email" name="email" placeholder="Correo electrónico" required 
                class="<?php echo (isset($_GET['error']) && $_GET['error'] === 'email_duplicado') ? 'input-error' : ''; ?>">
            <?php if (isset($_GET['error']) && $_GET['error'] === 'email_duplicado'): ?>
                <div class="field-error-message">¡Este correo electrónico ya existe!</div>
            <?php endif; ?>
            
            <input type="password" name="password" id="password" placeholder="Contraseña" required
                class="<?php echo (isset($_GET['error']) && $_GET['error'] === 'password_invalid') ? 'input-error' : ''; ?>">
            <?php if (isset($_GET['error']) && $_GET['error'] === 'password_invalid'): ?>
                <div class="field-error-message">¡La contraseña no cumple con los requisitos!</div>
            <?php else: ?>
                <div class="password-requirements">
                    La contraseña debe tener al menos 8 caracteres, una letra mayúscula, una minúscula y un número
                </div>
            <?php endif; ?>
            
            <input type="text" name="identificacion" placeholder="DNI/Cédula" required
                class="<?php echo (isset($_GET['error']) && $_GET['error'] === 'identificacion_duplicada') ? 'input-error' : ''; ?>">
            <?php if (isset($_GET['error']) && $_GET['error'] === 'identificacion_duplicada'): ?>
                <div class="field-error-message">¡Este número de Cedula ya existe!</div>
            <?php endif; ?>
            
            <input type="text" name="direccion" placeholder="Dirección" required>
            
            <input type="text" name="telefono" placeholder="Teléfono" required
                class="<?php echo (isset($_GET['error']) && $_GET['error'] === 'telefono_duplicado') ? 'input-error' : ''; ?>">
            <?php if (isset($_GET['error']) && $_GET['error'] === 'telefono_duplicado'): ?>
                <div class="field-error-message">¡Este número de teléfono ya existe!</div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error']) && in_array($_GET['error'], ['extension_invalida', 'subida_foto', 'duplicado', 'general'])): ?>
                <div class="error-banner">
                    <?php if ($_GET['error'] === 'extension_invalida'): ?>
                        ¡Tipo de archivo no permitido para la imagen!
                    <?php elseif ($_GET['error'] === 'subida_foto'): ?>
                        ¡Error al subir la foto de perfil! <?php echo isset($_GET['code']) ? "Código: " . $_GET['code'] : ""; ?>
                    <?php elseif ($_GET['error'] === 'duplicado'): ?>
                        ¡Uno de los datos ingresados ya existe!
                    <?php else: ?>
                        ¡Error en el registro!
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <button type="submit">Registrarse</button>
        </form>
        <p class="login-link">¿Ya tienes cuenta? <a href="index.php">Inicia sesión aquí</a></p>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function(event) {
            const password = document.getElementById('password').value;
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
            
            if (!passwordRegex.test(password)) {
                event.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres, una letra mayúscula, una minúscula y un número');
                document.getElementById('password').style.borderColor = '#e74c3c';
                return false;
            }
            
            const inputs = this.querySelectorAll('input');
            inputs.forEach(input => {
                if (!input.value.trim() && input.type !== 'file') {
                    input.style.borderColor = '#e74c3c';
                } else {
                    input.style.borderColor = '#ddd';
                }
            });
        });

        const profilePicInput = document.getElementById('profilePicInput');
        const profilePicPreview = document.getElementById('profilePicPreview');

        profilePicInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePicPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
