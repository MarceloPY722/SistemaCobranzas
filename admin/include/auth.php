<?php
function check_auth($required_role) {
    
    session_start();

    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        $_SESSION['error_message'] = "Por favor inicie sesión para acceder.";
        header("Location: ../login.php");
        exit;
    }

   
    require_once 'cnx.php';

    $user_id = $_SESSION['user_id'];

    $query = "SELECT rol_id FROM usuarios WHERE id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        
        $_SESSION['error_message'] = "Usuario no encontrado. Por favor inicie sesión nuevamente.";
        session_destroy();
        header("Location: ./index.php");
        exit;
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user['rol_id'] != $required_role) {
        
        $_SESSION['error_message'] = "Acceso denegado. No tiene el rol requerido.";
        header("Location: ../error.php");
        exit;
    }

}
?>