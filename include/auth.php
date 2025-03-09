<?php
session_start();
require 'bd/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos del formulario
    $nombre         = $_POST['nombre'];
    $email          = $_POST['email'];
    $identificacion = $_POST['identificacion'];
    $direccion      = $_POST['direccion'];
    $telefono       = $_POST['telefono'];
    $password       = $_POST['password'];
    $newFileName    = 'default.png'; // Valor por defecto
    
    // Validar contraseña
    if (strlen($password) < 8 || 
        !preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || 
        !preg_match('/[0-9]/', $password)) {
        header('Location: signup.php?error=password_invalid');
        exit();
    }
    
    // Verificar duplicados en la base de datos
    // Verificar email
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        header('Location: signup.php?error=email_duplicado');
        exit();
    }
    
    // Verificar identificación
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE identificacion = ?");
    $stmt->execute([$identificacion]);
    if ($stmt->fetchColumn() > 0) {
        header('Location: signup.php?error=identificacion_duplicada');
        exit();
    }
    
    // Verificar teléfono
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE telefono = ?");
    $stmt->execute([$telefono]);
    if ($stmt->fetchColumn() > 0) {
        header('Location: signup.php?error=telefono_duplicado');
        exit();
    }
    
    // Hash de la contraseña después de validarla
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Procesar la imagen de perfil
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['imagen']['tmp_name'];
        $fileName    = $_FILES['imagen']['name'];
        $fileSize    = $_FILES['imagen']['size'];
        $fileType    = $_FILES['imagen']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedExts = array('jpg', 'jpeg', 'png', 'gif');
        if (!in_array($fileExtension, $allowedExts)) {
            header('Location: signup.php?error=extension_invalida');
            exit();
        }

        $newFileName = $fileName;
        $uploadFileDir = $_SERVER['DOCUMENT_ROOT'] . '/sistemacobranzas/uploads/profiles/';
        

        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0775, true);
        }
        
        $dest_path = $uploadFileDir . $newFileName;

        // If file already exists, append number
        $counter = 1;
        while (file_exists($dest_path)) {
            $fileNameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
            $newFileName = $fileNameWithoutExt . '_' . $counter . '.' . $fileExtension;
            $dest_path = $uploadFileDir . $newFileName;
            $counter++;
        }

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
        $stmt->execute([$nombre, $email, $password_hash, $newFileName]);
        $usuario_id = $pdo->lastInsertId();

        // Insertar cliente - Modificado para incluir la imagen
        $stmt = $pdo->prepare("INSERT INTO clientes (usuario_id, nombre, identificacion, direccion, telefono, email, imagen) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$usuario_id, $nombre, $identificacion, $direccion, $telefono, $email, $newFileName]);

        $pdo->commit();

        $_SESSION['usuario_id'] = $usuario_id;
        $_SESSION['rol_id'] = 3;
        header('Location: cliente/index.php?success=1');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->errorInfo[1] == 1062) {
            header('Location: signup.php?error=duplicado');
        } else {
            error_log("Error en la base de datos: " . $e->getMessage());
            header('Location: signup.php?error=general');
        }
        exit();
    }
}
?>