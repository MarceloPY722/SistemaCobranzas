<?php
session_start();
require_once 'cnx.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

$claim_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($claim_id == 0) {
    header('Location: ../sidebar/reclamos/ver_reclamos.php?error=id_invalido');
    exit;
}

try {
    // Update claim status to 'cerrado'
    $stmt = $pdo->prepare("UPDATE reclamos SET estado = 'cerrado', respondido_por = ?, fecha_respuesta = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['user_id'], $claim_id]);
    
    header('Location: ../sidebar/reclamos/ver_reclamos.php?success=reclamo_cerrado');
    exit;
} catch (Exception $e) {
    header('Location: ../sidebar/reclamos/ver_reclamos.php?error=db_error&mensaje=' . urlencode($e->getMessage()));
    exit;
}
?>