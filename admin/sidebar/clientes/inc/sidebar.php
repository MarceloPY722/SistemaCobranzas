
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
      }
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
      .sidebar.unified-sidebar .submenu li a {
        display: block;
        padding: 8px 20px;
        color: #bbb;
        text-decoration: none;
        font-size: 0.9em;
        transition: background 0.3s;
      }
      .sidebar.unified-sidebar .submenu li a:hover {
        background: #5a5f69;
        color: #fff;
      }

      /* Estilos del contenido */
      .content-wrapper {
          margin-left: 250px;
          padding: 20px;
      }
      .bg-custom {
          background-color: #121a35;
      }
      .profile-image {
          object-fit: cover;
          border: 2px solid #121a35;
      }
      .btn-custom-edit {
          background-color: #121a35;
          color: white;
          margin-right: 5px;
      }
      .btn-custom-edit:hover {
          background-color: #1a2547;
          color: white;
      }
      .btn-custom-delete {
          background-color: #dc3545;
          color: white;
      }
      .btn-custom-delete:hover {
          background-color: #bb2d3b;
          color: white;
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

   

      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="bi bi-bar-chart icono-menu"></i> Estadisticas
          <span class="toggle-icon">&#9654;</span>
        </a>
        <ul class="submenu">
          <li>
            <a href="categoria4/subcategoria1.php">
              <i class="bi bi-graph-up"></i> Ver Estadisticas
            </a>
          </li>
          <li>
            <a href="categoria4/subcategoria2.php">
              <i class="bi bi-pie-chart"></i> Generar Reportes
            </a>
          </li>
        </ul>
      </li>

      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="bi bi-gear-fill icono-menu"></i> Configuracion
          <span class="toggle-icon">&#9654;</span>
        </a>
        <ul class="submenu">
          <li>
            <a href="categoria5/subcategoria1.php">
              <i class="bi bi-wrench"></i>
            </a>
          </li>
          <li>
            <a href="categoria5/subcategoria2.php">
              <i class="bi bi-tools"></i>
            </a>
          </li>
        </ul>
      </li>

      <li class="menu-item">
        <a href="../logout.php" class="menu-link">
          <i class="bi bi-box-arrow-right icono-menu"></i> Salir
        </a>
      </li>
    </ul>
  </div>

  <!-- JavaScript para el funcionamiento del sidebar -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Seleccionar todos los elementos del menú que tienen submenús
      const menuItems = document.querySelectorAll('.menu-item');
      
      // Añadir evento de clic a cada elemento del menú
      menuItems.forEach(function(item) {
        const menuLink = item.querySelector('.menu-link');
        
        // Solo añadir evento si el enlace tiene un submenú
        if (menuLink && item.querySelector('.submenu')) {
          menuLink.addEventListener('click', function(e) {
            // Prevenir la navegación si el enlace es "#"
            if (this.getAttribute('href') === '#') {
              e.preventDefault();
            }
            
            // Alternar la clase 'active' en el elemento del menú
            item.classList.toggle('active');
          });
        }
      });
      
      // Verificar la URL actual para activar el menú correspondiente
      const currentPath = window.location.pathname;
      
      // Buscar enlaces que coincidan con la ruta actual
      document.querySelectorAll('.submenu a').forEach(function(link) {
        if (link.getAttribute('href') === currentPath) {
          // Activar el elemento padre
          const parentItem = link.closest('.menu-item');
          if (parentItem) {
            parentItem.classList.add('active');
          }
        }
      });
    });
  </script>
