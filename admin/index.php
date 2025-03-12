<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Panel de Administración</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Estilos del modo oscuro -->
    <style>
      /* Estilos del sidebar */
      .sidebar.unified-sidebar {
        width: 250px;
        min-width: 250px;
        background: #343a40;
        border-right: 1px solid #222;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        overflow-y: auto;
        padding: 15px 0;
        box-shadow: 2px 0 5px rgba(0,0,0,0.2);
        z-index: 1000;
        transition: background-color 0.3s ease;
      }

      /* Estilos del modo oscuro */
      body.dark-mode {
        background-color: #121a35;
        color: #fff;
      }

      body.dark-mode .card {
        background-color: #1e2746;
        border-color: #2a3356;
      }

      body.dark-mode .card-title {
        color: #fff;
      }

      body.dark-mode .sidebar.unified-sidebar {
        background: #121a35;
        border-right-color: #0a0f20;
      }

      body.dark-mode .menu-link {
        color: #adb5bd;
      }

      body.dark-mode .menu-link:hover {
        background: #2a3356;
        color: #fff;
      }

      /* Resto de estilos existentes */
      .sidebar.unified-sidebar .menu,
      .sidebar.unified-sidebar .submenu {
        list-style: none;
        padding: 0;
        margin: 0;
      }

      .sidebar.unified-sidebar .submenu {
        padding-left: 20px;
        display: none;
        background: rgba(0, 0, 0, 0.1);
      }

      .sidebar.unified-sidebar .menu-item {
        position: relative;
      }

      .sidebar.unified-sidebar .menu-link {
        display: block;
        padding: 10px 20px;
        color: #ccc;
        text-decoration: none;
        font-size: 0.95em;
        transition: background 0.3s, color 0.3s;
      }

      .sidebar.unified-sidebar .menu-link:hover {
        background: #495057;
        color: #fff;
      }

      .sidebar.unified-sidebar .icono-menu {
        margin-right: 8px;
        font-size: 1em;
      }

      .sidebar.unified-sidebar .toggle-icon {
        float: right;
        transition: transform 0.3s;
      }

      .sidebar.unified-sidebar .menu-item.active > .menu-link {
        background: #764AF1;
        color: #fff;
      }

      .sidebar.unified-sidebar .menu-item.active .submenu {
        display: block;
      }

      .sidebar.unified-sidebar .menu-item.active .toggle-icon {
        transform: rotate(90deg);
      }

      .content-wrapper {
        margin-left: 250px;
        padding: 20px;
        transition: background-color 0.3s ease;
      }

      @media print {
        .sidebar, .btn-custom-edit, .btn-custom-delete {
          display: none;
        }
        .content-wrapper {
          margin-left: 0;
        }
      }
    </style>
    <!-- Add Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar unified-sidebar">
    <ul class="menu">
      <!-- Inicio -->
      <li class="menu-item">
        <a href="/sistemacobranzas/admin/index.php" class="menu-link">
          <i class="bi bi-house-door"></i> Inicio
        </a>
      </li>

      <!-- Categoría: Usuarios -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="bi bi-people icono-menu"></i> Usuarios
          <span class="toggle-icon">&#9654;</span>
        </a>
        <ul class="submenu">
          <li>
            <a href="/sistemacobranzas/admin/sidebar/usuarios/agregar.php">
              <i class="bi bi-plus-circle"></i> Agregar Usuarios
            </a>
          </li>
          <li>
            <a href="/sistemacobranzas/admin/sidebar/usuarios/ver_usuarios.php">
              <i class="bi bi-eye"></i> Ver Usuarios
            </a>
          </li>
        </ul>
      </li>

      <!-- Categoría: Clientes -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="bi bi-people icono-menu"></i> Clientes
          <span class="toggle-icon">&#9654;</span>
        </a>
        <ul class="submenu">
          <li>
            <a href="/sistemacobranzas/admin/sidebar/clientes/agregar.php">
              <i class="bi bi-person-plus"></i> Agregar Clientes
            </a>
          </li>
          <li>
            <a href="/sistemacobranzas/admin/sidebar/clientes/ver_clientes.php">
              <i class="bi bi-eye"></i> Ver Clientes
            </a>
          </li>
        </ul>
      </li>

      <!-- Categoría: Estadísticas -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="bi bi-bar-chart icono-menu"></i> Estadísticas
          <span class="toggle-icon">&#9654;</span>
        </a>
        <ul class="submenu">
          <li>
            <a href="/sistemacobranzas/admin/estadisticas/ver_estadisticas.php">
              <i class="bi bi-graph-up"></i> Ver Estadísticas
            </a>
          </li>
          <li>
            <a href="/sistemacobranzas/admin/estadisticas/generar_reportes.php">
              <i class="bi bi-pie-chart"></i> Generar Reportes
            </a>
          </li>
        </ul>
      </li>

      <!-- Categoría: Configuración -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="bi bi-gear-fill icono-menu"></i> Configuración
          <span class="toggle-icon">&#9654;</span>
        </a>
        <ul class="submenu">
          <li>
            <a href="#" class="d-flex align-items-center">
              <i class="bi bi-moon-fill me-2"></i> Modo Oscuro
              <label class="dark-mode-switch ms-auto">
                <input type="checkbox" id="darkModeToggle">
                <span class="slider">
                  <i class="bi bi-sun-fill"></i>
                  <i class="bi bi-moon-fill"></i>
                </span>
              </label>
            </a>
          </li>
          <li>
            <a href="/sistemacobranzas/admin/configuracion/perfil.php">
              <i class="bi bi-person-circle"></i> Perfil
            </a>
          </li>
        </ul>
      </li>

      <!-- Salir -->
      <li class="menu-item">
        <a href="/sistemacobranzas/logout.php" class="menu-link">
          <i class="bi bi-box-arrow-right icono-menu"></i> Salir
        </a>
      </li>
    </ul>
  </div>

  <!-- Content wrapper with charts -->
  <div class="content-wrapper">
    <div class="row">
      <div class="col-md-6 mb-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Crecimiento de Usuarios</h5>
            <canvas id="userGrowthChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6 mb-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Total de Usuarios por Rol</h5>
            <canvas id="userRolesChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script>
    // Función para el modo oscuro
    document.addEventListener('DOMContentLoaded', function() {
      const darkModeToggle = document.getElementById('darkModeToggle');
      
      // Verificar si hay una preferencia guardada
      const isDarkMode = localStorage.getItem('darkMode') === 'true';
      
      // Aplicar modo oscuro si está guardado
      if (isDarkMode) {
        document.body.classList.add('dark-mode');
        darkModeToggle.checked = true;
      }
      
      // Evento para cambiar el modo
      darkModeToggle.addEventListener('change', function() {
        if (this.checked) {
          document.body.classList.add('dark-mode');
          localStorage.setItem('darkMode', 'true');
        } else {
          document.body.classList.remove('dark-mode');
          localStorage.setItem('darkMode', 'false');
        }
      });

      // Código existente del sidebar
      const menuItems = document.querySelectorAll('.menu-item');
      
      menuItems.forEach(function(item) {
        const menuLink = item.querySelector('.menu-link');
        
        if (menuLink && item.querySelector('.submenu')) {
          menuLink.addEventListener('click', function(e) {
            if (this.getAttribute('href') === '#') {
              e.preventDefault();
            }
            item.classList.toggle('active');
          });
        }
      });
      
      const currentPath = window.location.pathname;
      
      document.querySelectorAll('.submenu a').forEach(function(link) {
        if (link.getAttribute('href') === currentPath) {
          const parentItem = link.closest('.menu-item');
          if (parentItem) {
            parentItem.classList.add('active');
          }
        }
      });

      // Inicialización de los gráficos
      fetch('get_user_stats.php')
        .then(response => response.json())
        .then(data => {
          // Gráfico de crecimiento
          const growthCtx = document.getElementById('userGrowthChart').getContext('2d');
          new Chart(growthCtx, {
            type: 'line',
            data: {
              labels: data.dates,
              datasets: [{
                label: 'Nuevos Usuarios',
                data: data.counts,
                borderColor: '#764AF1',
                tension: 0.4,
                fill: false
              }]
            },
            options: {
              responsive: true,
              interaction: {
                intersect: false,
                mode: 'index'
              },
              plugins: {
                tooltip: {
                  callbacks: {
                    label: function(context) {
                      return `Usuarios registrados: ${context.raw}`;
                    }
                  }
                }
              }
            }
          });

          // Gráfico de roles
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
              responsive: true,
              plugins: {
                legend: {
                  position: 'bottom'
                },
                tooltip: {
                  callbacks: {
                    label: function(context) {
                      return `${context.label}: ${context.raw} usuarios`;
                    }
                  }
                }
              }
            }
          });
        });
    });
  </script>
</body>
</html>