<?php
require_once '../../../admin/include/cnx.php';
$conn = $pdo;

// Verificar si se recibieron los datos del formulario
if (!isset($_POST['usuario_id']) || !isset($_POST['nombre']) || !isset($_POST['email'])) {
    header('Location: ver_usuarios.php?error=datos_incompletos');
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
    header('Location: editar_usuario.php?id=' . $usuario_id . '&error=datos_incompletos');
    exit();
}

// Obtener información actual del usuario
$query = "SELECT imagen, email FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bindParam(1, $usuario_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    header('Location: ver_usuarios.php?error=usuario_no_encontrado');
    exit();
}

$usuario_actual = $stmt->fetch(PDO::FETCH_ASSOC);
$imagen_actual = $usuario_actual['imagen'];

// Verificar si el email ya existe para otro usuario
if ($email !== $usuario_actual['email']) {
    $check_email = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bindParam(1, $email, PDO::PARAM_STR);
    $stmt->bindParam(2, $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        header('Location: editar_usuario.php?id=' . $usuario_id . '&error=email_duplicado');
        exit();
    }
}

// Iniciar transacción
$conn->beginTransaction();

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
    
    // Preparar la consulta SQL para actualizar el usuario
    if (!empty($password)) {
        // Si se proporcionó una nueva contraseña, actualizarla también
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
        $stmt->bindParam(1, $nombre, PDO::PARAM_STR);
        $stmt->bindParam(2, $email, PDO::PARAM_STR);
        $stmt->bindParam(3, $rol_id, PDO::PARAM_INT);
        $stmt->bindParam(4, $activo, PDO::PARAM_INT);
        $stmt->bindParam(5, $hashed_password, PDO::PARAM_STR);
        $stmt->bindParam(6, $imagen_nombre, PDO::PARAM_STR);
        $stmt->bindParam(7, $usuario_id, PDO::PARAM_INT);
    } else {
        // Si no se proporcionó una nueva contraseña, mantener la actual
        $update_query = "UPDATE usuarios SET 
                        nombre = ?, 
                        email = ?, 
                        rol_id = ?, 
                        activo = ?, 
                        imagen = ? 
                        WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bindParam(1, $nombre, PDO::PARAM_STR);
        $stmt->bindParam(2, $email, PDO::PARAM_STR);
        $stmt->bindParam(3, $rol_id, PDO::PARAM_INT);
        $stmt->bindParam(4, $activo, PDO::PARAM_INT);
        $stmt->bindParam(5, $imagen_nombre, PDO::PARAM_STR);
        $stmt->bindParam(6, $usuario_id, PDO::PARAM_INT);
    }
    
    // Ejecutar la consulta
    $stmt->execute();
    
    // Confirmar la transacción
    $conn->commit();
    
    // Redirigir con mensaje de éxito
    header('Location: editar_usuario.php?id=' . $usuario_id . '&success=1');
    exit();
    
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $conn->rollBack();
    
    // Redirigir con mensaje de error
    header('Location: editar_usuario.php?id=' . $usuario_id . '&error=' . $e->getMessage());
    exit();
}
?>