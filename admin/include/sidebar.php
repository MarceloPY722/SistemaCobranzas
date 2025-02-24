<div class="sidebar" id="sidebar">
        <button class="toggle-btn" id="toggleBtn">☰</button>
        <nav>
            <ul>
                <li>
                    <i class="bi bi-house"></i><span>Inicio</span> 
                </li>
                <li class="dropdown">
                    <div class="dropdown-header">
                        <i class="bi bi-people"></i><span>Usuarios</span>
                    </div>
                    <ul class="submenu">
                        <li> 
                            <a href="usuarios/agregar.php">
                                <i class="bi bi-person-add"></i>
                                <span>Agregar</span>
                            </a>
                        </li>
                        <li>
                            <a href="usuarios/modificar.php">
                                <i class="bi bi-person-gear"></i>
                                <span>Modificar</span>
                            </a>
                        </li>
                        <li>
                            <a href="usuarios/eliminar.php">
                                <i class="bi bi-person-x"></i>
                                <span>Eliminar</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="../logout.php" style="color:#fff">
                        <i class="bi bi-gear"></i>
                        <span class="title">Cerrar Sesion</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <style>
        .sidebar {
            background: #2c3e50;
            color: #ecf0f1;
            min-width: 250px;
            max-width: 250px;
            transition: all 0.3s;
            overflow: hidden;
        }
        .sidebar.collapsed {
            min-width: 80px;
            max-width: 80px;
        }
        .sidebar a {
            text-decoration: none;
            color: #ecf0f1;
        }
        .sidebar .toggle-btn {
            background: #1abc9c;
            border: none;
            width: 100%;
            padding: 10px;
            cursor: pointer;
            text-align: center;
        }
        .sidebar nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar nav ul li {
            padding: 15px;
            cursor: pointer;
        }
        .sidebar nav ul li:hover {
            background: #34495e;
        }
        .sidebar nav ul li i {
            font-size: 20px;
            margin-right: 10px;
            width: 30px;
            text-align: center;
            display: inline-block;
        }
        .dropdown-header {
            display: flex;
            align-items: center;
        }
        .submenu {
            display: none;
            padding-left: 20px;
            margin-top: 15px;
            margin-bottom: 5px;
        }
        .submenu li {
            padding: 10px !important;
        }
        .dropdown.active .submenu {
            display: block;
        }
        .sidebar.collapsed nav ul li span {
            display: none;
        }
    </style>

    <script>
        document.querySelectorAll('.dropdown').forEach(dropdown => {
            dropdown.addEventListener('click', function() {
                this.classList.toggle('active');
            });
        });
    </script>