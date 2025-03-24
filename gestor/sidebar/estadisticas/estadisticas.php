<?php
session_start();
// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../index.php');
    exit;
}

// Establecer conexión a la base de datos
require_once 'cnx.php';
// Incluir sidebar después de la verificación de sesión
include '../../../admin/include/sidebar.php';

// Función para formatear montos en guaraníes
function formatMoney($amount) {
    return number_format($amount, 0, ',', '.') . ' Gs.';
}

// Obtener últimos pagos realizados
$sql_ultimos_pagos = "
    SELECT p.*, d.descripcion, c.nombre as cliente_nombre
    FROM pagos p
    JOIN deudas d ON p.deuda_id = d.id
    JOIN clientes c ON d.cliente_id = c.id
    ORDER BY p.created_at DESC
    LIMIT 10
";
$result_pagos = $conn->query($sql_ultimos_pagos);

// Obtener datos para gráfico de pagos por mes
$sql_pagos_por_mes = "
    SELECT 
        DATE_FORMAT(fecha_pago, '%Y-%m') as mes,
        SUM(monto_pagado) as total
    FROM pagos
    WHERE fecha_pago >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(fecha_pago, '%Y-%m')
    ORDER BY mes ASC
";
$result_pagos_mes = $conn->query($sql_pagos_por_mes);

$meses = [];
$totales = [];
while ($row = $result_pagos_mes->fetch_assoc()) {
    $meses[] = date('M Y', strtotime($row['mes'] . '-01'));
    $totales[] = $row['total'];
}

// Obtener datos para gráfico de métodos de pago
$sql_metodos_pago = "
    SELECT 
        metodo_pago,
        COUNT(*) as cantidad,
        SUM(monto_pagado) as total
    FROM pagos
    GROUP BY metodo_pago
";
$result_metodos = $conn->query($sql_metodos_pago);

$metodos = [];
$cantidades = [];
$colores = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'];
$i = 0;
while ($row = $result_metodos->fetch_assoc()) {
    $metodos[] = $row['metodo_pago'] ?: 'No especificado';
    $cantidades[] = $row['cantidad'];
    $i++;
}

// Obtener estadísticas de clientes
$sql_clientes_stats = "
    SELECT 
        COUNT(*) as total_clientes,
        SUM(CASE WHEN password IS NOT NULL THEN 1 ELSE 0 END) as clientes_registrados,
        SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as nuevos_clientes
    FROM clientes
";
$result_clientes = $conn->query($sql_clientes_stats);
$clientes_stats = $result_clientes->fetch_assoc();

// Obtener estadísticas de deudas
$sql_deudas_stats = "
    SELECT 
        COUNT(*) as total_deudas,
        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as deudas_pendientes,
        SUM(CASE WHEN estado = 'vencido' THEN 1 ELSE 0 END) as deudas_vencidas,
        SUM(CASE WHEN estado = 'pagado' THEN 1 ELSE 0 END) as deudas_pagadas,
        SUM(monto) as monto_total,
        SUM(saldo_pendiente) as saldo_pendiente_total
    FROM deudas
";
$result_deudas = $conn->query($sql_deudas_stats);
$deudas_stats = $result_deudas->fetch_assoc();

// Obtener datos para gráfico de estado de deudas
$estados_deudas = [
    'pendiente' => $deudas_stats['deudas_pendientes'] ?: 0,
    'vencido' => $deudas_stats['deudas_vencidas'] ?: 0,
    'pagado' => $deudas_stats['deudas_pagadas'] ?: 0
];

// Add reclamos statistics query
$sql_reclamos_stats = "
    SELECT 
        COUNT(*) as total_reclamos,
        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as reclamos_pendientes,
        SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as reclamos_en_proceso,
        SUM(CASE WHEN estado = 'resuelto' THEN 1 ELSE 0 END) as reclamos_resueltos
    FROM reclamos
";
$result_reclamos = $conn->query($sql_reclamos_stats);
$reclamos_stats = $result_reclamos->fetch_assoc();

