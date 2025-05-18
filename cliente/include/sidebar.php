<?php include_once 'header.php'; ?>
  <div class="sidebar unified-sidebar">
    <div class="sidebar-header">
   <img src="/sistemacobranzas/img/logo128W.png"> 
    
    </div>

    <ul class="menu">
      <!-- Inicio -->
      <li class="menu-item">
        <a href="/sistemacobranzas/cliente/index.php" class="menu-link">
          <i class="bi bi-house-door icono-menu"></i> Inicio
        </a>
      </li>
  
      <!-- Categoría: Mis Préstamos -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="bi bi-cash-coin icono-menu"></i> Mis Préstamos
          <span class="toggle-icon">&#9654;</span>
        </a>
        <ul class="submenu">
          <li>
            <a href="/sistemacobranzas/cliente/prestamos/ver_prestamos.php">
              <i class="bi bi-eye"></i> Ver Préstamos
            </a>
          </li>
          <li>
            <a href="/sistemacobranzas/cliente/pagos/historial_pagos.php">
              <i class="bi bi-clock-history"></i> Historial de Pagos
            </a>
          </li>
        </ul>
      </li>
  
      <!-- Categoría: Pagos -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="bi bi-credit-card icono-menu"></i> Pagos
          <span class="toggle-icon">&#9654;</span>
        </a>
        <ul class="submenu">
          <li>
            <a href="/sistemacobranzas/cliente/pagos/realizar_pago.php">
              <i class="bi bi-cash"></i> Realizar Pago
            </a>
          </li>
          <li>
            <a href="/sistemacobranzas/cliente/pagos/proximos_vencimientos.php">
              <i class="bi bi-calendar-check"></i> Próximos Vencimientos
            </a>
          </li>
        </ul>
      </li>
  
      <!-- Categoría: Reclamos -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="bi bi-exclamation-circle icono-menu"></i> Reclamos
          <span class="toggle-icon">&#9654;</span>
        </a>
        <ul class="submenu">
          <li>
            <a href="/sistemacobranzas/cliente/reclamos/nuevo_reclamo.php">
              <i class="bi bi-plus-circle"></i> Nuevo Reclamo
            </a>
          </li>
          <li>
            <a href="/sistemacobranzas/cliente/reclamos/mis_reclamos.php">
              <i class="bi bi-list-check"></i> Mis Reclamos
            </a>
          </li>
        </ul>
      </li>
  
      <!-- Categoría: Mi Perfil -->
      <li class="menu-item">
        <a href="/sistemacobranzas/cliente/perfil/mi_perfil.php" class="menu-link">
          <i class="bi bi-person-circle icono-menu"></i> Mi Perfil
        </a>
      </li>
  
      <!-- Categoría: Configuración -->
      <li class="menu-item">
        <a href="#" class="menu-link">
          <i class="bi bi-gear-fill icono-menu"></i> Configuración
          <span class="toggle-icon">&#9654;</span>
        </a>
        <ul class="submenu">
          <li>
            <a href="#" id="darkModeButton" class="d-flex align-items-center">
              <i class="bi bi-moon-fill me-2"></i> <span id="darkModeText">Modo Oscuro</span>
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
  
      // Funcionalidad para el modo oscuro
      const darkModeButton = document.getElementById('darkModeButton');
      const darkModeText = document.getElementById('darkModeText');
      
      if (darkModeButton && darkModeText) {
        // Verificar si hay una preferencia guardada
        const isDarkMode = localStorage.getItem('darkMode') === 'true';
        
        // Aplicar modo oscuro si está guardado
        if (isDarkMode) {
          document.body.classList.add('dark-mode');
          darkModeText.textContent = 'Modo Claro';
        }
        
        // Evento para cambiar el modo
        darkModeButton.addEventListener('click', function(e) {
          e.preventDefault();
          
          if (document.body.classList.contains('dark-mode')) {
            document.body.classList.remove('dark-mode');
            darkModeText.textContent = 'Modo Oscuro';
            localStorage.setItem('darkMode', 'false');
          } else {
            document.body.classList.add('dark-mode');
            darkModeText.textContent = 'Modo Claro';
            localStorage.setItem('darkMode', 'true');
          }
        });
      }
    });
  </script>
  