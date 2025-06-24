<?php
session_start();

// Manejar logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php?msg=logout");
    exit();
}

$page_title = "Iniciar Sesión - Nexus Play";
$simple_header = true; // Activar header simplificado
$auth_page = true; // Cargar CSS de auth
require_once 'config/database.php';
require_once 'includes/header.php';

$mensaje = '';
$tipo_mensaje = '';

// Mostrar mensaje de logout
if (isset($_GET['msg']) && $_GET['msg'] === 'logout') {
    $mensaje = "Sesión cerrada correctamente";
    $tipo_mensaje = 'success';
}

// Procesar login (VULNERABLE)
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        // HASHEAR LA CONTRASEÑA CON MD5 PARA COMPARAR
        $password_md5 = md5($password);
        
        // CONSULTA VULNERABLE - Sin prepared statements pero con MD5
        $query = "SELECT * FROM usuarios WHERE username = '$username' AND password = '$password_md5'";
        
        $resultado = obtenerDatos($query);
        
        if (!empty($resultado)) {
            $usuario = $resultado[0];
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['rol'] = $usuario['rol'];
            
            $mensaje = "¡Bienvenido " . htmlspecialchars($usuario['username']) . "!";
            $tipo_mensaje = 'success';
            
            // Registrar actividad (también vulnerable)
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $log_query = "INSERT INTO logs_actividad (usuario_id, accion, ip_origen) VALUES ('{$usuario['id']}', 'Login exitoso', '$ip')";
            ejecutarConsulta($log_query);
            
            // Redireccionar según el rol
            if ($usuario['rol'] === 'administrador') {
                echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 2000);</script>";
            } else {
                echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 2000);</script>";
            }
        } else {
            $mensaje = "Credenciales incorrectas";
            $tipo_mensaje = 'error';
            
            // Debug: mostrar la consulta para ver qué está pasando
            echo "<!-- DEBUG: Query ejecutada: $query -->";
        }
    } else {
        $mensaje = "El usuario y contraseña son obligatorios";
        $tipo_mensaje = 'error';
    }
}
?>

<main class="main-content">
    <div class="auth-container">
        
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de Login -->
        <div class="auth-form-container">
            <h2 class="form-title">👤 Iniciar Sesión</h2>
            
            <form method="POST" class="auth-form">
                <div class="input-group">
                    <label for="username">Usuario:</label>
                    <input type="text" 
                           id="username"
                           name="username" 
                           placeholder="Ingresa tu usuario">
                </div>
                
                <div class="input-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" 
                           id="password"
                           name="password" 
                           placeholder="Ingresa tu contraseña">
                </div>
                
                <button type="submit" name="login" class="btn-auth btn-login">
                    Iniciar Sesión
                </button>
            </form>
            
            <div class="form-footer">
                <p>¿No tienes cuenta?</p>
                <a href="register.php" class="btn-link">Crear Cuenta</a>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>