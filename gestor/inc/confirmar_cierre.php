<?php
session_start();
require_once 'cnx.php';

if (!isset($_SESSION['user_id'])) {
    die('Acceso no autorizado');
}

$claim_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($claim_id == 0) {
    die('ID de reclamo inválido');
}

// Get claim details
$stmt = $pdo->prepare("SELECT * FROM reclamos WHERE id = ?");
$stmt->execute([$claim_id]);
$claim = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$claim) {
    die('Reclamo no encontrado');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Cierre de Reclamo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0">Confirmar Cierre de Reclamo #<?= $claim_id ?></h4>
            </div>
            <div class="card-body">
                <p class="lead">¿Está seguro que desea cerrar este reclamo?</p>
                <p><strong>Asunto:</strong> <?= htmlspecialchars($claim['asunto']) ?></p>
                <p><strong>Estado actual:</strong> <?= ucfirst(str_replace('_', ' ', $claim['estado'])) ?></p>
                
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="cerrar_reclamo.php?id=<?= $claim_id ?>" class="btn btn-success">Sí, cerrar reclamo</a>
                    <button onclick="window.close()" class="btn btn-secondary">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>