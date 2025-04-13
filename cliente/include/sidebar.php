<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Portal Cliente</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
      /* Estilos del sidebar */
      .sidebar.unified-sidebar {
        width: 250px;
        min-width: 250px;
        background: linear-gradient(to bottom, #343a40, #212529);
        border-right: 1px solid #222;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        overflow-y: auto;
        padding: 15px 0;
        box-shadow: 2px 0 10px rgba(0,0,0,0.3);
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
        background: rgba(0, 0, 0, 0.15);
        border-radius: 0 0 5px 5px;
        margin-top: 2px;
        overflow: hidden;
        transition: all 0.3s ease;
      }
      .sidebar.unified-sidebar .menu-item {
        position: relative;
        margin-bottom: 5px;
      }
      .sidebar.unified-sidebar .menu-link {
        display: block;
        padding: 12px 20px;
        color: #e9ecef;
        text-decoration: none !important;
        font-size: 0.95em;
        transition: all 0.3s ease;
        border-radius: 5px;
        margin: 0 8px;
      }
      .sidebar.unified-sidebar .menu-link:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        transform: translateX(3px);
      }
      .sidebar.unified-sidebar .icono-menu {
        margin-right: 10px;
        font-size: 1.1em;
        width: 20px;
        text-align: center;
      }
      .sidebar.unified-sidebar .toggle-icon {
        float: right;
        transition: transform 0.3s;
        opacity: 0.7;
        font-size: 0.8em;
        margin-top: 4px;
      }
      .sidebar.unified-sidebar .menu-item.active > .menu-link {
        background: #764AF1;
        color: #fff;
        box-shadow: 0 2px 5px rgba(118, 74, 241, 0.3);
      }
      .sidebar.unified-sidebar .menu-item.active .submenu {
        display: block;
      }
      .sidebar.unified-sidebar .menu-item.active .toggle-icon {
        transform: rotate(90deg);
        opacity: 1;
      }
      .sidebar.unified-sidebar .submenu li a {
        display: block;
        padding: 10px 15px;
        color: #ced4da;
        text-decoration: none !important;
        font-size: 0.9em;
        transition: all 0.3s ease;
        border-radius: 4px;
        margin: 0 5px;
      }
      .sidebar.unified-sidebar .submenu li a:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        transform: translateX(3px);
      }
  
      /* Añadir un poco de espacio para el logo o título */
      .sidebar-header {
        padding: 15px 20px;
        margin-bottom: 15px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        text-align: center;
      }
      
      .sidebar-header h4 {
        color: #fff;
        margin: 0;
        font-size: 1.2em;
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
        background: linear-gradient(to bottom, #121a35, #0a0f20);
        border-right-color: #0a0f20;
      }
  
      body.dark-mode .menu-link {
        color: #adb5bd;
      }
  
      body.dark-mode .menu-link:hover {
        background: #2a3356;
        color: #fff;
      }
  
      /* Estilos del contenido */
      .content-wrapper {
        margin-left: 250px;
        padding: 20px;
        transition: background-color 0.3s ease;
      }
      
      /* Estilos para la vista de préstamos */
      .loan-card {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        overflow: hidden;
      }
      
      .loan-progress {
        height: 10px;
        border-radius: 5px;
      }
      
      .loan-amount {
        font-size: 1.8rem;
        font-weight: bold;
      }
      
      .loan-detail {
        color: #6c757d;
        font-size: 0.9rem;
      }
      
      .loan-detail-value {
        font-weight: 600;
        font-size: 1.1rem;
      }
      
      .next-payment {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
      }
      
      .payment-number {
        width: 40px;
        height: 40px;
        background-color: #e9ecef;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #495057;
      }
      
      .payment-btn {
        border-radius: 25px;
        padding: 8px 20px;
      }
      
      /* Estilos para modo oscuro adicionales */
      body.dark-mode .loan-card {
        background-color: #1e2746;
        border-color: #2a3356;
      }
      
      body.dark-mode .next-payment {
        background-color: #2a3356;
      }
      
      body.dark-mode .payment-number {
        background-color: #121a35;
        color: #e9ecef;
      }
      
      body.dark-mode .loan-detail {
        color: #adb5bd;
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
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar unified-sidebar">
    <!-- Añadir un encabezado al sidebar -->
    <div class="sidebar-header">
      <h4>Portal Cliente</h4>
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