<?php
session_start();
require 'bd/conexion.php';

header('Content-Type: application/json');

// Function to mask email
function maskEmail($email) {
    if (empty($email)) return '';
    
    $parts = explode('@', $email);
    if (count($parts) != 2) return $email;
    
    $name = $parts[0];
    $domain = $parts[1];
    
    $nameLength = strlen($name);
    $maskedName = substr($name, 0, min(4, $nameLength)) . str_repeat('*', max(4, $nameLength - 4));
    
    return $maskedName . '@' . $domain;
}

// Function to mask phone number
function maskPhone($phone) {
    if (empty($phone)) return '';
    
    $length = strlen($phone);
    if ($length <= 6) return $phone;
    
    $firstPart = substr($phone, 0, 4);
    $lastPart = substr($phone, -2);
    $middlePart = str_repeat('*', $length - 6);
    
    return $firstPart . $middlePart . $lastPart;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ci'])) {
    $ci = $_POST['ci'];
    
    // Check if CI exists in the database and get password status
    $stmt = $pdo->prepare("SELECT id, nombre, telefono, email, password FROM Clientes WHERE identificacion = ?");
    $stmt->execute([$ci]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cliente) {
        // Check if client already has a password
        $hasPassword = !empty($cliente['password']);
        
        // Store original data in session
        $_SESSION['client_data'] = $cliente;
        
        // Create masked version for display
        $clienteDisplay = [
            'id' => $cliente['id'],
            'nombre' => $cliente['nombre'],
            'telefono' => maskPhone($cliente['telefono']),
            'email' => maskEmail($cliente['email']),
            'email_original' => $cliente['email'], // Send original email for auto-fill
            'has_password' => $hasPassword // Indicate if client already has a password
        ];
        
        // Return success response with client data
        echo json_encode([
            'success' => true,
            'cliente' => $clienteDisplay
        ]);
    } else {
        // Return error response
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró ningún cliente con ese número de CI.'
        ]);
    }
} else {
    // Return error response for invalid request
    echo json_encode([
        'success' => false,
        'message' => 'Solicitud inválida.'
    ]);
}