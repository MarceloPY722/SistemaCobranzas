<?php
require_once '../../inc/auth.php';
require_once '../cnx.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../index.php');
    exit;
}

$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'dia';

$fecha_inicio = null;
$fecha_fin = date('Y-m-d');

switch ($periodo) {
    case 'dia':
        $fecha_inicio = date('Y-m-d');
        $titulo_periodo = "Hoy";
        break;
    case 'semana':
        $fecha_inicio = date('Y-m-d', strtotime('-1 week'));
        $titulo_periodo = "Últimos 7 días";
        break;
    case 'mes':
        $fecha_inicio = date('Y-m-d', strtotime('-1 month'));
        $titulo_periodo = "Último mes";
        break;
    case 'tres_meses':
        $fecha_inicio = date('Y-m-d', strtotime('-3 months'));
        $titulo_periodo = "Últimos 3 meses";
        break;
    default:
        $fecha_inicio = date('Y-m-d');
        $titulo_periodo = "Hoy";
        $periodo = 'dia';
}

$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : null;
$metodo_pago = isset($_GET['metodo_pago']) ? $_GET['metodo_pago'] : null;

$query = "SELECT p.*, d.descripcion as deuda_descripcion, c.nombre as cliente_nombre, c.id as cliente_id
          FROM pagos p
          JOIN deudas d ON p.deuda_id = d.id
          JOIN clientes c ON d.cliente_id = c.id
          WHERE p.fecha_pago BETWEEN ? AND ?";

$params = [$fecha_inicio, $fecha_fin];
$types = "ss";

if ($cliente_id) {
    $query .= " AND c.id = ?";
    $params[] = $cliente_id;
    $types .= "i";
}

if ($metodo_pago) {
    $query .= " AND p.metodo_pago = ?";
    $params[] = $metodo_pago;
    $types .= "s";
}

$query .= " ORDER BY p.fecha_pago DESC, p.id DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$query_clientes = "SELECT id, nombre FROM clientes ORDER BY nombre ASC";
$result_clientes = $conn->query($query_clientes);

$query_metodos = "SELECT DISTINCT metodo_pago FROM pagos WHERE metodo_pago IS NOT NULL ORDER BY metodo_pago ASC";
$result_metodos = $conn->query($query_metodos);

$total_pagos = 0;
$pagos_array = [];

while ($row = $result->fetch_assoc()) {
    $pagos_array[] = $row;
    $total_pagos += $row['monto_pagado'];
}

function formatMoney($amount) {
    return number_format($amount, 0, ',', '.') . ' Gs.';
}

include '../../inc/header.php';
include '../../inc/sidebar.php';
?>

