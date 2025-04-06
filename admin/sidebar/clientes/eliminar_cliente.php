<?php
require_once '../cnx.php';
session_start();

// Verificar si se recibió un ID válido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ver_clientes.php?error=id_invalido');
    exit();
}

$cliente_id = intval($_GET['id']);

// Obtener información del cliente antes de eliminarlo
$query_cliente = "SELECT c.nombre, c.id 
                 FROM clientes c 
                 WHERE c.id = ?";
$stmt_cliente = $conn->prepare($query_cliente);
$stmt_cliente->bind_param("i", $cliente_id);
$stmt_cliente->execute();
$result_cliente = $stmt_cliente->get_result();

if ($result_cliente->num_rows === 0) {
    header('Location: ver_clientes.php?error=cliente_no_encontrado');
    exit();
}

$cliente = $result_cliente->fetch_assoc();
$nombre_cliente = $cliente['nombre'];

// Remove this line as usuario_id doesn't exist in the query results
// $usuario_id = $cliente['usuario_id'];

// Iniciar transacción
$conn->begin_transaction();

try {
    // Primero eliminar registros relacionados en la tabla clientes
    $delete_cliente = "DELETE FROM clientes WHERE id = ?";
    $stmt = $conn->prepare($delete_cliente);
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    
    // Remove this block since we don't have usuario_id
    /*
    // Luego eliminar el usuario asociado
    $delete_usuario = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($delete_usuario);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    */
    
    // Confirmar la transacción
    $conn->commit();
    
    // Redirigir con mensaje de éxito
    header("Location: ver_clientes.php?success=1&nombre=" . urlencode($nombre_cliente));
    exit();
    
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $conn->rollback();
    
    // Redirigir con mensaje de error
    header('Location: ver_clientes.php?error=eliminacion_fallida&mensaje=' . urlencode($e->getMessage()));
    exit();
}
?>