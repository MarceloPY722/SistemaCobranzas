<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'sistema_cobranzas';

try {
    $conn = new mysqli($host, $user, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>