<?php
session_start();
require 'bd/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos del formulario
    $nombre         = $_POST['nombre'];
    $email          = $_POST['email'];
    $password       = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $identificacion = $_POST['identificacion'];
    $direccion      = $_POST['direccion'];
    $telefono       = $_POST['telefono'];
    $newFileName    = 'default.png'; // Valor por defecto

    // Procesar la imagen de perfil
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['imagen']['tmp_name'];
        $fileName    = $_FILES['imagen']['name'];
        $fileSize    = $_FILES['imagen']['size'];
        $fileType    = $_FILES['imagen']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Validar extensión
        $allowedExts = array('jpg', 'jpeg', 'png', 'gif');
        if (!in_array($fileExtension, $allowedExts)) {
            header('Location: signup.php?error=extension_invalida');
            exit();
        }

        // Generar nombre único
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = $_SERVER['DOCUMENT_ROOT'] . '/admin/img/';

        // Asegurarse de que el directorio existe
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0775, true);
        }

        $dest_path = $uploadFileDir . $newFileName;

        // Depuración
        error_log("Intentando mover archivo a: " . $dest_path);

        if (!move_uploaded_file($fileTmpPath, $dest_path)) {
            error_log("Fallo al mover el archivo: " . $fileTmpPath . " a " . $dest_path);
            header('Location: signup.php?error=subida_foto');
            exit();
        }
    } elseif (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Manejar otros errores de subida
        $errorCode = $_FILES['imagen']['error'];
        header('Location: signup.php?error=subida_foto&code=' . $errorCode);
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Insertar usuario (rol Cliente)
        $stmt = $pdo->prepare("INSERT INTO usuarios (rol_id, nombre, email, password, imagen) VALUES (3, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $email, $password, $newFileName]);
        $usuario_id = $pdo->lastInsertId();

        // Insertar cliente
        $stmt = $pdo->prepare("INSERT INTO clientes (usuario_id, nombre, identificacion, direccion, telefono, email) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$usuario_id, $nombre, $identificacion, $direccion, $telefono, $email]);

        $pdo->commit();

        // Iniciar sesión y redirigir
        $_SESSION['usuario_id'] = $usuario_id;
        $_SESSION['rol_id'] = 3;
        header('Location: cliente/index.php?success=1');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->errorInfo[1] == 1062) {
            header('Location: signup.php?error=email_duplicado');
        } else {
            error_log("Error en la base de datos: " . $e->getMessage());
            header('Location: signup.php?error=general');
        }
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro Cliente</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #007bff, #6dd5fa);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .register-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
            position: relative;
        }
        .register-container h2 {
            margin-bottom: 25px;
            color: #2c3e50;
            font-size: 28px;
            font-weight: 600;
        }
        .register-container input[type="text"],
        .register-container input[type="email"],
        .register-container input[type="password"] {
            width: 100%;
            padding: 14px;
            margin: 10px 0;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .register-container input[type="text"]:focus,
        .register-container input[type="email"]:focus,
        .register-container input[type="password"]:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.2);
            outline: none;
        }
        .register-container button {
            width: 100%;
            padding: 14px;
            margin-top: 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .register-container button:hover {
            background-color: #0056b3;
            transform: scale(1.02);
        }
        .error-message {
            color: #e74c3c;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .login-link {
            margin-top: 25px;
            font-size: 15px;
            color: #34495e;
        }
        .login-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .profile-pic-container {
            position: relative;
            margin: 20px auto;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid #007bff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .profile-pic-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .profile-pic-container label {
            position: absolute;
            bottom: 0;
            right: 0;
            background-color: #007bff;
            color: white;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
        }
        .profile-pic-container label:hover {
            background-color: #0056b3;
        }
        .profile-pic-container input[type="file"] {
            display: none;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Registro de Cliente</h2>
        <?php if (isset($_GET['error'])): ?>
            <?php if ($_GET['error'] === 'email_duplicado'): ?>
                <p class="error-message">El correo ya está registrado</p>
            <?php elseif ($_GET['error'] === 'extension_invalida'): ?>
                <p class="error-message">Tipo de archivo no permitido para la imagen.</p>
            <?php elseif ($_GET['error'] === 'subida_foto'): ?>
                <p class="error-message">Error al subir la foto de perfil. <?php echo isset($_GET['code']) ? "Código: " . $_GET['code'] : ""; ?></p>
            <?php else: ?>
                <p class="error-message">Error en el registro</p>
            <?php endif; ?>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="profile-pic-container">
                <img id="profilePicPreview" src="https://via.placeholder.com/120" alt="Foto de perfil">
                <label for="profilePicInput">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                    </svg>
                </label>
                <input type="file" id="profilePicInput" name="imagen" accept="image/*">
            </div>

            <input type="text" name="nombre" placeholder="Nombre completo" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="text" name="identificacion" placeholder="DNI/Cédula" required>
            <input type="text" name="direccion" placeholder="Dirección" required>
            <input type="text" name="telefono" placeholder="Teléfono" required>
            <button type="submit">Registrarse</button>
        </form>
        <p class="login-link">¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function() {
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
</body>
</html>