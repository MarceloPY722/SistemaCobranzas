<?php
session_start();
require_once 'cnx.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mensaje = trim($_POST['mensaje']);
    $reclamo_id = intval($_POST['reclamo_id']); // Asegúrate de pasar el reclamo_id desde el formulario
    $user_id = $_SESSION['user_id'];

    if (!empty($mensaje)) {
        $stmt = $pdo->prepare("INSERT INTO chats (reclamo_id, emisor_id, contenido, tipo_emisor) VALUES (?, ?, ?, 'administrador')");
        $stmt->execute([$reclamo_id, $user_id, $mensaje]);
    }
    header("Location: responder_reclamo.php?id=$reclamo_id"); // Redirige de vuelta a la página
    exit;
}
?>