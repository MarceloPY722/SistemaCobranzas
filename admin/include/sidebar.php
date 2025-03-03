<?php include('header.php');?>
<!-- sidebar_admin.php -->
<div class="sidebar unified-sidebar">
  <ul class="menu">
    <!-- Usuarios -->
    <li class="menu-item">
      <a href="#" class="menu-link">
        <i class="bi bi-people-fill icono-menu"></i> Usuarios
        <span class="toggle-icon">&#9654;</span>
      </a>
      <ul class="submenu">
        <li>
          <a href="#">
            <i class="bi bi-person-plus"></i> Agregar Usuario
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bi bi-pencil-square"></i> Modificar Usuario
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bi bi-person-x"></i> Eliminar Usuario
          </a>
        </li>
      </ul>
    </li>

    <!-- Configuración -->
    <li class="menu-item">
      <a href="#" class="menu-link">
        <i class="bi bi-gear-fill icono-menu"></i> Configuración
        <span class="toggle-icon">&#9654;</span>
      </a>
      <ul class="submenu">
        <li>
          <a href="#">
            <i class="bi bi-tools"></i> Parámetros del Sistema
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bi bi-shield-lock"></i> Seguridad
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bi bi-cloud-arrow-up"></i> Backups
          </a>
        </li>
      </ul>
    </li>

    <!-- Reportes -->
    <li class="menu-item">
      <a href="#" class="menu-link">
        <i class="bi bi-bar-chart-fill icono-menu"></i> Reportes
        <span class="toggle-icon">&#9654;</span>
      </a>
      <ul class="submenu">
        <li>
          <a href="#">
            <i class="bi bi-people"></i> Reporte de Usuarios
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bi bi-graph-up"></i> Reporte de Actividad
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bi bi-pc-display"></i> Reporte del Sistema
          </a>
        </li>
      </ul>
    </li>

    <!-- Salir -->
    <li class="menu-item">
      <a href="../../logout.php" class="menu-link">
        <i class="bi bi-box-arrow-right icono-menu"></i> Salir
      </a>
    </li>
  </ul>
</div>

<style>
  .unified-sidebar {
    width: 250px;
    min-width: 250px;
    background: #343a40;
    border-right: 1px solid #222;
    height: 100vh;
    position: fixed;
    left: 0;
    overflow-y: auto;
    padding: 15px 0;
    box-shadow: 2px 0 5px rgba(0,0,0,0.2);
  }
  .unified-sidebar .menu {
    list-style: none; /* Quita los puntos de la lista */
    padding: 0;
    margin: 0;
  }
  .unified-sidebar .menu-item {
    position: relative;
  }
  .unified-sidebar .menu-link {
    display: block;
    padding: 10px 20px;
    color: #ccc;
    text-decoration: none;
    font-size: 0.95em;
    transition: background 0.3s, color 0.3s;
  }
  .unified-sidebar .menu-link:hover {
    background: #495057;
    color: #fff;
  }
  .unified-sidebar .icono-menu {
    margin-right: 8px;
    font-size: 1em;
  }
  .unified-sidebar .toggle-icon {
    float: right;
    transition: transform 0.3s;
  }
  .unified-sidebar .submenu {
    padding-left: 20px;
    display: none; /* Oculto por defecto */
  }
  .unified-sidebar .menu-item.active > .menu-link {
    background: #764AF1;
    color: #fff;
  }
  .unified-sidebar .menu-item.active .submenu {
    display: block; /* Se muestra al hacer clic */
  }
  .unified-sidebar .menu-item.active .toggle-icon {
    transform: rotate(90deg);
  }
  .unified-sidebar .submenu li a {
    display: block;
    padding: 8px 20px;
    color: #bbb;
    text-decoration: none;
    font-size: 0.9em;
    transition: background 0.3s;
  }
  .unified-sidebar .submenu li a i {
    margin-right: 6px; /* Espacio entre ícono y texto */
  }
  .unified-sidebar .submenu li a:hover {
    background: #5a5f69;
    color: #fff;
  }
</style>

<script>
  // Funcionalidad para desplegar el submenú al hacer clic
  document.querySelectorAll('.unified-sidebar .menu-link').forEach(link => {
    link.addEventListener('click', function(e) {
      const submenu = this.nextElementSibling;
      if (submenu && submenu.classList.contains('submenu')) {
        e.preventDefault();
        this.parentElement.classList.toggle('active');
      }
    });
  });
</script>
