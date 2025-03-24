<?php
require_once '../cnx.php';

// Verificar si se recibió una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $identificacion = $_POST['identificacion'];
    $direccion = isset($_POST['direccion']) ? $_POST['direccion'] : '';
    $telefono = isset($_POST['telefono']) ? $_POST['telefono'] : '';
    
    // Validar que los campos requeridos no estén vacíos
    if (empty($nombre) || empty($email) || empty($password) || empty($identificacion)) {
        header('Location: agregar.php?error=campos_vacios');
        exit();
    }
    
    // Verificar si el email ya existe
    $stmt = $conn->prepare("SELECT id FROM clientes WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        header('Location: agregar.php?error=email_duplicado');
        exit();
    }
    
    // Verificar si la identificación ya existe
    $stmt = $conn->prepare("SELECT id FROM clientes WHERE identificacion = ?");
    $stmt->bind_param("s", $identificacion);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        header('Location: agregar.php?error=identificacion_duplicada');
        exit();
    }
    
    // Verificar si el teléfono ya existe (si se proporcionó)
    if (!empty($telefono)) {
        $stmt = $conn->prepare("SELECT id FROM clientes WHERE telefono = ?");
        $stmt->bind_param("s", $telefono);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            header('Location: agregar.php?error=telefono_duplicado');
            exit();
        }
    }
    
    // Procesar la imagen si se subió una
    $imagen = 'default.png'; // Valor predeterminado
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['imagen']['tmp_name'];
        $file_name = $_FILES['imagen']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Verificar la extensión del archivo
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($file_ext, $allowed_exts)) {
            header('Location: agregar.php?error=extension_invalida');
            exit();
        }
        
        // Generar un nombre único para la imagen
        $new_file_name = uniqid() . '.' . $file_ext;
        $upload_path = '../../../uploads/profiles/' . $new_file_name;
        
        // Crear el directorio si no existe
        if (!file_exists('../../../uploads/profiles/')) {
            mkdir('../../../uploads/profiles/', 0777, true);
        }
        
        // Mover el archivo subido al directorio de destino
        if (move_uploaded_file($file_tmp, $upload_path)) {
            $imagen = $new_file_name;
        } else {
            header('Location: agregar.php?error=subida_foto&code=' . $_FILES['imagen']['error']);
            exit();
        }
    }
    
    // Hashear la contraseña
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar el nuevo cliente en la base de datos - including password field after identificacion
    $stmt = $conn->prepare("INSERT INTO clientes (nombre, identificacion, password, direccion, telefono, email, imagen, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssssss", $nombre, $identificacion, $hashed_password, $direccion, $telefono, $email, $imagen);
    
    if ($stmt->execute()) {
        // Redirigir a la página de clientes con un mensaje de éxito
        header('Location: ver_clientes.php?success=cliente_agregado');
        exit();
    } else {
        // Si hay un error, redirigir con un mensaje de error
        header('Location: agregar.php?error=db_error&message=' . urlencode($conn->error));
        exit();
    }
} else {
    // Si no es una solicitud POST, redirigir al formulario
    header('Location: agregar.php');
    exit();
}
?>