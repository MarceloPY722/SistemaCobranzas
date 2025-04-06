<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $code = $_POST['code'];
    
    // Update the verification code in the session
    $_SESSION['verification_code'] = $code;
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Código de verificación actualizado.'
    ]);
} else {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Solicitud inválida.'
    ]);
}