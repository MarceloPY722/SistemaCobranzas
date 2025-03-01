<?php include('header.php');?>

<!-- sidebar_gestor.php -->
<div class="sidebar unified-sidebar">
  <ul class="menu">
    <!-- Inicio -->
    <li class="menu-item">
      <a href="/sistemacobranzas/gestor/index.php" class="menu-link">
        <i class="bi bi-house-door"></i> Inicio
      </a>
    </li>

    <!-- Clientes -->
    <li class="menu-item">
      <a href="#" class="menu-link">
        <i class="bi bi-person-lines-fill icono-menu"></i> Clientes
        <span class="toggle-icon">&#9654;</span>
      </a>
      <ul class="submenu">
        <li>
          <a href="#">
            <i class="bi bi-person-plus"></i> Agregar Cliente
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bi bi-pencil-square"></i> Modificar Cliente
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bi bi-person-x"></i> Eliminar Cliente
          </a>
        </li>
      </ul>
    </li>

    <!-- Deudas -->
    <li class="menu-item">
      <a href="#" class="menu-link">
        <i class="bi bi-credit-card icono-menu"></i> Deudas
        <span class="toggle-icon">&#9654;</span>
      </a>
      <ul class="submenu">
        <li>
          <a href="#">
            <i class="bi bi-list-check"></i> Lista de Deudas
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bi bi-card-checklist"></i> Registrar Deuda
          </a>
        </li>
      </ul>
    </li>

    <!-- Pagos -->
    <li class="menu-item">
      <a href="#" class="menu-link">
        <i class="bi bi-wallet2 icono-menu"></i> Pagos
        <span class="toggle-icon">&#9654;</span>
      </a>
      <ul class="submenu">
        <li>
          <a href="#">
            <i class="bi bi-clock-history"></i> Historial de Pagos
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bi bi-plus-square"></i> Registrar Pago
          </a>
        </li>
      </ul>
    </li>

    <!-- Reclamos -->
    <li class="menu-item">
      <a href="#" class="menu-link">
        <i class="bi bi-exclamation-triangle icono-menu"></i> Reclamos
        <span class="toggle-icon">&#9654;</span>
      </a>
      <ul class="submenu">
        <li>
          <a href="#">
            <i class="bi bi-tools"></i> Gestionar Reclamos
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bi bi-chat-dots"></i> Responder Reclamos
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
            <i class="bi bi-ui-checks-grid"></i> Reporte General
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bi bi-cash-coin"></i> Reporte de Pagos
          </a>
        </li>
        <li>
          <a href="#">
            <i class="bi bi-journal-minus"></i> Reporte de Deudas
          </a>
        </li>
      </ul>
    </li>

    <!-- Salir -->
    <li class="menu-item">
      <a href="logout.php" class="menu-link">
        <i class="bi bi-box-arrow-right icono-menu"></i> Salir
      </a>
    </li>
  </ul>
</div>

<style>
  .sidebar.unified-sidebar {
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
  .sidebar.unified-sidebar .submenu li {
    margin: 0;
    padding: 0;
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
  .sidebar.unified-sidebar .submenu {
    padding-left: 20px;
    display: none;
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
</style>

<script>
  document.querySelectorAll('.sidebar.unified-sidebar .menu-link').forEach(link => {
    link.addEventListener('click', function(e) {
      const submenu = this.nextElementSibling;
      if (submenu && submenu.classList.contains('submenu')) {
        e.preventDefault();
        this.parentElement.classList.toggle('active');
      }
    });
  });
</script>
