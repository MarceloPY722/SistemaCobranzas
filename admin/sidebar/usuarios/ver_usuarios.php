<?php include 'inc/sidebar.php'; ?>

<?php
require_once 'inc/cnx.php';
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
                                  <th>Nombre</th>
                                  <th>Email</th>
                                  <th>Rol</th>
                                  <th>Estado</th>
                                  <th>Acciones</th>
                              </tr>
                          </thead>
                          <tbody>
                              <?php
                              // Consulta para obtener los usuarios con sus roles
                              $query = "SELECT u.id, u.nombre, u.email, u.imagen, u.activo, r.nombre as rol_nombre 
                                        FROM usuarios u 
                                        JOIN roles r ON u.rol_id = r.id
                                        ORDER BY u.id DESC";
                              $result = $conn->query($query);

                              if (!$result) {
                                  die("Error en la consulta: " . $conn->error);
                              }
                              while($row = $result->fetch_assoc()):
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
  </style>

  <script>
    function editarUsuario(id) {
        window.location.href = `editar_usuario.php?id=${id}`;
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