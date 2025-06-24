<?php
session_start();
require_once '../config/database.php';

// Manejar logout del panel admin
if (isset($_GET['logout_admin'])) {
    unset($_SESSION['admin_verified']);
    unset($_SESSION['admin_login_time']);
    header('Location: login.php?msg=admin_logout');
    exit();
}

// Mostrar mensaje de logout admin
$mensaje = '';
$tipo_mensaje = '';
if (isset($_GET['msg']) && $_GET['msg'] === 'admin_logout') {
    $mensaje = "Sesión del panel admin cerrada correctamente";
    $tipo_mensaje = 'success';
}

// Si ya está logueado como admin y tiene sesión admin, redirigir al dashboard
if (isset($_SESSION['user_id']) && $_SESSION['rol'] === 'administrador' && isset($_SESSION['admin_verified'])) {
    header('Location: index.php');
    exit();
}

// Si no es administrador del sitio principal, redirigir
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../login.php');
    exit();
}

// Procesar login SEGURO del panel admin
if (isset($_POST['admin_login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        // CONSULTA SEGURA - Con prepared statements
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = ? AND rol = 'administrador'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            
            // Verificar contraseña con MD5
            if (md5($password) === $usuario['password']) {
                // Crear sesión específica para el panel admin
                $_SESSION['admin_verified'] = true;
                $_SESSION['admin_login_time'] = time();
                
                // Registrar acceso al panel admin
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $log_stmt = $conn->prepare("INSERT INTO logs_actividad (usuario_id, accion, ip_origen) VALUES (?, ?, ?)");
                $accion = "Acceso al panel de administración";
                $log_stmt->bind_param("iss", $usuario['id'], $accion, $ip);
                $log_stmt->execute();
                
                header('Location: index.php');
                exit();
            } else {
                $mensaje = "Credenciales incorrectas";
                $tipo_mensaje = 'error';
            }
        } else {
            $mensaje = "Usuario no encontrado o sin permisos de administrador";
            $tipo_mensaje = 'error';
        }
        
        $stmt->close();
    } else {
        $mensaje = "Todos los campos son obligatorios";
        $tipo_mensaje = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Acceso Seguro</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/auth.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-login-body">
    <div class="admin-login-container">
        <div class="admin-login-card">
            <div class="admin-logo">🛡️</div>
            
            <h1 class="admin-title">Panel de Administración</h1>
            <p class="admin-subtitle">Acceso Seguro Requerido</p>
            
            <div class="security-notice">
                🔒 <strong>Acceso Seguro:</strong> Este panel requiere autenticación adicional.
            </div>
            
            <?php if (!empty($mensaje)): ?>
                <div class="mensaje <?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="input-group">
                    <label for="username">👤 Usuario:</label>
                    <input type="text" 
                           id="username"
                           name="username" 
                           required
                           placeholder="Ingresa tu usuario">
                </div>
                
                <div class="input-group">
                    <label for="password">🔒 Contraseña:</label>
                    <input type="password" 
                           id="password"
                           name="password" 
                           required
                           placeholder="Ingresa tu contraseña">
                </div>
                
                <button type="submit" name="admin_login" class="btn-auth btn-login">
                    🛡️ Acceder al Panel
                </button>
            </form>
        </div>
    </div>
</body>
</html>