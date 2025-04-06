<?php include 'include/header_login.php'; ?>
<?php
session_start();
require 'bd/conexion.php';

// Function to mask email
function maskEmail($email) {
    if (empty($email)) return '';
    
    $parts = explode('@', $email);
    if (count($parts) != 2) return $email;
    
    $name = $parts[0];
    $domain = $parts[1];
    
    $nameLength = strlen($name);
    $maskedName = substr($name, 0, min(4, $nameLength)) . str_repeat('*', max(4, $nameLength - 4));
    
    return $maskedName . '@' . $domain;
}

// Initialize variables
$message = '';
$messageType = '';
$showPasswordForm = false;
$showVerificationForm = false;
$userEmail = '';
$clientData = null;

// Handle CI verification request
if (isset($_POST['verify_ci'])) {
    $ci = $_POST['ci'];
    
    // Check if CI exists in the database
    $stmt = $pdo->prepare("SELECT * FROM Clientes WHERE identificacion = ?");
    $stmt->execute([$ci]);
    $clientData = $stmt->fetch();
    
    if ($clientData) {
        // Check if client already has a password
        if (!empty($clientData['password'])) {
            $message = "Este cliente ya tiene una cuenta activa. Por favor, inicie sesión.";
            $messageType = "error";
        } else {
            // Store client data in session
            $_SESSION['client_data'] = $clientData;
            
            // Generate a 6-digit verification code
            $verificationCode = sprintf("%06d", mt_rand(1, 999999));
            
            // Store the code in session for verification
            $_SESSION['verification_code'] = $verificationCode;
            $_SESSION['verification_email'] = $clientData['email'];
            
            // In a real application, you would send this code via email
            // For demonstration purposes, we'll just display it
            $message = "Código de verificación generado: $verificationCode (En un entorno real, este código sería enviado por correo electrónico a {$clientData['email']})";
            $messageType = "success";
            $showVerificationForm = true;
            $userEmail = $clientData['email'];
        }
    } else {
        $message = "No se encontró ningún cliente con ese número de CI.";
        $messageType = "error";
    }
}

// Handle verification code submission
if (isset($_POST['verify_code'])) {
    $submittedCode = $_POST['verification_code'];
    $storedCode = isset($_SESSION['verification_code']) ? $_SESSION['verification_code'] : '';
    $clientData = isset($_SESSION['client_data']) ? $_SESSION['client_data'] : null;
    
    if ($submittedCode === $storedCode) {
        // Code is correct, show password form
        $message = "Código verificado correctamente. Por favor establezca su contraseña.";
        $messageType = "success";
        $showPasswordForm = true;
        $showVerificationForm = false;
    } else {
        $message = "Código de verificación incorrecto. Por favor, intente nuevamente.";
        $messageType = "error";
        $showVerificationForm = true;
    }
}

