<?php
// Incluir archivo de conexión a la base de datos
include 'inc/cnx.php';

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger datos del formulario
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; // Se hará hash más adelante
    $rol_id = intval($_POST['rol_id']);
    $activo = intval($_POST['activo']);
    
    // Hash de la contraseña
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Imagen por defecto
    $imagen = "default.png";
    
    // Procesar la imagen si se ha subido
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
        $filename = $_FILES["imagen"]["name"];
        $filetype = $_FILES["imagen"]["type"];
        $filesize = $_FILES["imagen"]["size"];
        
        // Verificar extensión del archivo
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            die("Error: Por favor seleccione un formato de archivo válido.");
        }
        
        // Verificar tamaño del archivo - 5MB máximo
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            die("Error: El tamaño del archivo es mayor que el permitido.");
        }
        
        // Verificar tipo MIME
        if (in_array($filetype, $allowed)) {
            // Generar un nombre único para la imagen
            $imagen = md5(uniqid()) . "." . $ext;
            
            // Directorio de destino
            $target_dir = "../../../uploads/usuarios/";
            
            // Crear directorio si no existe
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            // Ruta completa del archivo
            $target_file = $target_dir . $imagen;
            
            // Mover el archivo subido al directorio de destino
            if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
                // Archivo subido correctamente
            } else {
                die("Error: Hubo un problema al subir el archivo.");
            }
        } else {
            die("Error: Hay un problema con el formato del archivo.");
        }
    }
    
    // Verificar si el correo electrónico ya existe
    $check_email = "SELECT id FROM usuarios WHERE email = '$email'";
    $result = $conn->query($check_email);
    
    if ($result->num_rows > 0) {
        die("Error: El correo electrónico ya está registrado.");
    }
    
    // Insertar usuario en la base de datos
    $sql = "INSERT INTO usuarios (rol_id, nombre, email, password, activo, imagen) 
            VALUES ('$rol_id', '$nombre', '$email', '$hashed_password', '$activo', '$imagen')";
    
    if ($conn->query($sql) === TRUE) {
        // Redirigir a la página de lista de usuarios con mensaje de éxito
        header("Location: ver_usuarios.php?success=1");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    
    $conn->close();
}
?>