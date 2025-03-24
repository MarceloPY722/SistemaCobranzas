<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Panel de Administración</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Add Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <!-- Incluir el sidebar desde el archivo externo -->
  <?php include 'include/sidebar.php'; ?>

  <!-- Content wrapper with charts -->
  <div class="content-wrapper">
    <div class="row">
      <div class="col-md-6 mb-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Crecimiento de Clientes</h5>
            <div style="height: 300px;">
              <canvas id="userGrowthChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">SuperUsuarios con Roles</h5>
            <canvas id="userRolesChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Inicialización de los gráficos
      fetch('get_user_stats.php')
        .then(response => response.json())
        .then(data => {
          // Gráfico de crecimiento de clientes
          const growthCtx = document.getElementById('userGrowthChart').getContext('2d');
          new Chart(growthCtx, {
            type: 'line',
            data: {
              labels: data.dates,
              datasets: [{
                label: 'Nuevos Clientes',
                data: data.counts,
                borderColor: '#764AF1',
                backgroundColor: 'rgba(118, 74, 241, 0.2)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 0
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              interaction: {
                intersect: false,
                mode: 'index'
              },
              scales: {
                x: {
                  grid: {
                    display: false
                  }
                },
                y: {
                  beginAtZero: true,
                  grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                  }
                }
              },
              plugins: {
                tooltip: {
                  callbacks: {
                    label: function(context) {
                      return `Clientes registrados: ${context.raw}`;
                    }
                  }
                }
              }
            }
          });

          // Gráfico de roles (sin cambios)
          const rolesCtx = document.getElementById('userRolesChart').getContext('2d');
          new Chart(rolesCtx, {
            type: 'doughnut',
            data: {
              labels: data.roles.map(r => r.nombre),
              datasets: [{
                data: data.roles.map(r => r.count),
                backgroundColor: [
                  '#764AF1',
                  '#36A2EB',
                  '#FFCE56'
                ]
              }]
            },
            options: {
              responsive: true
            }
          });
        })
        .catch(error => {
          console.error('Error fetching data:', error);
        });
    });
  </script>
</body>
</html>