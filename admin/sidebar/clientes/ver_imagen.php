<?php
require_once 'inc/cnx.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Content-Type: image/png');
    readfile($_SERVER['DOCUMENT_ROOT'] . '/sistemacobranzas/uploads/profiles/default.png');
    exit;
}

$cliente_id = intval($_GET['id']);

$query = "SELECT c.imagen FROM clientes c WHERE c.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  
    header('Content-Type: image/png');
    readfile($_SERVER['DOCUMENT_ROOT'] . '/sistemacobranzas/uploads/profiles/default.png');
    exit;
}

$cliente = $result->fetch_assoc();
$imagen = $cliente['imagen'];

if (empty($imagen) || $imagen === 'default.png') {
    header('Content-Type: image/png');
    readfile($_SERVER['DOCUMENT_ROOT'] . '/sistemacobranzas/uploads/profiles/default.png');
    exit;
}

$ruta_imagen = $_SERVER['DOCUMENT_ROOT'] . '/sistemacobranzas/uploads/profiles/' . $imagen;

if (!file_exists($ruta_imagen)) {
    header('Content-Type: image/png');
    readfile($_SERVER['DOCUMENT_ROOT'] . '/sistemacobranzas/uploads/profiles/default.png');
    exit;
}

$extension = strtolower(pathinfo($imagen, PATHINFO_EXTENSION));
$content_type = 'image/jpeg'; 

switch ($extension) {
    case 'png':
        $content_type = 'image/png';
        break;
    case 'gif':
        $content_type = 'image/gif';
        break;
    case 'jpg':
    case 'jpeg':
        $content_type = 'image/jpeg';
        break;
}

header('Content-Type: ' . $content_type);
readfile($ruta_imagen);
exit;
?>
