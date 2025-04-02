<?php include '../../inc/sidebar.php'; ?>

<?php
require_once '../cnx.php';
?>

<!-- Contenido principal -->
<div class="content-wrapper">
    <div class="container mt-4">
        <?php if(isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>¡Éxito!</strong> 
                <?php if(isset($_GET['nombre'])): ?>
                    El cliente "<?php echo htmlspecialchars($_GET['nombre']); ?>" ha sido <?php echo (isset($_GET['accion']) && $_GET['accion'] == 'eliminado') ? 'eliminado' : 'registrado'; ?> exitosamente.
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
                            echo "ID de cliente inválido.";
                            break;
                        case 'cliente_no_encontrado':
                            echo "El cliente no existe.";
                            break;
                        case 'eliminacion_fallida':
                            echo "No se pudo eliminar el cliente. ";
                            if(isset($_GET['mensaje'])) echo htmlspecialchars($_GET['mensaje']);
                            break;
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
                <div>
                    <button onclick="window.location.href='agregar.php'" class="btn btn-success me-2">
                        <i class="bi bi-person-plus"></i> Nuevo Cliente
                    </button>
                    <button onclick="window.location.href='generar_pdf.php'" class="btn btn-light">
                        <i class="bi bi-printer"></i> Imprimir
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tabla-clientes">
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
                                      FROM clientes 
                                      ORDER BY id DESC";
                            $stmt = $conn->prepare($query);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if (!$result) {
                                die("Error en la consulta: " . $conn->error);
                            }
                            
                            while($row = $result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td>
                                    <?php if(!empty($row['imagen']) && $row['imagen'] != 'default.png'): ?>
                                        <img src="ver_imagen.php?id=<?php echo $row['id']; ?>" 
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
                                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($row['identificacion']); ?></td>
                                <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="cliente_datos.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="editar_cliente.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="agregar_deuda.php?cliente_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" title="Agregar deuda">
                                            <i class="bi bi-plus-circle"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="confirmarEliminacion(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nombre'], ENT_QUOTES); ?>')" 
                                                title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
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

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="eliminarModal" tabindex="-1" aria-labelledby="eliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="eliminarModalLabel">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro que desea eliminar al cliente <span id="nombreCliente"></span>?
                <p class="text-danger mt-2">Esta acción no se puede deshacer y eliminará todos los datos asociados al cliente.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="btnEliminar" class="btn btn-danger">Eliminar</a>
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
    body.dark-mode .modal-content {
        background-color: #1e2746;
        color: #fff;
    }
    body.dark-mode .modal-body {
        color: #fff;
    }
    body.dark-mode .dataTables_info,
    body.dark-mode .dataTables_length,
    body.dark-mode .dataTables_filter label {
        color: #fff !important;
    }
    body.dark-mode .paginate_button,
    body.dark-mode .dataTables_length select,
    body.dark-mode .dataTables_filter input {
        color: #fff !important;
        background-color: #2a3356 !important;
        border-color: #3a4366 !important;
    }
    body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
    body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        color: #fff !important;
        background: #3a4366 !important;
        border-color: #4a5376 !important;
    }
    body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        color: #fff !important;
        background: #4a5376 !important;
        border-color: #5a6386 !important;
    }
</style>

<script>
    function confirmarEliminacion(id, nombre) {
        document.getElementById('nombreCliente').textContent = nombre;
        document.getElementById('btnEliminar').href = 'eliminar_cliente.php?id=' + id;
        var modal = new bootstrap.Modal(document.getElementById('eliminarModal'));
        modal.show();
    }

    // Inicializar DataTables
    $(document).ready(function() {
        $('#tabla-clientes').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            },
            "order": [[0, "desc"]],
            "pageLength": 10
        });
    });
</script>
