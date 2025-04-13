<?php
session_start();
require_once 'cliente/include/cnx.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['cliente_id']) && !isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Verificar si se proporcionó un ID de documento
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$documento_id = intval($_GET['id']);

// Obtener información del documento
$query = "SELECT * FROM documentos WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$documento_id]);
$documento = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar si el documento existe
if (!$documento) {
    die('Documento no encontrado');
}

// Verificar permisos (cliente solo puede ver sus propios documentos)
if (isset($_SESSION['cliente_id']) && $documento['cliente_id'] != $_SESSION['cliente_id']) {
    die('No tiene permisos para acceder a este documento');
}

// Ruta al archivo
$file_path = __DIR__ . '/uploads/documentos/' . $documento['ruta_archivo'];

// Verificar si el archivo existe
if (!file_exists($file_path)) {
    die('El archivo no existe en el servidor: ' . $file_path);
}

// Obtener información del archivo
$file_name = $documento['nombre_original'] ?? basename($documento['ruta_archivo']);
$file_size = filesize($file_path);

// Determinar el tipo MIME del archivo
if (function_exists('mime_content_type')) {
    $file_type = mime_content_type($file_path);
} else {
    // Fallback para determinar el tipo MIME basado en la extensión
    $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    $mime_types = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'txt' => 'text/plain',
    ];
    $file_type = $mime_types[$extension] ?? 'application/octet-stream';
}

// Asegurar que no haya salida antes de los encabezados
if (ob_get_level()) {
    ob_end_clean();
}

// Configurar encabezados para la descarga
header('Content-Description: File Transfer');
header('Content-Type: ' . $file_type);
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . $file_size);

// Leer y enviar el archivo
readfile($file_path);
exit;