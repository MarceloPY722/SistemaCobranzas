<?php
// Start or resume session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Not logged in, redirect to login page
    $_SESSION['error_message'] = "Por favor inicie sesión para acceder.";
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'cnx.php';

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Check if user has the correct role (Gestor de Cobranzas, role_id = 2)
$query = "SELECT rol_id FROM usuarios WHERE id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    // User not found in database
    $_SESSION['error_message'] = "Usuario no encontrado. Por favor inicie sesión nuevamente.";
    session_destroy();
    header("Location: login.php");
    exit;
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['rol_id'] != 2) {
    // User does not have the correct role
    $_SESSION['error_message'] = "Acceso denegado. Usted no tiene el rol de Gestor de Cobranzas.";
    header("Location: unauthorized.php");
    exit;
}

// If we reached here, user is authenticated and authorized
?>

