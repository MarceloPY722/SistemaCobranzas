<?php
session_start();
require 'cnx.php';

// Verificar si la conexión PDO está definida, de lo contrario, intentar reconectar
if (!isset($pdo) || !$pdo) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=tu_base_de_datos;charset=utf8mb4", "tu_usuario", "tu_contraseña");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Error de conexión a la base de datos: " . $e->getMessage());
    }
}

// Verificar si hay un usuario logueado para mostrar en el header/perfil
$userData = null;
if (isset($_SESSION['usuario_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT u.nombre, u.email, u.imagen, c.direccion, c.telefono 
                              FROM usuarios u 
                              LEFT JOIN clientes c ON u.id = c.usuario_id 
                              WHERE u.id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            $userData['image'] = $userData['imagen'] ? "/admin/img/" . $userData['imagen'] : "https://via.placeholder.com/150";
        } else {
            $userData = [
                'nombre' => 'Usuario Desconocido',
                'email' => '',
                'direccion' => '',
                'telefono' => '',
                'image' => 'https://via.placeholder.com/150'
            ];
        }
    } catch (PDOException $e) {
        error_log("Error al consultar usuario: " . $e->getMessage());
        $userData = [
            'nombre' => 'Usuario Desconocido',
            'email' => '',
            'direccion' => '',
            'telefono' => '',
            'image' => 'https://via.placeholder.com/150'
        ];
    }
} else {
    // Si no hay sesión, mostrar datos predeterminados
    $userData = [
        'nombre' => 'Usuario Desconocido',
        'email' => '',
        'direccion' => '',
        'telefono' => '',
        'image' => 'https://via.placeholder.com/150'
    ];
}

// Función para cerrar sesión
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php'); // Redirige a la página principal o de login
    exit();
}

// Función para borrar cuenta
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    if (isset($_SESSION['usuario_id'])) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("DELETE FROM clientes WHERE usuario_id = ?");
            $stmt->execute([$_SESSION['usuario_id']]);
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$_SESSION['usuario_id']]);
            $pdo->commit();
            session_destroy();
            header('Location: index.php'); // Redirige a la página principal o de login
            exit();
        } catch (PDOException $e) {
            error_log("Error al borrar cuenta: " . $e->getMessage());
            header('Location: header.php?error=delete_failed');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Header con Perfil</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #ffffff;
            margin: 0;
        }

        /* Estilo del Header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #ffffff;
            border-bottom: 1px solid #e0e0e0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .logo {
            height: 40px; /* Ajusta según el tamaño de tu logo */
        }

        .nav-menu {
            display: flex;
            gap: 30px;
        }

        .nav-menu a {
            text-decoration: none;
            color: #333;
            font-size: 16px;
            position: relative;
        }

        .nav-menu a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: #ff0000;
            transition: width 0.3s ease;
        }

        .nav-menu a:hover::after {
            width: 100%;
        }

        .nav-menu a.active::after {
            width: 100%;
        }

        .profile-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .profile-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid #007bff;
        }

        .profile-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Estilo del Modal de Perfil */
        .profile-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .profile-content {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .profile-content img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin-bottom: 20px;
            border: 4px solid #007bff;
        }

        .profile-content h2 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .profile-content p {
            color: #666;
            margin: 5px 0;
            font-size: 14px;
        }

        .profile-content .buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .profile-content button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .profile-content .logout-btn {
            background-color: #007bff;
            color: white;
        }

        .profile-content .logout-btn:hover {
            background-color: #0056b3;
        }

        .profile-content .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .profile-content .delete-btn:hover {
            background-color: #c82333;
        }

        .close-modal {
            position: absolute;
            top: 10px;
            right: 10px; 
            font-size: 24px;
            color: #333;
            cursor: pointer;
            border: none;
            background: none;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <img src=""  class="logo">
       
        <div class="profile-section">
            <div class="profile-icon" onclick="openProfileModal()">
                <img src="<?php echo htmlspecialchars($userData['image'] ?? 'https://via.placeholder.com/40'); ?>" alt="Perfil" id="profileIcon">
            </div>
        </div>
    </header>

    <!-- Modal para el perfil -->
    <div class="profile-modal" id="profileModal">
        <div class="profile-content">
            <button class="close-modal" onclick="closeProfileModal()">×</button>
            <img src="<?php echo htmlspecialchars($userData['image'] ?? 'https://via.placeholder.com/150'); ?>" alt="Foto de perfil" id="profileLargeImage">
            <h2 id="profileName"><?php echo htmlspecialchars($userData['nombre'] ?? 'Usuario Desconocido'); ?></h2>
            <p><strong>Dirección:</strong> <span id="profileAddress"><?php echo htmlspecialchars($userData['direccion'] ?? 'No especificada'); ?></span></p>
            <p><strong>Teléfono:</strong> <span id="profilePhone"><?php echo htmlspecialchars($userData['telefono'] ?? 'No especificado'); ?></span></p>
            <p><strong>Email:</strong> <span id="profileEmail"><?php echo htmlspecialchars($userData['email'] ?? 'No especificado'); ?></span></p>
            <div class="buttons">
                <button class="logout-btn" onclick="window.location.href='header.php?action=logout'">Cerrar Sesión</button>
                <button class="delete-btn" onclick="if(confirm('¿Estás seguro de que quieres borrar tu cuenta? Esta acción no se puede deshacer.')) window.location.href='header.php?action=delete'">Borrar Cuenta</button>
            </div>
        </div>
    </div>

    <script>
        // Datos del usuario desde PHP (si ya están en sesión)
        const userData = <?php echo json_encode($userData ?? null); ?>;

        // Función para abrir el modal del perfil
        function openProfileModal() {
            document.getElementById('profileModal').style.display = 'flex';
            
            if (userData) {
                document.getElementById('profileName').textContent = userData.nombre || 'Usuario Desconocido';
                document.getElementById('profileAddress').textContent = userData.direccion || 'No especificada';
                document.getElementById('profilePhone').textContent = userData.telefono || 'No especificado';
                document.getElementById('profileEmail').textContent = userData.email || 'No especificado';
                document.getElementById('profileLargeImage').src = userData.image;
                document.getElementById('profileIcon').src = userData.image;
            }
        }

        // Función para cerrar el modal del perfil
        function closeProfileModal() {
            document.getElementById('profileModal').style.display = 'none';
        }

        // Cerrar el modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('profileModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };
    </script>
</body>
</html>