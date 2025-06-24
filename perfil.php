<?php
session_start();
$page_title = "Mi Perfil - Nexus Play";
require_once 'config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Obtener información del usuario
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM usuarios WHERE id = '$user_id'";
$user_info = obtenerDatos($user_query);
$user = $user_info[0] ?? null;

$mensaje = '';
$tipo_mensaje = '';

// Procesar cambio de contraseña
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        // Verificar contraseña actual
        $current_password_md5 = md5($current_password);
        if ($current_password_md5 === $user['password']) {
            if ($new_password === $confirm_password) {
                // Actualizar contraseña
                $new_password_md5 = md5($new_password);
                $update_query = "UPDATE usuarios SET password = '$new_password_md5' WHERE id = '$user_id'";
                
                if (ejecutarConsulta($update_query)) {
                    $mensaje = "Contraseña actualizada exitosamente";
                    $tipo_mensaje = 'success';
                    
                    // Registrar actividad
                    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $log_query = "INSERT INTO logs_actividad (usuario_id, accion, ip_origen) VALUES ('$user_id', 'Cambio de contraseña', '$ip')";
                    ejecutarConsulta($log_query);
                } else {
                    $mensaje = "Error al actualizar la contraseña";
                    $tipo_mensaje = 'error';
                }
            } else {
                $mensaje = "Las contraseñas nuevas no coinciden";
                $tipo_mensaje = 'error';
            }
        } else {
            $mensaje = "Contraseña actual incorrecta";
            $tipo_mensaje = 'error';
        }
    } else {
        $mensaje = "Todos los campos son obligatorios";
        $tipo_mensaje = 'error';
    }
}

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="css/auth.css">
<style>
.profile-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
}

.profile-header {
    background: linear-gradient(135deg, #2d2d2d, #404040);
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
}

.user-avatar {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #ff6b35, #e55a2b);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 50px;
    color: white;
}

.user-info h1 {
    color: #ff6b35;
    margin-bottom: 10px;
}

.user-role {
    color: #4CAF50;
    font-weight: bold;
    font-size: 18px;
}

.profile-sections {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.profile-section {
    background: #2d2d2d;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
}

.section-title {
    color: #ff6b35;
    font-size: 20px;
    margin-bottom: 20px;
    border-bottom: 2px solid #ff6b35;
    padding-bottom: 10px;
}

.user-details {
    display: grid;
    gap: 15px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    background: #404040;
    border-radius: 8px;
}

.detail-label {
    color: #ccc;
    font-weight: bold;
}

.detail-value {
    color: white;
}

@media (max-width: 768px) {
    .profile-sections {
        grid-template-columns: 1fr;
    }
}
</style>

<main class="main-content">
    <div class="profile-container">
        
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <!-- Header del Perfil -->
        <div class="profile-header">
            <div class="user-avatar">
                <?php echo $_SESSION['rol'] === 'administrador' ? '🛡️' : '👤'; ?>
            </div>
            <div class="user-info">
                <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                <p class="user-role">
                    <?php echo $_SESSION['rol'] === 'administrador' ? '🛡️ Administrador' : '👤 Usuario'; ?>
                </p>
            </div>
        </div>

        <div class="profile-sections">
            <!-- Datos del Usuario -->
            <div class="profile-section">
                <h2 class="section-title">📋 Mis Datos</h2>
                <div class="user-details">
                    <div class="detail-item">
                        <span class="detail-label">👤 Usuario:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">📧 Email:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($user['email'] ?? 'No especificado'); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">📱 Teléfono:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($user['telefono'] ?? 'No especificado'); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">🎂 Fecha de Nacimiento:</span>
                        <span class="detail-value"><?php echo $user['fecha_nacimiento'] ? date('d/m/Y', strtotime($user['fecha_nacimiento'])) : 'No especificado'; ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">🔐 Rol:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($user['rol']); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">📅 Registro:</span>
                        <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($user['fecha_registro'])); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">🆔 ID:</span>
                        <span class="detail-value"><?php echo $user['id']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Cambiar Contraseña -->
            <div class="profile-section">
                <h2 class="section-title">🔒 Cambiar Contraseña</h2>
                
                <form method="POST" class="auth-form">
                    <div class="input-group">
                        <label for="current_password">Contraseña Actual:</label>
                        <input type="password" 
                               id="current_password"
                               name="current_password" 
                               required
                               placeholder="Ingresa tu contraseña actual">
                    </div>
                    
                    <div class="input-group">
                        <label for="new_password">Nueva Contraseña:</label>
                        <input type="password" 
                               id="new_password"
                               name="new_password" 
                               required
                               placeholder="Ingresa la nueva contraseña">
                    </div>
                    
                    <div class="input-group">
                        <label for="confirm_password">Confirmar Contraseña:</label>
                        <input type="password" 
                               id="confirm_password"
                               name="confirm_password" 
                               required
                               placeholder="Confirma la nueva contraseña">
                    </div>
                    
                    <button type="submit" name="change_password" class="btn-auth btn-register">
                        🔒 Cambiar Contraseña
                    </button>
                </form>
            </div>
        </div>

        <!-- Enlaces adicionales -->
        <div style="text-align: center; margin-top: 30px;">
            <?php if ($_SESSION['rol'] === 'administrador'): ?>
                <a href="admin/login.php" target="_blank" style="background: #ff6b35; color: white; padding: 15px 30px; border-radius: 10px; text-decoration: none; margin: 10px; display: inline-block;">
                    🛡️ Panel de Administrador
                </a>
            <?php endif; ?>
            <a href="index.php" style="background: #4CAF50; color: white; padding: 15px 30px; border-radius: 10px; text-decoration: none; margin: 10px; display: inline-block;">
                🏠 Volver al Inicio
            </a>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>