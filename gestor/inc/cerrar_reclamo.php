<?php
session_start();
require_once 'cnx.php'; // Asegúrate de que este archivo contenga la conexión a tu base de datos

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

// Procesar la solicitud POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reclamo_id = intval($_POST['reclamo_id']); // Obtener el ID del reclamo

    // Actualizar el estado del reclamo a "cerrado"
    $stmt = $pdo->prepare("UPDATE reclamos SET estado = 'cerrado' WHERE id = ?");
    $stmt->execute([$reclamo_id]);

    // Redirigir al index después de cerrar el reclamo
    header("Location: ../index.php");
    exit;
    header("Location: responder_reclamo.php?id=$reclamo_id");
    exit;
}
?>