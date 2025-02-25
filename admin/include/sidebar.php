<link rel="stylesheet" href="/sistemacobranzas/admin/assets/css/sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="sidebar">
    <ul class="menu">
        <li class="menu-item">
            <a href="#" class="menu-link">
                <i class="bi bi-house-door"></i> Inicio
            </a>
        </li>
        
        <li class="menu-item">
            <a href="#" class="menu-link">
                <i class="bi bi-people"></i> Clientes
                <span class="toggle-icon">&#9654;</span>
            </a>
            <ul class="submenu">
                <li><a href="#"><i class="bi bi-person-plus"></i> Agregar Cliente</a></li>
                <li><a href="#"><i class="bi bi-person-gear"></i> Modificar Cliente</a></li>
                <li><a href="#"><i class="bi bi-person-x"></i> Eliminar Cliente</a></li>
            </ul>
      </li>
      
      <!-- Deudas -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="bi bi-cash-stack"></i> Deudas
          <span class="toggle-icon">&#9654;</span>
        </a>
        <ul class="submenu">
          <li><a href="#">Lista de Deudas</a></li>
          <li><a href="#">Registrar Deuda</a></li>
          <li><a href="#">Eliminar Deuda</a></li>
        </ul>
      </li>
      
      <!-- Pagos -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="bi bi-credit-card"></i> Pagos
          <span class="toggle-icon">&#9654;</span>
        </a>
        <ul class="submenu">
          <li><a href="#">Historial de Pagos</a></li>
          <li><a href="#">Registrar Pago</a></li>
          <li><a href="#">Cancelar Pago</a></li>
        </ul>
      </li>
      
      <!-- Reclamos -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="bi bi-exclamation-triangle"></i> Reclamos
          <span class="toggle-icon">&#9654;</span>
        </a>
        <ul class="submenu">
          <li><a href="#">Gestión de Reclamos</a></li>
          <li><a href="#">Seguimiento de Reclamos</a></li>
          <li><a href="#">Cerrar Reclamo</a></li>
        </ul>
      </li>
      
      <!-- Documentos -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="bi bi-folder2"></i> Documentos
          <span class="toggle-icon">&#9654;</span>
        </a>
        <ul class="submenu">
          <li><a href="#">Archivos Adjuntos</a></li>
          <li><a href="#">Subir Documento</a></li>
          <li><a href="#">Eliminar Documento</a></li>
        </ul>
      </li>
      
      <!-- Configuración -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="bi bi-gear-fill"></i> Configuración
          <span class="toggle-icon">&#9654;</span>
        </a>
        <ul class="submenu">
          <li><a href="#">Políticas de Interés</a></li>
          <li><a href="#">Gestión de Usuarios</a></li>
          <li><a href="#">Preferencias</a></li>
        </ul>
      </li>
      
      <!-- Reportes -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="bi bi-bar-chart"></i> Reportes
          <span class="toggle-icon">&#9654;</span>
        </a>
        <ul class="submenu">
          <li><a href="#">Reporte General</a></li>
          <li><a href="#">Reporte de Ventas</a></li>
          <li><a href="#">Reporte de Clientes</a></li>
        </ul>
      </li>
      
      <!-- Cerrar Sesión -->
      <li class="menu-item">
        <a href="../logout.php" class="menu-link">
          <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
        </a>
      </li>
    </ul>
  </div>

  <script>
    // Agrega funcionalidad para mostrar/ocultar el submenú al hacer clic
    document.querySelectorAll('.menu-link').forEach(link => {
      link.addEventListener('click', function(e) {
        const submenu = this.nextElementSibling;
        if (submenu && submenu.classList.contains('submenu')) {
          e.preventDefault(); // Evita el comportamiento por defecto del enlace
          // Alterna la clase 'active' en el elemento padre para mostrar/ocultar el submenú
          this.parentElement.classList.toggle('active');
        }
      });
    });
  </script>
  
</body>
</html>
