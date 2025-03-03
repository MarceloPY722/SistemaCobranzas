<?php

session_start();

require_once 'cnx.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php'); 
    exit;
}

$claim_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($claim_id == 0) {
    echo "ID de reclamo inválido.";
    exit;
}

$stmt = $pdo->prepare("SELECT r.*, c.nombre AS cliente_nombre 
                       FROM reclamos r
                       JOIN clientes c ON r.cliente_id = c.id
                       WHERE r.id = ?");
$stmt->execute([$claim_id]);
$claim = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$claim) {
    echo "Reclamo no encontrado.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $respuesta = trim($_POST['respuesta']); // Obtener la respuesta del formulario
    $user_id = $_SESSION['user_id']; // ID del usuario que responde
    
    if (!empty($respuesta)) {

        $update_stmt = $pdo->prepare("UPDATE reclamos SET respuesta = ?, respondido_por = ? WHERE id = ?");
        $update_stmt->execute([$respuesta, $user_id, $claim_id]);
        
        echo "Respuesta enviada con éxito.";

    } else {
        echo "Por favor, ingrese una respuesta.";
    }
}
?>

    <style>
        :root {
            --primary-color: #343a40;
            --secondary-color: #495057;
            --accent-color: #fd7e14;
            --text-color: #333;
            --light-bg: #f5f7fa;
            --white: #ffffff;
            --border-radius: 4px;
            --box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--light-bg);
        }
        
        .header {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 1rem;
            box-shadow: var(--box-shadow);
        }
        
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .nav-brand {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 1.5rem;
        }
        
        .nav-links a {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
        }
        
        .nav-links a:hover {
            text-decoration: underline;
        }
        
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .page-title {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--light-bg);
        }
        
        .claim-info {
            background-color: var(--light-bg);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
        }
        
        .claim-info p {
            margin-bottom: 0.8rem;
        }
        
        .claim-label {
            font-weight: 600;
            margin-right: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-family: inherit;
            font-size: 1rem;
            resize: vertical;
            min-height: 150px;
        }
        
        textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(52, 58, 64, 0.2);
        }

        .btn {
            background-color: #343a40;
            color: var(--white);
            border: none;
            padding: 0.8rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #23272b;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        .btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
                
                .button-group {
                    display: flex;
                    gap: 10px;
                }
                
                .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: var(--border-radius);
            font-weight: 500;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        @media screen and (max-width: 768px) {
            .container {
                padding: 1rem;
                margin: 1rem;
            }
            
            .nav {
                flex-direction: column;
                text-align: center;
            }
            
            .nav-links {
                margin-top: 1rem;
                justify-content: center;
            }
            
            .nav-links li {
                margin: 0 0.75rem;
            }
        }
    </style>

    <header class="header">
        <nav class="nav">
            <div class="nav-brand">Gestionar Reclamos</div>
            <ul class="nav-links">
                <li><a href="../index.php">Inicio</a></li>
                <li><a href="../reclamos.php">Reclamos</a></li>
                <li><a href="../clientes.php">Clientes</a></li>
            </ul>
        </nav>
    </header>
    
    <div class="container">
    <h1 class="page-title">Responder al Reclamo #<?php echo $claim_id; ?></h1>
    
    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['respuesta'])): ?>
        <div class="alert alert-success">Respuesta enviada con éxito.</div>
    <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
        <div class="alert alert-danger">Por favor, ingrese una respuesta.</div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Columna izquierda: Formulario de respuesta -->
        <div class="col-md-6">
            <div class="claim-info">
                <p><span class="claim-label">Cliente:</span> <?php echo htmlspecialchars($claim['cliente_nombre']); ?></p>
                <p><span class="claim-label">Descripción:</span> <?php echo htmlspecialchars($claim['descripcion']); ?></p>
            </div>
            <form method="post">
                <div class="form-group">
                    <label for="respuesta">Respuesta:</label>
                    <textarea id="respuesta" name="respuesta" required></textarea>
                </div>
                <div class="button-group">
                    <button type="submit" class="btn">Enviar Respuesta</button>
                    <a href="../index.php" class="btn">Regresar</a>
                    <a href="cerrar_reclamo.php" class="btn" onclick="event.preventDefault(); if(confirm('¿Estás seguro de cerrar este reclamo?')) { document.getElementById('form-cerrar-reclamo').submit(); }">Cerrar Reclamo</a>
                </div>
                </form>
                <form id="form-cerrar-reclamo" method="post" action="cerrar_reclamo.php" style="display: none;">
                    <input type="hidden" name="reclamo_id" value="<?php echo $claim_id; ?>">
                </form>
        </div>
        
        <!-- Columna derecha: Chat con el cliente -->
        <div class="col-md-6">
            <h2>Chat con el cliente</h2>
            <div id="chat-historial" style="max-height: 300px; overflow-y: auto; background-color: #f5f7fa; padding: 1rem; border-radius: 4px;">
                <?php
                // Consulta para obtener mensajes del chat
                $chat_stmt = $pdo->prepare("SELECT c.*, u.nombre AS emisor_nombre 
                                            FROM chats c 
                                            JOIN usuarios u ON c.emisor_id = u.id 
                                            WHERE c.reclamo_id = ? 
                                            ORDER BY c.fecha_hora ASC");
                $chat_stmt->execute([$claim_id]);
                $messages = $chat_stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($messages as $message) {
                    $emisor = $message['tipo_emisor'] === 'administrador' ? 'Tú' : htmlspecialchars($message['emisor_nombre']);
                    echo "<p><strong>$emisor (" . date('H:i d/m/Y', strtotime($message['fecha_hora'])) . "):</strong> " . htmlspecialchars($message['contenido']) . "</p>";
                }
                ?>
            </div>
            <form method="post" action="enviar_mensaje.php" style="margin-top: 1rem;">
                <div class="form-group">
                    <textarea name="mensaje" placeholder="Escribe un mensaje..." required style="min-height: 80px;"></textarea>
                </div>
                <input type="hidden" name="reclamo_id" value="<?php echo $claim_id; ?>">
                <button type="submit" class="btn">Enviar</button>
            </form>
        </div>
    </div>
</div>