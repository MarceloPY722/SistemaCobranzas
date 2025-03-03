<?php include('include/sidebar.php'); ?>


<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

?>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f0f2f5;
    }
   
    .main-content {
      margin-left: 250px; 
      padding: 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    h1 {
      margin-bottom: 20px;
    }

    
    .chart-row {
      display: flex;
      justify-content: center; 
      align-items: center;    
      gap: 20px;               
      flex-wrap: wrap;       
      width: 100%;
      max-width: 800px;       
    }
    .chart-container {
      width: 200px;           
      background: #fff;
      padding: 10px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      text-align: center;
    }
   
    .chart-container canvas {
      max-width: 150px;
      max-height: 150px;
      margin: 0 auto;
    }
  </style>
</head>
<body>

 
  <div class="main-content">
    <h1>Dashboard en Tiempo Real</h1>
    
    <div class="chart-row">
      <div class="chart-container">
        <canvas id="clientsChart"></canvas>
      </div>

      
      <div class="chart-container">
        <canvas id="usersChart"></canvas>
      </div>
    </div>
  </div>

  <script>
 
    const ctxClients = document.getElementById('clientsChart').getContext('2d');
    const clientsChart = new Chart(ctxClients, {
      type: 'doughnut',
      data: {
        labels: ['Clientes'], 
        datasets: [{
          label: 'Cantidad de Clientes',
          data: [0], // Inicialmente 0
          backgroundColor: ['rgba(54, 162, 235, 0.6)'],
          borderColor: ['rgba(54, 162, 235, 1)'],
          borderWidth: 1
        }]
      },
      options: {
        plugins: {
          legend: {
            display: true, // Muestra leyenda (opcional)
          }
        }
      }
    });

    // Creamos el gráfico doughnut para Usuarios (inicialmente 0)
    const ctxUsers = document.getElementById('usersChart').getContext('2d');
    const usersChart = new Chart(ctxUsers, {
      type: 'doughnut',
      data: {
        labels: ['Usuarios'],
        datasets: [{
          label: 'Cantidad de Usuarios',
          data: [0],
          backgroundColor: ['rgba(255, 159, 64, 0.6)'],
          borderColor: ['rgba(255, 159, 64, 1)'],
          borderWidth: 1
        }]
      },
      options: {
        plugins: {
          legend: {
            display: true,
          }
        }
      }
    });

    // Función para actualizar los datos cada 5 segundos
    function updateData() {
      fetch('include/getData.php')
        .then(response => response.json())
        .then(data => {
          // Actualiza gráfico de Clientes
          clientsChart.data.datasets[0].data = [data.clients];
          clientsChart.update();

          // Actualiza gráfico de Usuarios
          usersChart.data.datasets[0].data = [data.users];
          usersChart.update();
        })
        .catch(error => console.error('Error al obtener datos:', error));
    }

    // Llamada inicial
    updateData();
    // Repetir cada 5 segundos
    setInterval(updateData, 5000);
  </script>

