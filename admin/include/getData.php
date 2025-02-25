<?php
// getData.php
header('Content-Type: application/json');
require_once 'cnx.php'; // Incluimos la conexión

try {
    // Ajusta el nombre de tus tablas
    $stmtClients = $pdo->query('SELECT COUNT(*) as count FROM clientes');
    $clients = $stmtClients->fetch()['count'];

    $stmtUsers = $pdo->query('SELECT COUNT(*) as count FROM usuarios');
    $users = $stmtUsers->fetch()['count'];

    echo json_encode([
      'clients' => (int)$clients,
      'users'   => (int)$users
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
