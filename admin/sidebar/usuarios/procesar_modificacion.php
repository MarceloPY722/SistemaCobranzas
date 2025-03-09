<?php
require_once 'inc/cnx.php';
session_start();

// Verificar si se recibieron los datos del formulario
if (!isset($_POST['usuario_id']) || !isset($_POST['nombre']) || !isset($_POST['email'])) {
    header('Location: modificar_usuarios.php?error=datos_incompletos');
    exit();
}

// Obtener los datos del formulario
$usuario_id = intval($_POST['usuario_id']);
$nombre = trim($_POST['nombre']);
$email = trim($_POST['email']);
$rol_id = intval($_POST['rol_id']);
$activo = intval($_POST['activo']);
$password = $_POST['password'] ?? '';

// Validar datos básicos
if (empty($nombre) || empty($email)) {
    header('Location: modificar_usuarios.php?id=' . $usuario_id . '&error=datos_incompletos');
    exit();
}

// Obtener información actual del usuario
$query = "SELECT imagen, email FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ver_usuarios.php?error=usuario_no_encontrado');
    exit();
}

$usuario_actual = $result->fetch_assoc();
$imagen_actual = $usuario_actual['imagen'];

// Verificar si el email ya existe para otro usuario
if ($email !== $usuario_actual['email']) {
    $check_email = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("si", $email, $usuario_id);
    $stmt->execute();
    $result_email = $stmt->get_result();
    
    if ($result_email->num_rows > 0) {
        header('Location: modificar_usuarios.php?id=' . $usuario_id . '&error=email_duplicado');
        exit();
    }
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // Procesar la imagen si se subió una nueva
    $imagen_nombre = $imagen_actual; // Por defecto mantener la imagen actual
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen_tmp = $_FILES['imagen']['tmp_name'];
        $imagen_tipo = $_FILES['imagen']['type'];
        $imagen_tamano = $_FILES['imagen']['size'];
        
        // Validar tipo de archivo
        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($imagen_tipo, $tipos_permitidos)) {
            throw new Exception('imagen_invalida');
        }
        
        // Validar tamaño (máximo 2MB)
        if ($imagen_tamano > 2 * 1024 * 1024) {
            throw new Exception('imagen_tamano');
        }
        
        // Generar nombre único para la imagen
        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $imagen_nombre = 'usuario_' . $usuario_id . '_' . time() . '.' . $extension;
        
        // Ruta de destino
        $ruta_destino = '../../../uploads/usuarios/' . $imagen_nombre;
        
        // Mover el archivo
        if (!move_uploaded_file($imagen_tmp, $ruta_destino)) {
            throw new Exception('error_subida');
        }
        
        // Si se subió una nueva imagen y la anterior no era la predeterminada, eliminar la anterior
        if ($imagen_actual != 'default.png' && file_exists('../../../uploads/usuarios/' . $imagen_actual)) {
            unlink('../../../uploads/usuarios/' . $imagen_actual);
        }
    }
    
    // Preparar la consulta SQL según si hay cambio de contraseña o no
    if (!empty($password)) {
        // Hash de la nueva contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $update_query = "UPDATE usuarios SET 
                        nombre = ?, 
                        email = ?, 
                        rol_id = ?, 
                        activo = ?, 
                        password = ?, 
                        imagen = ? 
                        WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssiissi", $nombre, $email, $rol_id, $activo, $hashed_password, $imagen_nombre, $usuario_id);
    } else {
        // Sin cambio de contraseña
        $update_query = "UPDATE usuarios SET 
                        nombre = ?, 
                        email = ?, 
                        rol_id = ?, 
                        activo = ?, 
                        imagen = ? 
                        WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssiisi", $nombre, $email, $rol_id, $activo, $imagen_nombre, $usuario_id);
    }
    
    // Ejecutar la actualización
    $stmt->execute();
    
    if ($stmt->affected_rows === 0 && $stmt->errno !== 0) {
        throw new Exception('error_actualizacion');
    }
    
    // Confirmar la transacción
    $conn->commit();
    
    // Redirigir con mensaje de éxito
    header('Location: modificar_usuarios.php?id=' . $usuario_id . '&success=1');
    exit();
    
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $conn->rollback();
    
    // Redirigir con mensaje de error
    header('Location: modificar_usuarios.php?id=' . $usuario_id . '&error=' . $e->getMessage());
    exit();
}
?>