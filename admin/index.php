<?php
include 'include/sidebar.php';

// Conexión a la base de datos (ajusta según tu configuración)
$conn = new mysqli("localhost:3306", "root", "", "sistema_cobranzas");

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta para obtener el total de clientes
$queryClientes = "SELECT COUNT(*) as totalClientes FROM clientes";
$resultClientes = $conn->query($queryClientes);
$totalClientes = $resultClientes->fetch_assoc()['totalClientes'];

// Consulta para obtener el total de usuarios
$queryUsuarios = "SELECT COUNT(*) as totalUsuarios FROM usuarios";
$resultUsuarios = $conn->query($queryUsuarios);
$totalUsuarios = $resultUsuarios->fetch_assoc()['totalUsuarios'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            display: flex;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .dashboard {
            flex: 1;
            padding: 20px;
            padding-left: 350px;
            height: 100vh;
            overflow-y: auto;
            box-sizing: border-box;
        }
        .charts {
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 80%;
        }
        canvas {
            max-width: 400px;
            max-height: 300px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
    </style>
</head>
<body>
    <!-- El sidebar ya está incluido desde include/sidebar.php -->
    <main>
        <div class="dashboard">
            <h1>Dashboard</h1>
            <div class="charts">
                <canvas id="clientesChart"></canvas>
                <canvas id="usuariosChart"></canvas>
            </div>
        </div>
    </main>

    <script>
        var totalClientes = <?php echo $totalClientes; ?>;
        var totalUsuarios = <?php echo $totalUsuarios; ?>;

        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de clientes
            var ctxClientes = document.getElementById('clientesChart').getContext('2d');
            new Chart(ctxClientes, {
                type: 'doughnut',
                data: {
                    labels: ['Total Clientes', 'Resto'],
                    datasets: [{
                        label: 'Clientes',
                        data: [totalClientes, 1000 - totalClientes], // Total y resto
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.2)', // Color para clientes
                            'rgba(200, 200, 200, 0.2)'  // Color para el resto
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(200, 200, 200, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: true
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return tooltipItem.label + ': ' + tooltipItem.raw;
                                }
                            }
                        }
                    }
                }
            });

            // Gráfico de usuarios
            var ctxUsuarios = document.getElementById('usuariosChart').getContext('2d');
            new Chart(ctxUsuarios, {
                type: 'doughnut',
                data: {
                    labels: ['Total Usuarios', 'Libre'],
                    datasets: [{
                        label: 'Usuarios',
                        data: [totalUsuarios, 1000 - totalUsuarios], // Total y resto
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)', // Color para usuarios
                            'rgba(200, 200, 200, 0.2)'  // Color para el resto
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(200, 200, 200, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: true
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return tooltipItem.label + ': ' + tooltipItem.raw;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>