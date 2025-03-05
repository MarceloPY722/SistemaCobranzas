<div class="sidebar unified-sidebar">
  <ul class="menu">
    <!-- Inicio -->
    <li class="menu-item">
      <a href="/sistemacobranzas/admin/index.php" class="menu-link">
        <i class="bi bi-house-door"></i> Inicio
      </a>
    </li>

    <!-- Categoría 1 -->
    <li class="menu-item">
      <a href="#" class="menu-link">
        <i class="bi bi-gear icono-menu"></i> Usuarios
        <span class="toggle-icon">&#9654;</span>
      </a>
      <ul class="submenu">
        <li>
          <a href="categoria1/subcategoria1.php">
            <i class="bi bi-plus-circle"></i> 
          Agregar Usuarios
          </a>
        </li>
        <li>
          <a href="categoria1/subcategoria2.php">
            <i class="bi bi-pencil"></i> Eliminar Usuarios
          </a>
        </li>
        <li>
          <a href="categoria1/subcategoria3.php">
            <i class="bi bi-trash"></i> Subcategoría 3
          </a>
        </li>
      </ul>
    </li>

    <!-- Categoría 2 -->
    <li class="menu-item">
      <a href="#" class="menu-link">
        <i class="bi bi-people icono-menu"></i> Categoría 2
        <span class="toggle-icon">&#9654;</span>
      </a>
      <ul class="submenu">
        <li>
          <a href="categoria2/subcategoria1.php">
            <i class="bi bi-person-plus"></i> Subcategoría 1
          </a>
        </li>
        <li>
          <a href="categoria2/subcategoria2.php">
            <i class="bi bi-person-lines-fill"></i> Subcategoría 2
          </a>
        </li>
        <li>
          <a href="categoria2/subcategoria3.php">
            <i class="bi bi-person-x"></i> Subcategoría 3
          </a>
        </li>
      </ul>
    </li>

    <!-- Categoría 3 -->
    <li class="menu-item">
      <a href="#" class="menu-link">
        <i class="bi bi-file-earmark-text icono-menu"></i> Categoría 3
        <span class="toggle-icon">&#9654;</span>
      </a>
      <ul class="submenu">
        <li>
          <a href="categoria3/subcategoria1.php">
            <i class="bi bi-file-earmark-plus"></i> Subcategoría 1
          </a>
        </li>
        <li>
          <a href="categoria3/subcategoria2.php">
            <i class="bi bi-file-earmark-check"></i> Subcategoría 2
          </a>
        </li>
        <li>
          <a href="categoria3/subcategoria3.php">
            <i class="bi bi-file-earmark-x"></i> Subcategoría 3
          </a>
        </li>
      </ul>
    </li>

    <!-- Categoría 4 -->
    <li class="menu-item">
      <a href="#" class="menu-link">
        <i class="bi bi-bar-chart icono-menu"></i> Categoría 4
        <span class="toggle-icon">&#9654;</span>
      </a>
      <ul class="submenu">
        <li>
          <a href="categoria4/subcategoria1.php">
            <i class="bi bi-graph-up"></i> Subcategoría 1
          </a>
        </li>
        <li>
          <a href="categoria4/subcategoria2.php">
            <i class="bi bi-pie-chart"></i> Subcategoría 2
          </a>
        </li>
        <li>
          <a href="categoria4/subcategoria3.php">
            <i class="bi bi-table"></i> Subcategoría 3
          </a>
        </li>
      </ul>
    </li>

    <!-- Categoría 5 -->
    <li class="menu-item">
      <a href="#" class="menu-link">
        <i class="bi bi-gear-fill icono-menu"></i> Categoría 5
        <span class="toggle-icon">&#9654;</span>
      </a>
      <ul class="submenu">
        <li>
          <a href="categoria5/subcategoria1.php">
            <i class="bi bi-wrench"></i> Subcategoría 1
          </a>
        </li>
        <li>
          <a href="categoria5/subcategoria2.php">
            <i class="bi bi-tools"></i> Subcategoría 2
          </a>
        </li>
        <li>
          <a href="categoria5/subcategoria3.php">
            <i class="bi bi-sliders"></i> Subcategoría 3
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

<!-- Estilo y funcionalidad del sidebar -->
<style>
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