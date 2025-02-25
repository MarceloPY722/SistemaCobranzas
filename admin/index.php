<?php

// Falta la verificacion de rol para que no entre otro que no sea admin


?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard en Tiempo Real</title>
  <!-- Chart.js desde CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f0f2f5;
    }
    /* Ajuste para que el contenido principal se ubique a la derecha del sidebar */
    .main-content {
      margin-left: 250px; /* Debe coincidir con el ancho del sidebar */
      padding: 20px;
      /* Para centrar el contenido en la sección principal */
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    h1 {
      margin-bottom: 20px;
    }

    /* Contenedor que agrupa los gráficos para ponerlos lado a lado */
    .chart-row {
      display: flex;
      justify-content: center; /* Centra los gráficos horizontalmente */
      align-items: center;     /* Centra verticalmente (si fuera necesario) */
      gap: 20px;               /* Separación entre gráficos */
      flex-wrap: wrap;         /* Para que se ajusten si la pantalla es pequeña */
      width: 100%;
      max-width: 800px;        /* Ancho máximo del contenedor de gráficos */
    }
    .chart-container {
      width: 200px;            /* Ajusta el ancho total del contenedor */
      background: #fff;
      padding: 10px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      text-align: center;
    }
    /* Controla el tamaño máximo del canvas para que sean más pequeños */
    .chart-container canvas {
      max-width: 150px;
      max-height: 150px;
      margin: 0 auto;
    }
  </style>
</head>
<body>

  <!-- Incluimos el sidebar -->
  <?php include('include/sidebar.php'); ?>

  <div class="main-content">
    <h1>Dashboard en Tiempo Real</h1>
    
    <!-- Fila de gráficos -->
    <div class="chart-row">
      <!-- Gráfico de Clientes -->
      <div class="chart-container">
        <canvas id="clientsChart"></canvas>
      </div>

      <!-- Gráfico de Usuarios -->
      <div class="chart-container">
        <canvas id="usersChart"></canvas>
      </div>
    </div>
  </div>

  <script>
    // Creamos el gráfico doughnut para Clientes (inicialmente 0)
    const ctxClients = document.getElementById('clientsChart').getContext('2d');
    const clientsChart = new Chart(ctxClients, {
      type: 'doughnut',
      data: {
        labels: ['Clientes'], // Solo una categoría
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
  
</body>
</html>
