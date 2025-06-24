<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../login.php');
    exit();
}

// Verificar si tiene acceso al panel admin
if (!isset($_SESSION['admin_verified'])) {
    header('Location: login.php');
    exit();
}

// Obtener información del administrador
$admin_id = $_SESSION['user_id'];
$admin_query = "SELECT * FROM usuarios WHERE id = '$admin_id'";
$admin_info = obtenerDatos($admin_query);
$admin = $admin_info[0] ?? null;

$mensaje = '';
$tipo_mensaje = '';

// Procesar actualización de perfil (SEGURO)
if (isset($_POST['update_profile'])) {
    $new_username = $_POST['new_username'] ?? '';
    $new_email = $_POST['new_email'] ?? '';
    $new_telefono = $_POST['new_telefono'] ?? '';
    $new_fecha_nacimiento = $_POST['new_fecha_nacimiento'] ?? '';
    
    if (!empty($new_username) && !empty($new_email)) {
        // Usar prepared statement para seguridad
        global $conn;
        $stmt = $conn->prepare("UPDATE usuarios SET username = ?, email = ?, telefono = ?, fecha_nacimiento = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $new_username, $new_email, $new_telefono, $new_fecha_nacimiento, $admin_id);
        
        if ($stmt->execute()) {
            $_SESSION['username'] = $new_username;
            $mensaje = "Perfil actualizado exitosamente";
            $tipo_mensaje = 'success';
            
            // Registrar actividad
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $log_stmt = $conn->prepare("INSERT INTO logs_actividad (usuario_id, accion, ip_origen) VALUES (?, ?, ?)");
            $accion = "Actualización de perfil desde panel admin";
            $log_stmt->bind_param("iss", $admin_id, $accion, $ip);
            $log_stmt->execute();
            
            // Actualizar datos del admin
            $admin['username'] = $new_username;
            $admin['email'] = $new_email;
            $admin['telefono'] = $new_telefono;
            $admin['fecha_nacimiento'] = $new_fecha_nacimiento;
        } else {
            $mensaje = "Error al actualizar el perfil";
            $tipo_mensaje = 'error';
        }
        $stmt->close();
    } else {
        $mensaje = "Usuario y email son obligatorios";
        $tipo_mensaje = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Panel Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/auth.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-panel">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">🛡️</div>
                <h3 class="sidebar-title">NEXUS ADMIN</h3>
                <p class="sidebar-user"><?php echo htmlspecialchars($admin['username']); ?></p>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="index.php?section=dashboard" class="nav-link">
                        <span class="nav-icon">📊</span>
                        Dashboard
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="index.php?section=users" class="nav-link">
                        <span class="nav-icon">👥</span>
                        Usuarios
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="index.php?section=transactions" class="nav-link">
                        <span class="nav-icon">💰</span>
                        Transacciones
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="index.php?section=activities" class="nav-link">
                        <span class="nav-icon">📋</span>
                        Actividades
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="settings.php" class="nav-link active">
                        <span class="nav-icon">⚙️</span>
                        Configuración
                    </a>
                </div>
                
                <div class="nav-item" style="margin-top: 20px; border-top: 1px solid #404040; padding-top: 20px;">
                    <a href="../perfil.php" class="nav-link">
                        <span class="nav-icon">👤</span>
                        Mi Perfil
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="../index.php" class="nav-link">
                        <span class="nav-icon">🏠</span>
                        Volver al Sitio
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="../logout.php" class="nav-link">
                        <span class="nav-icon">🚪</span>
                        Cerrar Sesión
                    </a>
                </div>
            </nav>
        </div>

        <!-- Contenido Principal -->
        <div class="admin-content">
            <div class="content-header">
                <h1 class="content-title">⚙️ Configuración</h1>
                <p class="content-subtitle">Editar información del perfil</p>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="mensaje <?php echo $tipo_mensaje; ?>" style="margin-bottom: 20px;">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <!-- Perfil del Administrador -->
            <div class="admin-form">
                <h3 style="color: #ff6b35; margin-bottom: 20px; font-size: 20px;">👤 Editar Perfil</h3>
                
                <!-- Información actual -->
                <div style="display: flex; align-items: center; margin-bottom: 30px; padding: 20px; background: #404040; border-radius: 10px;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #ff6b35, #e55a2b); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 20px; font-size: 40px; color: white;">
                        🛡️
                    </div>
                    <div>
                        <h4 style="color: #ff6b35; margin: 0; font-size: 18px;"><?php echo htmlspecialchars($admin['username']); ?></h4>
                        <p style="color: #ccc; margin: 5px 0;"><?php echo htmlspecialchars($admin['email'] ?? 'Sin email'); ?></p>
                        <p style="color: #ccc; margin: 5px 0;">📱 <?php echo htmlspecialchars($admin['telefono'] ?? 'Sin teléfono'); ?></p>
                        <p style="color: #4CAF50; margin: 0; font-weight: bold;">🛡️ Administrador</p>
                    </div>
                </div>

                <!-- Formulario de edición -->
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="new_username">👤 Nombre de Usuario:</label>
                            <input type="text" 
                                   id="new_username"
                                   name="new_username" 
                                   value="<?php echo htmlspecialchars($admin['username']); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_email">📧 Email:</label>
                            <input type="email" 
                                   id="new_email"
                                   name="new_email" 
                                   value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_telefono">📱 Teléfono:</label>
                            <input type="tel" 
                                   id="new_telefono"
                                   name="new_telefono" 
                                   value="<?php echo htmlspecialchars($admin['telefono'] ?? ''); ?>"
                                   placeholder="0987654321">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_fecha_nacimiento">🎂 Fecha de Nacimiento:</label>
                            <input type="date" 
                                   id="new_fecha_nacimiento"
                                   name="new_fecha_nacimiento" 
                                   value="<?php echo $admin['fecha_nacimiento'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn-admin">
                        💾 Guardar Cambios
                    </button>
                </form>
            </div>

            <!-- Información adicional -->
            <div class="admin-form">
                <h3 style="color: #ff6b35; margin-bottom: 20px; font-size: 20px;">ℹ️ Información</h3>
                
                <div class="form-grid">
                    <div style="background: #404040; padding: 15px; border-radius: 8px;">
                        <strong style="color: #ff6b35;">🆔 ID:</strong><br>
                        <span style="color: #ccc;"><?php echo $admin['id']; ?></span>
                    </div>
                    
                    <div style="background: #404040; padding: 15px; border-radius: 8px;">
                        <strong style="color: #ff6b35;">📅 Registro:</strong><br>
                        <span style="color: #ccc;"><?php echo date('d/m/Y', strtotime($admin['fecha_registro'])); ?></span>
                    </div>
                    
                    <div style="background: #404040; padding: 15px; border-radius: 8px;">
                        <strong style="color: #ff6b35;">🎂 Edad:</strong><br>
                        <span style="color: #ccc;">
                            <?php 
                            if ($admin['fecha_nacimiento']) {
                                $edad = date_diff(date_create($admin['fecha_nacimiento']), date_create('today'))->y;
                                echo $edad . ' años';
                            } else {
                                echo 'No especificado';
                            }
                            ?>
                        </span>
                    </div>
                    
                    <div style="background: #404040; padding: 15px; border-radius: 8px;">
                        <strong style="color: #ff6b35;">🔐 Rol:</strong><br>
                        <span style="color: #4CAF50;">🛡️ <?php echo htmlspecialchars($admin['rol']); ?></span>
                    </div>
                    
                    <div style="background: #404040; padding: 15px; border-radius: 8px;">
                        <strong style="color: #ff6b35;">⚡ Estado:</strong><br>
                        <span style="color: #4CAF50;">🟢 Activo</span>
                    </div>
                    
                    <div style="background: #404040; padding: 15px; border-radius: 8px;">
                        <strong style="color: #ff6b35;">🌐 IP Actual:</strong><br>
                        <span style="color: #ccc;"><?php echo $_SERVER['REMOTE_ADDR'] ?? 'N/A'; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>