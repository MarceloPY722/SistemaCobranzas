<?php
if (PHP_SESSION_ACTIVE !== session_status()) {
    session_start();
}
$page_title = "Bienvenido";

if (isset($_SESSION['cliente_id'])) {
    require_once __DIR__ . '/cnx.php';
    try {
        $cliente_id = $_SESSION['cliente_id'];
        $query = "SELECT nombre FROM clientes WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$cliente_id]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($cliente && !empty($cliente['nombre'])) {
            $nombre_cliente = htmlspecialchars($cliente['nombre']);
            $page_title .= " " . $nombre_cliente;
        }
    } catch (PDOException $e) {
        error_log("Database error fetching client name: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="shortcut icon" href="/sistemacobranzas/img/experto.png" type="image/x-icon">
</head>
<body>