<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row mb-4">
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../../index.php"><i class="bi bi-house">  Inicio</i> </a></li>
                        <li class="breadcrumb-item active" aria-current="page">Historial de Pagos</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-custom text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                        <span class="text-black"> <i class="bi bi-clock-history me-2"></i>
                            Historial de Pagos- <?php echo $titulo_periodo; ?> </span> 
                        </h5>
                        <div>
                            <button class="btn btn-sm btn-light" onclick="exportToExcel()">
                                <i class="bi bi-file-earmark-excel me-1"></i> Exportar a Excel
                            </button>
                            <button class="btn btn-sm btn-light ms-2" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i> Imprimir
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtros y Períodos -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="btn-group" role="group">
                                    <a href="?periodo=dia<?php echo $cliente_id ? '&cliente_id='.$cliente_id : ''; echo $metodo_pago ? '&metodo_pago='.$metodo_pago : ''; ?>" 
                                       class="btn <?php echo $periodo == 'dia' ? 'btn-primary' : 'btn-outline-primary'; ?>">Hoy</a>
                                    <a href="?periodo=semana<?php echo $cliente_id ? '&cliente_id='.$cliente_id : ''; echo $metodo_pago ? '&metodo_pago='.$metodo_pago : ''; ?>" 
                                       class="btn <?php echo $periodo == 'semana' ? 'btn-primary' : 'btn-outline-primary'; ?>">Últimos 7 días</a>
                                    <a href="?periodo=mes<?php echo $cliente_id ? '&cliente_id='.$cliente_id : ''; echo $metodo_pago ? '&metodo_pago='.$metodo_pago : ''; ?>" 
                                       class="btn <?php echo $periodo == 'mes' ? 'btn-primary' : 'btn-outline-primary'; ?>">Último mes</a>
                                    <a href="?periodo=tres_meses<?php echo $cliente_id ? '&cliente_id='.$cliente_id : ''; echo $metodo_pago ? '&metodo_pago='.$metodo_pago : ''; ?>" 
                                       class="btn <?php echo $periodo == 'tres_meses' ? 'btn-primary' : 'btn-outline-primary'; ?>">Últimos 3 meses</a>
                                </div>
                            </div>
                            
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-muted">Total de Pagos</h6>
                                        <h3 class="card-text text-primary"><?php echo count($pagos_array); ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-muted">Monto Total</h6>
                                        <h3 class="card-text text-success"><?php echo formatMoney($total_pagos); ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-muted">Promedio por Pago</h6>
                                        <h3 class="card-text text-info">
                                            <?php echo count($pagos_array) > 0 ? formatMoney($total_pagos / count($pagos_array)) : formatMoney(0); ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tabla de pagos -->
                        <?php if (count($pagos_array) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="tabla-pagos">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Descripción</th>
                                            <th>Monto</th>
                                            <th>Método</th>
                                            <th>Fecha</th>
                                            <th>Registrado por</th>
                                         
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pagos_array as $pago): ?>
                                            <tr>
                                                <td><?php echo $pago['id']; ?></td>
                                                <td>
                                                    <a href="../clientes/cliente_datos.php?id=<?php echo $pago['cliente_id']; ?>">
                                                        <?php echo htmlspecialchars($pago['cliente_nombre']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($pago['deuda_descripcion']); ?></td>
                                                <td class="text-end"><?php echo formatMoney($pago['monto_pagado']); ?></td>
                                                <td><?php echo htmlspecialchars($pago['metodo_pago'] ?: 'No especificado'); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></td>
                                              
                                                <td><?php echo 'Sistema'; ?></td>
                                                <td>
                            
                                                    <?php if ($pago['comprobante']): ?>
                                                        <a href="../../../uploads/comprobantes/<?php echo $pago['comprobante']; ?>" target="_blank" class="btn btn-sm btn-secondary">
                                                            <i class="bi bi-file-earmark"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i> No se encontraron pagos en el período seleccionado.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalle de pago -->
<div class="modal fade" id="detallePagoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-custom text-white">
                <h5 class="modal-title">Detalle del Pago</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detallePagoBody">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Función para ver detalle de pago
function verDetallePago(pagoId) {
    const modal = new bootstrap.Modal(document.getElementById('detallePagoModal'));
    const modalBody = document.getElementById('detallePagoBody');
    
    // Mostrar modal con spinner
    modal.show();
    
    // Aquí se implementaría la carga de datos del pago mediante AJAX
    // Por ahora, simulamos con datos estáticos
    setTimeout(() => {
        // Buscar el pago en el array de pagos
        <?php 
        $pagos_json = json_encode($pagos_array);
        echo "const pagos = " . $pagos_json . ";";
        ?>
        
        const pago = pagos.find(p => p.id == pagoId);
        
        if (pago) {
            let html = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1 text-muted">ID de Pago</p>
                        <p class="fw-bold">${pago.id}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-muted">Fecha de Pago</p>
                        <p class="fw-bold">${new Date(pago.fecha_pago).toLocaleDateString('es-ES')}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1 text-muted">Cliente</p>
                        <p class="fw-bold">${pago.cliente_nombre}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-muted">Monto Pagado</p>
                        <p class="fw-bold text-success">${formatMoney(pago.monto_pagado)}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1 text-muted">Método de Pago</p>
                        <p class="fw-bold">${pago.metodo_pago || 'No especificado'}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-muted">Registrado por</p>
                        <p class="fw-bold">${pago.usuario_nombre || 'Sistema'}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <p class="mb-1 text-muted">Descripción de la Deuda</p>
                        <p class="fw-bold">${pago.deuda_descripcion}</p>
                    </div>
                </div>`;
                
            if (pago.notas) {
                html += `
                <div class="row mb-3">
                    <div class="col-12">
                        <p class="mb-1 text-muted">Notas</p>
                        <p>${pago.notas}</p>
                    </div>
                </div>`;
            }
            
            if (pago.comprobante) {
                html += `
                <div class="row">
                    <div class="col-12 text-center">
                        <p class="mb-1 text-muted">Comprobante</p>
                        <a href="../../../uploads/comprobantes/${pago.comprobante}" target="_blank" class="btn btn-sm btn-primary">
                            <i class="bi bi-file-earmark me-1"></i> Ver Comprobante
                        </a>
                    </div>
                </div>`;
            }
            
            modalBody.innerHTML = html;
        } else {
            modalBody.innerHTML = '<div class="alert alert-danger">No se encontró información del pago.</div>';
        }
    }, 500);
}

// Función para formatear montos
function formatMoney(amount) {
    return new Intl.NumberFormat('es-PY', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount) + ' Gs.';
}

// Función para exportar a Excel
function exportToExcel() {
    // Crear un elemento temporal para descargar
    let link = document.createElement("a");
    
    // Obtener la tabla
    let table = document.getElementById("tabla-pagos");
    
    // Crear una cadena CSV
    let csv = [];
    let rows = table.querySelectorAll("tr");
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll("td, th");
        
        for (let j = 0; j < cols.length - 1; j++) { // Excluir la columna de acciones
            // Limpiar el texto (quitar espacios extras y saltos de línea)
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").replace(/(\s\s)/gm, " ");
            // Escapar comillas dobles
            data = data.replace(/"/g, '""');
            // Añadir comillas dobles alrededor del campo
            row.push('"' + data + '"');
        }
        csv.push(row.join(","));
    }
    
    // Combinar en una sola cadena CSV
    let csv_string = csv.join("\n");
    
    // Crear un blob con la cadena CSV
    let blob = new Blob(["\ufeff" + csv_string], { type: 'text/csv;charset=utf-8;' });
    
    // Crear URL para el blob
    let url = URL.createObjectURL(blob);
    
    // Configurar el enlace para descargar
    link.setAttribute("href", url);
    link.setAttribute("download", "historial_pagos_<?php echo $periodo; ?>.csv");
    link.style.visibility = 'hidden';
    
    // Añadir a la página, hacer clic y eliminar
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Inicializar tooltips y popovers de Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Inicializar popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
</script>

<style>
@media print {
    .sidebar, .navbar,  .btn-group, .collapse, button, .actions-column, .modal {
        display: none !important;
    
    }
    .breadcrumb {
        display: block!important;
        text-decoration: none; 
    }	

    .content-wrapper {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
    
    table {
        width: 100% !important;
    }
    
    th:last-child, td:last-child {
        display: none;
    }
}

/* Dark Mode Styles */
body.dark-mode {
    background-color: #121212;
    color: #e0e0e0;
}

body.dark-mode .content-wrapper {
    background-color: #121212;
}

body.dark-mode .card {
    background-color: #1e1e1e;
    border-color: #333;
}

body.dark-mode .card-header {
    background-color: #2c2c2c !important;
    color: #fff !important;
}

body.dark-mode .table {
    color: #ffffff !important; /* Changed to white for better visibility */
}

body.dark-mode .table-light th {
    background-color: #2c2c2c;
    color: #ffffff; /* Changed to white for better visibility */
    border-color: #444;
}

body.dark-mode .table td {
    border-color: #444;
    color: #ffffff; /* Added to ensure all table cells have white text */
}

body.dark-mode .table-hover tbody tr:hover {
    background-color: #2c2c2c;
}

/* Additional styles to ensure all text in the table is white in dark mode */
body.dark-mode .table a {
    color: #8bb9fe; /* Lighter blue for links in dark mode */
}

body.dark-mode .table .text-end,
body.dark-mode .table .text-success,
body.dark-mode .table .text-primary,
body.dark-mode .table .text-info {
    color: #ffffff !important; /* Force all text colors to be white */
}
body.dark-mode .table-hover tbody tr:hover {
    background-color: #2c2c2c;
}

body.dark-mode .breadcrumb {
    background-color: #1e1e1e;
}

body.dark-mode .breadcrumb-item a {
    color: #6ea8fe;
}

body.dark-mode .breadcrumb-item.active {
    color: #adb5bd;
}

body.dark-mode .card.bg-light {
    background-color: #2c2c2c !important;
}

body.dark-mode .text-muted {
    color: #adb5bd !important;
}

body.dark-mode .btn-outline-primary {
    color: #6ea8fe;
    border-color: #6ea8fe;
}

body.dark-mode .btn-outline-primary:hover {
    background-color: #0d6efd;
    color: #fff;
}

body.dark-mode .btn-outline-secondary {
    color: #adb5bd;
    border-color: #adb5bd;
}

body.dark-mode .modal-content {
    background-color: #1e1e1e;
    color: #e0e0e0;
    border-color: #333;
}

body.dark-mode .modal-header {
    border-bottom-color: #333;
}

body.dark-mode .modal-footer {
    border-top-color: #333;
}

body.dark-mode .alert-info {
    background-color: #0d3251;
    color: #6ea8fe;
    border-color: #0d4377;
}

body.dark-mode a {
    color: #6ea8fe;
}

/* Dark mode toggle button - Add this to your header or sidebar */
.dark-mode-toggle {
    cursor: pointer;
    padding: 5px 10px;
    border-radius: 4px;
    margin-left: 10px;
}
</style>

<script>
// Add dark mode toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    // Check for saved dark mode preference
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    
    // Apply dark mode if saved preference exists
    if (isDarkMode) {
        document.body.classList.add('dark-mode');
    }
    
    // Add dark mode toggle button to the DOM if it doesn't exist
    // This should be added to your header or sidebar in a separate file
    // But we'll include the toggle functionality here
    
    // Listen for dark mode toggle events from other parts of the application
    window.addEventListener('darkModeToggle', function() {
        document.body.classList.toggle('dark-mode');
        localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
    });
});
</script>