<?php
session_start();
$page_title = "Registrarse - Nexus Play";
$simple_header = true; // Activar header simplificado
$auth_page = true; // Cargar CSS de auth
require_once 'config/database.php';
require_once 'includes/header.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar registro (VULNERABLE)
if (isset($_POST['register'])) {
    $reg_username = $_POST['reg_username'] ?? '';
    $reg_email = $_POST['reg_email'] ?? '';
    $reg_password = $_POST['reg_password'] ?? '';
    
    if (!empty($reg_username) && !empty($reg_email) && !empty($reg_password)) {
        // Verificar si el usuario ya existe (vulnerable)
        $check_query = "SELECT * FROM usuarios WHERE username = '$reg_username' OR email = '$reg_email'";
        echo "<!-- DEBUG Check User Query: $check_query -->";
        $usuario_existe = obtenerDatos($check_query);
        
        if (empty($usuario_existe)) {
            // Insertar nuevo usuario (VULNERABLE - sin hash de contraseña)
            $insert_query = "INSERT INTO usuarios (username, email, password, rol) VALUES ('$reg_username', '$reg_email', '$reg_password', 'usuario')";
            echo "<!-- DEBUG Insert Query: $insert_query -->";
            
            if (ejecutarConsulta($insert_query)) {
                $mensaje = "Usuario registrado exitosamente. Ahora puedes iniciar sesión.";
                $tipo_mensaje = 'success';
                
                // Registrar actividad
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $log_query = "INSERT INTO logs_actividad (usuario_id, accion, ip_origen) VALUES (LAST_INSERT_ID(), 'Registro exitoso', '$ip')";
                ejecutarConsulta($log_query);
                
                // Redireccionar después de 2 segundos
                echo "<script>setTimeout(function(){ window.location.href = 'login.php'; }, 2000);</script>";
            } else {
                $mensaje = "Error al registrar usuario";
                $tipo_mensaje = 'error';
            }
        } else {
            $mensaje = "El usuario o email ya existe";
            $tipo_mensaje = 'error';
        }
    } else {
        $mensaje = "Todos los campos son obligatorios";
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

        <!-- Formulario de Registro -->
        <div class="auth-form-container">
            <h2 class="form-title">📝 Crear Cuenta</h2>
            
            <form method="POST" class="auth-form">
                <div class="input-group">
                    <label for="reg_username">Usuario:</label>
                    <input type="text" 
                           id="reg_username"
                           name="reg_username" 
                           required
                           placeholder="Elige un nombre de usuario">
                </div>
                
                <div class="input-group">
                    <label for="reg_email">Email:</label>
                    <input type="email" 
                           id="reg_email"
                           name="reg_email" 
                           required
                           placeholder="tu@email.com">
                </div>
                
                <div class="input-group">
                    <label for="reg_password">Contraseña:</label>
                    <input type="password" 
                           id="reg_password"
                           name="reg_password" 
                           required
                           placeholder="Ingresa una contraseña">
                </div>
                
                <button type="submit" name="register" class="btn-auth btn-register">
                    Registrarse
                </button>
            </form>
            
            <div class="form-footer">
                <p>¿Ya tienes cuenta?</p>
                <a href="login.php" class="btn-link">Iniciar Sesión</a>
            </div>
        </div>

        <!-- Información de Vulnerabilidades -->
        <div class="vuln-info">
            <h3>🔐 Información de Seguridad</h3>
            <div class="vuln-grid">
                <div class="vuln-section">
                    <h4>⚠️ Vulnerabilidades en Registro:</h4>
                    <ul>
                        <li>Inyección SQL en campos de entrada</li>
                        <li>Sin validación de formato de email</li>
                        <li>Sin sanitización de datos</li>
                        <li>Contraseñas almacenadas en texto plano</li>
                        <li>Sin verificación de email</li>
                        <li>Sin validación de longitud de contraseña</li>
                    </ul>
                </div>
                <div class="vuln-section">
                    <h4>🎯 Ejemplos de Payload para Registro:</h4>
                    <ul>
                        <li><code>'; INSERT INTO usuarios (username, rol) VALUES ('hacker', 'administrador')-- </code></li>
                        <li><code>' UNION SELECT * FROM usuarios-- </code></li>
                        <li><code>admin'; UPDATE usuarios SET rol='administrador' WHERE username='admin'-- </code></li>
                        <li><code>'; DROP TABLE logs_actividad-- </code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>