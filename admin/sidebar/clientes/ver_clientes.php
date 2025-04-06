<?php include '../../../admin/include/sidebar.php'; ?>

<?php
require_once '../../../admin/include/cnx.php';
$conn = $pdo;

// Procesar búsqueda si existe
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
?>

  <!-- Contenido principal -->
  <div class="content-wrapper">
      <div class="container mt-4">
          <?php if(isset($_GET['success']) && $_GET['success'] == 1 && isset($_GET['nombre'])): ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <strong>¡Éxito!</strong> El cliente "<?php echo htmlspecialchars($_GET['nombre']); ?>" ha sido registrado exitosamente.
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
          <?php endif; ?>
          
          <?php if(isset($_GET['error'])): ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <strong>¡Error!</strong> 
                  <?php 
                      switch($_GET['error']) {
                          case 'campos_requeridos':
                              echo "Todos los campos marcados son obligatorios.";
                              break;
                          case 'identificacion_existente':
                              echo "La identificación ya está registrada en el sistema.";
                              break;
                          case 'db_error':
                              echo "Error en la base de datos. ";
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
                  <div class="d-flex align-items-center">
                      <button onclick="window.location.href='agregar.php'" class="btn btn-success me-2">
                          <i class="bi bi-person-plus"></i> Nuevo Cliente
                      </button>
                      
                      <!-- Barra de búsqueda -->
                      <form action="" method="GET" class="d-flex me-2">
                          <div class="input-group">
                              <input type="text" class="form-control" 
                                     name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>">
                              <button class="btn btn-primary" type="submit">
                                  <i class="bi bi-search"></i>
                              </button>
                              <?php if(!empty($busqueda)): ?>
                              <a href="ver_clientes.php" class="btn btn-outline-secondary">
                                  <i class="bi bi-x-circle"></i>
                              </a>
                              <?php endif; ?>
                          </div>
                      </form>
                      
                      <button onclick="window.location.href='generar2_pdf.php'" class="btn btn-light">
                          <i class="bi bi-printer"></i> Imprimir
                      </button>
                  </div>
              </div>
              <div class="card-body">
                  <div class="table-responsive">
                      <table class="table table-hover">
                          <thead>
                              <tr>
                                  <th>#</th>
                                  <th>Imagen</th>
                                  <th>Nombre</th>
                                  <th>Identificación</th>
                                  <th>Teléfono</th>
                                  <th>Email</th>
                                  <th>Acciones</th>
                              </tr>
                          </thead>
                          <tbody>
                              <?php
                              // Consulta para obtener los clientes
                              $query = "SELECT id, nombre, identificacion, telefono, email, imagen 
                                        FROM clientes";
                              
                              // Agregar condición de búsqueda si existe
                              if (!empty($busqueda)) {
                                  $query .= " WHERE nombre LIKE :busqueda_nombre OR identificacion LIKE :busqueda_id";
                              }
                              
                              $query .= " ORDER BY id DESC";
                              
                              $stmt = $conn->prepare($query);
                              
                              // Vincular parámetros de búsqueda si existen
                              if (!empty($busqueda)) {
                                  $stmt->bindValue(':busqueda_nombre', '%' . $busqueda . '%', PDO::PARAM_STR);
                                  $stmt->bindValue(':busqueda_id', '%' . $busqueda . '%', PDO::PARAM_STR);
                              }
                              
                              $stmt->execute();
                              
                              if (!$stmt) {
                                  die("Error en la consulta: " . $conn->errorInfo()[2]);
                              }
                              
                              $resultados = $stmt->rowCount();
                              
                              while($row = $stmt->fetch()):
                              ?>
                              <tr>
                                  <td><?php echo $row['id']; ?></td>
                                  <td>
                                      <?php if(!empty($row['imagen']) && $row['imagen'] != 'default.png'): ?>
                                          <img src="../../../uploads/profiles/<?php echo $row['imagen']; ?>" 
                                               alt="Perfil" 
                                               class="rounded-circle profile-image"
                                               width="40" 
                                               height="40">
                                      <?php else: ?>
                                          <img src="../../../uploads/profiles/default.png" 
                                               alt="Perfil" 
                                               class="rounded-circle profile-image"
                                               width="40" 
                                               height="40">
                                      <?php endif; ?>
                                  </td>
                                  <td><?php echo $row['nombre']; ?></td>
                                  <td><?php echo $row['identificacion']; ?></td>
                                  <td><?php echo $row['telefono']; ?></td>
                                  <td><?php echo $row['email']; ?></td>
                                  <td>
                                      <button class="btn btn-sm btn-custom-info" onclick="verCliente(<?php echo $row['id']; ?>)">
                                          <i class="bi bi-eye"></i> Ver
                                      </button>
                                      <button class="btn btn-sm btn-primary" onclick="editarCliente(<?php echo $row['id']; ?>)">
                                          <i class="bi bi-pencil"></i> Editar
                                      </button>
                                      <button class="btn btn-sm btn-custom-delete" onclick="eliminarCliente(<?php echo $row['id']; ?>)">
                                          <i class="bi bi-trash"></i> Eliminar
                                      </button>
                                  </td>
                              </tr>
                              <?php endwhile; ?>
                              
                              <?php if($resultados == 0 && !empty($busqueda)): ?>
                              <tr>
                                  <td colspan="7" class="text-center">No se encontraron clientes que coincidan con "<?php echo htmlspecialchars($busqueda); ?>"</td>
                              </tr>
                              <?php endif; ?>
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
    
    /* Dark mode styles */
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
    
    /* Print styles */
    @media print {
        .sidebar, .btn-custom-info, .btn-custom-delete, .btn-primary {
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
    function verCliente(id) {
        window.location.href = `cliente_datos.php?id=${id}`;
    }

    function editarCliente(id) {
        window.location.href = `editar_cliente.php?id=${id}`;
    }

    function eliminarCliente(id) {
        if(confirm('¿Está seguro de que desea eliminar este cliente? Esta acción no se puede deshacer.')) {
            window.location.href = `eliminar_cliente.php?id=${id}`;
        }
    }
  </script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
