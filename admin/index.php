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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/css1.css">
</head>
<body>
    <!-- El sidebar ya está incluido desde include/sidebar.php -->
    
    <!-- Theme Toggle Switch -->
    <div class="theme-toggle">
        <label class="switch">
            <input type="checkbox" id="themeToggle">
            <span class="slider">
                <i class="bi bi-sun-fill"></i>
                <i class="bi bi-moon-fill"></i>
            </span>
        </label>
    </div>
    
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
        
        // Theme Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('themeToggle');
            
            // Check for saved theme preference or use preferred color scheme
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-mode');
                themeToggle.checked = true;
                updateChartsTheme(true);
            }
            
            // Toggle theme when switch is clicked
            themeToggle.addEventListener('change', function() {
                if (this.checked) {
                    document.body.classList.add('dark-mode');
                    localStorage.setItem('theme', 'dark');
                    updateChartsTheme(true);
                } else {
                    document.body.classList.remove('dark-mode');
                    localStorage.setItem('theme', 'light');
                    updateChartsTheme(false);
                }
            });
            
            // Function to update chart colors based on theme
            function updateChartsTheme(isDark) {
                // Update chart text colors based on theme
                window.clientesChart.options.plugins.legend.labels.color = isDark ? '#fff' : '#000';
                window.usuariosChart.options.plugins.legend.labels.color = isDark ? '#fff' : '#000';
                
                // Update the charts to reflect the changes
                window.clientesChart.update();
                window.usuariosChart.update();
            }
            
            // Gráfico de clientes
            var ctxClientes = document.getElementById('clientesChart').getContext('2d');
            window.clientesChart = new Chart(ctxClientes, {
                type: 'doughnut',
                data: {
                    labels: ['Total Clientes', 'Resto'],
                    datasets: [{
                        label: 'Clientes',
                        data: [totalClientes, 1000 - totalClientes], // Total y resto
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)', // Color para clientes (increased opacity)
                            'rgba(200, 200, 200, 0.3)'  // Color para el resto
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
                            display: true,
                            labels: {
                                color: document.body.classList.contains('dark-mode') ? '#fff' : '#000'
                            }
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
            window.usuariosChart = new Chart(ctxUsuarios, {
                type: 'doughnut',
                data: {
                    labels: ['Total Usuarios', 'Libre'],
                    datasets: [{
                        label: 'Usuarios',
                        data: [totalUsuarios, 1000 - totalUsuarios], // Total y resto
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)', // Color para usuarios (increased opacity)
                            'rgba(200, 200, 200, 0.3)'  // Color para el resto
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
                            display: true,
                            labels: {
                                color: document.body.classList.contains('dark-mode') ? '#fff' : '#000'
                            }
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