<?php include '../../../admin/include/sidebar.php'; ?>

<?php
require_once '../../../admin/include/cnx.php';
$conn = $pdo;
?>

  <!-- Contenido principal -->
  <div class="content-wrapper">
      <div class="container mt-4">
          <?php if(isset($_GET['success']) && $_GET['success'] == 1): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <strong>¡Éxito!</strong> 
                  <?php if(isset($_GET['nombre'])): ?>
                      El usuario "<?php echo htmlspecialchars($_GET['nombre']); ?>" ha sido borrado exitosamente.
                  <?php else: ?>
                      La operación se completó exitosamente.
                  <?php endif; ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
          <?php endif; ?>
          
          <?php if(isset($_GET['error'])): ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <strong>¡Error!</strong> 
                  <?php 
                      switch($_GET['error']) {
                          case 'id_invalido':
                              echo "ID de usuario inválido.";
                              break;
                          case 'usuario_no_encontrado':
                              echo "El usuario no existe.";
                              break;
                          case 'eliminacion_fallida':
                              echo "No se pudo eliminar el usuario. ";
                              if(isset($_GET['mensaje'])) echo htmlspecialchars($_GET['mensaje']);
                              break;
                          default:
                              echo "Ocurrió un error inesperado.";
                      }
                  ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
          <?php endif; ?>
          
          <div class="card">
              <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                  <h4 class="mb-0">Lista de Usuarios</h4>
                  <button onclick="window.location.href='generar01_pdf.php'; window.open('generar01_pdf.php');" class="btn btn-light" >
                      <i class="bi bi-printer"></i> Imprimir
                  </button>
              </div>
              <div class="card-body">
                  <div class="table-responsive">
                      <table class="table table-hover">
                          <thead>
                              <tr>
                                  <th>#</th>
                                  <th>Perfil</th>
                                  <th>Nombre</th>
                                  <th>Email</th>
                                  <th>Rol</th>
                                  <th>Estado</th>
                                  <th>Acciones</th>
                              </tr>
                          </thead>
                          <tbody>
                              <?php
                              $query = "SELECT u.id, u.nombre, u.email, u.imagen, u.activo, r.nombre as rol_nombre 
                                        FROM usuarios u 
                                        JOIN roles r ON u.rol_id = r.id
                                        WHERE r.id != 3
                                        ORDER BY u.id DESC";
                              $stmt = $conn->prepare($query);
                              $stmt->execute();
                              
                              if (!$stmt) {
                                  die("Error en la consulta: " . $conn->errorInfo()[2]);
                              }
                              
                              while($row = $stmt->fetch()):
                              ?>
                              <tr>
                                  <td><?php echo $row['id']; ?></td>
                                  <td>
                                      <?php if(!empty($row['imagen']) && $row['imagen'] != 'default.png'): ?>
                                          <img src="/sistemacobranzas/uploads/usuarios/<?php echo $row['imagen']; ?>" 
                                               alt="Perfil" 
                                               class="rounded-circle profile-image"
                                               width="40" 
                                               height="40">
                                      <?php else: ?>
                                          <img src="/sistemacobranzas/uploads/usuarios/default.png" 
                                               alt="Perfil" 
                                               class="rounded-circle profile-image"
                                               width="40" 
                                               height="40">
                                      <?php endif; ?>
                                  </td>
                                  <td><?php echo $row['nombre']; ?></td>
                                  <td><?php echo $row['email']; ?></td>
                                  <td><span class="badge bg-info"><?php echo $row['rol_nombre']; ?></span></td>
                                  <td>
                                      <?php if($row['activo'] == 1): ?>
                                          <span class="badge bg-success">Activo</span>
                                      <?php else: ?>
                                          <span class="badge bg-danger">Inactivo</span>
                                      <?php endif; ?>
                                  </td>
                                  <td>
                                      <button class="btn btn-sm btn-custom-edit" onclick="editarUsuario(<?php echo $row['id']; ?>)">
                                          <i class="bi bi-pencil"></i> Editar
                                      </button>
                                      <?php if($row['id'] != 1): // Evitar eliminar al usuario administrador principal ?>
                                      <button class="btn btn-sm btn-custom-delete" onclick="eliminarUsuario(<?php echo $row['id']; ?>)">
                                          <i class="bi bi-trash"></i> Eliminar
                                      </button>
                                      <?php endif; ?>
                                  </td>
                              </tr>
                              <?php endwhile; ?>
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
      </div>
  </div>

  <style>
    .content-wrapper {
        margin-left: 250px;
        padding: 20px;
    }
    .bg-custom {
        background-color: #121a35;
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
    .btn-custom-info {
        background-color: #0dcaf0;
        color: white;
    }
    .btn-custom-info:hover {
        background-color: #0bacda;
        color: white;
    }
    .profile-image {
        object-fit: cover;
        border: 2px solid #121a35;
    }
    @media print {
        .sidebar, .btn-custom-edit, .btn-custom-delete, .btn-custom-info {
            display: none;
        }
        .content-wrapper {
            margin-left: 0;
        }
    }
    
    /* Estilos para modo oscuro */
    body.dark-mode .table {
        color: #fff !important;
    }
    body.dark-mode .table td, 
    body.dark-mode .table th {
        color: #fff !important;
    }
    body.dark-mode .table-hover tbody tr:hover {
        background-color: #2a3356 !important;
        color: #fff !important;
    }
    body.dark-mode .table-hover tbody tr:hover td {
        color: #fff !important;
    }
    body.dark-mode .card {
        background-color: #1e2746;
        color: #fff;
    }
    body.dark-mode .card-header {
        background-color: #121a35;
        border-color: #2a3356;
    }
  </style>

  <script>
    // Funcionalidad para mostrar/ocultar submenús en el sidebar
    document.addEventListener('DOMContentLoaded', function() {
      // Seleccionar todos los elementos del menú que tienen submenús
      const menuItems = document.querySelectorAll('.sidebar.unified-sidebar .menu-item');
      
      // Añadir evento de clic a cada elemento del menú
      menuItems.forEach(function(item) {
        const menuLink = item.querySelector('.menu-link');
        
        if (menuLink) {
          menuLink.addEventListener('click', function(e) {
            // Prevenir la navegación si el enlace es "#"
            if (this.getAttribute('href') === '#') {
              e.preventDefault();
            }
            
            // Alternar la clase 'active' en el elemento del menú
            item.classList.toggle('active');
            
            // Rotar el icono de flecha
            const toggleIcon = this.querySelector('.toggle-icon');
            if (toggleIcon) {
              toggleIcon.style.transform = item.classList.contains('active') ? 'rotate(90deg)' : '';
            }
          });
        }
      });
      
      // Marcar como activo el menú actual basado en la URL
      const currentPath = window.location.pathname;
      document.querySelectorAll('.sidebar.unified-sidebar .submenu a').forEach(function(link) {
        if (link.getAttribute('href') === currentPath) {
          const parentItem = link.closest('.menu-item');
          if (parentItem) {
            parentItem.classList.add('active');
          }
        }
      });
    });

    function editarUsuario(id) {
        window.location.href = `modificar_usuarios.php?id=${id}`;
    }

    function eliminarUsuario(id) {
        if(confirm('¿Está seguro de que desea eliminar este usuario?')) {
            window.location.href = `eliminar_usuario.php?id=${id}`;
        }
    }
  </script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>