<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'sistema_cobranzas';

// Create mysqli connection
try {
    $conn = new mysqli($host, $user, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Also create PDO connection for PDO-specific queries
try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $password, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>