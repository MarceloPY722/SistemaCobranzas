<?php
session_start();
require_once 'inc/cnx.php';

// Updated validation
if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2 || $_SESSION['role'] !== 'Gestor de Cobranzas') {
    header("Location: ../index.php");
    exit();
}

// Obtener información del usuario
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT u.*, r.nombre as rol_nombre 
                       FROM usuarios u 
                       JOIN roles r ON u.rol_id = r.id 
                       WHERE u.id = ? AND r.id = 2");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: ../index.php");
    exit();
}

// Obtener estadísticas
$stats = [
    'clientes' => $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn(),
    'deudas_pendientes' => $pdo->query("SELECT COUNT(*) FROM deudas WHERE estado = 'pendiente'")->fetchColumn(),
    'pagos_hoy' => $pdo->query("SELECT COUNT(*) FROM pagos WHERE DATE(fecha_pago) = CURDATE()")->fetchColumn(),
    'reclamos_abiertos' => $pdo->query("SELECT COUNT(*) FROM reclamos WHERE estado = 'abierto'")->fetchColumn()
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Gestor</title>
    <?php include('inc/header.php'); ?>
    <style>
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
            margin-left: 270px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            margin: 0;
            color: #333;
            font-size: 1.1em;
        }
        .stat-card .number {
            font-size: 2em;
            color: #764AF1;
            font-weight: bold;
            margin: 10px 0;
        }
        .welcome-section {
            margin-left: 270px;
            padding: 20px;
        }
        .welcome-section h1 {
            color: #333;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include('inc/sidebar.php'); ?>

    <div class="welcome-section">
        <h1>Bienvenido, <?php echo htmlspecialchars($user['nombre']); ?></h1>
        <p>Panel de Control - Gestor de Cobranzas</p>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <h3>Total Clientes</h3>
            <div class="number"><?php echo $stats['clientes']; ?></div>
        </div>
        
        <div class="stat-card">
            <h3>Deudas Pendientes</h3>
            <div class="number"><?php echo $stats['deudas_pendientes']; ?></div>
        </div>
        
        <div class="stat-card">
            <h3>Pagos de Hoy</h3>
            <div class="number"><?php echo $stats['pagos_hoy']; ?></div>
        </div>
        
        <div class="stat-card">
            <h3>Reclamos Abiertos</h3>
            <div class="number"><?php echo $stats['reclamos_abiertos']; ?></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>