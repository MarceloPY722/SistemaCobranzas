<?php

$host = 'localhost';
$dbname = 'sistema_cobranzas';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("
        SELECT DATE(created_at) as join_date, COUNT(*) as count 
        FROM clientes 
        GROUP BY DATE(created_at) 
        ORDER BY join_date
    ");
    $growth_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("
        SELECT r.nombre, COUNT(u.id) as count
        FROM roles r
        LEFT JOIN usuarios u ON r.id = u.rol_id
        GROUP BY r.id, r.nombre
    ");
    $roles_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dates = array_column($growth_data, 'join_date');
    $counts = array_column($growth_data, 'count');

    echo json_encode([
        'dates' => $dates,
        'counts' => $counts,
        'roles' => $roles_data
    ]);

} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>