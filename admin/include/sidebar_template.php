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

    <li class="menu-item">
      <a href="#" class="menu-link">
        <i class="bi bi-bar-chart icono-menu"></i> Estadisticas
        <span class="toggle-icon">&#9654;</span>
      </a>
      <ul class="submenu">
        <li>
          <a href="/sistemacobranzas/admin/estadisticas/ver_estadisticas.php">
            <i class="bi bi-graph-up"></i> Ver Estadisticas
          </a>
        </li>
        <li>
          <a href="/sistemacobranzas/admin/estadisticas/generar_reportes.php">
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

    <li class="menu-item">
      <a href="/sistemacobranzas/logout.php" class="menu-link">
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