// Define estados_reclamos array for the chart
$estados_reclamos = [
    'pendiente' => $reclamos_stats['reclamos_pendientes'] ?: 0,
    'en_proceso' => $reclamos_stats['reclamos_en_proceso'] ?: 0,
    'resuelto' => $reclamos_stats['reclamos_resueltos'] ?: 0
];

// Obtener usuarios activos
$sql_usuarios_activos = "
    SELECT u.*, r.nombre as rol_nombre
    FROM usuarios u
    JOIN roles r ON u.rol_id = r.id
    WHERE u.last_activity >= NOW() - INTERVAL 24 HOUR
    ORDER BY u.last_activity DESC
";
$result_usuarios = $conn->query($sql_usuarios_activos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas del Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Agregar referencia al CSS de modo oscuro -->
    <link rel="stylesheet" href="/sistemacobranzas/admin/assets/css/dark-mode.css">
    <style>
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background-color: #121a35;
            color: white;
            border-radius: 15px 15px 0 0 !important;
        }
        .stats-icon {
            font-size: 2rem;
            margin-right: 10px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .online-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .online {
            background-color: #28a745;
        }
        .offline {
            background-color: #dc3545;
        }
        .stat-card {
            border-left: 4px solid;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .stat-card-primary {
            border-left-color: #4e73df;
        }
        .stat-card-success {
            border-left-color: #1cc88a;
        }
        .stat-card-info {
            border-left-color: #36b9cc;
        }
        .stat-card-warning {
            border-left-color: #f6c23e;
        }
        .stat-card-danger {
            border-left-color: #e74a3b;
        }
        .stat-icon {
            font-size: 2rem;
            opacity: 0.3;
        }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        /* Estilos específicos para modo oscuro en estadísticas */
        body.dark-mode .chart-container canvas {
            filter: brightness(0.85);
        }
        
        body.dark-mode .stat-card {
            background-color: #1e2746;
        }
        
        body.dark-mode .text-muted {
            color: #a8afc7 !important;
        }
        
        body.dark-mode .table-hover tbody tr:hover {
            background-color: #2a3356;
            color: #fff;
        }
        
        body.dark-mode .alert-info {
            background-color: #2a3356;
            color: #a8afc7;
            border-color: #36b9cc;
        }
    </style>
</head>
<body>

<div class="content-wrapper">
    <div class="container-fluid py-4">
        <h2 class="text-center mb-4">Estadísticas del Sistema</h2>
        
        <!-- Tarjetas de Resumen -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100 stat-card stat-card-primary">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs text-uppercase mb-1 text-primary">Total Clientes</div>
                                <div class="h5 mb-0 font-weight-bold"><?= $clientes_stats['total_clientes'] ?></div>
                                <div class="mt-2 text-muted small"><?= $clientes_stats['nuevos_clientes'] ?> nuevos en el último mes</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people stat-icon text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100 stat-card stat-card-success">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs text-uppercase mb-1 text-success">Deudas Pagadas</div>
                                <div class="h5 mb-0 font-weight-bold"><?= $deudas_stats['deudas_pagadas'] ?: 0 ?></div>
                                <div class="mt-2 text-muted small">De un total de <?= $deudas_stats['total_deudas'] ?> deudas</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-check-circle stat-icon text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100 stat-card stat-card-warning">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs text-uppercase mb-1 text-warning">Deudas Pendientes</div>
                                <div class="h5 mb-0 font-weight-bold"><?= $deudas_stats['deudas_pendientes'] ?: 0 ?></div>
                                <div class="mt-2 text-muted small"><?= formatMoney($deudas_stats['saldo_pendiente_total']) ?> pendiente</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-clock-history stat-icon text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100 stat-card stat-card-danger">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <div class="text-xs text-uppercase mb-1 text-danger">Deudas Vencidas</div>
                                <div class="h5 mb-0 font-weight-bold"><?= $deudas_stats['deudas_vencidas'] ?: 0 ?></div>
                                <div class="mt-2 text-muted small"><?= round(($deudas_stats['deudas_vencidas'] / $deudas_stats['total_deudas']) * 100, 1) ?>% del total</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-exclamation-triangle stat-icon text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gráficos -->
        <div class="row mb-4">
            <!-- Gráfico de Pagos por Mes -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="bi bi-bar-chart-line stats-icon"></i>
                        <h5 class="mb-0">Pagos por Mes</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="pagosPorMesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gráfico de Métodos de Pago -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="bi bi-pie-chart stats-icon"></i>
                        <h5 class="mb-0">Métodos de Pago</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="metodosPagoChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <!-- Gráfico de Estado de Deudas -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="bi bi-pie-chart stats-icon"></i>
                        <h5 class="mb-0">Estado de Deudas</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="estadoDeudasChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gráfico de Clientes Registrados -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="bi bi-person-check stats-icon"></i>
                        <h5 class="mb-0">Clientes Registrados</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="clientesRegistradosChart"></canvas>
                        </div>
                        <div class="text-center mt-3">
                            <div class="small text-muted">
                                <span class="text-secondary">■</span> No registrados: <?= $clientes_stats['total_clientes'] - $clientes_stats['clientes_registrados'] ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Últimos Pagos -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="bi bi-cash stats-icon"></i>
                        <h5 class="mb-0">Últimos Pagos Realizados</h5>
                    </div>
                    <div class="card-body">
                        <?php if($result_pagos->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Descripción</th>
                                            <th>Método</th>
                                            <th>Monto</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($pago = $result_pagos->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($pago['cliente_nombre']) ?></td>
                                            <td><?= htmlspecialchars($pago['descripcion']) ?></td>
                                            <td><?= htmlspecialchars($pago['metodo_pago'] ?: 'No especificado') ?></td>
                                            <td><?= formatMoney($pago['monto_pagado']) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($pago['created_at'])) ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                No hay pagos registrados.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si está en modo oscuro
    const isDarkMode = document.body.classList.contains('dark-mode');
    
    // Configuración de colores
    const primaryColor = '#4e73df';
    const successColor = '#1cc88a';
    const infoColor = '#36b9cc';
    const warningColor = '#f6c23e';
    const dangerColor = '#e74a3b';
    const secondaryColor = '#858796';
    
    // Configuración para modo oscuro
    const darkModeOptions = {
        scales: {
            x: {
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                },
                ticks: {
                    color: '#a8afc7'
                }
            },
            y: {
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                },
                ticks: {
                    color: '#a8afc7',
                    callback: function(value) {
                        return value.toLocaleString('es-PY') + ' Gs.';
                    }
                }
            }
        },
        plugins: {
            legend: {
                labels: {
                    color: '#a8afc7'
                }
            }
        }
    };
    
    // Configuración para modo claro
    const lightModeOptions = {
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString('es-PY') + ' Gs.';
                    }
                }
            }
        }
    };
    
    // Seleccionar opciones según el modo
    const chartOptions = isDarkMode ? darkModeOptions : lightModeOptions;
    
    // Gráfico de Pagos por Mes
    const ctxPagosMes = document.getElementById('pagosPorMesChart').getContext('2d');
    const pagosPorMesChart = new Chart(ctxPagosMes, {
        type: 'bar',
        data: {
            labels: <?= json_encode($meses) ?>,
            datasets: [{
                label: 'Pagos Recibidos',
                data: <?= json_encode($totales) ?>,
                backgroundColor: primaryColor,
                borderColor: primaryColor,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            ...chartOptions,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += new Intl.NumberFormat('es-PY', { 
                                style: 'currency', 
                                currency: 'PYG',
                                maximumFractionDigits: 0
                            }).format(context.raw);
                            return label;
                        }
                    }
                },
                ...(isDarkMode ? darkModeOptions.plugins : {})
            }
        }
    });
    
    // Actualizar gráficos cuando cambia el modo
    const darkModeButton = document.getElementById('darkModeButton');
    if (darkModeButton) {
        darkModeButton.addEventListener('click', function() {
            setTimeout(function() {
                const isDarkMode = document.body.classList.contains('dark-mode');
                
                // Actualizar opciones de todos los gráficos
                const charts = [
                    pagosPorMesChart, 
                    window.metodosPagoChart, 
                    window.estadoDeudasChart, 
                    window.clientesRegistradosChart,
                    window.reclamosChart // Add the new chart
                ];
                
                charts.forEach(chart => {
                    if (chart) {
                        if (isDarkMode) {
                            // Aplicar opciones de modo oscuro
                            if (chart.options.scales.x) {
                                chart.options.scales.x.grid.color = 'rgba(255, 255, 255, 0.1)';
                                chart.options.scales.x.ticks.color = '#a8afc7';
                            }
                            if (chart.options.scales.y) {
                                chart.options.scales.y.grid.color = 'rgba(255, 255, 255, 0.1)';
                                chart.options.scales.y.ticks.color = '#a8afc7';
                            }
                            if (chart.options.plugins && chart.options.plugins.legend) {
                                chart.options.plugins.legend.labels.color = '#a8afc7';
                            }
                        } else {
                            // Restaurar opciones de modo claro
                            if (chart.options.scales.x) {
                                chart.options.scales.x.grid.color = 'rgba(0, 0, 0, 0.1)';
                                chart.options.scales.x.ticks.color = '#666';
                            }
                            if (chart.options.scales.y) {
                                chart.options.scales.y.grid.color = 'rgba(0, 0, 0, 0.1)';
                                chart.options.scales.y.ticks.color = '#666';
                            }
                            if (chart.options.plugins && chart.options.plugins.legend) {
                                chart.options.plugins.legend.labels.color = '#666';
                            }
                        }
                        chart.update();
                    }
                });
            }, 100);
        });
    }
    
    // Resto de los gráficos con las mismas adaptaciones para modo oscuro
    // Gráfico de Métodos de Pago
    const ctxMetodos = document.getElementById('metodosPagoChart').getContext('2d');
    window.metodosPagoChart = new Chart(ctxMetodos, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($metodos) ?>,
            datasets: [{
                data: <?= json_encode($cantidades) ?>,
                backgroundColor: [primaryColor, successColor, infoColor, warningColor, dangerColor, secondaryColor],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: isDarkMode ? '#a8afc7' : '#666'
                    }
                }
            }
        }
    });
    
    // Gráfico de Estado de Deudas
    const ctxEstadoDeudas = document.getElementById('estadoDeudasChart').getContext('2d');
    window.estadoDeudasChart = new Chart(ctxEstadoDeudas, {
        type: 'doughnut',
        data: {
            labels: ['Pendientes', 'Vencidas', 'Pagadas'],
            datasets: [{
                data: [
                    <?= $estados_deudas['pendiente'] ?>, 
                    <?= $estados_deudas['vencido'] ?>, 
                    <?= $estados_deudas['pagado'] ?>
                ],
                backgroundColor: [warningColor, dangerColor, successColor],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: isDarkMode ? '#a8afc7' : '#666'
                    }
                }
            }
        }
    });
    
    // Gráfico de Clientes Registrados
    const ctxClientesRegistrados = document.getElementById('clientesRegistradosChart').getContext('2d');
    window.clientesRegistradosChart = new Chart(ctxClientesRegistrados, {
        type: 'doughnut',
        data: {
            labels: ['Registrados', 'No Registrados'],
            datasets: [{
                data: [
                    <?= $clientes_stats['clientes_registrados'] ?>, 
                    <?= $clientes_stats['total_clientes'] - $clientes_stats['clientes_registrados'] ?>
                ],
                backgroundColor: [primaryColor, secondaryColor],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: isDarkMode ? '#a8afc7' : '#666'
                    }
                }
            }
        }
    });
    
    // Gráfico de Estado de Reclamos
    const ctxReclamos = document.getElementById('reclamosChart').getContext('2d');
    window.reclamosChart = new Chart(ctxReclamos, {
        type: 'doughnut',
        data: {
            labels: ['Pendientes', 'En Proceso', 'Resueltos'],
            datasets: [{
                data: [
                    <?= $estados_reclamos['pendiente'] ?>, 
                    <?= $estados_reclamos['en_proceso'] ?>, 
                    <?= $estados_reclamos['resuelto'] ?>
                ],
                backgroundColor: [warningColor, infoColor, successColor],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: isDarkMode ? '#a8afc7' : '#666'
                    }
                }
            }
        }
    });
});

// Actualizar la página cada 5 minutos
setInterval(function() {
    location.reload();
}, 300000);
</script>

</body>
</html>