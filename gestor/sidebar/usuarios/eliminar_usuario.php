<?php
// Incluir archivo de conexión a la base de datos
require_once 'inc/cnx.php';

// Verificar si se ha proporcionado un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ver_usuarios.php?error=id_invalido");
    exit();
}

$id = intval($_GET['id']);

// Evitar eliminar al usuario administrador principal (ID 1)
if ($id == 1) {
    header("Location: ver_usuarios.php?error=eliminacion_fallida&mensaje=No se puede eliminar al usuario administrador principal");
    exit();
}

// Obtener información del usuario antes de eliminarlo
$query = "SELECT nombre, imagen FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ver_usuarios.php?error=usuario_no_encontrado");
    exit();
}

$usuario = $result->fetch_assoc();
$nombre = $usuario['nombre'];
$imagen = $usuario['imagen'];

// Iniciar transacción
$conn->begin_transaction();

try {
    // Eliminar el usuario
    $delete_query = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("No se pudo eliminar el usuario.");
    }
    
    // Confirmar transacción
    $conn->commit();
    
    // Eliminar la imagen si no es la predeterminada
    if ($imagen != 'default.png') {
        $imagen_path = "../../../uploads/usuarios/" . $imagen;
        if (file_exists($imagen_path)) {
            unlink($imagen_path);
        }
    }
    
    // Redirigir con mensaje de éxito
    header("Location: ver_usuarios.php?success=1&nombre=" . urlencode($nombre));
    exit();
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    header("Location: ver_usuarios.php?error=eliminacion_fallida&mensaje=" . urlencode($e->getMessage()));
    exit();
}
?>