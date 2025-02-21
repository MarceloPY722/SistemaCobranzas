<?php
session_start();
require 'bd/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = $_POST['usernameOrEmail'];
    $password = $_POST['password'];
    
    // Consulta para buscar por email o nombre de usuario
    $stmt = $pdo->prepare("SELECT u.*, r.nombre as rol 
                          FROM Usuarios u 
                          JOIN Roles r ON u.rol_id = r.id 
                          WHERE u.email = ? OR u.nombre = ?");
    $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_rol'] = $user['rol'];
        
        switch ($user['rol']) {
            case 'Administrador':
                header('Location: admin/index.php');
                break;
            case 'Gestor de Cobranzas':
                header('Location: gestor/index.php');
                break;
            case 'Cliente':
                header('Location: cliente/index.php');
                break;
            default:
                header('Location: index.php?error=1');
        }
        exit();
    } else {
        header('Location: index.php?error=1');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión </title>
   <link rel="stylesheet" href="css/css1.css">
</head>
<body>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <?php if (isset($_GET['error'])): ?>
            <p class="error-message">Credenciales incorrectas</p>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="usernameOrEmail" placeholder="Correo o nombre de usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>
        <p class="signup-link">¿No tienes cuenta? <a href="signup.php">Regístrate como cliente</a></p>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function() {
            const inputs = this.querySelectorAll('input');
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = 'red';
                } else {
                    input.style.borderColor = '#ddd';
                }
            });
        });
    </script>
</body>
</html>