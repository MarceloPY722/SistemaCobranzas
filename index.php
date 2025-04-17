<?php include 'include/header_login.php'; ?>
<?php
session_start();
require 'bd/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = $_POST['usernameOrEmail'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT u.*, r.nombre as rol 
                          FROM Usuarios u 
                          JOIN Roles r ON u.rol_id = r.id 
                          WHERE u.email = ? OR u.nombre = ?");
    $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['rol_id'] = $user['rol_id']; 
        $_SESSION['role'] = $user['rol'];
        
        switch ($user['rol']) {
            case 'Administrador':
                header('Location: admin/index.php');
                break;
            case 'Gestor de Cobranzas':
                header('Location: gestor/index.php');
                break;
            default:
                header('Location: index.php?error=1');
        }
        exit();
    } else {
        $stmt = $pdo->prepare("SELECT * FROM Clientes WHERE email = ? OR identificacion = ?");
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $cliente = $stmt->fetch();
        
        if ($cliente) {
            
            $stmt = $pdo->prepare("SELECT password FROM Clientes WHERE id = ?");
            $stmt->execute([$cliente['id']]);
            $clienteAuth = $stmt->fetch();
            
            if (isset($clienteAuth['password']) && password_verify($password, $clienteAuth['password'])) {
                $_SESSION['user_id'] = $cliente['id'];
                $_SESSION['cliente_id'] = $cliente['id']; 
                $_SESSION['role'] = 'Cliente';
                $_SESSION['nombre'] = $cliente['nombre'];
                $_SESSION['email'] = $cliente['email'];
                
                header('Location: cliente/index.php');
                exit();
            }
        }
        
        header('Location: index.php?error=1');
        exit();
    }
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="css/css1.css">

<div class="login-container">
    <h2>Iniciar Sesión</h2>
    <?php if (isset($_GET['error'])): ?>
        <div class="error-message">
            <i class="bi bi-exclamation-triangle-fill"></i> Credenciales incorrectas. Por favor, intente nuevamente.
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['logout'])): ?>
        <div class="success-message">
            <i class="bi bi-check-circle-fill"></i> Ha cerrado sesión correctamente.
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['registered'])): ?>
        <div class="success-message">
            <i class="bi bi-check-circle-fill"></i> Registro exitoso. Ahora puede iniciar sesión.
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="input-group">
            <i class="bi bi-person-fill input-icon"></i>
            <input type="text" name="usernameOrEmail" placeholder="Correo,o Cedula de Identidad" required>
        </div>
        <div class="input-group">
            <i class="bi bi-lock-fill input-icon"></i>
            <input type="password" name="password" id="password" placeholder="Contraseña" required>
            <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
        </div>
        <button type="submit">
            <i class="bi bi-box-arrow-in-right"></i> Ingresar
        </button>
    </form>
    <p class="signup-link">¿No tienes cuenta? <a href="signup.php">Solicitar Cuenta!!</a></p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        form.addEventListener('submit', function() {
            const inputs = this.querySelectorAll('input');
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = '#e74c3c';
                    input.style.boxShadow = '0 0 0 0.2rem rgba(231, 76, 60, 0.25)';
                } else {
                    input.style.borderColor = '#121a35';
                    input.style.boxShadow = '0 0 10px rgba(18, 26, 53, 0.2)';
                }
            });
        });
        
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        if (togglePassword && password) {
            togglePassword.addEventListener('click', function() {
              
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                this.classList.toggle('bi-eye');
                this.classList.toggle('bi-eye-slash');
            });
        }
    });
</script>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background-color: #121a35;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background-image: url('img/pattern.png');
        background-size: cover;
        background-blend-mode: overlay;
    }

    .login-container {
        background-color: rgba(255, 255, 255, 0.95);
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        width: 100%;
        max-width: 400px;
        text-align: center;
        position: relative;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .login-container h2 {
        margin-bottom: 30px;
        color: #121a35;
        font-size: 28px;
        font-weight: 700;
        position: relative;
        padding-bottom: 10px;
    }

    .login-container h2:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 50px;
        height: 3px;
        background-color: #121a35;
    }

    .input-group {
        position: relative;
        margin-bottom: 15px;
    }

    .input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #121a35;
        font-size: 18px;
    }

    .login-container input[type="text"],
    .login-container input[type="email"],
    .login-container input[type="password"] {
        width: 100%;
        padding: 15px 15px 15px 45px;
        margin: 5px 0;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 16px;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
    }

    .login-container input[type="text"]:focus,
    .login-container input[type="email"]:focus,
    .login-container input[type="password"]:focus {
        border-color: #121a35;
        box-shadow: 0 0 10px rgba(18, 26, 53, 0.2);
        outline: none;
        background-color: #fff;
    }

    .login-container button {
        width: 100%;
        padding: 15px;
        margin-top: 25px;
        background-color: #121a35;
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }

    .login-container button:before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: all 0.6s ease;
        z-index: -1;
    }

    .login-container button:hover {
        background-color: #1a2547;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(18, 26, 53, 0.4);
    }

    .login-container button:hover:before {
        left: 100%;
    }

    .error-message {
        color: #e74c3c;
        margin-bottom: 20px;
        font-size: 14px;
        background-color: #fdecea;
        padding: 10px;
        border-radius: 8px;
        border-left: 4px solid #e74c3c;
        text-align: left;
    }

    .success-message {
        color: #2ecc71;
        margin-bottom: 20px;
        font-size: 14px;
        background-color: #e8f8f5;
        padding: 10px;
        border-radius: 8px;
        border-left: 4px solid #2ecc71;
        text-align: left;
    }

    .signup-link {
        margin-top: 25px;
        font-size: 15px;
        color: #555;
    }

    .signup-link a {
        color: #121a35;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .signup-link a:hover {
        color: #1a2547;
        text-decoration: underline;
    }
    
    /* Password toggle icon */
    .password-toggle {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #121a35;
        font-size: 18px;
        cursor: pointer;
        z-index: 10;
    }
    
    .password-toggle:hover {
        color: #1a2547;
    }
</style>