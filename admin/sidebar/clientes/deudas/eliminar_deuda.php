<?php
// Iniciar buffer de salida para evitar "headers already sent"
ob_start();

include '../../../../admin/include/sidebar.php';
require_once '../../cnx.php';

// Verificar si se proporcionó un ID de deuda
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../ver_clientes.php?error=id_invalido');
    exit();
}

$deuda_id = $_GET['id'];

// Obtener información de la deuda antes de eliminarla
$query_deuda = "SELECT cliente_id, descripcion FROM deudas WHERE id = ?";
$stmt_deuda = $conn->prepare($query_deuda);
$stmt_deuda->bind_param("i", $deuda_id);
$stmt_deuda->execute();
$result_deuda = $stmt_deuda->get_result();

if ($result_deuda->num_rows === 0) {
    header('Location: ../../ver_clientes.php?error=deuda_no_encontrada');
    exit();
}

$deuda = $result_deuda->fetch_assoc();
$cliente_id = $deuda['cliente_id'];
$descripcion_deuda = $deuda['descripcion'];

// Iniciar transacción
$conn->begin_transaction();

try {
    // 1. Eliminar las cuotas asociadas a la deuda
    $query_eliminar_cuotas = "DELETE FROM cuotas_deuda WHERE deuda_id = ?";
    $stmt_eliminar_cuotas = $conn->prepare($query_eliminar_cuotas);
    $stmt_eliminar_cuotas->bind_param("i", $deuda_id);
    $stmt_eliminar_cuotas->execute();
    
    // 2. Eliminar los documentos asociados a la deuda
    $query_eliminar_documentos = "DELETE FROM documentos WHERE deuda_id = ?";
    $stmt_eliminar_documentos = $conn->prepare($query_eliminar_documentos);
    $stmt_eliminar_documentos->bind_param("i", $deuda_id);
    $stmt_eliminar_documentos->execute();
    
    // 3. Eliminar los reclamos asociados a la deuda
    $query_eliminar_reclamos = "DELETE FROM reclamos WHERE deuda_id = ?";
    $stmt_eliminar_reclamos = $conn->prepare($query_eliminar_reclamos);
    $stmt_eliminar_reclamos->bind_param("i", $deuda_id);
    $stmt_eliminar_reclamos->execute();
    
    // 4. Eliminar los pagos asociados a la deuda
    $query_eliminar_pagos = "DELETE FROM pagos WHERE deuda_id = ?";
    $stmt_eliminar_pagos = $conn->prepare($query_eliminar_pagos);
    $stmt_eliminar_pagos->bind_param("i", $deuda_id);
    $stmt_eliminar_pagos->execute();
    
    // 5. Eliminar registros del historial de la deuda
    $query_eliminar_historial = "DELETE FROM historial_deudas WHERE deuda_id = ?";
    $stmt_eliminar_historial = $conn->prepare($query_eliminar_historial);
    $stmt_eliminar_historial->bind_param("i", $deuda_id);
    $stmt_eliminar_historial->execute();
    
    // 6. Finalmente, eliminar la deuda
    $query_eliminar_deuda = "DELETE FROM deudas WHERE id = ?";
    $stmt_eliminar_deuda = $conn->prepare($query_eliminar_deuda);
    $stmt_eliminar_deuda->bind_param("i", $deuda_id);
    $stmt_eliminar_deuda->execute();
    
    // Registrar la acción en el historial general (si existe una tabla para esto)
    if ($conn->query("SHOW TABLES LIKE 'historial_sistema'")->num_rows > 0) {
        $accion = "Eliminación de deuda #$deuda_id: $descripcion_deuda";
        $usuario_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        
        $query_historial = "INSERT INTO historial_sistema (usuario_id, accion, detalles) VALUES (?, ?, ?)";
        $stmt_historial = $conn->prepare($query_historial);
        $detalles = "Se eliminó la deuda #$deuda_id con descripción: $descripcion_deuda";
        $stmt_historial->bind_param("iss", $usuario_id, $accion, $detalles);
        $stmt_historial->execute();
    }
    
    // Confirmar transacción
    $conn->commit();
    
    // Redireccionar a la página del cliente con mensaje de éxito
    header("Location: ../cliente_datos.php?id=$cliente_id&success=deuda_eliminada");
    exit();
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    
    // Redireccionar con mensaje de error
    header("Location: ver_deuda.php?id=$deuda_id&error=eliminar_deuda&mensaje=" . urlencode($e->getMessage()));
    exit();
}

// Liberar el buffer y finalizar el script
ob_end_flush();
exit();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminando Deuda - Sistema de Cobranzas</title>
</head>
<body>
    <div class="content-wrapper">
        <div class="container mt-5">
            <div class="card">
                <div class="card-body text-center">
                    <h3>Procesando eliminación...</h3>
                    <p>Por favor espere mientras se elimina la deuda.</p>
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>