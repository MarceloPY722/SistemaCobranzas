<?php include 'inc/sidebar.php'; ?>

<?php
require_once 'inc/cnx.php';
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
                  <button onclick="window.print()" class="btn btn-light">
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
                              $result = $conn->query($query);

                              if (!$result) {
                                  die("Error en la consulta: " . $conn->error);
                              }
                              while($row = $result->fetch_assoc()):
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

  <script>
    // Funcionalidad para mostrar/ocultar submenús en el sidebar
    document.querySelectorAll('.sidebar.unified-sidebar .menu-link').forEach(link => {
      link.addEventListener('click', function(e) {
        const submenu = this.nextElementSibling;
        if (submenu && submenu.classList.contains('submenu')) {
          e.preventDefault();
          this.parentElement.classList.toggle('active');
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
