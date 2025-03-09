<?php
require_once 'inc/cnx.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir datos del formulario
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $identificacion = $_POST['identificacion'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    
    // Manejar la imagen
    $imagen = 'default.png';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['imagen']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $imagen_nombre = uniqid() . '_' . $filename;
            $destino = '../../uploads/profiles/' . $imagen_nombre;
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
                $imagen = $imagen_nombre;
            }
        }
    }

    try {
        $conn->begin_transaction();

        // Insertar en la tabla usuarios
        $sql_usuario = "INSERT INTO usuarios (rol_id, nombre, email, password, imagen) VALUES (3, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_usuario);
        $stmt->bind_param("ssss", $nombre, $email, $password, $imagen);
        $stmt->execute();
        
        $usuario_id = $conn->insert_id;

        // Insertar en la tabla clientes
        $sql_cliente = "INSERT INTO clientes (usuario_id, nombre, identificacion, direccion, telefono, email, imagen) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_cliente);
        $stmt->bind_param("issssss", $usuario_id, $nombre, $identificacion, $direccion, $telefono, $email, $imagen);
        $stmt->execute();

        $conn->commit();
        
        // Redirigir con mensaje de éxito
        header("Location: agregar.php?success=1");
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: agregar.php?error=1");
        exit();
    }
}
?>