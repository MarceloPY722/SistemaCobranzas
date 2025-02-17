<?php
session_start();
require 'bd/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos del formulario
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $identificacion = $_POST['identificacion'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];

    try {
        $pdo->beginTransaction();

        // Insertar usuario (siempre como Cliente - rol_id = 3)
        $stmt = $pdo->prepare("INSERT INTO Usuarios (rol_id, nombre, email, password) VALUES (3, ?, ?, ?)");
        $stmt->execute([$nombre, $email, $password]);
        $usuario_id = $pdo->lastInsertId();

        // Insertar cliente con información completa
        $stmt = $pdo->prepare("INSERT INTO Clientes (usuario_id, nombre, identificacion, direccion, telefono, email) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$usuario_id, $nombre, $identificacion, $direccion, $telefono, $email]);

        $pdo->commit();
        header('Location: index.php?success=1');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->errorInfo[1] == 1062) {
            header('Location: signup.php?error=email_duplicado');
        } else {
            header('Location: signup.php?error=general');
        }
        exit();
    }
}
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro Cliente</title>
    <link rel="stylesheet" href="css/css2.css">
</head>
<body>
    <div class="register-container">
        <h2>Registro de Cliente</h2>
        <?php if (isset($_GET['error'])): ?>
            <?php if ($_GET['error'] === 'email_duplicado'): ?>
                <p class="error-message">El correo ya está registrado</p>
            <?php else: ?>
                <p class="error-message">Error en el registro</p>
            <?php endif; ?>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="nombre" placeholder="Nombre completo" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <input type="text" name="identificacion" placeholder="DNI/Cédula" required>
            <input type="text" name="direccion" placeholder="Dirección" required>
            <input type="text" name="telefono" placeholder="Teléfono" required>
            <button type="submit">Registrarse</button>
        </form>
        <p class="login-link">¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
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