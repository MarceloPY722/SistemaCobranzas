<?php include '../../../admin/include/sidebar.php'; ?>

<?php
require_once '../../../admin/include/cnx.php';
$conn = $pdo;
?>

  <!-- Contenido principal -->
  <div class="content-wrapper">
      <div class="container mt-4">
          <?php if(isset($_GET['success']) && $_GET['success'] == 1 && isset($_GET['nombre'])): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <strong>¡Éxito!</strong> El cliente "<?php echo htmlspecialchars($_GET['nombre']); ?>" ha sido borrado exitosamente.
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
          <?php endif; ?>
          
          <?php if(isset($_GET['error'])): ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <strong>¡Error!</strong> 
                  <?php 
                      switch($_GET['error']) {
                          case 'id_invalido':
                              echo "ID de cliente inválido.";
                              break;
                          case 'cliente_no_encontrado':
                              echo "El cliente no existe.";
                              break;
                          case 'eliminacion_fallida':
                              echo "No se pudo eliminar el cliente. ";
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
                  <h4 class="mb-0">Lista de Clientes</h4>
                  <button onclick="window.location.href='generar2_pdf.php'" class="btn btn-light">
                      <i class="bi bi-printer"></i> Imprimir
                  </button>
              </div>
              <div class="card-body">
                  <div class="table-responsive">
                      <table class="table table-hover">
                          <thead>
                              <tr>
                                  <th>#</th>
                                  <th>Imagen</th>
                                  <th>Identificación</th>
                                  <th>Nombre</th>
                                  <th>Email</th>
                                  <th>Acciones</th>
                              </tr>
                          </thead>
                          <tbody>
                              <?php
                              // Consulta para obtener los clientes
                              $query = "SELECT c.id, c.identificacion, c.email, c.imagen, c.nombre 
                                        FROM clientes c 
                                        ORDER BY c.id DESC";
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
                                      <?php if(!empty($row['imagen'])): ?>
                                          <!-- Se muestra la imagen desde la BD usando mostrar_imagen.php -->
                                          <img src="ver_imagen.php?id=<?php echo $row['id']; ?>" 
                                               alt="Perfil" 
                                               class="rounded-circle profile-image"
                                               width="40" 
                                               height="40">
                                      <?php else: ?>
                                          <!-- Si no hay imagen en la BD, se muestra la imagen por defecto -->
                                          <img src="/sistemacobranzas/uploads/profiles/default.png" 
                                               alt="Perfil" 
                                               class="rounded-circle profile-image"
                                               width="40" 
                                               height="40">
                                      <?php endif; ?>
                                  </td>
                                  <td><?php echo $row['identificacion']; ?></td>
                                  <td><?php echo $row['nombre']; ?></td>
                                  <td><?php echo $row['email']; ?></td>
                                  <td>
                                      <button class="btn btn-sm btn-custom-info" onclick="verCliente(<?php echo $row['id']; ?>)">
                                          <i class="bi bi-eye"></i> Ver Cliente
                                      </button>
                                      <button class="btn btn-sm btn-custom-delete" onclick="eliminarCliente(<?php echo $row['id']; ?>)">
                                          <i class="bi bi-trash"></i> Eliminar
                                      </button>
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
    /* Estilos generales */
    .content-wrapper {
        margin-left: 250px;
        padding: 20px;
    }
    .bg-custom {
        background-color: #121a35;
    }
    .btn-custom-info {
        background-color: #0dcaf0;
        color: white;
    }
    .btn-custom-info:hover {
        background-color: #0bacda;
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
    .profile-image {
        object-fit: cover;
        border: 2px solid #121a35;
    }
    
    /* Estilos específicos para modo oscuro */
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
    
    /* Estilos para impresión */
    @media print {
        .sidebar, .btn-custom-edit, .btn-custom-delete, .btn-custom-info {
            display: none;
        }
        .content-wrapper {
            margin-left: 0;
            padding: 0;
        }
        body {
            background-color: white !important;
            color: black !important;
        }
        .card {
            border: none !important;
        }
        .card-header {
            background-color: white !important;
            color: black !important;
            border-bottom: 1px solid #ddd;
        }
        .table {
            color: black !important;
        }
        .table td, .table th {
            color: black !important;
        }
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

    function verCliente(id) {
        window.location.href = `cliente_datos.php?id=${id}`;
    }

    function eliminarCliente(id) {
        if(confirm('¿Está seguro de que desea eliminar este cliente?')) {
            window.location.href = `eliminar_cliente.php?id=${id}`;
        }
    }
  </script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