// Handle password setup
if (isset($_POST['setup_password'])) {
    $clientData = isset($_SESSION['client_data']) ? $_SESSION['client_data'] : null;
    $clienteId = isset($clientData['id']) ? $clientData['id'] : '';
    $userEmail = isset($clientData['email']) ? $clientData['email'] : '';
    
    if (!$clientData) {
        $message = "Por favor, verifique su CI primero.";
        $messageType = "error";
    } else {
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if ($password === $confirmPassword) {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Update the client's password in the database
            $stmt = $pdo->prepare("UPDATE Clientes SET password = ? WHERE id = ?");
            $result = $stmt->execute([$hashedPassword, $clienteId]);
            
            if ($result) {
                // Clear session data
                unset($_SESSION['client_data']);
                
                // Redirect to login page with success message
                header('Location: index.php?registered=1');
                exit();
            } else {
                $message = "Error al actualizar la contraseña. Por favor, intente nuevamente.";
                $messageType = "error";
                $showPasswordForm = true;
            }
        } else {
            $message = "Las contraseñas no coinciden. Por favor, intente nuevamente.";
            $messageType = "error";
            $showPasswordForm = true;
        }
    }
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="css/css1.css">

<div class="signup-container">
    <h2>Activar Cuenta</h2>
    
    <?php if ($message): ?>
        <div class="<?php echo $messageType === 'error' ? 'error-message' : 'success-message'; ?>">
            <i class="bi <?php echo $messageType === 'error' ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill'; ?>"></i> 
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if (!$showVerificationForm && !$showPasswordForm): ?>
    <!-- CI Verification Form -->
    <form method="POST" id="initialForm">
        <div class="input-group-with-btn">
            <div class="input-group" style="flex: 1; margin-bottom: 0;">
                <i class="bi bi-person-badge input-icon"></i>
                <input type="text" name="ci" id="ci" placeholder="Cédula de Identidad" required>
            </div>
            <button type="submit" name="verify_ci" class="verify-btn">
                <i class="bi bi-search"></i> Verificar
            </button>
        </div>
        
        <p class="info-text">Ingrese su Cédula de Identidad para verificar sus datos y activar su cuenta.</p>
    </form>
    
    <?php elseif ($showVerificationForm): ?>
    <!-- Verification Code Form -->
    <form method="POST" id="verificationForm">
        <div class="input-group">
            <i class="bi bi-person-fill input-icon"></i>
            <input type="text" value="<?php echo htmlspecialchars($clientData['nombre']); ?>" readonly>
        </div>
        
        <div class="input-group">
            <i class="bi bi-envelope-check-fill input-icon"></i>
            <input type="email" value="<?php echo htmlspecialchars($userEmail); ?>" readonly>
        </div>
        
        <div class="verification-section">
            <div class="input-group verification-group">
                <i class="bi bi-shield-lock-fill input-icon"></i>
                <input type="text" name="verification_code" placeholder="Código de 6 dígitos" maxlength="6" pattern="[0-9]{6}" required>
                <button type="button" id="resendCode" class="resend-btn">
                    <i class="bi bi-arrow-repeat"></i> Recibir Código
                </button>
            </div>
        </div>
        
        <button type="submit" name="verify_code">
            <i class="bi bi-check-circle"></i> Verificar Código
        </button>
    </form>
    
    <?php else: ?>
    <!-- Password Setup Form -->
    <form method="POST" id="passwordForm">
        <div class="input-group">
            <i class="bi bi-person-fill input-icon"></i>
            <input type="text" value="<?php echo htmlspecialchars($clientData['nombre']); ?>" readonly>
        </div>
        
        <div class="input-group">
            <i class="bi bi-envelope-check-fill input-icon"></i>
            <input type="email" value="<?php echo htmlspecialchars($userEmail); ?>" readonly>
        </div>
        
        <div class="input-group">
            <i class="bi bi-lock-fill input-icon"></i>
            <input type="password" name="password" id="password" placeholder="Nueva Contraseña" required>
            <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
        </div>
        
        <div class="input-group">
            <i class="bi bi-lock-fill input-icon"></i>
            <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirmar Contraseña" required>
            <i class="bi bi-eye-slash password-toggle" id="toggleConfirmPassword"></i>
        </div>
        
        <button type="submit" name="setup_password">
            <i class="bi bi-check2-all"></i> Activar Cuenta
        </button>
    </form>
    <?php endif; ?>
    
    <p class="login-link">¿Ya tienes cuenta? <a href="index.php">Iniciar Sesión</a></p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                const inputs = this.querySelectorAll('input');
                inputs.forEach(input => {
                    if (!input.value.trim() && input.hasAttribute('required')) {
                        input.style.borderColor = '#e74c3c';
                        input.style.boxShadow = '0 0 0 0.2rem rgba(231, 76, 60, 0.25)';
                    } else {
                        input.style.borderColor = '#121a35';
                        input.style.boxShadow = '0 0 10px rgba(18, 26, 53, 0.2)';
                    }
                });
            });
        }
        
        // Display client data when loaded from session
        <?php if (isset($_SESSION['client_data']) && !empty($_SESSION['client_data'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const clientData = <?php echo json_encode($_SESSION['client_data']); ?>;
            if (clientData) {
                // Display client data if available
                if (document.getElementById('nombre')) {
                    document.getElementById('nombre').value = clientData.nombre;
                }
                if (document.getElementById('email')) {
                    document.getElementById('email').value = clientData.email;
                }
            }
        });
        <?php endif; ?>
        
        // Add code for resend button
        const resendBtn = document.getElementById('resendCode');
        if (resendBtn) {
            resendBtn.addEventListener('click', function() {
                // Generate a new 6-digit code
                const newCode = Math.floor(100000 + Math.random() * 900000);
                
                fetch('update_verification_code.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `code=${newCode}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Nuevo código generado: ${newCode} (En un entorno real, este código sería enviado por correo electrónico)`);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        }
    });

     const togglePassword = document.getElementById('togglePassword');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirmPassword');
            
            if (togglePassword && password) {
                togglePassword.addEventListener('click', function() {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    this.classList.toggle('bi-eye');
                    this.classList.toggle('bi-eye-slash');
                });
            }
            if (toggleConfirmPassword && confirmPassword) {
                toggleConfirmPassword.addEventListener('click', function() {
                    const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                    confirmPassword.setAttribute('type', type);
                    this.classList.toggle('bi-eye');
                    this.classList.toggle('bi-eye-slash');
                });
            }
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

    .signup-container {
        background-color: rgba(255, 255, 255, 0.95);
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        width: 100%;
        max-width: 500px;
        text-align: center;
        position: relative;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .signup-container h2 {
        margin-bottom: 30px;
        color: #121a35;
        font-size: 28px;
        font-weight: 700;
        position: relative;
        padding-bottom: 10px;
    }

    .signup-container h2:after {
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
    
    /* Modified styles for input groups with buttons */
    .input-group-with-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .input-group-with-btn input {
        flex: 1;
    }

    /* Modified verify button style */
    .verify-btn {
        width: auto !important;
        min-width: 120px;
        padding: 15px !important;
        margin-top: 0 !important; /* Changed from 5px to 0 */
        font-size: 14px !important;
        border-radius: 8px;
        flex-shrink: 0;
        height: 52px; /* Match the height of the input */
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Adjust input margins in groups with buttons */
    .input-group-with-btn .input-group input {
        margin: 0;
    }
    
    .input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #121a35;
        font-size: 18px;
    }

    .signup-container input[type="text"],
    .signup-container input[type="email"],
    .signup-container input[type="password"] {
        width: 100%;
        padding: 15px 15px 15px 45px;
        margin: 5px 0;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 16px;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
    }

    .signup-container input[type="text"]:focus,
    .signup-container input[type="email"]:focus,
    .signup-container input[type="password"]:focus {
        border-color: #121a35;
        box-shadow: 0 0 10px rgba(18, 26, 53, 0.2);
        outline: none;
        background-color: #fff;
    }

    .signup-container input[readonly] {
        background-color: #e9ecef;
        cursor: not-allowed;
    }

    .signup-container button {
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

    .verify-btn {
        width: auto !important;
        min-width: 120px;
        padding: 15px !important;
        margin-top: 5px !important;
        font-size: 14px !important;
        border-radius: 8px;
        flex-shrink: 0;
    }

    .signup-container button:before {
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

    .signup-container button:hover {
        background-color: #1a2547;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(18, 26, 53, 0.4);
    }

    .signup-container button:hover:before {
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

    .login-link {
        margin-top: 25px;
        font-size: 15px;
        color: #555;
    }

    .login-link a {
        color: #121a35;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .login-link a:hover {
        color: #1a2547;
        text-decoration: underline;
    }


    .verification-section {
        margin: 20px 0;
        padding: 15px;
        background-color: #f0f4f8;
        border-radius: 10px;
        border-left: 4px solid #121a35;
    }

    .verification-group {
        display: flex;
        align-items: center;
    }

    .resend-btn {
        width: auto !important;
        min-width: 140px;
        padding: 15px !important;
        margin-top: 5px !important;
        font-size: 14px !important;
        border-radius: 8px;
        flex-shrink: 0;
    }

    .info-text {
        font-size: 14px;
        color: #666;
        margin-top: 10px;
        text-align: left;
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
