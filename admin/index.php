<?php include 'include/header.php'; ?>
<?php include 'include/sidebar.php';?>
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ./index.php"); 
    exit();
}

$host     = "localhost";
$dbname   = "sistema_cobranzas";
$db_user  = "root";
$db_pass  = "";

// Conexión usando mysqli
$conn = new mysqli($host, $db_user, $db_pass, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener el nombre del usuario autenticado
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$username = $user['nombre'];

// Obtener el número de clientes
$clients_count = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM clientes");
if ($result && $row = $result->fetch_assoc()) {
    $clients_count = $row['total'];
}

// Obtener el número de usuarios
$users_count = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
if ($result && $row = $result->fetch_assoc()) {
    $users_count = $row['total'];
}

// Datos de ejemplo para los gráficos (porcentaje de activos/inactivos)
$clients_active   = 70; // Ejemplo: 70% de clientes activos
$clients_inactive = 30; // Ejemplo: 30% de clientes inactivos
$users_active     = 80; // Ejemplo: 80% de usuarios activos
$users_inactive   = 20; // Ejemplo: 20% de usuarios inactivos
?>

  


    <div style="flex: 1; display: flex; flex-direction: column;">
        <!-- Header que muestra el nombre del usuario -->
        <div class="header">
            Bienvenido, <?php echo htmlspecialchars($username); ?>
        </div>
        <!-- Área principal con datos y gráficos -->
        <div class="main-content">
            <h2>Dashboard</h2>
            <div class="cards">
                <!-- Tarjeta de Clientes -->
                <div class="card">
                    <h3>Clientes</h3>
                    <p>Total: <?php echo $clients_count; ?></p>
                    <canvas id="clientsChart"></canvas>
                </div>
                <!-- Tarjeta de Usuarios -->
                <div class="card">
                    <h3>Usuarios</h3>
                    <p>Total: <?php echo $users_count; ?></p>
                    <canvas id="usersChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Funcionalidad para contraer/expandir el sidebar
        const toggleBtn = document.getElementById('toggleBtn');
        const sidebar  = document.getElementById('sidebar');
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            // Forzar actualización de los gráficos
            clientsChart.resize();
            usersChart.resize();
        });

        // Función de navegación (puedes adaptarla para redirigir o cargar contenido dinámico)
        function navigate(page) {
            console.log("Navegar a:", page);
            // Aquí se puede implementar la redirección o carga dinámica según la opción seleccionada
        }

        // Inicialización de Chart.js para el gráfico de Clientes
        const ctxClients = document.getElementById('clientsChart').getContext('2d');
        const clientsChart = new Chart(ctxClients, {
            type: 'doughnut',
            data: {
                labels: ['Activos', 'Inactivos'],
                datasets: [{
                    data: [<?php echo $clients_active; ?>, <?php echo $clients_inactive; ?>],
                    backgroundColor: ['#1abc9c', '#e74c3c']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Evita que el gráfico mantenga su relación de aspecto
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + '%';
                            }
                        }
                    }
                }
            }
        });

        // Inicialización de Chart.js para el gráfico de Usuarios
        const ctxUsers = document.getElementById('usersChart').getContext('2d');
        const usersChart = new Chart(ctxUsers, {
            type: 'doughnut',
            data: {
                labels: ['Activos', 'Inactivos'],
                datasets: [{
                    data: [<?php echo $users_active; ?>, <?php echo $users_inactive; ?>],
                    backgroundColor: ['#3498db', '#f39c12']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Evita que el gráfico mantenga su relación de aspecto
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + '%';
                            }
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>